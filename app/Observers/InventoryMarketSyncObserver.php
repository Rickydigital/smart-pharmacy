<?php

namespace App\Observers;

use App\Jobs\SyncMarketProductToSmartControl;
use App\Models\Inventory;

class InventoryMarketSyncObserver
{
    public function created(Inventory $inventory): void
    {
        $this->sync($inventory);
    }

    public function updated(Inventory $inventory): void
    {
        if ($inventory->wasChanged([
            'available_quantity_base_units',
            'status',
            'is_active',
            'expiry_date',
            'branch_id',
            'product_id',
        ])) {
            $this->sync($inventory);
        }
    }

    public function deleted(Inventory $inventory): void
    {
        $this->sync($inventory);
    }

    private function sync(Inventory $inventory): void
    {
        if (! $inventory->branch_id || ! $inventory->product_id) {
            return;
        }

        SyncMarketProductToSmartControl::dispatch(
            (int) $inventory->branch_id,
            (int) $inventory->product_id
        )->delay(now()->addSeconds(5));
    }
}
