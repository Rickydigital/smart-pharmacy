<?php

namespace App\Http\Controllers;

use App\Exports\Reports\ReportTableExport;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturnItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:report.view', only: ['index', 'export']),
            new Middleware('permission:report.sales', only: ['sales']),
            new Middleware('permission:report.stock', only: ['stock']),
            new Middleware('permission:report.purchase', only: ['purchases']),
            new Middleware('permission:report.profit', only: ['profit']),
            new Middleware('permission:report.expense', only: ['expenses']),
            new Middleware('permission:report.prescription', only: ['prescriptions']),
        ];
    }

    public function index(Request $request): View
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

        return view('reports.index', compact(
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
        ));
    }

    public function sales(Request $request): View
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

        return view('reports.sales', compact(
            'sales',
            'summary',
            'branches',
            'dateFrom',
            'dateTo',
            'branchId',
            'paymentMethod',
            'saleType'
        ));
    }

    public function stock(Request $request): View
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

        return view('reports.stock', compact(
            'inventories',
            'summary',
            'branches',
            'branchId',
            'status',
            'risk'
        ));
    }

    public function purchases(Request $request): View
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

        return view('reports.purchases', compact(
            'purchases',
            'summary',
            'branches',
            'dateFrom',
            'dateTo',
            'branchId',
            'status'
        ));
    }

    public function profit(Request $request): View
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

        // Compatibility with your existing profit Blade if it still uses these keys.
        $summary['profit'] = $summary['gross_profit'];
        $summary['margin'] = $summary['gross_margin'];

        return view('reports.profit', compact(
            'items',
            'summary',
            'branches',
            'dateFrom',
            'dateTo',
            'branchId'
        ));
    }

    public function expenses(Request $request): View
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

        return view('reports.expenses', compact(
            'expenses',
            'summary',
            'branches',
            'categoryBreakdown',
            'dateFrom',
            'dateTo',
            'branchId',
            'paymentMethod',
            'status'
        ));
    }

    public function prescriptions(Request $request): View
    {
        $this->guardOwnerReport();

        return view('reports.prescriptions', [
            'message' => 'Prescription module is not implemented yet. This report page is ready for connection after prescriptions are added.',
        ]);
    }

    public function export(Request $request, string $report): BinaryFileResponse|HttpResponse|RedirectResponse
    {
        $this->guardOwnerReport();

        $format = $request->input('format', 'pdf');

        if (! in_array($format, ['pdf', 'excel'], true)) {
            return back()->with('error', 'Invalid export format.');
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
        ])->setPaper('a4', $export['orientation']);

        return $pdf->stream($export['filename'] . '.pdf');
    }

    private function buildExportData(Request $request, string $report): array
    {
        return match ($report) {
            'center' => $this->centerExport($request),
            'sales' => $this->salesExport($request),
            'stock' => $this->stockExport($request),
            'purchases' => $this->purchasesExport($request),
            'profit' => $this->profitExport($request),
            'expenses' => $this->expensesExport($request),
            default => abort(404),
        };
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
    private function centerExport(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');

        $salesQuery = $this->completedSalesQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);
        $profitQuery = $this->profitItemsQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);
        $purchaseQuery = $this->purchaseQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);
        $expenseQuery = $this->expenseQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);

        $salesTotal = (float) (clone $salesQuery)->sum('total_amount');
        $grossProfit = (float) (clone $profitQuery)->sum('profit_amount');
        $cost = (float) (clone $profitQuery)->sum('total_cost');
        $expenseTotal = (float) (clone $expenseQuery)->sum('amount');
        $netProfit = $grossProfit - $expenseTotal;

        $rows = [
            ['Total Sales', number_format($salesTotal, 2)],
            ['Sales Receipts', number_format((clone $salesQuery)->count())],
            ['Cost Sold', number_format($cost, 2)],
            ['Gross Profit', number_format($grossProfit, 2)],
            ['Expenses', number_format($expenseTotal, 2)],
            ['Net Profit', number_format($netProfit, 2)],
            ['Gross Margin %', $salesTotal > 0 ? number_format(($grossProfit / $salesTotal) * 100, 2) . '%' : '0.00%'],
            ['Net Margin %', $salesTotal > 0 ? number_format(($netProfit / $salesTotal) * 100, 2) . '%' : '0.00%'],
            ['Purchase Total', number_format((float) (clone $purchaseQuery)->sum('total_amount'), 2)],
            ['Purchase Count', number_format((clone $purchaseQuery)->count())],
        ];

        return [
            'title' => 'Report Center Summary',
            'subtitle' => 'Owner business summary',
            'filename' => 'report-center-summary-' . now()->format('YmdHis'),
            'headings' => ['Metric', 'Value'],
            'rows' => $rows,
            'orientation' => 'portrait',
            'meta' => $this->exportMeta($dateFrom, $dateTo, $branchId),
        ];
    }

    private function salesExport(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');
        $paymentMethod = $request->input('payment_method');
        $saleType = $request->input('sale_type');

        $rows = Sale::query()
            ->with(['branch', 'creator'])
            ->withCount('items')
            ->where('pharmacy_id', $pharmacy->id)
            ->whereDate('sold_at', '>=', $dateFrom)
            ->whereDate('sold_at', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($paymentMethod, fn ($q) => $q->where('payment_method', $paymentMethod))
            ->when($saleType, fn ($q) => $q->where('sale_type', $saleType))
            ->latest('sold_at')
            ->get()
            ->map(fn (Sale $sale) => [
                $sale->sale_no,
                $sale->branch?->name ?: '-',
                $sale->displayCustomer(),
                $sale->items_count,
                ucfirst($sale->sale_type),
                str_replace('_', ' ', ucfirst($sale->payment_method)),
                ucfirst($sale->status),
                number_format((float) $sale->subtotal_amount, 2),
                number_format((float) $sale->discount_amount, 2),
                number_format((float) $sale->total_amount, 2),
                $sale->sold_at?->format('d M Y h:i A') ?: '-',
            ])
            ->toArray();

        return [
            'title' => 'Sales Report',
            'subtitle' => 'Detailed sales report',
            'filename' => 'sales-report-' . now()->format('YmdHis'),
            'headings' => ['Receipt', 'Branch', 'Customer', 'Items', 'Sale Type', 'Payment', 'Status', 'Subtotal', 'Discount', 'Total', 'Date'],
            'rows' => $rows,
            'orientation' => 'landscape',
            'meta' => $this->exportMeta($dateFrom, $dateTo, $branchId),
        ];
    }

    private function stockExport(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $branchId = $request->input('branch_id');
        $status = $request->input('status');
        $risk = $request->input('risk');

        $rows = Inventory::query()
            ->with(['product.baseUnit', 'branch'])
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($risk === 'low', fn ($q) => $q->where('available_quantity_base_units', '<=', 10))
            ->when($risk === 'expired', fn ($q) => $q->whereDate('expiry_date', '<', now()->toDateString()))
            ->when($risk === 'expiring', function ($q) {
                $q->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '>=', now()->toDateString())
                    ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString());
            })
            ->orderBy('product_id')
            ->get()
            ->map(fn (Inventory $inventory) => [
                $inventory->product?->name ?: '-',
                $inventory->product?->baseUnit?->name ?: '-',
                $inventory->branch?->name ?: '-',
                $inventory->batch_no ?: '-',
                $inventory->expiry_date?->format('d M Y') ?: '-',
                number_format((int) $inventory->received_quantity_base_units),
                number_format((int) $inventory->available_quantity_base_units),
                number_format((float) $inventory->unit_cost_base, 2),
                number_format((float) $inventory->available_quantity_base_units * (float) $inventory->unit_cost_base, 2),
                ucfirst($inventory->status),
            ])
            ->toArray();

        return [
            'title' => 'Stock Report',
            'subtitle' => 'Current stock report',
            'filename' => 'stock-report-' . now()->format('YmdHis'),
            'headings' => ['Product', 'Base Unit', 'Branch', 'Batch', 'Expiry', 'Received Qty', 'Available Qty', 'Cost/Base', 'Stock Value', 'Status'],
            'rows' => $rows,
            'orientation' => 'landscape',
            'meta' => $this->exportMeta(null, null, $branchId),
        ];
    }

    private function purchasesExport(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');
        $status = $request->input('status');

        $rows = Purchase::query()
            ->with(['branch', 'supplier'])
            ->withCount('items')
            ->where('pharmacy_id', $pharmacy->id)
            ->whereDate('purchase_date', '>=', $dateFrom)
            ->whereDate('purchase_date', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('purchase_date')
            ->get()
            ->map(fn (Purchase $purchase) => [
                $purchase->purchase_no,
                $purchase->supplier_invoice_no ?: '-',
                $purchase->supplier?->name ?: '-',
                $purchase->branch?->name ?: '-',
                $purchase->items_count,
                number_format((float) $purchase->subtotal_amount, 2),
                number_format((float) $purchase->discount_amount, 2),
                number_format((float) $purchase->total_amount, 2),
                number_format((float) $purchase->paid_amount, 2),
                number_format((float) $purchase->balance_amount, 2),
                ucfirst($purchase->status),
                $purchase->purchase_date?->format('d M Y') ?: '-',
            ])
            ->toArray();

        return [
            'title' => 'Purchase Report',
            'subtitle' => 'Supplier purchase report',
            'filename' => 'purchase-report-' . now()->format('YmdHis'),
            'headings' => ['Purchase No', 'Invoice No', 'Supplier', 'Branch', 'Items', 'Subtotal', 'Discount', 'Total', 'Paid', 'Balance', 'Status', 'Date'],
            'rows' => $rows,
            'orientation' => 'landscape',
            'meta' => $this->exportMeta($dateFrom, $dateTo, $branchId),
        ];
    }

    private function profitExport(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');

        $profitQuery = $this->profitItemsQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);
        $expenseQuery = $this->expenseQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);

        $sales = (float) (clone $profitQuery)->sum('line_total');
        $cost = (float) (clone $profitQuery)->sum('total_cost');
        $grossProfit = (float) (clone $profitQuery)->sum('profit_amount');
        $expenses = (float) (clone $expenseQuery)->sum('amount');
        $netProfit = $grossProfit - $expenses;

        $summaryRows = [
            ['SUMMARY', '', '', '', '', '', '', '', ''],
            ['Sales', '', '', '', '', number_format($sales, 2), '', '', ''],
            ['Cost Sold', '', '', '', '', number_format($cost, 2), '', '', ''],
            ['Gross Profit', '', '', '', '', number_format($grossProfit, 2), '', '', ''],
            ['Expenses', '', '', '', '', number_format($expenses, 2), '', '', ''],
            ['Net Profit', '', '', '', '', number_format($netProfit, 2), '', '', ''],
            ['', '', '', '', '', '', '', '', ''],
        ];

        $itemRows = (clone $profitQuery)
            ->with(['sale.branch', 'product', 'productUnit.unit'])
            ->latest()
            ->get()
            ->map(fn (SaleItem $item) => [
                $item->sale?->sale_no ?: '-',
                $item->sale?->branch?->name ?: '-',
                $item->product?->name ?: '-',
                $item->productUnit?->unit?->name ?: '-',
                number_format((int) $item->quantity),
                number_format((float) $item->line_total, 2),
                number_format((float) $item->total_cost, 2),
                number_format((float) $item->profit_amount, 2),
                $item->sale?->sold_at?->format('d M Y') ?: '-',
            ])
            ->toArray();

        return [
            'title' => 'Profit Report',
            'subtitle' => 'Gross profit, expenses and net profit report',
            'filename' => 'profit-report-' . now()->format('YmdHis'),
            'headings' => ['Receipt', 'Branch', 'Product', 'Unit', 'Qty', 'Sales', 'Cost', 'Profit', 'Date'],
            'rows' => array_merge($summaryRows, $itemRows),
            'orientation' => 'landscape',
            'meta' => $this->exportMeta($dateFrom, $dateTo, $branchId),
        ];
    }

    private function expensesExport(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');
        $paymentMethod = $request->input('payment_method');
        $status = $request->input('status', 'paid');

        $rows = Expense::query()
            ->with(['branch', 'category', 'creator'])
            ->where('pharmacy_id', $pharmacy->id)
            ->whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($paymentMethod, fn ($q) => $q->where('payment_method', $paymentMethod))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('expense_date')
            ->get()
            ->map(fn (Expense $expense) => [
                $expense->expense_no,
                $expense->branch?->name ?: '-',
                $expense->category?->name ?: '-',
                $expense->title,
                str_replace('_', ' ', ucfirst($expense->payment_method)),
                ucfirst($expense->status),
                number_format((float) $expense->amount, 2),
                $expense->reference_no ?: '-',
                $expense->creator?->displayName() ?: ($expense->creator?->username ?? '-'),
                $expense->expense_date?->format('d M Y') ?: '-',
            ])
            ->toArray();

        return [
            'title' => 'Expense Report',
            'subtitle' => 'Operating expense report',
            'filename' => 'expense-report-' . now()->format('YmdHis'),
            'headings' => ['Expense No', 'Branch', 'Category', 'Title', 'Payment', 'Status', 'Amount', 'Reference', 'Recorded By', 'Date'],
            'rows' => $rows,
            'orientation' => 'landscape',
            'meta' => $this->exportMeta($dateFrom, $dateTo, $branchId),
        ];
    }

    private function exportMeta(?string $dateFrom, ?string $dateTo, mixed $branchId): array
    {
        $branchName = 'All branches';

        if ($branchId) {
            $branchName = Branch::query()->whereKey($branchId)->value('name') ?: 'Selected branch';
        }

        return [
            'branch' => $branchName,
            'date_from' => $dateFrom ?: '-',
            'date_to' => $dateTo ?: '-',
        ];
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

    private function guardOwnerReport(): void
    {
        $user = Auth::user();

        if (! $user?->hasAnyRole(['Owner', 'Admin'])) {
            abort(403);
        }
    }

    private function branches(int $pharmacyId)
    {
        return Branch::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();
    }
}