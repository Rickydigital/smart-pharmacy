<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Pharmacy;
use App\Services\Intelligence\PharmacyIntelligenceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GeneratePharmacyIntelligence extends Command
{
    protected $signature = 'intelligence:generate 
                            {--pharmacy_id= : Generate for one pharmacy}
                            {--branch_id= : Generate for one branch}
                            {--month= : Month in Y-m format, example 2026-05}';

    protected $description = 'Generate pharmacy product intelligence snapshots and alerts';

    public function handle(PharmacyIntelligenceService $service): int
    {
        $month = $this->option('month')
            ? Carbon::createFromFormat('Y-m', $this->option('month'))->startOfMonth()
            : now()->startOfMonth();

        $periodStart = $month->copy()->startOfMonth();
        $periodEnd = $month->copy()->endOfMonth();

        $pharmacyQuery = Pharmacy::query();

        if ($this->option('pharmacy_id')) {
            $pharmacyQuery->where('id', $this->option('pharmacy_id'));
        }

        $total = 0;

        foreach ($pharmacyQuery->get() as $pharmacy) {
            $branchId = $this->option('branch_id');

            if ($branchId) {
                $count = $service->generateForPharmacy(
                    pharmacyId: $pharmacy->id,
                    branchId: (int) $branchId,
                    periodStart: $periodStart,
                    periodEnd: $periodEnd
                );

                $this->info("Generated {$count} product intelligence snapshots for {$pharmacy->name}, branch {$branchId}.");
                $total += $count;
                continue;
            }

            $count = $service->generateForPharmacy(
                pharmacyId: $pharmacy->id,
                branchId: null,
                periodStart: $periodStart,
                periodEnd: $periodEnd
            );

            $this->info("Generated {$count} product intelligence snapshots for {$pharmacy->name}.");

            $branches = Branch::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->get();

            foreach ($branches as $branch) {
                $branchCount = $service->generateForPharmacy(
                    pharmacyId: $pharmacy->id,
                    branchId: $branch->id,
                    periodStart: $periodStart,
                    periodEnd: $periodEnd
                );

                $this->info("Generated {$branchCount} branch snapshots for {$branch->name}.");
                $total += $branchCount;
            }

            $total += $count;
        }

        $this->info("Done. Total generated: {$total}");

        return self::SUCCESS;
    }
}