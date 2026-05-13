<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\Sale;
use App\Services\Pos\PosCatalogService;
use App\Services\Pos\PosCheckoutService;
use App\Services\Pos\PosDashboardService;
use App\Services\Pos\PosExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
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

    public function index(
        PosCatalogService $catalogService,
        PosExpenseService $expenseService
    ): View {
        $pharmacy = Pharmacy::query()->firstOrFail();
        $user = request()->user();

        $branches = $catalogService->branches($pharmacy);
        $defaultBranch = $catalogService->defaultBranch($branches, $user);
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;
        $expenseCategories = $expenseService->categories($pharmacy);

        return view('pos.index', compact(
            'pharmacy',
            'branches',
            'defaultBranch',
            'isAdminOrOwner',
            'expenseCategories',
        ));
    }

    public function searchProducts(Request $request, PosCatalogService $catalogService): JsonResponse
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

        $products = $catalogService->searchProducts(
            pharmacy: $pharmacy,
            branchId: (int) $validated['branch_id'],
            queryText: trim((string) ($validated['q'] ?? '')),
            saleType: $validated['sale_type'] ?? 'retail',
        );

        return response()->json([
            'ok' => true,
            'products' => $products,
        ]);
    }

    public function productUnits(
        Request $request,
        Product $product,
        PosCatalogService $catalogService
    ): JsonResponse {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'sale_type' => ['nullable', Rule::in(['retail', 'wholesale'])],
        ]);

        $data = $catalogService->productUnits(
            pharmacy: $pharmacy,
            product: $product,
            branchId: (int) $validated['branch_id'],
            saleType: $validated['sale_type'] ?? 'retail',
        );

        return response()->json([
            'ok' => true,
            ...$data,
        ]);
    }

    public function checkout(Request $request, PosCheckoutService $checkoutService): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $this->validateCheckout($request, $pharmacy->id);

        try {
            $sale = $checkoutService->checkout($pharmacy, $validated, $request->user());

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

    public function receipt(Sale $sale, PosCheckoutService $checkoutService): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $data = $checkoutService->receiptData($pharmacy, $sale);

        $html = view('pos.partials.receipt', [
            'sale' => $data['sale'],
            'cashierName' => $data['receipt']['cashier'],
        ])->render();

        return response()->json([
            'ok' => true,
            'sale_no' => $sale->sale_no,
            'html' => $html,
        ]);
    }

    public function dayStats(Request $request, PosDashboardService $dashboardService): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        return response()->json([
            'ok' => true,
            'stats' => $dashboardService->dayStats(
                pharmacy: $pharmacy,
                branchId: $request->filled('branch_id') ? (int) $request->input('branch_id') : null,
            ),
        ]);
    }

    public function todaySales(Request $request, PosDashboardService $dashboardService): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
        ]);

        return response()->json([
            'ok' => true,
            'sales' => $dashboardService->todaySales(
                pharmacy: $pharmacy,
                branchId: (int) $validated['branch_id'],
            ),
        ]);
    }

    public function expenses(Request $request, PosExpenseService $expenseService): JsonResponse
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

        return response()->json([
            'ok' => true,
            ...$expenseService->list(
                pharmacy: $pharmacy,
                branchId: (int) $validated['branch_id'],
                user: $request->user(),
                year: isset($validated['year']) ? (int) $validated['year'] : null,
                expenseDate: $validated['expense_date'] ?? null,
            ),
        ]);
    }

    public function storeExpense(Request $request, PosExpenseService $expenseService): JsonResponse
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

        try {
            $expense = $expenseService->store($pharmacy, $validated, $request->user());

            return response()->json([
                'ok' => true,
                'message' => 'Expense recorded successfully.',
                'expense_no' => $expense->expense_no,
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function destroyExpense(
        Request $request,
        Expense $expense,
        PosExpenseService $expenseService
    ): JsonResponse {
        $pharmacy = Pharmacy::query()->firstOrFail();

        try {
            $expenseService->delete($pharmacy, $expense, $request->user());

            return response()->json([
                'ok' => true,
                'message' => 'Expense deleted successfully.',
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    private function validateCheckout(Request $request, int $pharmacyId): array
    {
        return $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacyId),
            ],
            'sale_type' => ['required', Rule::in(['retail', 'wholesale'])],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'payment_method' => ['required', Rule::in(['cash', 'mobile_money', 'card', 'bank', 'credit'])],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'mobile_reference' => ['nullable', 'string', 'max:120'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:50'],
            'synced_at' => ['nullable', 'date'],
            'offline_created_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where('pharmacy_id', $pharmacyId),
            ],
            'items.*.product_unit_id' => [
                'required',
                Rule::exists('product_units', 'id')->where('pharmacy_id', $pharmacyId),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.line_discount' => ['nullable', 'numeric', 'min:0'],
        ]);
    }
}