<?php

namespace App\Jobs;

use App\Models\Branch;
use App\Services\SmartControlBranchSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncBranchToSmartControlJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $branchId)
    {
        //
    }

    public function handle(SmartControlBranchSyncService $service): void
    {
        $branch = Branch::query()->find($this->branchId);

        if (! $branch) {
            return;
        }

        $service->syncBranch($branch);
    }
}