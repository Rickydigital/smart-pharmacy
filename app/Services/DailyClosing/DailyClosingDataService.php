<?php

namespace App\Services\DailyClosing;

use App\Models\Branch;
use App\Models\DailyClosing;
use App\Models\Expense;
use App\Models\Pharmacy;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\User;
use App\Services\SystemNotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DailyClosingDataService
{
    public function index(Request $request, mixed $user): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');
        $status = $request->input('status');

        $closings = DailyClosing::query()
            ->with(['branch', 'cashier', 'creator', 'verifier'])
            ->where('pharmacy_id', $pharmacy->id)
            ->when(! $isAdminOrOwner, fn($q) => $q->where('created_by', $user?->id))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->whereDate('closing_date', '>=', $dateFrom)
            ->whereDate('closing_date', '<=', $dateTo)
            ->latest('closing_date')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $branches = Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $cashierIds = DailyClosing::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->whereNotNull('cashier_id')
            ->distinct()
            ->pluck('cashier_id')
            ->values();

        $cashiers = User::query()
            ->when($cashierIds->isNotEmpty(), fn($q) => $q->whereIn('id', $cashierIds->all()))
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->orderBy('username', 'asc')
            ->limit(100)
            ->get();

        if ($cashiers->isEmpty()) {
            $cashiers = User::query()
                ->orderBy('first_name', 'asc')
                ->orderBy('last_name', 'asc')
                ->orderBy('username', 'asc')
                ->limit(100)
                ->get();
        }

        $summaryQuery = DailyClosing::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->when(! $isAdminOrOwner, fn($q) => $q->where('created_by', $user?->id))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->whereDate('closing_date', '>=', $dateFrom)
            ->whereDate('closing_date', '<=', $dateTo);

        return [
            'closings' => $closings,
            'branches' => $branches,
            'cashiers' => $cashiers,
            'summary' => [
                'count' => (clone $summaryQuery)->count(),
                'expected_cash' => (float) (clone $summaryQuery)->sum('expected_cash_amount'),
                'counted_cash' => (float) (clone $summaryQuery)->sum('counted_cash_amount'),
                'difference' => (float) (clone $summaryQuery)->sum('difference_amount'),
            ],
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'branchId' => $branchId,
            'branch_id' => $branchId,
            'status' => $status,
            'isAdminOrOwner' => $isAdminOrOwner,
            'is_admin_or_owner' => $isAdminOrOwner,
        ];
    }

    public function validateCalculate(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        return $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'closing_date' => ['required', 'date'],
        ]);
    }

    public function calculate(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();
        $validated = $this->validateCalculate($request);

        return $this->calculateTotals(
            pharmacyId: $pharmacy->id,
            branchId: (int) $validated['branch_id'],
            closingDate: $validated['closing_date']
        );
    }

    public function store(Request $request, mixed $user): DailyClosing
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'closing_date' => ['required', 'date'],
            'counted_cash_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'action' => ['required', Rule::in(['draft', 'submit'])],
        ]);

        $this->ensureDayCanBeClosed(
            pharmacyId: $pharmacy->id,
            branchId: (int) $validated['branch_id'],
            closingDate: $validated['closing_date']
        );

        $totals = $this->calculateTotals(
            pharmacyId: $pharmacy->id,
            branchId: (int) $validated['branch_id'],
            closingDate: $validated['closing_date']
        );

        $countedCash = (float) $validated['counted_cash_amount'];
        $difference = $countedCash - (float) $totals['expected_cash_amount'];
        $status = $validated['action'] === 'submit' ? 'submitted' : 'draft';

        $closing = DailyClosing::query()->updateOrCreate(
            [
                'pharmacy_id' => $pharmacy->id,
                'branch_id' => $validated['branch_id'],
                'closing_date' => $validated['closing_date'],
            ],
            [
                'cashier_id' => null,
                ...$totals,
                'counted_cash_amount' => $countedCash,
                'difference_amount' => $difference,
                'closing_result' => $this->closingResult($difference),
                'status' => $status,
                'submitted_at' => $status === 'submitted' ? now() : null,
                'notes' => $validated['notes'] ?? null,
                'rejection_reason' => null,
                'verified_by' => null,
                'verified_at' => null,
                'created_by' => $user?->id,
            ]
        );

        activity()
            ->useLog('daily_closing')
            ->event($status)
            ->performedOn($closing)
            ->causedBy($user)
            ->withProperties([
                'closing_id' => $closing->id,
                'closing_date' => $closing->closing_date?->toDateString(),
                'branch_id' => $closing->branch_id,
                'created_by' => $user?->id,
            ])
            ->log('Branch daily closing saved');

        if ($status === 'submitted') {
            $closing->load('branch');
            app(SystemNotificationService::class)->notifyDailyClosingSubmitted($closing);
        }

        return $closing;
    }

    public function submit(DailyClosing $dailyClosing, mixed $user): DailyClosing
    {
        $this->guardClosing($dailyClosing);

        if (! $dailyClosing->isDraft() && ! $dailyClosing->isRejected()) {
            abort(422, 'Only draft or rejected closing can be submitted.');
        }

        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        if (! $isAdminOrOwner && (int) $dailyClosing->created_by !== (int) $user?->id) {
            abort(403);
        }

        $dailyClosing->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        $dailyClosing->refresh()->load('branch');
        app(SystemNotificationService::class)->notifyDailyClosingSubmitted($dailyClosing);

        return $dailyClosing;
    }

    public function verify(DailyClosing $dailyClosing, mixed $user): DailyClosing
    {
        $this->guardClosing($dailyClosing);

        if (! $dailyClosing->isSubmitted()) {
            abort(422, 'Only submitted closing can be verified. Recalculate first if it needs recalculation.');
        }

        $dailyClosing->update([
            'status' => 'verified',
            'verified_by' => $user?->id,
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        activity()
            ->useLog('daily_closing')
            ->event('verified')
            ->performedOn($dailyClosing)
            ->causedBy($user)
            ->log('Daily closing verified');

        return $dailyClosing->refresh();
    }

    public function reject(Request $request, DailyClosing $dailyClosing, mixed $user): DailyClosing
    {
        $this->guardClosing($dailyClosing);

        if (! $dailyClosing->isSubmitted()) {
            abort(422, 'Only submitted closing can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $dailyClosing->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'verified_by' => $user?->id,
            'verified_at' => now(),
        ]);

        return $dailyClosing->refresh();
    }

    public function recalculate(DailyClosing $dailyClosing, mixed $user): DailyClosing
    {
        $this->guardClosing($dailyClosing);

        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        if (! $isAdminOrOwner) {
            abort(403);
        }

        if (! in_array($dailyClosing->status, ['needs_recalculation', 'rejected', 'draft'], true)) {
            abort(422, 'Only closings needing recalculation, rejected, or draft closings can be recalculated.');
        }

        $totals = $this->calculateTotals(
            pharmacyId: (int) $dailyClosing->pharmacy_id,
            branchId: (int) $dailyClosing->branch_id,
            closingDate: $dailyClosing->closing_date?->toDateString()
        );

        $countedCash = (float) $dailyClosing->counted_cash_amount;
        $difference = $countedCash - (float) $totals['expected_cash_amount'];

        $dailyClosing->update([
            ...$totals,
            'difference_amount' => $difference,
            'closing_result' => $this->closingResult($difference),
            'status' => 'submitted',
            'submitted_at' => now(),
            'verified_by' => null,
            'verified_at' => null,
            'rejection_reason' => null,
        ]);

        activity()
            ->useLog('daily_closing')
            ->event('recalculated')
            ->performedOn($dailyClosing)
            ->causedBy($user)
            ->log('Daily closing recalculated');

        $dailyClosing->refresh()->load('branch');
        app(SystemNotificationService::class)->notifyDailyClosingSubmitted($dailyClosing);

        return $dailyClosing;
    }

    private function approvedReturnsQuery(int $pharmacyId, int $branchId, string $closingDate)
    {
        return SalesReturn::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('branch_id', $branchId)
            ->where('status', 'approved')
            ->whereDate('return_date', $closingDate);
    }

    private function calculateTotals(int $pharmacyId, int $branchId, string $closingDate): array
    {
        $salesBaseQuery = Sale::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('branch_id', $branchId)
            ->whereIn('status', ['completed', 'partially_returned'])
            ->whereDate(DB::raw('COALESCE(sold_at, created_at)'), $closingDate);

        $cashSales = (float) (clone $salesBaseQuery)
            ->whereRaw('LOWER(payment_method) = ?', ['cash'])
            ->sum('total_amount');

        $mobileSales = (float) (clone $salesBaseQuery)
            ->whereRaw('LOWER(payment_method) = ?', ['mobile_money'])
            ->sum('total_amount');

        $cardSales = (float) (clone $salesBaseQuery)
            ->whereRaw('LOWER(payment_method) = ?', ['card'])
            ->sum('total_amount');

        $bankSales = (float) (clone $salesBaseQuery)
            ->whereRaw('LOWER(payment_method) = ?', ['bank'])
            ->sum('total_amount');

        $creditSales = (float) (clone $salesBaseQuery)
            ->whereRaw('LOWER(payment_method) = ?', ['credit'])
            ->sum('total_amount');

        $totalSales = (float) (clone $salesBaseQuery)->sum('total_amount');
        $totalDiscount = (float) (clone $salesBaseQuery)->sum('discount_amount');

        $returnQuery = $this->approvedReturnsQuery($pharmacyId, $branchId, $closingDate);

        $cashReturns = (float) (clone $returnQuery)->where('refund_method', 'cash')->sum('refund_amount');
        $mobileReturns = (float) (clone $returnQuery)->where('refund_method', 'mobile_money')->sum('refund_amount');
        $cardReturns = (float) (clone $returnQuery)->where('refund_method', 'card')->sum('refund_amount');
        $bankReturns = (float) (clone $returnQuery)->where('refund_method', 'bank')->sum('refund_amount');
        $totalReturns = (float) (clone $returnQuery)->sum('refund_amount');

        $cashSales = max(0, $cashSales - $cashReturns);
        $mobileSales = max(0, $mobileSales - $mobileReturns);
        $cardSales = max(0, $cardSales - $cardReturns);
        $bankSales = max(0, $bankSales - $bankReturns);
        $totalSales = max(0, $totalSales - $totalReturns);

        $expenseBaseQuery = Expense::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('branch_id', $branchId)
            ->where('status', 'paid')
            ->whereDate('expense_date', $closingDate);

        $cashExpenses = (float) (clone $expenseBaseQuery)
            ->whereRaw('LOWER(payment_method) = ?', ['cash'])
            ->sum('amount');

        $totalExpenses = (float) (clone $expenseBaseQuery)->sum('amount');
        $otherExpenses = max(0, $totalExpenses - $cashExpenses);
        $expectedCash = $cashSales - $cashExpenses;

        return [
            'cash_sales_amount' => $cashSales,
            'mobile_money_sales_amount' => $mobileSales,
            'card_sales_amount' => $cardSales,
            'bank_sales_amount' => $bankSales,
            'credit_sales_amount' => $creditSales,
            'total_sales_amount' => $totalSales,
            'total_discount_amount' => $totalDiscount,
            'cash_expenses_amount' => $cashExpenses,
            'other_expenses_amount' => $otherExpenses,
            'total_expenses_amount' => $totalExpenses,
            'expected_cash_amount' => $expectedCash,
        ];
    }

    private function closingResult(float $difference): string
    {
        return $difference < 0 ? 'short' : ($difference > 0 ? 'over' : 'balanced');
    }

    private function ensureDayCanBeClosed(int $pharmacyId, int $branchId, string $closingDate): void
    {
        $existing = DailyClosing::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('branch_id', $branchId)
            ->whereDate('closing_date', $closingDate)
            ->first();

        if (! $existing) {
            return;
        }

        if ($existing->status === 'verified') {
            abort(403, 'This branch closing is already verified and locked.');
        }

        if ($existing->status === 'submitted') {
            abort(403, 'This branch closing is already submitted and waiting for verification.');
        }
    }

    private function guardClosing(DailyClosing $dailyClosing): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $dailyClosing->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }
}
