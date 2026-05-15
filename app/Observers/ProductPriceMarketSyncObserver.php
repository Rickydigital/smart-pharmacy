<?php

namespace App\Observers;

use App\Jobs\SyncMarketProductToSmartControl;
use App\Models\Inventory;
use App\Models\ProductPrice;

class ProductPriceMarketSyncObserver
{
    public function created(ProductPrice $price): void
    {
        $this->sync($price);
    }

    public function updated(ProductPrice $price): void
    {
        if ($price->wasChanged([
            'price',
            'price_type',
            'is_active',
            'product_id',
            'product_unit_id',
        ])) {
            $this->sync($price);
        }
    }

    public function deleted(ProductPrice $price): void
    {
        $this->sync($price);
    }

    private function sync(ProductPrice $price): void
    {
        if (! $price->product_id) {
            return;
        }

        $branchIds = Inventory::query()
            ->where('product_id', $price->product_id)
            ->distinct()
            ->pluck('branch_id')
            ->filter();

        foreach ($branchIds as $branchId) {
            SyncMarketProductToSmartControl::dispatch(
                (int) $branchId,
                (int) $price->product_id
            )->delay(now()->addSeconds(5));
        }
    }
}