<?php

namespace App\Providers;

use App\Models\Inventory;
use App\Models\ProductPrice;
use App\Observers\InventoryMarketSyncObserver;
use App\Observers\ProductPriceMarketSyncObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Inventory::observe(InventoryMarketSyncObserver::class);
        ProductPrice::observe(ProductPriceMarketSyncObserver::class);
    }
}
