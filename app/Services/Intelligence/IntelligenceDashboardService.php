<?php

namespace App\Services\Intelligence;

use App\Models\IntelligenceAlert;
use App\Models\ProductIntelligenceSnapshot;

class IntelligenceDashboardService
{
    public function dashboard(
        int $pharmacyId,
        ?int $branchId = null
    ): array {

        $snapshotQuery = ProductIntelligenceSnapshot::query()
            ->where('pharmacy_id', $pharmacyId);

        $alertQuery = IntelligenceAlert::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('status', 'open');

        if ($branchId) {
            $snapshotQuery->where('branch_id', $branchId);
            $alertQuery->where('branch_id', $branchId);
        }

        return [

            'summary' => [

                'critical_alerts' =>
                    (clone $alertQuery)
                        ->where('severity','critical')
                        ->count(),

                'warning_alerts' =>
                    (clone $alertQuery)
                        ->where('severity','warning')
                        ->count(),

                'average_priority' =>
                    round(
                        (clone $snapshotQuery)
                        ->avg('priority_score') ?? 0,
                        2
                    ),

                'total_products' =>
                    (clone $snapshotQuery)
                        ->count(),
            ],

            'critical_restock' =>
                (clone $snapshotQuery)
                    ->with('product')
                    ->where('recommendation_type','restock')
                    ->orderByDesc('priority_score')
                    ->limit(10)
                    ->get(),

            'missed_demand' =>
                (clone $snapshotQuery)
                    ->with('product')
                    ->where('recommendation_type','missed_demand')
                    ->orderByDesc('priority_score')
                    ->limit(10)
                    ->get(),

            'near_expiry' =>
                (clone $snapshotQuery)
                    ->with('product')
                    ->where('near_expiry_base_units','>',0)
                    ->orderByDesc('near_expiry_base_units')
                    ->limit(10)
                    ->get(),

            'slow_products' =>
                (clone $snapshotQuery)
                    ->with('product')
                    ->where('recommendation_type','slow_moving')
                    ->limit(10)
                    ->get(),

            'high_profit_products' =>
                (clone $snapshotQuery)
                    ->with('product')
                    ->orderByDesc('gross_profit')
                    ->limit(10)
                    ->get(),

            'top_demand_products' =>
                (clone $snapshotQuery)
                    ->with('product')
                    ->orderByDesc('sales_base_units')
                    ->limit(10)
                    ->get(),

            'alerts' =>
                (clone $alertQuery)
                    ->latest()
                    ->limit(20)
                    ->get(),

        ];
    }
}