<?php

namespace App\Console\Commands;

use App\Services\SmartControlMarketProductSyncService;
use Illuminate\Console\Command;

class SyncMarketProductsToSmartControl extends Command
{
    protected $signature = 'smart-control:sync-market-products
        {--branch_id= : Sync only one branch}
        {--product_id= : Sync only one product in the selected branch}';

    protected $description = 'Sync wholesale market products to Smart Control';

    public function handle(SmartControlMarketProductSyncService $service): int
    {
        $branchId = $this->option('branch_id');
        $productId = $this->option('product_id');

        if ($branchId && $productId) {
            $synced = $service->syncProductForBranch((int) $branchId, (int) $productId);

            $this->info($synced
                ? 'Selected market product synced to Smart Control.'
                : 'Selected market product was not synced.'
            );

            return self::SUCCESS;
        }

        $count = $service->syncAllBranches();

        $this->info("Market products synced to Smart Control. Products: {$count}");

        return self::SUCCESS;
    }
}
