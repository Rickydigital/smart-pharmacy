<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DailyClosing;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\Inventory\InventorySellingService;
use App\Services\Sale\SaleNumberService;
use Illuminate\Http\JsonResponse;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\SystemNotificationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PosController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:pos.use'),
        ];
    }


    public function index(): View
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

        

        if ($isAdminOrOwner) {
            $defaultBranch = $branches->firstWhere('is_main', true) ?: $branches->first();
        } else {
            $defaultBranch = $branches->firstWhere('id', $user?->branch_id)
                ?: $branches->firstWhere('is_main', true)
                ?: $branches->first();
        }
        $expenseCategories = ExpenseCategory::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();


        return view('pos.index', compact(
            'pharmacy',
            'branches',
            'defaultBranch',
            'isAdminOrOwner',
            'expenseCategories',
        ));
    }

        private function markClosingNeedsRecalculation(int $pharmacyId, int $branchId, string $date, string $reason): void
{
    DailyClosing::query()
        ->where('pharmacy_id', $pharmacyId)
        ->where('branch_id', $branchId)
        ->whereDate('closing_date', $date)
        ->where('status', 'verified')
        ->update([
            'status' => 'needs_recalculation',
            'rejection_reason' => $reason,
            'verified_by' => null,
            'verified_at' => null,
        ]);
}

    public function searchProducts(Request $request, InventorySellingService $inventorySellingService): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'q' => ['nullable', 'string', 'max:120'],
            'sale_type' => ['nullable', Rule::in(['retail', 'wholesale'])],
        ]);

        $queryText = trim((string) ($validated['q'] ?? ''));
        $branchId = (int) $validated['branch_id'];
        $saleType = $validated['sale_type'] ?? 'retail';

        $products = Product::query()
            ->with([
                'productType',
                'category',
                'baseUnit',
                'units.unit',
                'units.prices',
            ])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)

            // Only products that have available inventory in selected branch
            ->whereHas('inventories', function ($inventoryQuery) use ($pharmacy, $branchId) {
                $inventoryQuery
                    ->where('pharmacy_id', $pharmacy->id)
                    ->where('branch_id', $branchId)
                    ->where('status', 'available')
                    ->where('is_active', true)
                    ->where('available_quantity_base_units', '>', 0)
                    ->where(function ($expiryQuery) {
                        $expiryQuery
                            ->whereNull('expiry_date')
                            ->orWhereDate('expiry_date', '>=', now()->toDateString());
                    });
            })

            // Search product, category, and type
            ->when($queryText !== '', function ($query) use ($queryText) {
                $query->where(function ($q) use ($queryText) {
                    $q->where('name', 'like', "%{$queryText}%")
                        ->orWhere('code', 'like', "%{$queryText}%")
                        ->orWhere('barcode', 'like', "%{$queryText}%")
                        ->orWhere('generic_name', 'like', "%{$queryText}%")
                        ->orWhere('brand', 'like', "%{$queryText}%")
                        ->orWhere('strength', 'like', "%{$queryText}%")

                        // Example: antibiotics, pain relief, medical device
                        ->orWhereHas('category', function ($categoryQuery) use ($queryText) {
                            $categoryQuery
                                ->where('name', 'like', "%{$queryText}%")
                                ->orWhere('code', 'like', "%{$queryText}%");
                        })

                        // Example: medicine, medical device, consumable
                        ->orWhereHas('productType', function ($typeQuery) use ($queryText) {
                            $typeQuery
                                ->where('name', 'like', "%{$queryText}%")
                                ->orWhere('code', 'like', "%{$queryText}%");
                        });
                });
            })
            ->orderBy('name')
            ->limit(30)
            ->get();

        $items = $products->map(function (Product $product) use ($inventorySellingService, $pharmacy, $branchId, $saleType) {
            $availableBaseUnits = $inventorySellingService->availableBaseUnits(
                pharmacyId: $pharmacy->id,
                branchId: $branchId,
                productId: $product->id
            );

            $defaultUnit = $product->units
                ->where('is_default_sale_unit', true)
                ->first()
                ?: $product->units->where('is_base', true)->first()
                ?: $product->units->sortBy('quantity_in_base_units')->first();

            $price = $defaultUnit
                ? $this->resolveUnitPrice($product, $defaultUnit, $saleType)
                : 0;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'generic_name' => $product->generic_name,
                'brand' => $product->brand,
                'strength' => $product->strength,
                'type' => $product->productType?->name,
                'category' => $product->category?->name,
                'base_unit' => $product->baseUnit?->name,
                'available_base_units' => $availableBaseUnits,
                'has_stock' => $availableBaseUnits > 0,
                'default_unit' => $defaultUnit ? [
                    'product_unit_id' => $defaultUnit->id,
                    'unit_name' => $defaultUnit->unit?->name,
                    'quantity_in_base_units' => (int) $defaultUnit->quantity_in_base_units,
                    'price' => $price,
                ] : null,
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'products' => $items,
        ]);
    }

    public function productUnits(Request $request, Product $product, InventorySellingService $inventorySellingService): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $product->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }

        $validated = $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'sale_type' => ['nullable', Rule::in(['retail', 'wholesale'])],
        ]);

        $branchId = (int) $validated['branch_id'];
        $saleType = $validated['sale_type'] ?? 'retail';

        $product->load([
            'baseUnit',
            'units.unit',
            'units.prices',
        ]);

        $availableBaseUnits = $inventorySellingService->availableBaseUnits(
            pharmacyId: $pharmacy->id,
            branchId: $branchId,
            productId: $product->id
        );

        $units = $product->units
            ->where('is_active', true)
            ->where('can_sell_' . $saleType, true)
            ->sortBy('quantity_in_base_units')
            ->values()
            ->map(function (ProductUnit $productUnit) use ($product, $saleType, $availableBaseUnits) {
                $quantityInBaseUnits = max(1, (int) $productUnit->quantity_in_base_units);

                return [
                    'product_unit_id' => $productUnit->id,
                    'unit_id' => $productUnit->unit_id,
                    'unit_name' => $productUnit->unit?->name,
                    'quantity_in_base_units' => $quantityInBaseUnits,
                    'is_base' => (bool) $productUnit->is_base,
                    'is_default_sale_unit' => (bool) $productUnit->is_default_sale_unit,
                    'price' => $this->resolveUnitPrice($product, $productUnit, $saleType),
                    'available_sale_units' => intdiv($availableBaseUnits, $quantityInBaseUnits),
                    'available_base_units' => $availableBaseUnits,
                ];
            });

        return response()->json([
            'ok' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'base_unit' => $product->baseUnit?->name,
                'available_base_units' => $availableBaseUnits,
            ],
            'units' => $units,
        ]);
    }

    public function expenses(Request $request): JsonResponse
{
    $pharmacy = Pharmacy::query()->firstOrFail();

    $validated = $request->validate([
        'branch_id' => [
            'required',
            Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
        ],
        'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
        'expense_date' => ['nullable', 'date'],
    ]);

    $user = Auth::user();
    $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

    $branchId = (int) $validated['branch_id'];
    $year = (int) ($validated['year'] ?? now()->year);
    $expenseDate = $validated['expense_date'] ?? null;

    $query = Expense::query()
        ->with(['category', 'creator'])
        ->where('pharmacy_id', $pharmacy->id)
        ->where('branch_id', $branchId)
        ->whereYear('expense_date', $year)
        ->when(! $isAdminOrOwner, function ($q) {
            $q->where('created_by', Auth::id());
        })
        ->when($expenseDate, fn ($q) => $q->whereDate('expense_date', $expenseDate))
        ->latest('expense_date')
        ->latest();

    $expenses = $query->limit(100)->get()->map(function (Expense $expense) use ($isAdminOrOwner, $expenseDate) {
        $isSpecificDay = filled($expenseDate);

        $canDelete = $isSpecificDay
            && ! $expense->isVoided()
            && (
                $isAdminOrOwner
                || (int) $expense->created_by === (int) Auth::id()
            );

        return [
            'id' => $expense->id,
            'expense_no' => $expense->expense_no,
            'title' => $expense->title,
            'category' => $expense->category?->name ?: '-',
            'amount' => (float) $expense->amount,
            'payment_method' => str_replace('_', ' ', ucfirst($expense->payment_method)),
            'status' => ucfirst($expense->status),
            'expense_date' => $expense->expense_date?->format('d M Y'),
            'notes' => $expense->notes,
            'created_by' => $this->userLabel($expense->creator),
            'can_delete' => $canDelete,
            'delete_url' => $canDelete ? route('pos.expenses.destroy', $expense) : null,
        ];
    })->values();

    $summaryQuery = Expense::query()
        ->where('pharmacy_id', $pharmacy->id)
        ->where('branch_id', $branchId)
        ->whereYear('expense_date', $year)
        ->when(! $isAdminOrOwner, function ($q) {
            $q->where('created_by', Auth::id());
        })
        ->when($expenseDate, fn ($q) => $q->whereDate('expense_date', $expenseDate));

    return response()->json([
        'ok' => true,
        'mode' => [
            'is_admin_or_owner' => $isAdminOrOwner,
            'is_specific_day' => filled($expenseDate),
        ],
        'summary' => [
            'count' => (clone $summaryQuery)->count(),
            'paid_total' => (float) (clone $summaryQuery)->where('status', 'paid')->sum('amount'),
            'voided_total' => (float) (clone $summaryQuery)->where('status', 'voided')->sum('amount'),
        ],
        'expenses' => $expenses,
    ]);
}

public function destroyExpense(Request $request, Expense $expense, SystemNotificationService $notifier): JsonResponse
{
    $pharmacy = Pharmacy::query()->firstOrFail();

    if ((int) $expense->pharmacy_id !== (int) $pharmacy->id) {
        abort(403);
    }

    $user = Auth::user();
    $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

    if (! $isAdminOrOwner && (int) $expense->created_by !== (int) Auth::id()) {
        abort(403, 'You can only delete your own POS expense.');
    }

    if ($expense->isVoided()) {
        return response()->json([
            'ok' => false,
            'message' => 'Voided expense cannot be deleted.',
        ], 422);
    }

    /*
     * Optional strict safety:
     * Cashier can delete only today’s own expense.
     * Owner/Admin can delete any day.
     */
    if (! $isAdminOrOwner && ! $expense->expense_date?->isToday()) {
        return response()->json([
            'ok' => false,
            'message' => 'You can only delete your own expense recorded today.',
        ], 403);
    }
    $deletedExpense = $expense->replicate();
    $deletedExpense->id = $expense->id;
    $deletedExpense->exists = true;

    Expense::query()
        ->whereKey($expense->id)
        ->delete();

    $notifier->notifyPosExpenseDeleted($deletedExpense);

    $this->markClosingNeedsRecalculation(
        pharmacyId: $pharmacy->id,
        branchId: (int) $expense->branch_id,
        date: $expense->expense_date?->toDateString() ?: now()->toDateString(),
        reason: 'An expense was deleted after verification. Please recalculate and verify again.'
    );

    return response()->json([
        'ok' => true,
        'message' => 'Expense deleted successfully.',
    ]);
}

    public function storeExpense(Request $request, SystemNotificationService $notifier): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'expense_category_id' => [
                'required',
                Rule::exists('expense_categories', 'id')
                    ->where('pharmacy_id', $pharmacy->id)
                    ->where('is_active', true),
            ],
            'expense_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:160'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(['cash', 'mobile_money', 'card', 'bank'])],
            'reference_no' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ]);

        $expense = Expense::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'branch_id' => $validated['branch_id'],
            'expense_category_id' => $validated['expense_category_id'],
            'expense_no' => $this->generatePosExpenseNumber(),
            'expense_date' => $validated['expense_date'],
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'status' => 'paid',
            'reference_no' => $validated['reference_no'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $this->markClosingNeedsRecalculation(
            pharmacyId: $pharmacy->id,
            branchId: (int) $expense->branch_id,
            date: $expense->expense_date?->toDateString() ?: now()->toDateString(),
            reason: 'New expense was recorded after verification. Please recalculate and verify again.'
        );

        $notifier->notifyPosExpenseCreated($expense);

        return response()->json([
            'ok' => true,
            'message' => 'Expense recorded successfully.',
            'expense_no' => $expense->expense_no,
        ]);
    }

    private function generatePosExpenseNumber(): string
    {
        $prefix = 'EXP-' . now()->format('Ymd');

        $lastExpense = Expense::query()
            ->where('expense_no', 'like', $prefix . '-%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastExpense) {
            $parts = explode('-', $lastExpense->expense_no);
            $nextNumber = ((int) end($parts)) + 1;
        }

        do {
            $expenseNo = $prefix . '-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (Expense::query()->where('expense_no', $expenseNo)->exists());

        return $expenseNo;
    }
    public function todaySales(Request $request): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
        ]);

        $branchId = (int) $validated['branch_id'];

        $sales = Sale::query()
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
                    'receipt_url' => route('pos.sales.receipt', $sale),
                ];
            })
            ->values();

        return response()->json([
            'ok' => true,
            'sales' => $sales,
        ]);
    }

    public function receipt(Sale $sale): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $sale->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }

        $sale->load([
            'pharmacy',
            'branch',
            'creator',
            'items.product',
            'items.productUnit.unit',
        ]);

        $html = view('pos.partials.receipt', [
            'sale' => $sale,
            'cashierName' => $this->userLabel($sale->creator),
        ])->render();

        return response()->json([
            'ok' => true,
            'sale_no' => $sale->sale_no,
            'html' => $html,
        ]);
    }

    private function userLabel($user): string
    {
        if (! $user) {
            return '-';
        }

        if (method_exists($user, 'displayName')) {
            return $user->displayName();
        }

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        return $name ?: ($user->name ?? $user->username ?? $user->email ?? 'User');
    }

    public function dayStats(Request $request): JsonResponse
{
    $pharmacy = Pharmacy::query()->firstOrFail();
    $branchId = $request->input('branch_id');
    $today    = now()->toDateString();
 
    // Revenue & sales count
    $salesQuery = Sale::query()
        ->where('pharmacy_id', $pharmacy->id)
        ->where('status', 'completed')
        ->whereDate('sold_at', $today)
        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
 
    $revenue    = (float) (clone $salesQuery)->sum('total_amount');
    $salesCount = (int)   (clone $salesQuery)->count();
 
    // Expenses
    $expenses = (float) Expense::query()
        ->where('pharmacy_id', $pharmacy->id)
        ->where('status', 'paid')
        ->whereDate('expense_date', $today)
        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
        ->sum('amount');
 
    // Hourly sales (array of 24 floats, index = hour 0–23)
    $hourlySales = array_fill(0, 24, 0.0);
 
    $hourlyRows = (clone $salesQuery)
        ->selectRaw('HOUR(sold_at) as hour_num, SUM(total_amount) as hour_total')
        ->groupByRaw('HOUR(sold_at)')
        ->get();
 
    foreach ($hourlyRows as $row) {
        $h = (int) $row->hour_num;
        if ($h >= 0 && $h < 24) {
            $hourlySales[$h] = (float) $row->hour_total;
        }
    }
 
    return response()->json([
        'ok'    => true,
        'stats' => [
            'revenue'      => $revenue,
            'sales_count'  => $salesCount,
            'expenses'     => $expenses,
            'target'       => 50,            // adjust or make configurable
            'hourly_sales' => $hourlySales,
        ],
    ]);
}
    public function checkout(
        Request $request,
        SaleNumberService $saleNumberService,
        InventorySellingService $inventorySellingService
    ): JsonResponse {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'sale_type' => ['required', Rule::in(['retail', 'wholesale'])],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'payment_method' => ['required', Rule::in(['cash', 'mobile_money', 'card', 'bank', 'credit'])],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'items.*.product_unit_id' => [
                'required',
                Rule::exists('product_units', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.line_discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $sale = DB::transaction(function () use ($validated, $pharmacy, $saleNumberService, $inventorySellingService) {
                $saleType = $validated['sale_type'];
                $branchId = (int) $validated['branch_id'];

                $preparedItems = [];
                $subtotalAmount = 0;
                $itemDiscountAmount = 0;

                foreach ($validated['items'] as $itemData) {
                    $product = Product::query()
                        ->where('pharmacy_id', $pharmacy->id)
                        ->where('is_active', true)
                        ->findOrFail($itemData['product_id']);

                    $productUnit = ProductUnit::query()
                        ->with(['unit', 'prices'])
                        ->where('pharmacy_id', $pharmacy->id)
                        ->where('product_id', $product->id)
                        ->where('is_active', true)
                        ->where('can_sell_' . $saleType, true)
                        ->findOrFail($itemData['product_unit_id']);

                    $quantity = (int) $itemData['quantity'];
                    $quantityInBaseUnits = max(1, (int) $productUnit->quantity_in_base_units);
                    $totalBaseUnits = $quantity * $quantityInBaseUnits;

                    $unitPrice = (float) $itemData['unit_price'];
                    $lineDiscount = (float) ($itemData['line_discount'] ?? 0);
                    $lineSubtotal = $unitPrice * $quantity;
                    $lineTotal = max(0, $lineSubtotal - $lineDiscount);

                    $preview = $inventorySellingService->previewAllocations(
                        pharmacyId: $pharmacy->id,
                        branchId: $branchId,
                        product: $product,
                        productUnit: $productUnit,
                        quantity: $quantity
                    );

                    $preparedItems[] = [
                        'product' => $product,
                        'product_unit' => $productUnit,
                        'quantity' => $quantity,
                        'quantity_in_base_units' => $quantityInBaseUnits,
                        'total_base_units' => $totalBaseUnits,
                        'unit_price' => $unitPrice,
                        'line_discount' => $lineDiscount,
                        'line_total' => $lineTotal,
                        'preview_total_cost' => (float) $preview['total_cost'],
                        'preview_cost_per_base_unit' => (float) $preview['cost_per_base_unit'],
                    ];

                    $subtotalAmount += $lineSubtotal;
                    $itemDiscountAmount += $lineDiscount;
                }

                $saleDiscount = (float) ($validated['discount_amount'] ?? 0);
                $taxAmount = (float) ($validated['tax_amount'] ?? 0);
                $discountAmount = $itemDiscountAmount + $saleDiscount;

                $totalAmount = max(0, $subtotalAmount - $discountAmount + $taxAmount);
                $paidAmount = (float) $validated['paid_amount'];
                $changeAmount = max(0, $paidAmount - $totalAmount);
                $balanceAmount = max(0, $totalAmount - $paidAmount);

                $paymentStatus = 'paid';

                if ($balanceAmount > 0 && $paidAmount > 0) {
                    $paymentStatus = 'partial';
                } elseif ($balanceAmount > 0 && $paidAmount <= 0) {
                    $paymentStatus = 'unpaid';
                }

                /** @var Sale $sale */
                $sale = Sale::query()->create([
                    'pharmacy_id' => $pharmacy->id,
                    'branch_id' => $branchId,
                    'sale_no' => $saleNumberService->generate(),
                    'customer_name' => $validated['customer_name'] ?? null,
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'sale_type' => $saleType,
                    'subtotal_amount' => $subtotalAmount,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'balance_amount' => $balanceAmount,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $paymentStatus,
                    'status' => 'completed',
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => Auth::id(),
                    'sold_at' => now(),
                ]);

                foreach ($preparedItems as $preparedItem) {
                    $sellResult = $inventorySellingService->sell(
                        sale: $sale,
                        product: $preparedItem['product'],
                        productUnit: $preparedItem['product_unit'],
                        quantity: $preparedItem['quantity']
                    );

                    $totalCost = (float) $sellResult['total_cost'];
                    $profitAmount = (float) $preparedItem['line_total'] - $totalCost;

                    SaleItem::query()->create([
                        'pharmacy_id' => $pharmacy->id,
                        'branch_id' => $branchId,
                        'sale_id' => $sale->id,
                        'product_id' => $preparedItem['product']->id,
                        'product_unit_id' => $preparedItem['product_unit']->id,
                        'quantity' => $preparedItem['quantity'],
                        'quantity_in_base_units' => $preparedItem['quantity_in_base_units'],
                        'total_base_units' => $preparedItem['total_base_units'],
                        'unit_price' => $preparedItem['unit_price'],
                        'line_discount' => $preparedItem['line_discount'],
                        'line_tax' => 0,
                        'line_total' => $preparedItem['line_total'],
                        'cost_per_base_unit' => $sellResult['cost_per_base_unit'],
                        'total_cost' => $totalCost,
                        'profit_amount' => $profitAmount,
                        'inventory_allocations' => $sellResult['allocations'],
                    ]);
                }

                activity()
                    ->useLog('sale')
                    ->event('completed')
                    ->performedOn($sale)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'sale_id' => $sale->id,
                        'sale_no' => $sale->sale_no,
                        'total_amount' => $sale->total_amount,
                        'items_count' => count($preparedItems),
                    ])
                    ->log('Sale completed');

                return $sale->fresh(['items.product', 'items.productUnit.unit']);
            });

            $this->markClosingNeedsRecalculation(
                pharmacyId: $pharmacy->id,
                branchId: (int) $sale->branch_id,
                date: $sale->sold_at?->toDateString() ?: now()->toDateString(),
                reason: 'New sale was recorded after verification. Please recalculate and verify again.'
            );

            return response()->json([
                'ok' => true,
                'message' => 'Sale completed successfully.',
                'sale' => [
                    'id' => $sale->id,
                    'sale_no' => $sale->sale_no,
                    'total_amount' => (float) $sale->total_amount,
                    'paid_amount' => (float) $sale->paid_amount,
                    'change_amount' => (float) $sale->change_amount,
                    'balance_amount' => (float) $sale->balance_amount,
                    'payment_status' => $sale->payment_status,
                    'receipt_url' => route('pos.sales.receipt', $sale),
                ],
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }


    private function resolveUnitPrice(Product $product, ProductUnit $productUnit, string $saleType): float
    {
        $directPrice = $productUnit->prices
            ->where('price_type', $saleType)
            ->where('is_active', true)
            ->first();

        if ($directPrice) {
            return (float) $directPrice->price;
        }

        $baseProductUnit = $product->units
            ->where('is_base', true)
            ->first();

        if (! $baseProductUnit) {
            return 0;
        }

        $basePrice = $baseProductUnit->prices
            ->where('price_type', $saleType)
            ->where('is_active', true)
            ->first();

        if (! $basePrice) {
            return 0;
        }

        return (float) $basePrice->price * max(1, (int) $productUnit->quantity_in_base_units);
    }
}
