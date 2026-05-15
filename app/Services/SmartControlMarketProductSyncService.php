<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmartControlMarketProductSyncService
{
    public function syncProductForBranch(int $branchId, int $productId): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        $branch = Branch::query()->find($branchId);

        if (! $branch) {
            return false;
        }

        return $this->send($branch->id, $this->productPayload($branch, $productId));
    }

    public function syncBranch(Branch $branch): int
    {
        if (! $this->enabled()) {
            return 0;
        }

        $payload = $this->productsPayload($branch);

        if ($payload === []) {
            return 0;
        }

        return $this->send($branch->id, $payload) ? count($payload) : 0;
    }

    public function syncAllBranches(): int
    {
        $total = 0;

        Branch::query()
            ->where('is_active', true)
            ->chunkById(20, function ($branches) use (&$total) {
                foreach ($branches as $branch) {
                    $total += $this->syncBranch($branch);
                }
            });

        return $total;
    }

    private function enabled(): bool
    {
        return (bool) config('services.smart_control.enabled')
            && trim((string) config('services.smart_control.url')) !== ''
            && trim((string) config('services.smart_control.instance_id')) !== '';
    }

    private function productsPayload(Branch $branch): array
    {
        $productIds = Inventory::query()
            ->where('pharmacy_id', $branch->pharmacy_id)
            ->where('branch_id', $branch->id)
            ->distinct()
            ->pluck('product_id')
            ->filter()
            ->values();

        $payload = [];

        foreach ($productIds as $productId) {
            $payload = array_merge(
                $payload,
                $this->productPayload($branch, (int) $productId)
            );
        }

        return $payload;
    }

    private function productPayload(Branch $branch, int $productId): array
    {
        $product = Product::query()
            ->with([
                'category',
                'baseUnit',
                'productUnits.unit',
            ])
            ->where('pharmacy_id', $branch->pharmacy_id)
            ->find($productId);

        if (! $product || ! $product->is_active) {
            return [];
        }

        $availableBaseUnits = (float) Inventory::query()
            ->where('pharmacy_id', $branch->pharmacy_id)
            ->where('branch_id', $branch->id)
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->where('status', 'available')
            ->where('available_quantity_base_units', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', now()->toDateString());
            })
            ->sum('available_quantity_base_units');

        if ($availableBaseUnits <= 0) {
            return [];
        }

        $units = $product->productUnits
            ->where('is_active', true)
            ->where('can_sell_wholesale', true)
            ->sortBy('quantity_in_base_units')
            ->values();

        if ($units->isEmpty()) {
            return [];
        }

        return $units
            ->map(function ($productUnit) use ($branch, $product, $availableBaseUnits) {
                $quantityInBaseUnits = max(1, (int) $productUnit->quantity_in_base_units);

                $wholesalePrice = $this->calculatedPriceForUnit(
                    productId: (int) $product->id,
                    quantityInBaseUnits: $quantityInBaseUnits,
                    priceType: 'wholesale'
                );

                if ($wholesalePrice <= 0) {
                    return null;
                }

                return [
                    'product_id' => $product->id,
                    'product_unit_id' => $productUnit->id,

                    'name' => $product->name,
                    'code' => $product->code,
                    'generic_name' => $product->generic_name,
                    'brand' => $product->brand,
                    'strength' => $product->strength,
                    'category_name' => $product->category?->name,

                    'unit_name' => $productUnit->unit?->name ?? $product->baseUnit?->name,
                    'quantity_in_base_units' => $quantityInBaseUnits,

                    // IMPORTANT:
                    // Product prices are stored only on the base unit.
                    // Non-base package prices are calculated as:
                    // base unit price × quantity_in_base_units.
                    'wholesale_price' => $wholesalePrice,

                    // IMPORTANT:
                    // This is always base unit stock.
                    'available_stock' => $availableBaseUnits,

                    'min_order_quantity' => $productUnit->min_order_quantity ?? null,
                    'min_order_amount' => $productUnit->min_order_amount ?? null,

                    'requires_prescription' => (bool) $product->requires_prescription,
                    'can_sell_wholesale' => true,
                    'is_base' => (bool) $productUnit->is_base,
                    'is_active' => true,

                    'meta' => [
                        'branch_id' => $branch->id,
                        'base_unit_id' => $product->base_unit_id,
                        'unit_id' => $productUnit->unit_id,
                        'available_unit_quantity' => (int) floor($availableBaseUnits / $quantityInBaseUnits),
                        'price_source' => $productUnit->is_base ? 'stored_base_price' : 'calculated_from_base_price',
                        'price_formula' => 'base_price * quantity_in_base_units',
                        'synced_from' => 'inventory_event',
                    ],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function calculatedPriceForUnit(
        int $productId,
        int $quantityInBaseUnits,
        string $priceType = 'wholesale'
    ): float {
        $basePrice = ProductPrice::query()
            ->where('product_id', $productId)
            ->where('price_type', $priceType)
            ->where('is_active', true)
            ->whereHas('productUnit', function ($query) {
                $query->where('is_base', true);
            })
            ->value('price');

        if ($basePrice === null) {
            $basePrice = ProductPrice::query()
                ->where('product_id', $productId)
                ->where('is_active', true)
                ->whereHas('productUnit', function ($query) {
                    $query->where('is_base', true);
                })
                ->orderByRaw("
                    CASE
                        WHEN price_type = 'wholesale' THEN 0
                        ELSE 1
                    END
                ")
                ->value('price');
        }

        if ($basePrice === null) {
            return 0;
        }

        return round((float) $basePrice * max(1, $quantityInBaseUnits), 2);
    }

    private function send(?int $branchId, array $products): bool
    {
        if ($products === []) {
            return true;
        }

        $baseUrl = rtrim((string) config('services.smart_control.url'), '/');

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'X-Project' => config('services.smart_control.project'),
                'X-License-Key' => config('services.smart_control.license_key'),
                'X-Client-Key' => config('services.smart_control.client_key'),
                'X-Client-Secret' => config('services.smart_control.client_secret'),
                'X-Instance-Id' => config('services.smart_control.instance_id'),
            ])->timeout(30)->post($baseUrl . '/api/sync/market-supplier-products', [
                'instance_id' => config('services.smart_control.instance_id'),
                'branch_id' => $branchId,
                'products' => $products,
            ]);

            if (! $response->successful()) {
                Log::warning('Smart Control market products sync failed', [
                    'branch_id' => $branchId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Smart Control market products sync exception', [
                'branch_id' => $branchId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}