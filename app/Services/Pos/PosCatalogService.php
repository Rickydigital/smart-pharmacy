<?php

namespace App\Services\Pos;

use App\Models\Branch;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\User;
use App\Services\Inventory\InventorySellingService;
use Illuminate\Support\Collection;

class PosCatalogService
{
    public function __construct(
        private InventorySellingService $inventorySellingService
    ) {
    }

    public function branches(Pharmacy $pharmacy): Collection
    {
        return Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();
    }

    public function defaultBranch(Collection $branches, ?User $user): ?Branch
    {
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        if ($isAdminOrOwner) {
            return $branches->firstWhere('is_main', true) ?: $branches->first();
        }

        return $branches->firstWhere('id', $user?->branch_id)
            ?: $branches->firstWhere('is_main', true)
            ?: $branches->first();
    }

    public function searchProducts(
        Pharmacy $pharmacy,
        int $branchId,
        string $queryText = '',
        string $saleType = 'retail',
        int $limit = 30,
    ): Collection {
        $products = Product::query()
            ->with([
                'productType',
                'category',
                'baseUnit',
                'units.unit',
                'units.prices',
            ])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->whereHas('inventories', function ($inventoryQuery) use ($pharmacy, $branchId) {
                $inventoryQuery
                    ->where('pharmacy_id', $pharmacy->id)
                    ->where('branch_id', $branchId)
                    ->where('status', 'available')
                    ->where('is_active', true)
                    ->where('available_quantity_base_units', '>', 0)
                    ->where(function ($expiryQuery) {
                        $expiryQuery
                            ->whereNull('expiry_date')
                            ->orWhereDate('expiry_date', '>=', now()->toDateString());
                    });
            })
            ->when(trim($queryText) !== '', function ($query) use ($queryText) {
                $query->where(function ($q) use ($queryText) {
                    $q->where('name', 'like', "%{$queryText}%")
                        ->orWhere('code', 'like', "%{$queryText}%")
                        ->orWhere('barcode', 'like', "%{$queryText}%")
                        ->orWhere('generic_name', 'like', "%{$queryText}%")
                        ->orWhere('brand', 'like', "%{$queryText}%")
                        ->orWhere('strength', 'like', "%{$queryText}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($queryText) {
                            $categoryQuery
                                ->where('name', 'like', "%{$queryText}%")
                                ->orWhere('code', 'like', "%{$queryText}%");
                        })
                        ->orWhereHas('productType', function ($typeQuery) use ($queryText) {
                            $typeQuery
                                ->where('name', 'like', "%{$queryText}%")
                                ->orWhere('code', 'like', "%{$queryText}%");
                        });
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return $products->map(function (Product $product) use ($pharmacy, $branchId, $saleType) {
            $availableBaseUnits = $this->inventorySellingService->availableBaseUnits(
                pharmacyId: $pharmacy->id,
                branchId: $branchId,
                productId: $product->id
            );

            $defaultUnit = $product->units
                ->where('is_default_sale_unit', true)
                ->first()
                ?: $product->units->where('is_base', true)->first()
                ?: $product->units->sortBy('quantity_in_base_units')->first();

            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'generic_name' => $product->generic_name,
                'brand' => $product->brand,
                'strength' => $product->strength,
                'type' => $product->productType?->name,
                'category' => $product->category?->name,
                'base_unit' => $product->baseUnit?->name,
                'available_base_units' => $availableBaseUnits,
                'has_stock' => $availableBaseUnits > 0,
                'default_unit' => $defaultUnit ? [
                    'product_unit_id' => $defaultUnit->id,
                    'unit_name' => $defaultUnit->unit?->name,
                    'quantity_in_base_units' => (int) $defaultUnit->quantity_in_base_units,
                    'price' => $this->resolveUnitPrice($product, $defaultUnit, $saleType),
                ] : null,
            ];
        })->values();
    }

    public function productUnits(
        Pharmacy $pharmacy,
        Product $product,
        int $branchId,
        string $saleType = 'retail',
    ): array {
        if ((int) $product->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }

        $product->load([
            'baseUnit',
            'units.unit',
            'units.prices',
        ]);

        $availableBaseUnits = $this->inventorySellingService->availableBaseUnits(
            pharmacyId: $pharmacy->id,
            branchId: $branchId,
            productId: $product->id
        );

        $units = $product->units
            ->where('is_active', true)
            ->where('can_sell_'.$saleType, true)
            ->sortBy('quantity_in_base_units')
            ->values()
            ->map(function (ProductUnit $productUnit) use ($product, $saleType, $availableBaseUnits) {
                $quantityInBaseUnits = max(1, (int) $productUnit->quantity_in_base_units);

                return [
                    'product_unit_id' => $productUnit->id,
                    'unit_id' => $productUnit->unit_id,
                    'unit_name' => $productUnit->unit?->name,
                    'quantity_in_base_units' => $quantityInBaseUnits,
                    'is_base' => (bool) $productUnit->is_base,
                    'is_default_sale_unit' => (bool) $productUnit->is_default_sale_unit,
                    'price' => $this->resolveUnitPrice($product, $productUnit, $saleType),
                    'available_sale_units' => intdiv($availableBaseUnits, $quantityInBaseUnits),
                    'available_base_units' => $availableBaseUnits,
                ];
            })
            ->values();

        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'base_unit' => $product->baseUnit?->name,
                'available_base_units' => $availableBaseUnits,
            ],
            'units' => $units,
        ];
    }

    public function resolveUnitPrice(Product $product, ProductUnit $productUnit, string $saleType): float
    {
        $directPrice = $productUnit->prices
            ->where('price_type', $saleType)
            ->where('is_active', true)
            ->first();

        if ($directPrice) {
            return (float) $directPrice->price;
        }

        $baseProductUnit = $product->units
            ->where('is_base', true)
            ->first();

        if (! $baseProductUnit) {
            return 0;
        }

        $basePrice = $baseProductUnit->prices
            ->where('price_type', $saleType)
            ->where('is_active', true)
            ->first();

        if (! $basePrice) {
            return 0;
        }

        return (float) $basePrice->price * max(1, (int) $productUnit->quantity_in_base_units);
    }
}