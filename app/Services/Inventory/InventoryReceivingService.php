<?php

namespace App\Services\Inventory;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class InventoryReceivingService
{
    public function receivePurchase(Purchase $purchase, User $user): Purchase
    {
        if (! $purchase->isDraft()) {
            throw new RuntimeException('Only draft purchases can be received.');
        }

        $purchase->load([
            'items.product',
            'items.productUnit',
            'branch',
            'pharmacy',
        ]);

        if ($purchase->items->isEmpty()) {
            throw new RuntimeException('Purchase cannot be received because it has no items.');
        }

        return DB::transaction(function () use ($purchase, $user) {
            foreach ($purchase->items as $item) {
                $this->receivePurchaseItem($purchase, $item, $user);
            }

            $purchase->update([
                'status' => 'received',
                'received_date' => now()->toDateString(),
                'received_by' => $user->id,
                'received_at' => now(),
            ]);

            activity()
                ->useLog('inventory')
                ->event('receive_purchase')
                ->performedOn($purchase)
                ->causedBy($user)
                ->withProperties([
                    'purchase_id' => $purchase->id,
                    'purchase_no' => $purchase->purchase_no,
                    'branch_id' => $purchase->branch_id,
                    'supplier_id' => $purchase->supplier_id,
                    'items_count' => $purchase->items->count(),
                ])
                ->log('Purchase received into inventory');

            return $purchase->fresh([
                'items',
                'inventories',
                'movements',
            ]);
        });
    }

    private function receivePurchaseItem(Purchase $purchase, PurchaseItem $item, User $user): Inventory
    {
        if (! $item->product) {
            throw new RuntimeException("Purchase item {$item->id} has no product.");
        }

        if (! $item->productUnit) {
            throw new RuntimeException("Purchase item {$item->id} has no product unit.");
        }

        $quantityInBaseUnits = (int) $item->quantity_in_base_units;
        $totalBaseUnits = (int) $item->total_base_units;

        if ($quantityInBaseUnits <= 0) {
            $quantityInBaseUnits = max(1, (int) $item->productUnit->quantity_in_base_units);
        }

        if ($totalBaseUnits <= 0) {
            $totalBaseUnits = (int) $item->quantity * $quantityInBaseUnits;
        }

        if ($totalBaseUnits <= 0) {
            throw new RuntimeException("Purchase item {$item->id} has invalid quantity.");
        }

        $unitCostBase = $totalBaseUnits > 0
            ? ((float) $item->line_total / $totalBaseUnits)
            : 0;

        $totalCost = (float) $item->line_total;

        $inventory = Inventory::query()->create([
            'pharmacy_id' => $purchase->pharmacy_id,
            'branch_id' => $purchase->branch_id,
            'product_id' => $item->product_id,
            'purchase_id' => $purchase->id,
            'purchase_item_id' => $item->id,
            'batch_no' => $item->batch_no ?: $this->generateBatchNumber($purchase, $item),
            'expiry_date' => $item->expiry_date,
            'received_quantity_base_units' => $totalBaseUnits,
            'available_quantity_base_units' => $totalBaseUnits,
            'unit_cost_base' => $unitCostBase,
            'total_cost' => $totalCost,
            'status' => 'available',
            'is_active' => true,
        ]);

        InventoryMovement::query()->create([
            'pharmacy_id' => $purchase->pharmacy_id,
            'branch_id' => $purchase->branch_id,
            'product_id' => $item->product_id,
            'inventory_id' => $inventory->id,
            'movement_no' => $this->generateMovementNumber(),
            'movement_type' => 'purchase_receive',
            'direction' => 'in',
            'quantity_base_units' => $totalBaseUnits,
            'balance_before_base_units' => 0,
            'balance_after_base_units' => $totalBaseUnits,
            'source_type' => Purchase::class,
            'source_id' => $purchase->id,
            'reason' => 'Purchase received: ' . $purchase->purchase_no,
            'created_by' => $user->id,
            'moved_at' => now(),
        ]);

        return $inventory;
    }

    private function generateBatchNumber(Purchase $purchase, PurchaseItem $item): string
    {
        return 'BATCH-' .
            $purchase->id .
            '-' .
            $item->id .
            '-' .
            strtoupper(Str::random(4));
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