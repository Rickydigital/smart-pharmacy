<?php

namespace App\Console\Commands;

use App\Models\DailyClosing;
use App\Models\Pharmacy;
use App\Models\Purchase;
use App\Models\SalesReturn;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Services\SystemNotificationService;
use Illuminate\Console\Command;

class DetectPendingSystemActions extends Command
{
    protected $signature = 'system:detect-pending-actions {--pharmacy_id=}';

    protected $description = 'Detect pending system actions and notify permitted users';

    public function handle(SystemNotificationService $notifier): int
    {
        $pharmacies = Pharmacy::query()
            ->when(
                $this->option('pharmacy_id'),
                fn ($query) => $query->whereKey($this->option('pharmacy_id'))
            )
            ->get();

        foreach ($pharmacies as $pharmacy) {
            $this->line('Checking: ' . $pharmacy->name);

            /*
             |--------------------------------------------------------------------------
             | Daily Closings - Submitted
             |--------------------------------------------------------------------------
             */
            $submittedClosings = DailyClosing::query()
                ->with('branch')
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'submitted')
                ->latest()
                ->limit(100)
                ->get();

            foreach ($submittedClosings as $closing) {
                $notifier->notifyDailyClosingSubmitted($closing);
            }

            /*
             |--------------------------------------------------------------------------
             | Daily Closings - Needs Recalculation
             |--------------------------------------------------------------------------
             */
            $recalculationClosings = DailyClosing::query()
                ->with('branch')
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'needs_recalculation')
                ->latest()
                ->limit(100)
                ->get();

            foreach ($recalculationClosings as $closing) {
                $notifier->notifyDailyClosingNeedsRecalculation($closing);
            }

            /*
             |--------------------------------------------------------------------------
             | Sales Returns - Draft
             |--------------------------------------------------------------------------
             */
            $salesReturns = SalesReturn::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'draft')
                ->latest()
                ->limit(100)
                ->get();

            foreach ($salesReturns as $return) {
                $notifier->notifySalesReturnCreated($return);
            }

            /*
             |--------------------------------------------------------------------------
             | Stock Adjustments - Draft
             |--------------------------------------------------------------------------
             */
            $stockAdjustments = StockAdjustment::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'draft')
                ->latest()
                ->limit(100)
                ->get();

            foreach ($stockAdjustments as $adjustment) {
                $notifier->notifyStockAdjustmentCreated($adjustment);
            }

            /*
             |--------------------------------------------------------------------------
             | Stock Transfers - Draft
             |--------------------------------------------------------------------------
             */
            $draftTransfers = StockTransfer::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'draft')
                ->latest()
                ->limit(100)
                ->get();

            foreach ($draftTransfers as $transfer) {
                $notifier->notifyStockTransferCreated($transfer);
            }

            /*
             |--------------------------------------------------------------------------
             | Stock Transfers - Approved
             |--------------------------------------------------------------------------
             */
            $approvedTransfers = StockTransfer::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'approved')
                ->latest()
                ->limit(100)
                ->get();

            foreach ($approvedTransfers as $transfer) {
                $notifier->notifyStockTransferApproved($transfer);
            }

            /*
             |--------------------------------------------------------------------------
             | Stock Transfers - Dispatched
             |--------------------------------------------------------------------------
             */
            $dispatchedTransfers = StockTransfer::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'dispatched')
                ->latest()
                ->limit(100)
                ->get();

            foreach ($dispatchedTransfers as $transfer) {
                $notifier->notifyStockTransferDispatched($transfer);
            }

            /*
             |--------------------------------------------------------------------------
             | Purchases - Draft
             |--------------------------------------------------------------------------
             */
            $draftPurchases = Purchase::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'draft')
                ->latest()
                ->limit(100)
                ->get();

            foreach ($draftPurchases as $purchase) {
                $notifier->notifyPurchaseCreated($purchase);
            }

            /*
             |--------------------------------------------------------------------------
             | Expenses
             |--------------------------------------------------------------------------
             | Current ExpenseController records expenses as paid/voided,
             | not pending approval.
             |
             | If later you add expense status = submitted/pending_approval,
             | then add expense notification here.
             */
        }

        $this->info('Pending action detection completed.');

        return self::SUCCESS;
    }
}