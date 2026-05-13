<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\MobileCheckoutRequest;
use App\Http\Resources\Api\ProductResource;
use App\Http\Resources\Api\SaleResource;
use App\Models\Expense;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\Sale;
use App\Services\Pos\PosCatalogService;
use App\Services\Pos\PosCheckoutService;
use App\Services\Pos\PosDashboardService;
use App\Services\Pos\PosExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PosController extends ApiController
{
    public function index(
        PosCatalogService $catalogService,
        PosExpenseService $expenseService
    ): JsonResponse {
        $pharmacy = Pharmacy::query()->firstOrFail();
        $user = request()->user();

        $branches = $catalogService->branches($pharmacy);
        $defaultBranch = $catalogService->defaultBranch($branches, $user);

        return $this->success([
            'pharmacy' => $pharmacy,
            'branches' => $branches,
            'default_branch' => $defaultBranch,
            'is_admin_or_owner' => $user?->hasAnyRole(['Admin', 'Owner']) ?? false,
            'expense_categories' => $expenseService->categories($pharmacy),
        ]);
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

        return $this->success([
            'products' => $catalogService->searchProducts(
                pharmacy: $pharmacy,
                branchId: (int) $validated['branch_id'],
                queryText: trim((string) ($validated['q'] ?? '')),
                saleType: $validated['sale_type'] ?? 'retail',
            ),
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

        return $this->success(
            $catalogService->productUnits(
                pharmacy: $pharmacy,
                product: $product,
                branchId: (int) $validated['branch_id'],
                saleType: $validated['sale_type'] ?? 'retail',
            )
        );
    }

    public function showProduct(Product $product): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $product->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }

        $product->load([
            'productType',
            'category',
            'baseUnit',
            'units.unit',
            'units.prices',
            'inventories.branch',
        ]);

        return $this->success(new ProductResource($product));
    }

    public function checkout(
        MobileCheckoutRequest $request,
        PosCheckoutService $checkoutService
    ): JsonResponse {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validated();
        $mobileReference = trim((string) ($validated['mobile_reference'] ?? ''));

        $existingSale = $checkoutService->existingByMobileReference(
            pharmacy: $pharmacy,
            mobileReference: $mobileReference,
        );

        if ($existingSale) {
            return $this->success([
                'sale' => new SaleResource($existingSale),
                'idempotent' => true,
            ], 'Sale already synced.');
        }

        try {
            $sale = $checkoutService->checkout(
                pharmacy: $pharmacy,
                data: $validated,
                user: $request->user(),
            );

            return $this->success([
                'sale' => new SaleResource($sale),
                'sale_id' => $sale->id,
                'sale_no' => $sale->sale_no,
                'idempotent' => false,
            ], 'Sale completed successfully.', 201);
        } catch (\Throwable $exception) {
            $existingSale = $checkoutService->existingByMobileReference(
                pharmacy: $pharmacy,
                mobileReference: $mobileReference,
            );

            if ($existingSale) {
                return $this->success([
                    'sale' => new SaleResource($existingSale),
                    'idempotent' => true,
                ], 'Sale already synced.');
            }

            return $this->error($exception->getMessage(), 422);
        }
    }

    public function receipt(Sale $sale, PosCheckoutService $checkoutService): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $data = $checkoutService->receiptData($pharmacy, $sale);

        return $this->success([
            'sale' => new SaleResource($data['sale']),
            'receipt' => $data['receipt'],
        ]);
    }

    public function dayStats(Request $request, PosDashboardService $dashboardService): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        return $this->success([
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

        return $this->success([
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

        return $this->success(
            $expenseService->list(
                pharmacy: $pharmacy,
                branchId: (int) $validated['branch_id'],
                user: $request->user(),
                year: isset($validated['year']) ? (int) $validated['year'] : null,
                expenseDate: $validated['expense_date'] ?? null,
            )
        );
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

            return $this->success([
                'expense_id' => $expense->id,
                'expense_no' => $expense->expense_no,
                'expense' => $expense,
            ], 'Expense recorded successfully.', 201);
        } catch (\Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    public function destroyExpense(
        Request $request,
        Expense $expense,
        PosExpenseService $expenseService
    ): JsonResponse {
        $pharmacy = Pharmacy::query()->firstOrFail();

        try {
            $expenseId = $expense->id;

            $expenseService->delete($pharmacy, $expense, $request->user());

            return $this->success([
                'expense_id' => $expenseId,
            ], 'Expense deleted successfully.');
        } catch (\Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }
}