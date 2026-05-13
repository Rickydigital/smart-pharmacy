<?php

namespace App\Console\Commands;

use App\Services\SmartControlBranchSyncService;
use Illuminate\Console\Command;

class SyncBranchesToSmartControl extends Command
{
    protected $signature = 'smart-control:sync-branches';

    protected $description = 'Sync pharmacy branches to Smart Control';

    public function handle(SmartControlBranchSyncService $service): int
    {
        $result = $service->syncAllBranches();

        $this->info('Branches sync completed.');
        $this->line('Attempted: ' . $result['attempted']);
        $this->line('Synced: ' . $result['synced']);
        $this->line('Skipped: ' . $result['skipped']);
        $this->line('Failed: ' . $result['failed']);

        foreach ($result['messages'] as $message) {
            $this->line('- ' . $message);
        }

        return self::SUCCESS;
    }
}
