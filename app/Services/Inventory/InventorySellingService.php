<?php

namespace App\Services\Inventory;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventorySellingService
{
    /**
     * Check total available quantity in base units.
     */
    public function availableBaseUnits(int $pharmacyId, int $branchId, int $productId): int
    {
        return (int) Inventory::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->where('status', 'available')
            ->where('is_active', true)
            ->where('available_quantity_base_units', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', now()->toDateString());
            })
            ->sum('available_quantity_base_units');
    }

    /**
     * Convert selected sale unit quantity to base units.
     */
    public function calculateRequiredBaseUnits(ProductUnit $productUnit, int $quantity): int
    {
        return max(1, (int) $productUnit->quantity_in_base_units) * $quantity;
    }

    /**
     * Preview FEFO allocations without reducing inventory.
     */
    public function previewAllocations(
        int $pharmacyId,
        int $branchId,
        Product $product,
        ProductUnit $productUnit,
        int $quantity
    ): array {
        $requiredBaseUnits = $this->calculateRequiredBaseUnits($productUnit, $quantity);
        $availableBaseUnits = $this->availableBaseUnits($pharmacyId, $branchId, $product->id);

        if ($availableBaseUnits < $requiredBaseUnits) {
            throw new RuntimeException(
                'Not enough inventory. Available: ' . $availableBaseUnits . ', required: ' . $requiredBaseUnits . '.'
            );
        }

        $inventories = $this->availableInventories($pharmacyId, $branchId, $product->id);

        $remaining = $requiredBaseUnits;
        $allocations = [];
        $totalCost = 0;

        foreach ($inventories as $inventory) {
            if ($remaining <= 0) {
                break;
            }

            $take = min((int) $inventory->available_quantity_base_units, $remaining);

            $cost = $take * (float) $inventory->unit_cost_base;

            $allocations[] = [
                'inventory_id' => $inventory->id,
                'batch_no' => $inventory->batch_no,
                'expiry_date' => $inventory->expiry_date?->toDateString(),
                'quantity_base_units' => $take,
                'unit_cost_base' => (float) $inventory->unit_cost_base,
                'cost' => $cost,
            ];

            $totalCost += $cost;
            $remaining -= $take;
        }

        return [
            'required_base_units' => $requiredBaseUnits,
            'available_base_units' => $availableBaseUnits,
            'allocations' => $allocations,
            'total_cost' => $totalCost,
            'cost_per_base_unit' => $requiredBaseUnits > 0 ? $totalCost / $requiredBaseUnits : 0,
        ];
    }

    /**
     * Reduce inventory using FEFO and create movements.
     */
    public function sell(
        Sale $sale,
        Product $product,
        ProductUnit $productUnit,
        int $quantity
    ): array {
        return DB::transaction(function () use ($sale, $product, $productUnit, $quantity) {
            $requiredBaseUnits = $this->calculateRequiredBaseUnits($productUnit, $quantity);
            $availableBaseUnits = $this->availableBaseUnits($sale->pharmacy_id, $sale->branch_id, $product->id);

            if ($availableBaseUnits < $requiredBaseUnits) {
                throw new RuntimeException(
                    'Not enough inventory for ' . $product->name . '. Available: ' . $availableBaseUnits . ', required: ' . $requiredBaseUnits . '.'
                );
            }

            $inventories = $this->availableInventories($sale->pharmacy_id, $sale->branch_id, $product->id, lock: true);

            $remaining = $requiredBaseUnits;
            $allocations = [];
            $totalCost = 0;

            foreach ($inventories as $inventory) {
                if ($remaining <= 0) {
                    break;
                }

                $before = (int) $inventory->available_quantity_base_units;
                $take = min($before, $remaining);
                $after = $before - $take;

                $cost = $take * (float) $inventory->unit_cost_base;

                $inventory->update([
                    'available_quantity_base_units' => $after,
                    'status' => $after > 0 ? 'available' : 'depleted',
                ]);

                InventoryMovement::query()->create([
                    'pharmacy_id' => $sale->pharmacy_id,
                    'branch_id' => $sale->branch_id,
                    'product_id' => $product->id,
                    'inventory_id' => $inventory->id,
                    'movement_no' => $this->generateMovementNumber(),
                    'movement_type' => 'sale_out',
                    'direction' => 'out',
                    'quantity_base_units' => $take,
                    'balance_before_base_units' => $before,
                    'balance_after_base_units' => $after,
                    'source_type' => Sale::class,
                    'source_id' => $sale->id,
                    'reason' => 'Sale completed: ' . $sale->sale_no,
                    'created_by' => $sale->created_by,
                    'moved_at' => now(),
                ]);

                $allocations[] = [
                    'inventory_id' => $inventory->id,
                    'batch_no' => $inventory->batch_no,
                    'expiry_date' => $inventory->expiry_date?->toDateString(),
                    'quantity_base_units' => $take,
                    'unit_cost_base' => (float) $inventory->unit_cost_base,
                    'cost' => $cost,
                ];

                $totalCost += $cost;
                $remaining -= $take;
            }

            if ($remaining > 0) {
                throw new RuntimeException('Inventory allocation failed. Some required quantity was not allocated.');
            }

            return [
                'required_base_units' => $requiredBaseUnits,
                'allocations' => $allocations,
                'total_cost' => $totalCost,
                'cost_per_base_unit' => $requiredBaseUnits > 0 ? $totalCost / $requiredBaseUnits : 0,
            ];
        });
    }

    private function availableInventories(
        int $pharmacyId,
        int $branchId,
        int $productId,
        bool $lock = false
    ): Collection {
        $query = Inventory::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->where('status', 'available')
            ->where('is_active', true)
            ->where('available_quantity_base_units', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', now()->toDateString());
            })
            ->orderByRaw("CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END")
            ->orderBy('expiry_date')
            ->orderBy('id');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    private function generateMovementNumber(): string
    {
        do {
            $movementNo = 'MOV-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
        } while (
            InventoryMovement::query()
                ->where('movement_no', $movementNo)
                ->exists()
        );

        return $movementNo;
    }
}