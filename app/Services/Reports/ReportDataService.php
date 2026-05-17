<?php

namespace App\Services\Reports;

use App\Exports\Reports\ReportTableExport;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockAdjustmentItem;
use App\Models\StockTransferItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            ->selectRaw('product_id, product_unit_id, SUM(quantity) as quantity_sold, SUM(line_total) as sales_amount, SUM(profit_amount) as profit_amount')
            ->where('pharmacy_id', $pharmacy->id)
            ->whereHas('sale', function ($query) use ($dateFrom, $dateTo, $branchId) {
                $query->whereIn('status', ['completed', 'partially_returned'])
                    ->whereDate('sold_at', '>=', $dateFrom)
                    ->whereDate('sold_at', '<=', $dateTo)
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId));
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
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
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
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        $recentMovements = InventoryMovement::query()
            ->with(['product', 'branch', 'creator'])
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
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
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($paymentMethod, fn($q) => $q->where('payment_method', $paymentMethod))
            ->when($saleType, fn($q) => $q->where('sale_type', $saleType));

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

        return compact('sales', 'summary', 'branches', 'dateFrom', 'dateTo', 'branchId', 'paymentMethod', 'saleType');
    }

    public function stock(Request $request): array
    {
        $this->guardOwnerReport();

        $pharmacy = Pharmacy::query()->firstOrFail();

        $branchId = $request->input('branch_id');
        $status = $request->input('status');
        $risk = $request->input('risk');

        $branches = $this->branches($pharmacy->id);

        $query = $this->stockQuery($request, $pharmacy->id);

        $inventories = (clone $query)
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'items' => (clone $query)->count(),
            'available_qty' => (int) (clone $query)->sum('available_quantity_base_units'),
            'stock_value' => (float) ((clone $query)->selectRaw('SUM(available_quantity_base_units * unit_cost_base) as value')->value('value') ?? 0),
            'low_stock' => Inventory::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('available_quantity_base_units', '<=', 10)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->count(),
        ];

        return compact('inventories', 'summary', 'branches', 'branchId', 'status', 'risk');
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
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($status, fn($q) => $q->where('status', $status));

        $purchases = (clone $query)->latest('purchase_date')->paginate(20)->withQueryString();

        $summary = [
            'count' => (clone $query)->count(),
            'total' => (float) (clone $query)->sum('total_amount'),
            'paid' => (float) (clone $query)->sum('paid_amount'),
            'balance' => (float) (clone $query)->sum('balance_amount'),
        ];

        return compact('purchases', 'summary', 'branches', 'dateFrom', 'dateTo', 'branchId', 'status');
    }

    public function profit(Request $request): array
    {
        $this->guardOwnerReport();

        $pharmacy = Pharmacy::query()->firstOrFail();

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');

        $branches = $this->branches($pharmacy->id);

        $query = $this->saleItemsQuery($request, $pharmacy->id);

        $items = (clone $query)->latest()->paginate(20)->withQueryString();

        $expenseQuery = $this->expenseQuery($pharmacy->id, $dateFrom, $dateTo, $branchId);

        $summary = [
            'sales' => (float) (clone $query)->sum('line_total'),
            'cost' => (float) (clone $query)->sum('total_cost'),
            'gross_profit' => (float) (clone $query)->sum('profit_amount'),
            'expenses' => (float) (clone $expenseQuery)->sum('amount'),
        ];

        $summary['net_profit'] = $summary['gross_profit'] - $summary['expenses'];
        $summary['gross_margin'] = $summary['sales'] > 0 ? round(($summary['gross_profit'] / $summary['sales']) * 100, 2) : 0;
        $summary['net_margin'] = $summary['sales'] > 0 ? round(($summary['net_profit'] / $summary['sales']) * 100, 2) : 0;
        $summary['profit'] = $summary['gross_profit'];
        $summary['margin'] = $summary['gross_margin'];

        return compact('items', 'summary', 'branches', 'dateFrom', 'dateTo', 'branchId');
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
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($paymentMethod, fn($q) => $q->where('payment_method', $paymentMethod))
            ->when($status, fn($q) => $q->where('status', $status));

        $expenses = (clone $query)->latest('expense_date')->latest()->paginate(20)->withQueryString();

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

        return compact('expenses', 'summary', 'branches', 'categoryBreakdown', 'dateFrom', 'dateTo', 'branchId', 'paymentMethod', 'status');
    }

    public function prescriptions(Request $request): array
    {
        $this->guardOwnerReport();

        return [
            'message' => 'Prescription module is not implemented yet. This report page is ready for connection after prescriptions are added.',
        ];
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
                new ReportTableExport(
                    $export['title'],
                    $export['headings'],
                    $export['rows'],
                    $export['meta'] ?? [],
                    $export['summary'] ?? []
                ),
                $export['filename'] . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('reports.exports.table-pdf', [
            'title' => $export['title'],
            'subtitle' => $export['subtitle'],
            'headings' => $export['headings'],
            'rows' => $export['rows'],
            'meta' => $export['meta'],
            'summary' => $export['summary'] ?? [],
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
            'prescriptions' => $this->exportPrescriptions($request),
            default => abort(404, 'Report not found.'),
        };
    }

    private function exportSales(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $rows = $this->saleItemsQuery($request, $pharmacy->id)
            ->orderByDesc('id')
            ->get()
            ->map(function (SaleItem $item) {
                $sale = $item->sale;

                return [
                    $sale?->sale_no ?? '-',
                    $sale?->sold_at?->format('Y-m-d H:i') ?? '-',
                    $sale?->branch?->name ?? '-',
                    $this->saleCustomer($sale),
                    ucfirst((string) ($sale?->sale_type ?? '-')),
                    str_replace('_', ' ', ucfirst((string) ($sale?->payment_method ?? '-'))),
                    $item->product?->code ?? '-',
                    $item->product?->name ?? '-',
                    $item->productUnit?->unit?->name ?? '-',
                    (float) $item->quantity,
                    $this->money($item->unit_price ?? 0),
                    $this->money($item->line_total ?? 0),
                    $this->money($item->total_cost ?? 0),
                    $this->money($item->profit_amount ?? 0),
                    $sale?->status ?? '-',
                    $sale?->creator?->name ?? $sale?->creator?->username ?? '-',
                ];
            })
            ->values()
            ->all();

        return $this->exportPayload(
            title: 'Sales Report',
            subtitle: 'Full item-level sales export',
            filename: 'sales-report',
            headings: [
                'Sale No',
                'Sold At',
                'Branch',
                'Customer',
                'Sale Type',
                'Payment',
                'Product Code',
                'Product',
                'Unit',
                'Qty',
                'Unit Price',
                'Sales Value',
                'Cost Value',
                'Profit',
                'Status',
                'Cashier',
            ],
            rows: $rows,
            request: $request,
            summary: [
                'rows' => count($rows),
                'sales_value' => $this->sumColumn($rows, 11),
                'cost_value' => $this->sumColumn($rows, 12),
                'profit' => $this->sumColumn($rows, 13),
            ]
        );
    }

    private function exportStock(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $isPdf = $request->input('format', 'pdf') === 'pdf';

        $inventories = $this->stockQuery($request, $pharmacy->id)
            ->with([
                'branch',
                'purchase.supplier',
                'purchaseItem.productUnit.unit',
                'product.category',
                'product.productUnits.unit',
                'product.productUnits.prices',
                'product.baseUnit',
                'movements',
            ])
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->latest()
            ->get();

        $rows = $inventories->map(function (Inventory $inventory) use ($isPdf) {
            $product = $inventory->product;
            $prices = $this->basePrices($inventory);
            $movement = $this->inventoryMovementSummary($inventory);

            $availableQty = (int) $inventory->available_quantity_base_units;
            $unitCost = (float) $inventory->unit_cost_base;
            $availableCostValue = $availableQty * $unitCost;

            $expectedRetailValue = $availableQty * $prices['retail'];
            $expectedWholesaleValue = $availableQty * $prices['wholesale'];

            $sales = $this->inventorySalesValue($inventory);
            $actualSalesValue = $sales['retail_sales'] + $sales['wholesale_sales'];
            $soldCost = $movement['sold'] * $unitCost;
            $profit = $actualSalesValue - $soldCost;

            if ($isPdf) {
                return [
                    $product?->name ?? '-',
                    $inventory->branch?->name ?? '-',
                    $inventory->batch_no ?? '-',
                    $inventory->expiry_date?->format('Y-m-d') ?? '-',
                    $product?->baseUnit?->name ?? '-',
                    (int) $inventory->received_quantity_base_units,
                    $movement['sold'],
                    $availableQty,
                    $inventory->status,
                ];
            }

            return [
                $product?->code ?? '-',
                $product?->name ?? '-',
                $product?->generic_name ?? '-',
                $product?->category?->name ?? '-',
                $inventory->branch?->name ?? '-',
                $inventory->batch_no ?? '-',
                $inventory->expiry_date?->format('Y-m-d') ?? '-',
                $product?->baseUnit?->name ?? '-',
                $this->configuredUnits($inventory),
                (int) $inventory->received_quantity_base_units,
                $this->money($inventory->total_cost ?? 0),
                $movement['sold'],
                $movement['returned'],
                $movement['expired'],
                $movement['adjusted_in'],
                $movement['adjusted_out'],
                $movement['transferred_in'],
                $movement['transferred_out'],
                $availableQty,
                $this->money($unitCost),
                $this->money($availableCostValue),
                $this->money($prices['retail']),
                $this->money($prices['wholesale']),
                $this->money($expectedRetailValue),
                $this->money($expectedWholesaleValue),
                $this->money($sales['retail_sales']),
                $this->money($sales['wholesale_sales']),
                $this->money($actualSalesValue),
                $this->money($profit),
                $inventory->status,
            ];
        })->values()->all();

        $headings = $isPdf
            ? [
                'Product',
                'Branch',
                'Batch No',
                'Expiry',
                'Base Unit',
                'Purchased Qty',
                'Sold Qty',
                'Available Qty',
                'Status',
            ]
            : [
                'Product Code',
                'Product Name',
                'Generic Name',
                'Category',
                'Branch',
                'Batch No',
                'Expiry Date',
                'Base Unit',
                'Configured Units',
                'Purchased Qty Base',
                'Purchased Value',
                'Sold Qty Base',
                'Returned Qty Base',
                'Expired Qty Base',
                'Adjusted In',
                'Adjusted Out',
                'Transferred In',
                'Transferred Out',
                'Available Qty Base',
                'Cost/Base',
                'Available Cost Value',
                'Retail Price/Base',
                'Wholesale Price/Base',
                'Expected Retail Value',
                'Expected Wholesale Value',
                'Actual Retail Sales',
                'Actual Wholesale Sales',
                'Total Sales Value',
                'Gross Profit',
                'Status',
            ];

        return $this->exportPayload(
            title: 'Inventory Report',
            subtitle: $isPdf
                ? 'Inventory availability and batch movement summary'
                : 'Batch-level inventory control, value, movement and profit report',
            filename: 'stock-report',
            headings: $headings,
            rows: $rows,
            request: $request,
            summary: [
                'batches' => count($rows),
                'available_qty_base' => $inventories->sum('available_quantity_base_units'),
            ]
        );
    }
    private function exportPurchases(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');
        $status = $request->input('status');

        $rows = PurchaseItem::query()
            ->with(['purchase.branch', 'purchase.supplier', 'purchase.creator', 'product', 'productUnit.unit'])
            ->where('pharmacy_id', $pharmacy->id)
            ->whereHas('purchase', function ($query) use ($dateFrom, $dateTo, $branchId, $status) {
                $query->whereDate('purchase_date', '>=', $dateFrom)
                    ->whereDate('purchase_date', '<=', $dateTo)
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->when($status, fn($q) => $q->where('status', $status));
            })
            ->latest()
            ->get()
            ->map(function (PurchaseItem $item) {
                $purchase = $item->purchase;

                return [
                    $purchase?->purchase_no ?? '-',
                    $purchase?->purchase_date?->format('Y-m-d') ?? '-',
                    $purchase?->received_date?->format('Y-m-d') ?? '-',
                    $purchase?->supplier?->name ?? '-',
                    $purchase?->branch?->name ?? '-',
                    $item->product?->code ?? '-',
                    $item->product?->name ?? '-',
                    $item->batch_no ?? '-',
                    $item->expiry_date?->format('Y-m-d') ?? '-',
                    $item->productUnit?->unit?->name ?? '-',
                    (float) $item->quantity,
                    (int) $item->quantity_in_base_units,
                    (int) $item->total_base_units,
                    $this->money($item->unit_cost ?? 0),
                    $this->money($item->line_discount ?? 0),
                    $this->money($item->line_tax ?? 0),
                    $this->money($item->line_total ?? 0),
                    $purchase?->payment_status ?? '-',
                    $purchase?->status ?? '-',
                    $purchase?->creator?->name ?? $purchase?->creator?->username ?? '-',
                ];
            })
            ->values()
            ->all();

        return $this->exportPayload(
            title: 'Purchase Report',
            subtitle: 'Full item-level purchase export',
            filename: 'purchase-report',
            headings: [
                'Purchase No',
                'Purchase Date',
                'Received Date',
                'Supplier',
                'Branch',
                'Product Code',
                'Product',
                'Batch No',
                'Expiry Date',
                'Purchased Unit',
                'Qty',
                'Qty In Base Units',
                'Total Base Units',
                'Unit Cost',
                'Discount',
                'Tax',
                'Line Total',
                'Payment Status',
                'Purchase Status',
                'Created By',
            ],
            rows: $rows,
            request: $request,
            summary: [
                'rows' => count($rows),
                'purchase_value' => $this->sumColumn($rows, 16),
            ]
        );
    }

    private function exportProfit(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $rows = $this->saleItemsQuery($request, $pharmacy->id)
            ->orderByDesc('id')
            ->get()
            ->map(function (SaleItem $item) {
                $returns = $this->returnSummaryForSaleItem($item);
                $grossProfit = (float) $item->profit_amount;
                $netProfit = $grossProfit - $returns['profit_reversed'];

                return [
                    $item->sale?->sale_no ?? '-',
                    $item->sale?->sold_at?->format('Y-m-d H:i') ?? '-',
                    $item->sale?->branch?->name ?? '-',
                    ucfirst((string) ($item->sale?->sale_type ?? '-')),
                    $item->product?->code ?? '-',
                    $item->product?->name ?? '-',
                    $item->productUnit?->unit?->name ?? '-',
                    (float) $item->quantity,
                    $this->money($item->line_total ?? 0),
                    $this->money($item->total_cost ?? 0),
                    $this->money($grossProfit),
                    $returns['qty'],
                    $this->money($returns['refund']),
                    $this->money($returns['cost']),
                    $this->money($returns['profit_reversed']),
                    $this->money($netProfit),
                ];
            })
            ->values()
            ->all();

        return $this->exportPayload(
            title: 'Profit Report',
            subtitle: 'Item-level profit, return impact and net profit export',
            filename: 'profit-report',
            headings: [
                'Sale No',
                'Sold At',
                'Branch',
                'Sale Type',
                'Product Code',
                'Product',
                'Unit',
                'Qty Sold',
                'Sales Value',
                'Cost Value',
                'Gross Profit',
                'Returned Qty',
                'Refund Amount',
                'Return Cost',
                'Profit Reversed',
                'Net Profit',
            ],
            rows: $rows,
            request: $request,
            summary: [
                'rows' => count($rows),
                'sales_value' => $this->sumColumn($rows, 8),
                'cost_value' => $this->sumColumn($rows, 9),
                'gross_profit' => $this->sumColumn($rows, 10),
                'profit_reversed' => $this->sumColumn($rows, 14),
                'net_profit' => $this->sumColumn($rows, 15),
            ]
        );
    }

    private function exportExpenses(Request $request): array
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
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($paymentMethod, fn($q) => $q->where('payment_method', $paymentMethod))
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest('expense_date')
            ->get()
            ->map(function ($expense) {
                return [
                    $expense->expense_no ?? '-',
                    $expense->expense_date?->format('Y-m-d') ?? '-',
                    $expense->title ?? '-',
                    $expense->category?->name ?? '-',
                    $expense->branch?->name ?? '-',
                    $this->money($expense->amount ?? 0),
                    str_replace('_', ' ', ucfirst((string) $expense->payment_method)),
                    $expense->reference_no ?? '-',
                    $expense->status ?? '-',
                    $expense->creator?->name ?? $expense->creator?->username ?? '-',
                    $expense->notes ?? '-',
                ];
            })
            ->values()
            ->all();

        return $this->exportPayload(
            title: 'Expense Report',
            subtitle: 'Full operating expense export',
            filename: 'expense-report',
            headings: [
                'Expense No',
                'Date',
                'Title',
                'Category',
                'Branch',
                'Amount',
                'Payment Method',
                'Reference No',
                'Status',
                'Recorded By',
                'Notes',
            ],
            rows: $rows,
            request: $request,
            summary: [
                'rows' => count($rows),
                'expense_total' => $this->sumColumn($rows, 5),
            ]
        );
    }

    private function exportCenter(Request $request): array
    {
        $data = $this->index($request);
        $summary = $data['summary'];

        $rows = [
            ['Business Summary', 'Total Sales', $this->money($summary['sales_total']), 'Completed less approved returns'],
            ['Business Summary', 'Gross Profit', $this->money($summary['profit_total']), 'Sales profit less reversed return profit'],
            ['Business Summary', 'Expenses', $this->money($summary['expense_total']), 'Paid operating expenses'],
            ['Business Summary', 'Net Profit', $this->money($summary['net_profit']), 'Gross profit minus expenses'],
            ['Business Summary', 'Purchases', $this->money($summary['purchase_total']), 'Supplier purchases'],
            ['Business Summary', 'Cost Sold', $this->money($summary['cost_total']), 'Inventory cost of sold items'],
            ['Business Summary', 'Gross Margin', $summary['gross_margin'] . '%', '-'],
            ['Business Summary', 'Net Margin', $summary['net_margin'] . '%', '-'],
        ];

        foreach ($data['paymentBreakdown'] as $row) {
            $rows[] = ['Payment Breakdown', $row->payment_method, $this->money($row->total_amount), $row->sales_count . ' receipt(s)'];
        }

        foreach ($data['saleTypeBreakdown'] as $row) {
            $rows[] = ['Sale Type Breakdown', $row->sale_type, $this->money($row->total_amount), $row->sales_count . ' receipt(s)'];
        }

        foreach ($data['expenseBreakdown'] as $row) {
            $rows[] = ['Expense Breakdown', $row->category?->name ?? 'Uncategorized', $this->money($row->total_amount), $row->expenses_count . ' record(s)'];
        }

        foreach ($data['topProducts'] as $row) {
            $rows[] = ['Top Products', $row->product?->name ?? '-', $this->money($row->sales_amount), 'Qty: ' . $row->quantity_sold];
        }

        foreach ($data['lowStock'] as $inventory) {
            $rows[] = ['Low Stock', $inventory->product?->name ?? '-', $inventory->available_quantity_base_units, $inventory->branch?->name ?? '-'];
        }

        foreach ($data['expiringSoon'] as $inventory) {
            $rows[] = ['Expiring Soon', $inventory->product?->name ?? '-', $inventory->expiry_date?->format('Y-m-d') ?? '-', $inventory->branch?->name ?? '-'];
        }

        return $this->exportPayload(
            title: 'Report Center',
            subtitle: 'Business health summary export',
            filename: 'report-center',
            headings: ['Section', 'Metric', 'Value', 'Note'],
            rows: $rows,
            request: $request,
            summary: [
                'sales_total' => $this->money($summary['sales_total']),
                'gross_profit' => $this->money($summary['profit_total']),
                'expenses' => $this->money($summary['expense_total']),
                'net_profit' => $this->money($summary['net_profit']),
            ]
        );
    }

    private function exportPrescriptions(Request $request): array
    {
        return $this->exportPayload(
            title: 'Prescription Report',
            subtitle: 'Prescription module is not implemented yet',
            filename: 'prescription-report',
            headings: ['Message'],
            rows: [['Prescription module is not implemented yet.']],
            request: $request
        );
    }

    private function stockQuery(Request $request, int $pharmacyId)
    {
        $branchId = $request->input('branch_id');
        $status = $request->input('status');
        $risk = $request->input('risk');

        return Inventory::query()
            ->with(['product.baseUnit', 'branch', 'purchase'])
            ->where('pharmacy_id', $pharmacyId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($risk === 'low', fn($q) => $q->where('available_quantity_base_units', '<=', 10))
            ->when($risk === 'expired', fn($q) => $q->whereDate('expiry_date', '<', now()->toDateString()))
            ->when($risk === 'expiring', function ($q) {
                $q->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '>=', now()->toDateString())
                    ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString());
            });
    }

    private function saleItemsQuery(Request $request, int $pharmacyId)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');
        $paymentMethod = $request->input('payment_method');
        $saleType = $request->input('sale_type');

        return SaleItem::query()
            ->with(['sale.branch', 'sale.creator', 'product', 'productUnit.unit'])
            ->where('pharmacy_id', $pharmacyId)
            ->whereHas('sale', function ($q) use ($dateFrom, $dateTo, $branchId, $paymentMethod, $saleType) {
                $q->whereIn('status', ['completed', 'partially_returned'])
                    ->whereDate('sold_at', '>=', $dateFrom)
                    ->whereDate('sold_at', '<=', $dateTo)
                    ->when($branchId, fn($saleQuery) => $saleQuery->where('branch_id', $branchId))
                    ->when($paymentMethod, fn($saleQuery) => $saleQuery->where('payment_method', $paymentMethod))
                    ->when($saleType, fn($saleQuery) => $saleQuery->where('sale_type', $saleType));
            });
    }

    private function approvedReturnItemsQuery(int $pharmacyId, string $dateFrom, string $dateTo, mixed $branchId)
    {
        return SalesReturnItem::query()
            ->where('sales_return_items.pharmacy_id', $pharmacyId)
            ->whereHas('salesReturn', function ($query) use ($dateFrom, $dateTo, $branchId) {
                $query->where('status', 'approved')
                    ->whereDate('return_date', '>=', $dateFrom)
                    ->whereDate('return_date', '<=', $dateTo)
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId));
            });
    }

    private function completedSalesQuery(int $pharmacyId, string $dateFrom, string $dateTo, mixed $branchId)
    {
        return Sale::query()
            ->where('pharmacy_id', $pharmacyId)
            ->whereIn('status', ['completed', 'partially_returned'])
            ->whereDate('sold_at', '>=', $dateFrom)
            ->whereDate('sold_at', '<=', $dateTo)
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId));
    }

    private function purchaseQuery(int $pharmacyId, string $dateFrom, string $dateTo, mixed $branchId)
    {
        return Purchase::query()
            ->where('pharmacy_id', $pharmacyId)
            ->whereDate('purchase_date', '>=', $dateFrom)
            ->whereDate('purchase_date', '<=', $dateTo)
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId));
    }

    private function profitItemsQuery(int $pharmacyId, string $dateFrom, string $dateTo, mixed $branchId)
    {
        return SaleItem::query()
            ->where('pharmacy_id', $pharmacyId)
            ->whereHas('sale', function ($query) use ($dateFrom, $dateTo, $branchId) {
                $query->whereIn('status', ['completed', 'partially_returned'])
                    ->whereDate('sold_at', '>=', $dateFrom)
                    ->whereDate('sold_at', '<=', $dateTo)
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId));
            });
    }

    private function expenseQuery(int $pharmacyId, string $dateFrom, string $dateTo, mixed $branchId)
    {
        return Expense::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('status', 'paid')
            ->whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo)
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId));
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

    private function configuredUnits(Inventory $inventory): string
    {
        return $inventory->product?->productUnits
            ? $inventory->product->productUnits
            ->sortBy('quantity_in_base_units')
            ->map(fn($unit) => ($unit->unit?->name ?? '-') . ': ' . (int) $unit->quantity_in_base_units)
            ->implode(' | ')
            : '-';
    }

    private function basePrices(Inventory $inventory): array
    {
        $product = $inventory->product;

        if (! $product || ! $product->productUnits) {
            return ['retail' => 0.0, 'wholesale' => 0.0];
        }

        $baseUnit = $product->productUnits->firstWhere('is_base', true)
            ?: $product->productUnits->sortBy('quantity_in_base_units')->first();

        if (! $baseUnit) {
            return ['retail' => 0.0, 'wholesale' => 0.0];
        }

        $retail = (float) ($baseUnit->prices?->firstWhere('price_type', 'retail')?->price ?? 0);
        $wholesale = (float) ($baseUnit->prices?->firstWhere('price_type', 'wholesale')?->price ?? 0);

        return [
            'retail' => $retail,
            'wholesale' => $wholesale,
        ];
    }

    private function inventoryMovementSummary(Inventory $inventory): array
    {
        $movements = $inventory->movements ?? collect();

        $sold = (int) $movements
            ->filter(fn($m) => $m->direction === 'out' && ($m->movement_type === 'sale_out' || $m->source_type === Sale::class))
            ->sum('quantity_base_units');

        $returned = (int) $movements
            ->filter(fn($m) => $m->direction === 'in' && in_array($m->movement_type, ['sale_return_in', 'return_in'], true))
            ->sum('quantity_base_units');

        $adjustedIn = (int) $movements
            ->filter(fn($m) => $m->direction === 'in' && str_contains((string) $m->movement_type, 'adjustment'))
            ->sum('quantity_base_units');

        $adjustedOut = (int) $movements
            ->filter(fn($m) => $m->direction === 'out' && str_contains((string) $m->movement_type, 'adjustment'))
            ->sum('quantity_base_units');

        $expired = (int) StockAdjustmentItem::query()
            ->where('inventory_id', $inventory->id)
            ->where('direction', 'out')
            ->whereHas('stockAdjustment', fn($q) => $q->where('status', 'approved')->where('adjustment_type', 'expiry'))
            ->sum('quantity_base_units');

        if ($expired <= 0 && $inventory->status === 'expired') {
            $expired = (int) $inventory->available_quantity_base_units;
        }

        $transferredOut = (int) StockTransferItem::query()
            ->where('source_inventory_id', $inventory->id)
            ->whereHas('stockTransfer', fn($q) => $q->whereIn('status', ['dispatched', 'received']))
            ->sum('quantity_base_units');

        $transferredIn = (int) StockTransferItem::query()
            ->where('destination_inventory_id', $inventory->id)
            ->whereHas('stockTransfer', fn($q) => $q->where('status', 'received'))
            ->sum('quantity_base_units');

        return [
            'sold' => $sold,
            'returned' => $returned,
            'expired' => $expired,
            'adjusted_in' => $adjustedIn,
            'adjusted_out' => $adjustedOut,
            'transferred_in' => $transferredIn,
            'transferred_out' => $transferredOut,
        ];
    }

    private function inventorySalesValue(Inventory $inventory): array
    {
        $retailSales = 0.0;
        $wholesaleSales = 0.0;

        $saleMovements = ($inventory->movements ?? collect())
            ->filter(fn($m) => $m->direction === 'out' && ($m->movement_type === 'sale_out' || $m->source_type === Sale::class));

        foreach ($saleMovements as $movement) {
            if (! $movement->source_id) {
                continue;
            }

            $saleItems = SaleItem::query()
                ->with(['sale', 'productUnit'])
                ->where('sale_id', $movement->source_id)
                ->where('product_id', $inventory->product_id)
                ->get();

            $saleItem = $saleItems->first();

            if (! $saleItem) {
                continue;
            }

            $baseUnits = max(1, (int) $saleItem->quantity * max(1, (int) ($saleItem->productUnit?->quantity_in_base_units ?? 1)));
            $pricePerBase = ((float) $saleItem->line_total) / $baseUnits;
            $movementSales = $pricePerBase * (int) $movement->quantity_base_units;

            if ($saleItem->sale?->sale_type === 'wholesale') {
                $wholesaleSales += $movementSales;
            } else {
                $retailSales += $movementSales;
            }
        }

        return [
            'retail_sales' => $retailSales,
            'wholesale_sales' => $wholesaleSales,
        ];
    }

    private function returnSummaryForSaleItem(SaleItem $item): array
    {
        $items = SalesReturnItem::query()
            ->where('sale_item_id', $item->id)
            ->whereHas('salesReturn', fn($q) => $q->where('status', 'approved'))
            ->get();

        return [
            'qty' => (float) $items->sum('quantity'),
            'refund' => (float) $items->sum('refund_amount'),
            'cost' => (float) $items->sum('total_cost'),
            'profit_reversed' => (float) $items->sum('profit_reversed'),
        ];
    }

    private function saleCustomer(?Sale $sale): string
    {
        if (! $sale) {
            return '-';
        }

        if (method_exists($sale, 'displayCustomer')) {
            return (string) $sale->displayCustomer();
        }

        return $sale->customer_name ?? $sale->customer?->name ?? '-';
    }

    private function exportPayload(
        string $title,
        string $subtitle,
        string $filename,
        array $headings,
        array $rows,
        Request $request,
        array $summary = []
    ): array {
        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'filename' => $filename . '-' . now()->format('Ymd-His'),
            'headings' => $headings,
            'rows' => $rows,
            'summary' => $summary,
            'meta' => [
                'branch' => $this->branchName($request->input('branch_id')),
                'date_from' => $request->input('date_from', '-'),
                'date_to' => $request->input('date_to', '-'),
                'status' => $request->input('status', '-'),
                'payment_method' => $request->input('payment_method', '-'),
                'sale_type' => $request->input('sale_type', '-'),
            ],
        ];
    }

    private function branchName(mixed $branchId): string
    {
        if (! $branchId) {
            return 'All branches';
        }

        return Branch::query()->whereKey($branchId)->value('name') ?? 'Selected branch';
    }

    private function money(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function sumColumn(array $rows, int $index): string
    {
        return $this->money(collect($rows)->sum(fn($row) => (float) str_replace(',', '', (string) ($row[$index] ?? 0))));
    }

    private function guardOwnerReport(): void
    {
        $user = Auth::user();

        if (! $user?->hasAnyRole(['Owner', 'Admin'])) {
            abort(403, 'You do not have permission to view reports.');
        }
    }
}
