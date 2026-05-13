<?php

namespace App\Services\Reports;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Exports\Reports\ReportTableExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\Response as HttpResponse;
use App\Models\Pharmacy;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportDataService
{
    public function index(Request $request): array
    {
        $this->guardOwnerReport();

        $pharmacy = Pharmacy::query()->firstOrFail();

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');

        $branches = $this->branches($pharmacy->id);

        $salesQuery = $this->completedSalesQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);
        $purchaseQuery = $this->purchaseQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);
        $profitQuery = $this->profitItemsQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);
        $expenseQuery = $this->expenseQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);

        $salesTotal = (float) (clone $salesQuery)->sum('total_amount');
        $grossProfit = (float) (clone $profitQuery)->sum('profit_amount');
        $costTotal = (float) (clone $profitQuery)->sum('total_cost');
        $expenseTotal = (float) (clone $expenseQuery)->sum('amount');

        $returnItemsQuery = $this->approvedReturnItemsQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);

        $returnSales = (float) (clone $returnItemsQuery)->sum('refund_amount');
        $returnCost = (float) (clone $returnItemsQuery)->sum('total_cost');
        $returnProfit = (float) (clone $returnItemsQuery)->sum('profit_reversed');

        $salesTotal = max(0, $salesTotal - $returnSales);
        $costTotal = max(0, $costTotal - $returnCost);
        $grossProfit = $grossProfit - $returnProfit;
        $netProfit = $grossProfit - $expenseTotal;

        $summary = [
            'sales_count' => (clone $salesQuery)->count(),
            'sales_total' => $salesTotal,
            'sales_paid' => (float) (clone $salesQuery)->sum('paid_amount'),
            'discount_total' => (float) (clone $salesQuery)->sum('discount_amount'),

            'purchase_count' => (clone $purchaseQuery)->count(),
            'purchase_total' => (float) (clone $purchaseQuery)->sum('total_amount'),
            'purchase_paid' => (float) (clone $purchaseQuery)->sum('paid_amount'),

            'profit_total' => $grossProfit,
            'cost_total' => $costTotal,

            'expense_count' => (clone $expenseQuery)->count(),
            'expense_total' => $expenseTotal,
            'cash_expense_total' => (float) (clone $expenseQuery)->where('payment_method', 'cash')->sum('amount'),

            'net_profit' => $netProfit,
        ];

        $summary['gross_margin'] = $summary['sales_total'] > 0
            ? round(($summary['profit_total'] / $summary['sales_total']) * 100, 2)
            : 0;

        $summary['net_margin'] = $summary['sales_total'] > 0
            ? round(($summary['net_profit'] / $summary['sales_total']) * 100, 2)
            : 0;

        $paymentBreakdown = (clone $salesQuery)
            ->selectRaw('payment_method, COUNT(*) as sales_count, SUM(total_amount) as total_amount')
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get();

        $saleTypeBreakdown = (clone $salesQuery)
            ->selectRaw('sale_type, COUNT(*) as sales_count, SUM(total_amount) as total_amount')
            ->groupBy('sale_type')
            ->orderByDesc('total_amount')
            ->get();

        $expenseBreakdown = (clone $expenseQuery)
            ->with('category')
            ->selectRaw('expense_category_id, COUNT(*) as expenses_count, SUM(amount) as total_amount')
            ->groupBy('expense_category_id')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        $paymentExpenseBreakdown = (clone $expenseQuery)
            ->selectRaw('payment_method, COUNT(*) as expenses_count, SUM(amount) as total_amount')
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get();

        $topProducts = SaleItem::query()
            ->with(['product', 'productUnit.unit'])
            ->selectRaw('
                product_id,
                product_unit_id,
                SUM(quantity) as quantity_sold,
                SUM(line_total) as sales_amount,
                SUM(profit_amount) as profit_amount
            ')
            ->where('pharmacy_id', $pharmacy->id)
            ->whereHas('sale', function ($query) use ($dateFrom, $dateTo, $branchId) {
                $query->whereIn('status', ['completed', 'partially_returned'])
                    ->whereDate('sold_at', '>=', $dateFrom)
                    ->whereDate('sold_at', '<=', $dateTo)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
            })
            ->groupBy('product_id', 'product_unit_id')
            ->orderByDesc('sales_amount')
            ->limit(10)
            ->get();

        $lowStock = Inventory::query()
            ->with(['product.baseUnit', 'branch'])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('status', 'available')
            ->where('is_active', true)
            ->where('available_quantity_base_units', '>', 0)
            ->where('available_quantity_base_units', '<=', 10)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('available_quantity_base_units')
            ->limit(10)
            ->get();

        $expiringSoon = Inventory::query()
            ->with(['product.baseUnit', 'branch'])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('status', 'available')
            ->where('is_active', true)
            ->where('available_quantity_base_units', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        $recentMovements = InventoryMovement::query()
            ->with(['product', 'branch', 'creator'])
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->latest('moved_at')
            ->limit(10)
            ->get();

        return compact(
            'branches',
            'dateFrom',
            'dateTo',
            'branchId',
            'summary',
            'paymentBreakdown',
            'saleTypeBreakdown',
            'expenseBreakdown',
            'paymentExpenseBreakdown',
            'topProducts',
            'lowStock',
            'expiringSoon',
            'recentMovements'
        );
    }

    public function sales(Request $request): array
    {
        $this->guardOwnerReport();

        $pharmacy = Pharmacy::query()->firstOrFail();

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');
        $paymentMethod = $request->input('payment_method');
        $saleType = $request->input('sale_type');

        $branches = $this->branches($pharmacy->id);

        $query = Sale::query()
            ->with(['branch', 'creator'])
            ->withCount('items')
            ->where('pharmacy_id', $pharmacy->id)
            ->whereDate('sold_at', '>=', $dateFrom)
            ->whereDate('sold_at', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($paymentMethod, fn ($q) => $q->where('payment_method', $paymentMethod))
            ->when($saleType, fn ($q) => $q->where('sale_type', $saleType));

        $sales = (clone $query)
            ->latest('sold_at')
            ->paginate(20)
            ->withQueryString();

        $completed = (clone $query)->whereIn('status', ['completed', 'partially_returned']);

        $summary = [
            'count' => (clone $completed)->count(),
            'total' => (float) (clone $completed)->sum('total_amount'),
            'paid' => (float) (clone $completed)->sum('paid_amount'),
            'discount' => (float) (clone $completed)->sum('discount_amount'),
        ];

        return compact(
            'sales',
            'summary',
            'branches',
            'dateFrom',
            'dateTo',
            'branchId',
            'paymentMethod',
            'saleType'
        );
    }

    public function stock(Request $request): array
    {
        $this->guardOwnerReport();

        $pharmacy = Pharmacy::query()->firstOrFail();

        $branchId = $request->input('branch_id');
        $status = $request->input('status');
        $risk = $request->input('risk');

        $branches = $this->branches($pharmacy->id);

        $query = Inventory::query()
            ->with(['product.baseUnit', 'branch', 'purchase'])
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($risk === 'low', fn ($q) => $q->where('available_quantity_base_units', '<=', 10))
            ->when($risk === 'expired', fn ($q) => $q->whereDate('expiry_date', '<', now()->toDateString()))
            ->when($risk === 'expiring', function ($q) {
                $q->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '>=', now()->toDateString())
                    ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString());
            });

        $inventories = (clone $query)
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'items' => (clone $query)->count(),
            'available_qty' => (int) (clone $query)->sum('available_quantity_base_units'),
            'stock_value' => (float) ((clone $query)
                ->selectRaw('SUM(available_quantity_base_units * unit_cost_base) as value')
                ->value('value') ?? 0),
            'low_stock' => Inventory::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('available_quantity_base_units', '<=', 10)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->count(),
        ];

        return compact(
            'inventories',
            'summary',
            'branches',
            'branchId',
            'status',
            'risk'
        );
    }

    public function purchases(Request $request): array
    {
        $this->guardOwnerReport();

        $pharmacy = Pharmacy::query()->firstOrFail();

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');
        $status = $request->input('status');

        $branches = $this->branches($pharmacy->id);

        $query = Purchase::query()
            ->with(['branch', 'supplier', 'creator'])
            ->withCount('items')
            ->where('pharmacy_id', $pharmacy->id)
            ->whereDate('purchase_date', '>=', $dateFrom)
            ->whereDate('purchase_date', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($status, fn ($q) => $q->where('status', $status));

        $purchases = (clone $query)
            ->latest('purchase_date')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'count' => (clone $query)->count(),
            'total' => (float) (clone $query)->sum('total_amount'),
            'paid' => (float) (clone $query)->sum('paid_amount'),
            'balance' => (float) (clone $query)->sum('balance_amount'),
        ];

        return compact(
            'purchases',
            'summary',
            'branches',
            'dateFrom',
            'dateTo',
            'branchId',
            'status'
        );
    }

    public function profit(Request $request): array
    {
        $this->guardOwnerReport();

        $pharmacy = Pharmacy::query()->firstOrFail();

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');

        $branches = $this->branches($pharmacy->id);

        $query = SaleItem::query()
            ->with(['sale.branch', 'sale.creator', 'product', 'productUnit.unit'])
            ->where('pharmacy_id', $pharmacy->id)
            ->whereHas('sale', function ($q) use ($dateFrom, $dateTo, $branchId) {
                $q->whereIn('status', ['completed', 'partially_returned'])
                    ->whereDate('sold_at', '>=', $dateFrom)
                    ->whereDate('sold_at', '<=', $dateTo)
                    ->when($branchId, fn ($saleQuery) => $saleQuery->where('branch_id', $branchId));
            });

        $items = (clone $query)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $expenseQuery = $this->expenseQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);

        $summary = [
            'sales' => (float) (clone $query)->sum('line_total'),
            'cost' => (float) (clone $query)->sum('total_cost'),
            'gross_profit' => (float) (clone $query)->sum('profit_amount'),
            'expenses' => (float) (clone $expenseQuery)->sum('amount'),
        ];

        $summary['net_profit'] = $summary['gross_profit'] - $summary['expenses'];

        $summary['gross_margin'] = $summary['sales'] > 0
            ? round(($summary['gross_profit'] / $summary['sales']) * 100, 2)
            : 0;

        $summary['net_margin'] = $summary['sales'] > 0
            ? round(($summary['net_profit'] / $summary['sales']) * 100, 2)
            : 0;

        $summary['profit'] = $summary['gross_profit'];
        $summary['margin'] = $summary['gross_margin'];

        return compact(
            'items',
            'summary',
            'branches',
            'dateFrom',
            'dateTo',
            'branchId'
        );
    }

    public function expenses(Request $request): array
    {
        $this->guardOwnerReport();

        $pharmacy = Pharmacy::query()->firstOrFail();

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');
        $paymentMethod = $request->input('payment_method');
        $status = $request->input('status', 'paid');

        $branches = $this->branches($pharmacy->id);

        $query = Expense::query()
            ->with(['branch', 'category', 'creator'])
            ->where('pharmacy_id', $pharmacy->id)
            ->whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($paymentMethod, fn ($q) => $q->where('payment_method', $paymentMethod))
            ->when($status, fn ($q) => $q->where('status', $status));

        $expenses = (clone $query)
            ->latest('expense_date')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'count' => (clone $query)->count(),
            'total' => (float) (clone $query)->sum('amount'),
            'cash' => (float) (clone $query)->where('payment_method', 'cash')->sum('amount'),
            'other' => (float) (clone $query)->where('payment_method', '!=', 'cash')->sum('amount'),
        ];

        $categoryBreakdown = (clone $query)
            ->with('category')
            ->selectRaw('expense_category_id, COUNT(*) as expenses_count, SUM(amount) as total_amount')
            ->groupBy('expense_category_id')
            ->orderByDesc('total_amount')
            ->get();

        return compact(
            'expenses',
            'summary',
            'branches',
            'categoryBreakdown',
            'dateFrom',
            'dateTo',
            'branchId',
            'paymentMethod',
            'status'
        );
    }

    public function prescriptions(Request $request): array
    {
        $this->guardOwnerReport();

        return [
            'message' => 'Prescription module is not implemented yet. This report page is ready for connection after prescriptions are added.',
        ];
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

    private function purchaseQuery(int $pharmacyId, string $dateFrom, string $dateTo, mixed $branchId)
    {
        return Purchase::query()
            ->where('pharmacy_id', $pharmacyId)
            ->whereDate('purchase_date', '>=', $dateFrom)
            ->whereDate('purchase_date', '<=', $dateTo)
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

    private function expenseQuery(int $pharmacyId, string $dateFrom, string $dateTo, mixed $branchId)
    {
        return Expense::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('status', 'paid')
            ->whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));
    }

    private function branches(int $pharmacyId)
    {
        return Branch::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function export(Request $request, string $report): BinaryFileResponse|HttpResponse
{
    $this->guardOwnerReport();

    $format = $request->input('format', 'pdf');

    if (! in_array($format, ['pdf', 'excel'], true)) {
        abort(422, 'Invalid export format.');
    }

    $export = $this->buildExportData($request, $report);

    if ($format === 'excel') {
        return Excel::download(
            new ReportTableExport($export['title'], $export['headings'], $export['rows']),
            $export['filename'] . '.xlsx'
        );
    }

    $pdf = Pdf::loadView('reports.exports.table-pdf', [
        'title' => $export['title'],
        'subtitle' => $export['subtitle'],
        'headings' => $export['headings'],
        'rows' => $export['rows'],
        'meta' => $export['meta'],
        'generatedAt' => now(),
    ])->setPaper('a4', 'landscape');

    return $pdf->download($export['filename'] . '.pdf');
}

public function buildExportData(Request $request, string $report): array
{
    return match ($report) {
        'center' => $this->exportCenter($request),
        'sales' => $this->exportSales($request),
        'stock' => $this->exportStock($request),
        'purchases' => $this->exportPurchases($request),
        'profit' => $this->exportProfit($request),
        'expenses' => $this->exportExpenses($request),
        default => abort(404, 'Report not found.'),
    };
}

private function exportSales(Request $request): array
{
    $data = $this->sales($request);

    $rows = collect($data['sales']->items())->map(function ($sale) {
        return [
            $sale->sale_no,
            $sale->branch?->name ?? '-',
            $sale->sale_type,
            $sale->payment_method,
            number_format((float) $sale->total_amount, 2),
            number_format((float) $sale->paid_amount, 2),
            number_format((float) $sale->discount_amount, 2),
            $sale->status,
            optional($sale->sold_at)->format('Y-m-d H:i'),
            $sale->creator?->name ?? $sale->creator?->username ?? '-',
        ];
    })->values()->all();

    return $this->exportPayload(
        title: 'Sales Report',
        subtitle: 'Detailed sales report',
        filename: 'sales-report',
        headings: ['Sale No', 'Branch', 'Type', 'Payment', 'Total', 'Paid', 'Discount', 'Status', 'Sold At', 'Cashier'],
        rows: $rows,
        data: $data
    );
}

private function exportStock(Request $request): array
{
    $data = $this->stock($request);

    $rows = collect($data['inventories']->items())->map(function ($inventory) {
        return [
            $inventory->product?->name ?? '-',
            $inventory->branch?->name ?? '-',
            $inventory->batch_no ?? '-',
            optional($inventory->expiry_date)->format('Y-m-d') ?? '-',
            $inventory->available_quantity_base_units,
            number_format((float) $inventory->unit_cost_base, 2),
            number_format((float) ($inventory->available_quantity_base_units * $inventory->unit_cost_base), 2),
            $inventory->status,
        ];
    })->values()->all();

    return $this->exportPayload(
        title: 'Stock Report',
        subtitle: 'Inventory stock report',
        filename: 'stock-report',
        headings: ['Product', 'Branch', 'Batch', 'Expiry', 'Available Qty', 'Unit Cost', 'Stock Value', 'Status'],
        rows: $rows,
        data: $data
    );
}

private function exportPurchases(Request $request): array
{
    $data = $this->purchases($request);

    $rows = collect($data['purchases']->items())->map(function ($purchase) {
        return [
            $purchase->purchase_no,
            $purchase->supplier?->name ?? '-',
            $purchase->branch?->name ?? '-',
            $purchase->items_count,
            number_format((float) $purchase->total_amount, 2),
            number_format((float) $purchase->paid_amount, 2),
            number_format((float) $purchase->balance_amount, 2),
            $purchase->status,
            optional($purchase->purchase_date)->format('Y-m-d'),
        ];
    })->values()->all();

    return $this->exportPayload(
        title: 'Purchase Report',
        subtitle: 'Supplier purchase report',
        filename: 'purchase-report',
        headings: ['Purchase No', 'Supplier', 'Branch', 'Items', 'Total', 'Paid', 'Balance', 'Status', 'Date'],
        rows: $rows,
        data: $data
    );
}

private function exportProfit(Request $request): array
{
    $data = $this->profit($request);

    $rows = collect($data['items']->items())->map(function ($item) {
        return [
            $item->sale?->sale_no ?? '-',
            $item->product?->name ?? '-',
            $item->productUnit?->unit?->name ?? '-',
            $item->quantity,
            number_format((float) $item->line_total, 2),
            number_format((float) $item->total_cost, 2),
            number_format((float) $item->profit_amount, 2),
            optional($item->sale?->sold_at)->format('Y-m-d H:i') ?? '-',
        ];
    })->values()->all();

    return $this->exportPayload(
        title: 'Profit Report',
        subtitle: 'Gross profit and item margin report',
        filename: 'profit-report',
        headings: ['Sale No', 'Product', 'Unit', 'Qty', 'Sales', 'Cost', 'Profit', 'Sold At'],
        rows: $rows,
        data: $data
    );
}

private function exportExpenses(Request $request): array
{
    $data = $this->expenses($request);

    $rows = collect($data['expenses']->items())->map(function ($expense) {
        return [
            $expense->expense_no,
            $expense->title,
            $expense->category?->name ?? '-',
            $expense->branch?->name ?? '-',
            number_format((float) $expense->amount, 2),
            $expense->payment_method,
            $expense->reference_no ?? '-',
            $expense->status,
            optional($expense->expense_date)->format('Y-m-d'),
            $expense->creator?->name ?? $expense->creator?->username ?? '-',
        ];
    })->values()->all();

    return $this->exportPayload(
        title: 'Expense Report',
        subtitle: 'Operating expense report',
        filename: 'expense-report',
        headings: ['Expense No', 'Title', 'Category', 'Branch', 'Amount', 'Payment', 'Reference', 'Status', 'Date', 'Recorded By'],
        rows: $rows,
        data: $data
    );
}

private function exportCenter(Request $request): array
{
    $data = $this->index($request);
    $summary = $data['summary'];

    $rows = [
        ['Total Sales', number_format((float) $summary['sales_total'], 2)],
        ['Gross Profit', number_format((float) $summary['profit_total'], 2)],
        ['Expenses', number_format((float) $summary['expense_total'], 2)],
        ['Net Profit', number_format((float) $summary['net_profit'], 2)],
        ['Purchases', number_format((float) $summary['purchase_total'], 2)],
        ['Cost Sold', number_format((float) $summary['cost_total'], 2)],
    ];

    return $this->exportPayload(
        title: 'Report Center',
        subtitle: 'Business health summary',
        filename: 'report-center',
        headings: ['Metric', 'Value'],
        rows: $rows,
        data: $data
    );
}

private function exportPayload(
    string $title,
    string $subtitle,
    string $filename,
    array $headings,
    array $rows,
    array $data
): array {
    return [
        'title' => $title,
        'subtitle' => $subtitle,
        'filename' => $filename . '-' . now()->format('Ymd-His'),
        'headings' => $headings,
        'rows' => $rows,
        'meta' => [
            'branch' => 'All branches',
            'date_from' => $data['dateFrom'] ?? '-',
            'date_to' => $data['dateTo'] ?? '-',
        ],
    ];
}
    private function guardOwnerReport(): void
    {
        $user = Auth::user();

        if (! $user?->hasAnyRole(['Owner', 'Admin'])) {
            abort(403, 'You do not have permission to view reports.');
        }
    }
}