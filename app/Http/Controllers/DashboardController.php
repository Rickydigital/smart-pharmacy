<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DailyClosing;
use App\Models\Expense;
use App\Models\Pharmacy;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        $branches = Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $defaultBranchId = $isAdminOrOwner
            ? null
            : $user?->branch_id;

        $dateFrom = $request->input('date_from', now()->subDays(6)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id', $defaultBranchId);
        $activityLog = $request->input('activity_log');
        $activitySearch = trim((string) $request->input('activity_search'));

        $salesQuery = $this->completedSalesQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);
        $expenseQuery = $this->expenseQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);
        $profitQuery = $this->profitItemsQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);
        $closingQuery = $this->dailyClosingQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);

        $salesTotal = (float) (clone $salesQuery)->sum('total_amount');
        $salesCount = (clone $salesQuery)->count();
        $expenseTotal = (float) (clone $expenseQuery)->sum('amount');
        $grossProfit = (float) (clone $profitQuery)->sum('profit_amount');
        $costSold = (float) (clone $profitQuery)->sum('total_cost');

        $returnItemsQuery = $this->approvedReturnItemsQuery(
            $pharmacy->id,
            $dateFrom,
            $dateTo,
            $branchId
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

        $summary = [
            'sales_total' => $salesTotal,
            'sales_count' => $salesCount,
            'customer_count' => (int) $customerCount,
            'expense_total' => $expenseTotal,
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
            'cost_sold' => $costSold,
            'expected_cash' => $expectedCash,
            'counted_cash' => $countedCash,
            'cash_difference' => $countedCash - $expectedCash,
            'net_margin' => $salesTotal > 0 ? round(($netProfit / $salesTotal) * 100, 2) : 0,
        ];

        $trendData = $this->buildTrendData(
            pharmacyId: $pharmacy->id,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            branchId: $branchId
        );

        $topProducts = SaleItem::query()
            ->with(['product', 'productUnit.unit'])
            ->selectRaw('product_id, product_unit_id, SUM(quantity) as quantity_sold, SUM(line_total) as sales_amount, SUM(profit_amount) as profit_amount')
            ->where('pharmacy_id', $pharmacy->id)
            ->whereHas('sale', function ($query) use ($dateFrom, $dateTo, $branchId) {
                $query->whereIn('status', ['completed', 'partially_returned'])
                    ->whereDate('sold_at', '>=', $dateFrom)
                    ->whereDate('sold_at', '<=', $dateTo)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
            })
            ->groupBy('product_id', 'product_unit_id')
            ->orderByDesc('sales_amount')
            ->limit(5)
            ->get();

        $recentSales = Sale::query()
            ->with(['branch', 'creator'])
            ->withCount('items')
            ->where('pharmacy_id', $pharmacy->id)
            ->whereDate('sold_at', '>=', $dateFrom)
            ->whereDate('sold_at', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->latest('sold_at')
            ->limit(8)
            ->get();

        $activityLogNames = Activity::query()
            ->whereNotNull('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name');

        $activities = Activity::query()
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

        return view('dashboard', compact(
            'branches',
            'dateFrom',
            'dateTo',
            'branchId',
            'isAdminOrOwner',
            'summary',
            'trendData',
            'topProducts',
            'recentSales',
            'activities',
            'activityLogNames',
            'activityLog',
            'activitySearch'
        ));
    }

    private function buildTrendData(int $pharmacyId, string $dateFrom, string $dateTo, mixed $branchId): array
    {
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

        $profitRows = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id', 'inner', false)
            ->where('sale_items.pharmacy_id', $pharmacyId)
            ->whereIn('sales.status', ['completed', 'partially_returned'])
            ->whereDate('sales.sold_at', '>=', $dateFrom)
            ->whereDate('sales.sold_at', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('sales.branch_id', $branchId))
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

    private function approvedReturnItemsQuery(int $pharmacyId, string $dateFrom, string $dateTo, mixed $branchId)
{
    return SalesReturnItem::query()
        ->where('sales_return_items.pharmacy_id', $pharmacyId)
        ->whereHas('salesReturn', function ($query) use ($dateFrom, $dateTo, $branchId) {
            $query->where('status', 'approved')
                ->whereDate('return_date', '>=', $dateFrom)
                ->whereDate('return_date', '<=', $dateTo)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
        });
}
    private function completedSalesQuery(int $pharmacyId, string $dateFrom, string $dateTo, mixed $branchId)
    {
        return Sale::query()
            ->where('pharmacy_id', $pharmacyId)
            ->whereIn('status', ['completed', 'partially_returned'])
            ->whereDate('sold_at', '>=', $dateFrom)
            ->whereDate('sold_at', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));
    }

    private function expenseQuery(int $pharmacyId, string $dateFrom, string $dateTo, mixed $branchId)
    {
        return Expense::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('status', 'paid')
            ->whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));
    }

    private function profitItemsQuery(int $pharmacyId, string $dateFrom, string $dateTo, mixed $branchId)
    {
        return SaleItem::query()
            ->where('pharmacy_id', $pharmacyId)
            ->whereHas('sale', function ($query) use ($dateFrom, $dateTo, $branchId) {
                $query->whereIn('status', ['completed', 'partially_returned'])
                    ->whereDate('sold_at', '>=', $dateFrom)
                    ->whereDate('sold_at', '<=', $dateTo)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
            });
    }

    private function dailyClosingQuery(int $pharmacyId, string $dateFrom, string $dateTo, mixed $branchId)
    {
        return DailyClosing::query()
            ->where('pharmacy_id', $pharmacyId)
            ->whereDate('closing_date', '>=', $dateFrom)
            ->whereDate('closing_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));
    }
}