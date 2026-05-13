<?php

namespace App\Services\Expense;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExpenseDataService
{
    public function index(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $expenses = Expense::query()
            ->with(['branch', 'category', 'creator', 'voider'])
            ->where('pharmacy_id', $pharmacy->id)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('expense_no', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->input('branch_id')))
            ->when($request->filled('expense_category_id'), fn ($q) => $q->where('expense_category_id', $request->input('expense_category_id')))
            ->when($request->filled('payment_method'), fn ($q) => $q->where('payment_method', $request->input('payment_method')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('expense_date', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('expense_date', '<=', $request->input('date_to')))
            ->latest('expense_date')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summaryQuery = Expense::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->input('branch_id')))
            ->when($request->filled('expense_category_id'), fn ($q) => $q->where('expense_category_id', $request->input('expense_category_id')))
            ->when($request->filled('payment_method'), fn ($q) => $q->where('payment_method', $request->input('payment_method')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('expense_date', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('expense_date', '<=', $request->input('date_to')));

        $summary = [
            'count' => (clone $summaryQuery)->count(),
            'paid_total' => (float) (clone $summaryQuery)->where('status', 'paid')->sum('amount'),
            'voided_total' => (float) (clone $summaryQuery)->where('status', 'voided')->sum('amount'),
            'today_total' => (float) Expense::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'paid')
                ->whereDate('expense_date', now()->toDateString())
                ->sum('amount'),
        ];

        $branches = Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name', 'asc')
            ->get();

        $categories = ExpenseCategory::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->orderByDesc('is_active')
            ->orderBy('name', 'asc')
            ->get();

        return [
            'expenses' => $expenses,
            'summary' => $summary,
            'branches' => $branches,
            'categories' => $categories,
            'activeCategories' => $categories->where('is_active', true)->values(),
            'active_categories' => $categories->where('is_active', true)->values(),
        ];
    }

    public function store(Request $request, mixed $user): Expense
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id)],
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

        return Expense::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'branch_id' => $validated['branch_id'],
            'expense_category_id' => $validated['expense_category_id'],
            'expense_no' => $this->generateExpenseNumber(),
            'expense_date' => $validated['expense_date'],
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'status' => 'paid',
            'reference_no' => $validated['reference_no'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $user?->id ?? Auth::id(),
        ]);
    }

    public function update(Request $request, Expense $expense): Expense
    {
        $this->guardExpense($expense);

        if ($expense->isVoided()) {
            abort(403, 'Voided expense cannot be edited.');
        }

        $validated = $request->validate([
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('pharmacy_id', $expense->pharmacy_id)],
            'expense_category_id' => [
                'required',
                Rule::exists('expense_categories', 'id')
                    ->where('pharmacy_id', $expense->pharmacy_id)
                    ->where('is_active', true),
            ],
            'expense_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:160'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(['cash', 'mobile_money', 'card', 'bank'])],
            'reference_no' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ]);

        $expense->update($validated);

        return $expense->refresh();
    }

    public function void(Request $request, Expense $expense, mixed $user): Expense
    {
        $this->guardExpense($expense);

        if ($expense->isVoided()) {
            abort(403, 'Expense is already voided.');
        }

        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:500'],
        ]);

        $expense->update([
            'status' => 'voided',
            'voided_by' => $user?->id ?? Auth::id(),
            'voided_at' => now(),
            'void_reason' => $validated['void_reason'],
        ]);

        return $expense->refresh();
    }

    public function storeCategory(Request $request, mixed $user): ExpenseCategory
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('expense_categories', 'name')->where('pharmacy_id', $pharmacy->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return ExpenseCategory::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'name' => $validated['name'],
            'code' => $this->generateCategoryCode($pharmacy->id, $validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $user?->id ?? Auth::id(),
        ]);
    }

    public function updateCategory(Request $request, ExpenseCategory $expenseCategory): ExpenseCategory
    {
        $this->guardCategory($expenseCategory);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('expense_categories', 'name')
                    ->where('pharmacy_id', $expenseCategory->pharmacy_id)
                    ->ignore($expenseCategory->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $expenseCategory->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return $expenseCategory->refresh();
    }

    public function toggleCategory(ExpenseCategory $expenseCategory): ExpenseCategory
    {
        $this->guardCategory($expenseCategory);

        $expenseCategory->update([
            'is_active' => ! $expenseCategory->is_active,
        ]);

        return $expenseCategory->refresh();
    }

    private function generateExpenseNumber(): string
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

    private function generateCategoryCode(int $pharmacyId, string $name): string
    {
        $base = Str::upper(Str::slug($name, '_')) ?: 'EXPENSE_CATEGORY';
        $code = $base;
        $counter = 1;

        while (
            ExpenseCategory::query()
                ->where('pharmacy_id', $pharmacyId)
                ->where('code', $code)
                ->exists()
        ) {
            $code = $base . '_' . $counter;
            $counter++;
        }

        return $code;
    }

    private function guardExpense(Expense $expense): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $expense->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }

    private function guardCategory(ExpenseCategory $expenseCategory): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $expenseCategory->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }
}