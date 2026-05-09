<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class QuickSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $user = Auth::user();

        $isAdminOrOwner = $user && method_exists($user, 'hasAnyRole')
            ? $user->hasAnyRole(['Owner', 'Admin'])
            : false;

        $search = trim((string) $request->input('q'));

        if (mb_strlen($search) < 2) {
            return response()->json([
                'ok' => true,
                'results' => [],
            ]);
        }

        $results = collect();

        if (Gate::allows('product.view')) {
            $products = Product::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                })
                ->limit(5)
                ->get()
                ->map(fn ($product) => [
                    'type' => 'Product',
                    'title' => $product->name,
                    'subtitle' => trim(
                        ($product->code ? 'Code: ' . $product->code : '') .
                        ($product->brand ? ' • Brand: ' . $product->brand : '')
                    ),
                    'icon' => 'mdi-pill',
                    'url' => $this->routeUrl('products.index', [
                        'search' => $product->name,
                    ], route('inventory.index', ['search' => $product->name])),
                ]);

            $results = $results->merge($products);
        }

        if (Gate::allows('stock.view') || Gate::allows('inventory_alert.view')) {
            $inventories = Inventory::query()
                ->with(['product', 'branch'])
                ->where('pharmacy_id', $pharmacy->id)
                ->when(! $isAdminOrOwner, fn ($query) => $query->where('branch_id', $user?->branch_id))
                ->where(function ($query) use ($search) {
                    $query->where('batch_no', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%")
                                ->orWhere('barcode', 'like', "%{$search}%")
                                ->orWhere('generic_name', 'like', "%{$search}%")
                                ->orWhere('brand', 'like', "%{$search}%");
                        });
                })
                ->limit(5)
                ->get()
                ->map(fn ($inventory) => [
                    'type' => 'Inventory',
                    'title' => $inventory->product?->name ?: 'Inventory batch',
                    'subtitle' => 'Batch: ' . ($inventory->batch_no ?: '-') .
                        ' • Branch: ' . ($inventory->branch?->name ?: '-') .
                        ' • Qty: ' . number_format((int) $inventory->available_quantity_base_units),
                    'icon' => 'mdi-warehouse',
                    'url' => route('inventory.index', [
                        'search' => $inventory->batch_no ?: $inventory->product?->name,
                    ]),
                ]);

            $results = $results->merge($inventories);
        }

        if (Gate::allows('pos.use') || Gate::allows('sale.view')) {
            $sales = Sale::query()
                ->with('branch')
                ->where('pharmacy_id', $pharmacy->id)
                ->when(! $isAdminOrOwner, fn ($query) => $query->where('branch_id', $user?->branch_id))
                ->where(function ($query) use ($search) {
                    $query->where('sale_no', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                })
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($sale) => [
                    'type' => 'Sale',
                    'title' => $sale->sale_no,
                    'subtitle' => ($sale->customer_name ?: 'Walk-in customer') .
                        ' • ' . ($sale->branch?->name ?: '-') .
                        ' • ' . number_format((float) $sale->total_amount, 2),
                    'icon' => 'mdi-receipt-outline',
                    'url' => route('pos.index', [
                        'receipt' => $sale->sale_no,
                    ]),
                ]);

            $results = $results->merge($sales);
        }

        if (Gate::allows('purchase.view')) {
            $purchases = Purchase::query()
                ->with(['branch', 'supplier'])
                ->where('pharmacy_id', $pharmacy->id)
                ->when(! $isAdminOrOwner, fn ($query) => $query->where('branch_id', $user?->branch_id))
                ->where(function ($query) use ($search) {
                    $query->where('purchase_no', 'like', "%{$search}%")
                        ->orWhere('supplier_invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($supplierQuery) => $supplierQuery->where('name', 'like', "%{$search}%"));
                })
                ->latest()
                ->limit(4)
                ->get()
                ->map(fn ($purchase) => [
                    'type' => 'Purchase',
                    'title' => $purchase->purchase_no,
                    'subtitle' => ($purchase->supplier?->name ?: 'No supplier') .
                        ' • ' . ($purchase->branch?->name ?: '-') .
                        ' • ' . ucfirst((string) $purchase->status),
                    'icon' => 'mdi-cart-arrow-down',
                    'url' => route('purchases.index', [
                        'search' => $purchase->purchase_no,
                    ]),
                ]);

            $results = $results->merge($purchases);
        }

        if (Gate::allows('sales_return.view')) {
            $returns = SalesReturn::query()
                ->with(['branch', 'sale'])
                ->where('pharmacy_id', $pharmacy->id)
                ->when(! $isAdminOrOwner, fn ($query) => $query->where('branch_id', $user?->branch_id))
                ->where(function ($query) use ($search) {
                    $query->where('return_no', 'like', "%{$search}%")
                        ->orWhereHas('sale', fn ($saleQuery) => $saleQuery->where('sale_no', 'like', "%{$search}%"));
                })
                ->latest()
                ->limit(4)
                ->get()
                ->map(fn ($return) => [
                    'type' => 'Sales Return',
                    'title' => $return->return_no,
                    'subtitle' => 'Sale: ' . ($return->sale?->sale_no ?: '-') .
                        ' • ' . ($return->branch?->name ?: '-') .
                        ' • ' . ucfirst((string) $return->status),
                    'icon' => 'mdi-backup-restore',
                    'url' => route('sales-returns.show', $return),
                ]);

            $results = $results->merge($returns);
        }

        if (Gate::allows('stock_adjustment.view')) {
            $adjustments = StockAdjustment::query()
                ->with('branch')
                ->where('pharmacy_id', $pharmacy->id)
                ->when(! $isAdminOrOwner, fn ($query) => $query->where('branch_id', $user?->branch_id))
                ->where(function ($query) use ($search) {
                    $query->where('adjustment_no', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                })
                ->latest()
                ->limit(4)
                ->get()
                ->map(fn ($adjustment) => [
                    'type' => 'Stock Adjustment',
                    'title' => $adjustment->adjustment_no,
                    'subtitle' => ($adjustment->branch?->name ?: '-') .
                        ' • ' . ucfirst((string) $adjustment->status),
                    'icon' => 'mdi-clipboard-edit-outline',
                    'url' => route('stock-adjustments.show', $adjustment),
                ]);

            $results = $results->merge($adjustments);
        }

        if (Gate::allows('stock_transfer.view')) {
            $transfers = StockTransfer::query()
                ->with(['sourceBranch', 'destinationBranch'])
                ->where('pharmacy_id', $pharmacy->id)
                ->when(! $isAdminOrOwner, function ($query) use ($user) {
                    $query->where(function ($q) use ($user) {
                        $q->where('source_branch_id', $user?->branch_id)
                            ->orWhere('destination_branch_id', $user?->branch_id);
                    });
                })
                ->where(function ($query) use ($search) {
                    $query->where('transfer_no', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                })
                ->latest()
                ->limit(4)
                ->get()
                ->map(fn ($transfer) => [
                    'type' => 'Stock Transfer',
                    'title' => $transfer->transfer_no,
                    'subtitle' => ($transfer->sourceBranch?->name ?: '-') .
                        ' → ' . ($transfer->destinationBranch?->name ?: '-') .
                        ' • ' . ucfirst((string) $transfer->status),
                    'icon' => 'mdi-swap-horizontal-bold',
                    'url' => route('stock-transfers.show', $transfer),
                ]);

            $results = $results->merge($transfers);
        }

        if (Gate::allows('expense.view')) {
            $expenses = Expense::query()
                ->with(['branch', 'category'])
                ->where('pharmacy_id', $pharmacy->id)
                ->when(! $isAdminOrOwner, fn ($query) => $query->where('branch_id', $user?->branch_id))
                ->where(function ($query) use ($search) {
                    $query->where('expense_no', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
                })
                ->latest()
                ->limit(4)
                ->get()
                ->map(fn ($expense) => [
                    'type' => 'Expense',
                    'title' => $expense->expense_no,
                    'subtitle' => $expense->title .
                        ' • ' . ($expense->branch?->name ?: '-') .
                        ' • ' . number_format((float) $expense->amount, 2),
                    'icon' => 'mdi-cash-minus',
                    'url' => route('expenses.index', [
                        'search' => $expense->expense_no,
                    ]),
                ]);

            $results = $results->merge($expenses);
        }

        return response()->json([
            'ok' => true,
            'results' => $results->take(20)->values(),
        ]);
    }

    private function routeUrl(string $routeName, array $parameters = [], ?string $fallback = null): string
    {
        if (Route::has($routeName)) {
            return route($routeName, $parameters);
        }

        return $fallback ?: url('/');
    }
}