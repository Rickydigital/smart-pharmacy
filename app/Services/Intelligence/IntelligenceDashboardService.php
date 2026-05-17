<?php

namespace App\Services\Intelligence;

use App\Models\IntelligenceAlert;
use App\Models\ProductIntelligenceSnapshot;
use Carbon\Carbon;

class IntelligenceDashboardService
{
    public function dashboard(
        int $pharmacyId,
        ?int $branchId = null,
        ?string $month = null
    ): array {
        $period = $this->periodFromMonth($month);

        $snapshotQuery = ProductIntelligenceSnapshot::query()
            ->where('pharmacy_id', $pharmacyId)
            ->whereDate('period_start', $period['start'])
            ->whereDate('period_end', $period['end']);

        $alertQuery = IntelligenceAlert::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('status', 'open');

        if ($branchId) {
            $snapshotQuery->where('branch_id', $branchId);
            $alertQuery->where('branch_id', $branchId);
        }

        return [
            'period' => [
                'month' => $period['month'],
                'period_start' => $period['start'],
                'period_end' => $period['end'],
            ],

            'summary' => [
                'critical_alerts' => (clone $alertQuery)
                    ->where('severity', 'critical')
                    ->count(),

                'warning_alerts' => (clone $alertQuery)
                    ->where('severity', 'warning')
                    ->count(),

                'average_priority' => round(
                    (clone $snapshotQuery)->avg('priority_score') ?? 0,
                    2
                ),

                'total_products' => (clone $snapshotQuery)->count(),
            ],

            'critical_restock' => (clone $snapshotQuery)
                ->with('product')
                ->where('recommendation_type', 'restock')
                ->orderByDesc('priority_score')
                ->limit(10)
                ->get(),

            'missed_demand' => (clone $snapshotQuery)
                ->with('product')
                ->where('recommendation_type', 'missed_demand')
                ->orderByDesc('priority_score')
                ->limit(10)
                ->get(),

            'near_expiry' => (clone $snapshotQuery)
                ->with('product')
                ->where('near_expiry_base_units', '>', 0)
                ->orderByDesc('near_expiry_base_units')
                ->limit(10)
                ->get(),

            'slow_products' => (clone $snapshotQuery)
                ->with('product')
                ->where('recommendation_type', 'slow_moving')
                ->limit(10)
                ->get(),

            'high_profit_products' => (clone $snapshotQuery)
                ->with('product')
                ->orderByDesc('gross_profit')
                ->limit(10)
                ->get(),

            'top_demand_products' => (clone $snapshotQuery)
                ->with('product')
                ->orderByDesc('sales_base_units')
                ->limit(10)
                ->get(),

            'alerts' => (clone $alertQuery)
                ->latest()
                ->limit(20)
                ->get(),
        ];
    }

    private function periodFromMonth(?string $month): array
    {
        $date = $month
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();

        return [
            'month' => $date->format('Y-m'),
            'start' => $date->copy()->startOfMonth()->toDateString(),
            'end' => $date->copy()->endOfMonth()->toDateString(),
        ];
    }
}