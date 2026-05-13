<?php

namespace App\Services\Dashboard;

use App\Models\Branch;
use App\Models\DailyClosing;
use App\Models\Expense;
use App\Models\Pharmacy;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class DashboardDataService
{
    public function build(Request $request, mixed $user): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;
        $isCashier = $user?->hasRole('Cashier') ?? false;
        $isPharmacist = $user?->hasRole('Pharmacist') ?? false;
        $isStorekeeper = $user?->hasRole('Storekeeper') ?? false;

        $branches = Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $dateFrom = $request->input('date_from', now()->subDays(6)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $branchId = $isAdminOrOwner
            ? $request->input('branch_id')
            : $user?->branch_id;

        $activityLog = $request->input('activity_log');
        $activitySearch = trim((string) $request->input('activity_search'));

        $salesQuery = $this->completedSalesQuery(
            pharmacyId: $pharmacy->id,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            branchId: $branchId,
            user: $user,
            isCashier: $isCashier
        );

        $expenseQuery = $this->expenseQuery(
            pharmacyId: $pharmacy->id,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            branchId: $branchId
        );

        $profitQuery = $this->profitItemsQuery(
            pharmacyId: $pharmacy->id,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            branchId: $branchId,
            user: $user,
            isCashier: $isCashier
        );

        $closingQuery = $this->dailyClosingQuery(
            pharmacyId: $pharmacy->id,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            branchId: $branchId
        );

        $salesTotal = (float) (clone $salesQuery)->sum('total_amount');
        $salesCount = (clone $salesQuery)->count();
        $expenseTotal = (float) (clone $expenseQuery)->sum('amount');
        $grossProfit = (float) (clone $profitQuery)->sum('profit_amount');
        $costSold = (float) (clone $profitQuery)->sum('total_cost');

        $returnItemsQuery = $this->approvedReturnItemsQuery(
            pharmacyId: $pharmacy->id,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            branchId: $branchId,
            user: $user,
            isCashier: $isCashier
        );

        $returnSales = (float) (clone $returnItemsQuery)->sum('refund_amount');
        $returnCost = (float) (clone $returnItemsQuery)->sum('total_cost');
        $returnProfit = (float) (clone $returnItemsQuery)->sum('profit_reversed');

        $salesTotal = max(0, $salesTotal - $returnSales);
        $costSold = max(0, $costSold - $returnCost);
        $grossProfit = $grossProfit - $returnProfit;
        $netProfit = $grossProfit - $expenseTotal;

        $customerCount = (clone $salesQuery)
            ->where(function ($query) {
                $query->whereNotNull('customer_phone')
                    ->orWhereNotNull('customer_name');
            })
            ->selectRaw("COUNT(DISTINCT COALESCE(NULLIF(customer_phone, ''), NULLIF(customer_name, ''), sale_no)) as customers_count")
            ->value('customers_count') ?? 0;

        $expectedCash = (float) (clone $closingQuery)->sum('expected_cash_amount');
        $countedCash = (float) (clone $closingQuery)->sum('counted_cash_amount');

        $canSeeFullFinancials = $isAdminOrOwner || $user?->hasRole('Owner');

        $summary = [
            'sales_total' => $salesTotal,
            'sales_count' => $salesCount,
            'customer_count' => (int) $customerCount,

            'expense_total' => $canSeeFullFinancials ? $expenseTotal : 0,
            'gross_profit' => $canSeeFullFinancials ? $grossProfit : 0,
            'net_profit' => $canSeeFullFinancials ? $netProfit : 0,
            'cost_sold' => $canSeeFullFinancials ? $costSold : 0,
            'expected_cash' => $canSeeFullFinancials ? $expectedCash : 0,
            'counted_cash' => $canSeeFullFinancials ? $countedCash : 0,
            'cash_difference' => $canSeeFullFinancials ? ($countedCash - $expectedCash) : 0,
            'net_margin' => $canSeeFullFinancials && $salesTotal > 0
                ? round(($netProfit / $salesTotal) * 100, 2)
                : 0,
        ];

        $trendData = $this->buildTrendData(
            pharmacyId: $pharmacy->id,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            branchId: $branchId,
            user: $user,
            isCashier: $isCashier,
            canSeeFullFinancials: $canSeeFullFinancials
        );

        $topProducts = $this->topProducts(
            pharmacyId: $pharmacy->id,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            branchId: $branchId,
            user: $user,
            isCashier: $isCashier
        );

        $recentSales = $this->recentSales(
            pharmacyId: $pharmacy->id,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            branchId: $branchId,
            user: $user,
            isCashier: $isCashier
        );

        $activityLogNames = $isAdminOrOwner
            ? Activity::query()
                ->whereNotNull('log_name')
                ->distinct()
                ->orderBy('log_name')
                ->pluck('log_name')
            : collect();

        $activities = $isAdminOrOwner
            ? $this->activities($dateFrom, $dateTo, $activityLog, $activitySearch)
            : collect();

        return [
            'branches' => $isAdminOrOwner ? $branches : collect(),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'branchId' => $branchId,
            'branch_id' => $branchId,
            'isAdminOrOwner' => $isAdminOrOwner,
            'is_admin_or_owner' => $isAdminOrOwner,
            'is_cashier' => $isCashier,
            'is_pharmacist' => $isPharmacist,
            'is_storekeeper' => $isStorekeeper,
            'can_see_full_financials' => $canSeeFullFinancials,
            'summary' => $summary,
            'trendData' => $trendData,
            'trend_data' => $trendData,
            'topProducts' => $topProducts,
            'top_products' => $topProducts,
            'recentSales' => $recentSales,
            'recent_sales' => $recentSales,
            'activities' => $activities,
            'activityLogNames' => $activityLogNames,
            'activity_log_names' => $activityLogNames,
            'activityLog' => $activityLog,
            'activity_log' => $activityLog,
            'activitySearch' => $activitySearch,
            'activity_search' => $activitySearch,
        ];
    }

    private function buildTrendData(
        int $pharmacyId,
        string $dateFrom,
        string $dateTo,
        mixed $branchId,
        mixed $user,
        bool $isCashier,
        bool $canSeeFullFinancials
    ): array {
        $period = collect();

        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);

        while ($start->lte($end)) {
            $date = $start->toDateString();

            $period->put($date, [
                'day' => $start->format('d M'),
                'revenue' => 0,
                'sales' => 0,
                'profit' => 0,
                'expenses' => 0,
                'closing' => 0,
            ]);

            $start->addDay();
        }

        $salesRows = Sale::query()
            ->where('pharmacy_id', $pharmacyId)
            ->whereIn('status', ['completed', 'partially_returned'])
            ->whereDate('sold_at', '>=', $dateFrom)
            ->whereDate('sold_at', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($isCashier, fn ($query) => $query->where('created_by', $user?->id))
            ->selectRaw('DATE(sold_at) as report_date, SUM(total_amount) as revenue, COUNT(*) as sales_count')
            ->groupByRaw('DATE(sold_at)')
            ->get();

        foreach ($salesRows as $row) {
            if ($period->has($row->report_date)) {
                $item = $period->get($row->report_date);
                $item['revenue'] = (float) $row->revenue;
                $item['sales'] = (int) $row->sales_count;
                $period->put($row->report_date, $item);
            }
        }

        if (! $canSeeFullFinancials) {
            return $period->values()->toArray();
        }

        $profitRows = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id', 'inner', false)
            ->where('sale_items.pharmacy_id', $pharmacyId)
            ->whereIn('sales.status', ['completed', 'partially_returned'])
            ->whereDate('sales.sold_at', '>=', $dateFrom)
            ->whereDate('sales.sold_at', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('sales.branch_id', $branchId))
            ->when($isCashier, fn ($query) => $query->where('sales.created_by', $user?->id))
            ->selectRaw('DATE(sales.sold_at) as report_date, SUM(sale_items.profit_amount) as profit')
            ->groupByRaw('DATE(sales.sold_at)')
            ->get();

        foreach ($profitRows as $row) {
            if ($period->has($row->report_date)) {
                $item = $period->get($row->report_date);
                $item['profit'] = (float) $row->profit;
                $period->put($row->report_date, $item);
            }
        }

        $expenseRows = Expense::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('status', 'paid')
            ->whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->selectRaw('expense_date as report_date, SUM(amount) as expenses')
            ->groupBy('expense_date')
            ->get();

        foreach ($expenseRows as $row) {
            if ($period->has($row->report_date)) {
                $item = $period->get($row->report_date);
                $item['expenses'] = (float) $row->expenses;
                $period->put($row->report_date, $item);
            }
        }

        $closingRows = DailyClosing::query()
            ->where('pharmacy_id', $pharmacyId)
            ->whereDate('closing_date', '>=', $dateFrom)
            ->whereDate('closing_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->selectRaw('closing_date as report_date, SUM(expected_cash_amount) as closing_amount')
            ->groupBy('closing_date')
            ->get();

        foreach ($closingRows as $row) {
            if ($period->has($row->report_date)) {
                $item = $period->get($row->report_date);
                $item['closing'] = (float) $row->closing_amount;
                $period->put($row->report_date, $item);
            }
        }

        return $period->values()->toArray();
    }

    private function topProducts(
        int $pharmacyId,
        string $dateFrom,
        string $dateTo,
        mixed $branchId,
        mixed $user,
        bool $isCashier
    ): Collection {
        return SaleItem::query()
            ->with(['product', 'productUnit.unit'])
            ->selectRaw('product_id, product_unit_id, SUM(quantity) as quantity_sold, SUM(line_total) as sales_amount, SUM(profit_amount) as profit_amount')
            ->where('pharmacy_id', $pharmacyId)
            ->whereHas('sale', function ($query) use ($dateFrom, $dateTo, $branchId, $isCashier, $user) {
                $query->whereIn('status', ['completed', 'partially_returned'])
                    ->whereDate('sold_at', '>=', $dateFrom)
                    ->whereDate('sold_at', '<=', $dateTo)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->when($isCashier, fn ($q) => $q->where('created_by', $user?->id));
            })
            ->groupBy('product_id', 'product_unit_id')
            ->orderByDesc('sales_amount')
            ->limit(5)
            ->get();
    }

    private function recentSales(
        int $pharmacyId,
        string $dateFrom,
        string $dateTo,
        mixed $branchId,
        mixed $user,
        bool $isCashier
    ): Collection {
        return Sale::query()
            ->with(['branch', 'creator'])
            ->withCount('items')
            ->where('pharmacy_id', $pharmacyId)
            ->whereDate('sold_at', '>=', $dateFrom)
            ->whereDate('sold_at', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($isCashier, fn ($query) => $query->where('created_by', $user?->id))
            ->latest('sold_at')
            ->limit(8)
            ->get();
    }

    private function activities(
        string $dateFrom,
        string $dateTo,
        mixed $activityLog,
        string $activitySearch
    ): Collection {
        return Activity::query()
            ->with('causer')
            ->when($activityLog, fn ($query) => $query->where('log_name', $activityLog))
            ->when($activitySearch !== '', function ($query) use ($activitySearch) {
                $query->where(function ($q) use ($activitySearch) {
                    $q->where('description', 'like', "%{$activitySearch}%")
                        ->orWhere('event', 'like', "%{$activitySearch}%")
                        ->orWhere('log_name', 'like', "%{$activitySearch}%");
                });
            })
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->latest()
            ->limit(8)
            ->get();
    }

    private function approvedReturnItemsQuery(
        int $pharmacyId,
        string $dateFrom,
        string $dateTo,
        mixed $branchId,
        mixed $user,
        bool $isCashier
    ) {
        return SalesReturnItem::query()
            ->where('sales_return_items.pharmacy_id', $pharmacyId)
            ->whereHas('salesReturn', function ($query) use ($dateFrom, $dateTo, $branchId, $user, $isCashier) {
                $query->where('status', 'approved')
                    ->whereDate('return_date', '>=', $dateFrom)
                    ->whereDate('return_date', '<=', $dateTo)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->when($isCashier, fn ($q) => $q->where('created_by', $user?->id));
            });
    }

    private function completedSalesQuery(
        int $pharmacyId,
        string $dateFrom,
        string $dateTo,
        mixed $branchId,
        mixed $user,
        bool $isCashier
    ) {
        return Sale::query()
            ->where('pharmacy_id', $pharmacyId)
            ->whereIn('status', ['completed', 'partially_returned'])
            ->whereDate('sold_at', '>=', $dateFrom)
            ->whereDate('sold_at', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($isCashier, fn ($query) => $query->where('created_by', $user?->id));
    }

    private function expenseQuery(
        int $pharmacyId,
        string $dateFrom,
        string $dateTo,
        mixed $branchId
    ) {
        return Expense::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('status', 'paid')
            ->whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));
    }

    private function profitItemsQuery(
        int $pharmacyId,
        string $dateFrom,
        string $dateTo,
        mixed $branchId,
        mixed $user,
        bool $isCashier
    ) {
        return SaleItem::query()
            ->where('pharmacy_id', $pharmacyId)
            ->whereHas('sale', function ($query) use ($dateFrom, $dateTo, $branchId, $user, $isCashier) {
                $query->whereIn('status', ['completed', 'partially_returned'])
                    ->whereDate('sold_at', '>=', $dateFrom)
                    ->whereDate('sold_at', '<=', $dateTo)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->when($isCashier, fn ($q) => $q->where('created_by', $user?->id));
            });
    }

    private function dailyClosingQuery(
        int $pharmacyId,
        string $dateFrom,
        string $dateTo,
        mixed $branchId
    ) {
        return DailyClosing::query()
            ->where('pharmacy_id', $pharmacyId)
            ->whereDate('closing_date', '>=', $dateFrom)
            ->whereDate('closing_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));
    }
}