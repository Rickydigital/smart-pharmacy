<?php

namespace App\Services\Pos;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Pharmacy;
use App\Models\User;
use App\Services\SystemNotificationService;
use Illuminate\Support\Collection;
use RuntimeException;

class PosExpenseService
{
    public function __construct(
        private PosCheckoutService $checkoutService,
        private SystemNotificationService $notifier,
    ) {
    }

    public function categories(Pharmacy $pharmacy): Collection
    {
        return ExpenseCategory::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function list(
        Pharmacy $pharmacy,
        int $branchId,
        ?User $user,
        ?int $year = null,
        ?string $expenseDate = null,
    ): array {
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;
        $year = $year ?: now()->year;

        $query = Expense::query()
            ->with(['category', 'creator'])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('branch_id', $branchId)
            ->whereYear('expense_date', $year)
            ->when(! $isAdminOrOwner, fn ($q) => $q->where('created_by', $user?->id))
            ->when($expenseDate, fn ($q) => $q->whereDate('expense_date', $expenseDate))
            ->latest('expense_date')
            ->latest();

        $expenses = $query->limit(100)->get()->map(function (Expense $expense) use ($isAdminOrOwner, $expenseDate, $user) {
            $isSpecificDay = filled($expenseDate);

            $canDelete = $isSpecificDay
                && ! $expense->isVoided()
                && (
                    $isAdminOrOwner
                    || (int) $expense->created_by === (int) $user?->id
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
            ];
        })->values();

        $summaryQuery = Expense::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('branch_id', $branchId)
            ->whereYear('expense_date', $year)
            ->when(! $isAdminOrOwner, fn ($q) => $q->where('created_by', $user?->id))
            ->when($expenseDate, fn ($q) => $q->whereDate('expense_date', $expenseDate));

        return [
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
        ];
    }

    public function store(Pharmacy $pharmacy, array $data, ?User $user): Expense
    {
        $expense = Expense::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'branch_id' => $data['branch_id'],
            'expense_category_id' => $data['expense_category_id'],
            'expense_no' => $this->generatePosExpenseNumber(),
            'expense_date' => $data['expense_date'],
            'title' => $data['title'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'status' => 'paid',
            'reference_no' => $data['reference_no'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $user?->id,
        ]);

        $this->checkoutService->markClosingNeedsRecalculation(
            pharmacyId: $pharmacy->id,
            branchId: (int) $expense->branch_id,
            date: $expense->expense_date?->toDateString() ?: now()->toDateString(),
            reason: 'New expense was recorded after verification. Please recalculate and verify again.'
        );

        $this->notifier->notifyPosExpenseCreated($expense);

        return $expense;
    }

    public function delete(Pharmacy $pharmacy, Expense $expense, ?User $user): void
    {
        if ((int) $expense->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }

        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        if (! $isAdminOrOwner && (int) $expense->created_by !== (int) $user?->id) {
            abort(403, 'You can only delete your own POS expense.');
        }

        if ($expense->isVoided()) {
            throw new RuntimeException('Voided expense cannot be deleted.');
        }

        if (! $isAdminOrOwner && ! $expense->expense_date?->isToday()) {
            abort(403, 'You can only delete your own expense recorded today.');
        }

        $deletedExpense = $expense->replicate();
        $deletedExpense->id = $expense->id;
        $deletedExpense->exists = true;

        $branchId = (int) $expense->branch_id;
        $date = $expense->expense_date?->toDateString() ?: now()->toDateString();

         Expense::query()
            ->whereKey($expense->id)
            ->delete();

        $this->notifier->notifyPosExpenseDeleted($deletedExpense);

        $this->checkoutService->markClosingNeedsRecalculation(
            pharmacyId: $pharmacy->id,
            branchId: $branchId,
            date: $date,
            reason: 'An expense was deleted after verification. Please recalculate and verify again.'
        );
    }

    private function generatePosExpenseNumber(): string
    {
        $prefix = 'EXP-'.now()->format('Ymd');

        $lastExpense = Expense::query()
            ->where('expense_no', 'like', $prefix.'-%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = $lastExpense
            ? ((int) last(explode('-', $lastExpense->expense_no))) + 1
            : 1;

        do {
            $expenseNo = $prefix.'-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (Expense::query()->where('expense_no', $expenseNo)->exists());

        return $expenseNo;
    }

    private function userLabel($user): string
    {
        if (! $user) {
            return '-';
        }

        if (method_exists($user, 'displayName')) {
            return $user->displayName();
        }

        $name = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));

        return $name ?: ($user->name ?? $user->username ?? $user->email ?? 'User');
    }
}