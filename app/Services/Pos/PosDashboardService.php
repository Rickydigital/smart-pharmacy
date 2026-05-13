<?php

namespace App\Services\Pos;

use App\Models\Expense;
use App\Models\Pharmacy;
use App\Models\Sale;
use Illuminate\Support\Collection;

class PosDashboardService
{
    public function dayStats(Pharmacy $pharmacy, ?int $branchId = null): array
    {
        $today = now()->toDateString();

        $salesQuery = Sale::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('status', 'completed')
            ->whereDate('sold_at', $today)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $revenue = (float) (clone $salesQuery)->sum('total_amount');
        $salesCount = (int) (clone $salesQuery)->count();

        $expenses = (float) Expense::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('status', 'paid')
            ->whereDate('expense_date', $today)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        $hourlySales = array_fill(0, 24, 0.0);

        $hourlyRows = (clone $salesQuery)
            ->selectRaw('HOUR(sold_at) as hour_num, SUM(total_amount) as hour_total')
            ->groupByRaw('HOUR(sold_at)')
            ->get();

        foreach ($hourlyRows as $row) {
            $hour = (int) $row->hour_num;

            if ($hour >= 0 && $hour < 24) {
                $hourlySales[$hour] = (float) $row->hour_total;
            }
        }

        return [
            'revenue' => $revenue,
            'sales_count' => $salesCount,
            'expenses' => $expenses,
            'target' => 50,
            'hourly_sales' => $hourlySales,
        ];
    }

    public function todaySales(Pharmacy $pharmacy, int $branchId): Collection
    {
        return Sale::query()
            ->with(['creator'])
            ->withCount('items')
            ->where('pharmacy_id', $pharmacy->id)
            ->where('branch_id', $branchId)
            ->whereDate('sold_at', now()->toDateString())
            ->latest('sold_at')
            ->limit(100)
            ->get()
            ->map(function (Sale $sale) {
                return [
                    'id' => $sale->id,
                    'sale_no' => $sale->sale_no,
                    'customer' => $sale->displayCustomer(),
                    'items_count' => $sale->items_count,
                    'total_amount' => (float) $sale->total_amount,
                    'payment_method' => str_replace('_', ' ', ucfirst($sale->payment_method)),
                    'payment_status' => ucfirst($sale->payment_status),
                    'cashier' => $this->userLabel($sale->creator),
                    'sold_at' => $sale->sold_at?->format('h:i A') ?: $sale->created_at?->format('h:i A'),
                ];
            })
            ->values();
    }

    private function userLabel($user): string
    {
        if (! $user) {
            return '-';
        }

        if (method_exists($user, 'displayName')) {
            return $user->displayName();
        }

        $name = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));

        return $name ?: ($user->name ?? $user->username ?? $user->email ?? 'User');
    }
}