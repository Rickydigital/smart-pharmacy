<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\DailyClosing;
use App\Models\Expense;
use App\Models\Pharmacy;
use App\Models\Purchase;
use App\Models\SalesReturn;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\User;
use App\Notifications\SystemActionNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class SystemNotificationService
{
    public function notifyUsersWithPermission(
        int $pharmacyId,
        string $permission,
        string $title,
        string $message,
        ?string $url = null,
        string $type = 'system',
        string $severity = 'info',
        ?string $pendingKey = null,
        array $meta = []
    ): void {
        $users = User::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('status', 'active')
            ->permission($permission)
            ->get();

        $this->sendOnce($users, $title, $message, $url, $type, $severity, $pendingKey, $meta);
    }

    public function notifyOwnersAndAdmins(
        int $pharmacyId,
        string $title,
        string $message,
        ?string $url = null,
        string $type = 'system',
        string $severity = 'info',
        ?string $pendingKey = null,
        array $meta = []
    ): void {
        $users = User::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('status', 'active')
            ->role(['Owner', 'Admin'])
            ->get();

        $this->sendOnce($users, $title, $message, $url, $type, $severity, $pendingKey, $meta);
    }

    public function notifyBranchUsersWithPermission(
        int $pharmacyId,
        int $branchId,
        string $permission,
        string $title,
        string $message,
        ?string $url = null,
        string $type = 'system',
        string $severity = 'info',
        ?string $pendingKey = null,
        array $meta = []
    ): void {
        $users = User::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->permission($permission)
            ->get();

        $this->sendOnce($users, $title, $message, $url, $type, $severity, $pendingKey, $meta);
    }

    public function notifyDailyClosingSubmitted(DailyClosing $closing): void
    {
        $this->notifyUsersWithPermission(
            pharmacyId: (int) $closing->pharmacy_id,
            permission: 'daily_closing.verify',
            title: 'Daily closing needs verification',
            message: 'Daily closing for ' . ($closing->branch?->name ?: 'branch') . ' is waiting for verification.',
            url: route('daily-closings.index', ['status' => 'submitted']),
            type: 'daily_closing',
            severity: 'warning',
            pendingKey: 'daily_closing_verify_' . $closing->id,
            meta: [
                'daily_closing_id' => $closing->id,
                'branch_id' => $closing->branch_id,
                'status' => $closing->status,
            ]
        );
    }

    public function notifyDailyClosingNeedsRecalculation(DailyClosing $closing): void
    {
        $this->notifyUsersWithPermission(
            pharmacyId: (int) $closing->pharmacy_id,
            permission: 'daily_closing.verify',
            title: 'Daily closing needs recalculation',
            message: 'A verified closing changed because of sales, expenses or returns. Recalculation is required.',
            url: route('daily-closings.index', ['status' => 'needs_recalculation']),
            type: 'daily_closing',
            severity: 'critical',
            pendingKey: 'daily_closing_recalculate_' . $closing->id,
            meta: [
                'daily_closing_id' => $closing->id,
                'branch_id' => $closing->branch_id,
                'status' => $closing->status,
            ]
        );
    }

    public function notifyExpenseCreated(Expense $expense): void
    {
        $this->notifyOwnersAndAdmins(
            pharmacyId: (int) $expense->pharmacy_id,
            title: 'Expense recorded',
            message: 'Expense ' . $expense->expense_no . ' was recorded and needs owner review.',
            url: route('expenses.index', ['search' => $expense->expense_no]),
            type: 'expense',
            severity: 'info',
            pendingKey: 'expense_review_' . $expense->id,
            meta: [
                'expense_id' => $expense->id,
                'branch_id' => $expense->branch_id,
                'amount' => $expense->amount,
                'status' => $expense->status,
            ]
        );
    }

    public function notifyPurchaseCreated(Purchase $purchase): void
    {
        $this->notifyOwnersAndAdmins(
            pharmacyId: (int) $purchase->pharmacy_id,
            title: 'Purchase created',
            message: 'Purchase ' . $purchase->purchase_no . ' was created and may need receiving/payment review.',
            url: route('purchases.index', ['search' => $purchase->purchase_no]),
            type: 'purchase',
            severity: 'info',
            pendingKey: 'purchase_review_' . $purchase->id,
            meta: [
                'purchase_id' => $purchase->id,
                'branch_id' => $purchase->branch_id,
                'status' => $purchase->status,
            ]
        );
    }

    public function notifySalesReturnCreated(SalesReturn $salesReturn): void
    {
        $this->notifyUsersWithPermission(
            pharmacyId: (int) $salesReturn->pharmacy_id,
            permission: 'sales_return.approve',
            title: 'Sales return needs approval',
            message: 'Sales return ' . $salesReturn->return_no . ' is waiting for approval.',
            url: route('sales-returns.show', $salesReturn),
            type: 'sales_return',
            severity: 'warning',
            pendingKey: 'sales_return_approve_' . $salesReturn->id,
            meta: [
                'sales_return_id' => $salesReturn->id,
                'branch_id' => $salesReturn->branch_id,
                'status' => $salesReturn->status,
            ]
        );
    }

    public function notifyStockAdjustmentCreated(StockAdjustment $adjustment): void
    {
        $this->notifyUsersWithPermission(
            pharmacyId: (int) $adjustment->pharmacy_id,
            permission: 'stock_adjustment.approve',
            title: 'Stock adjustment needs approval',
            message: 'Stock adjustment ' . $adjustment->adjustment_no . ' is waiting for approval.',
            url: route('stock-adjustments.show', $adjustment),
            type: 'stock_adjustment',
            severity: 'warning',
            pendingKey: 'stock_adjustment_approve_' . $adjustment->id,
            meta: [
                'stock_adjustment_id' => $adjustment->id,
                'branch_id' => $adjustment->branch_id,
                'status' => $adjustment->status,
            ]
        );
    }

    public function notifyStockTransferCreated(StockTransfer $transfer): void
    {
        $this->notifyUsersWithPermission(
            pharmacyId: (int) $transfer->pharmacy_id,
            permission: 'stock_transfer.approve',
            title: 'Stock transfer needs approval',
            message: 'Transfer ' . $transfer->transfer_no . ' is waiting for approval.',
            url: route('stock-transfers.show', $transfer),
            type: 'stock_transfer',
            severity: 'warning',
            pendingKey: 'stock_transfer_approve_' . $transfer->id,
            meta: [
                'stock_transfer_id' => $transfer->id,
                'source_branch_id' => $transfer->source_branch_id,
                'destination_branch_id' => $transfer->destination_branch_id,
                'status' => $transfer->status,
            ]
        );
    }

    public function notifyStockTransferApproved(StockTransfer $transfer): void
    {
        $this->notifyBranchUsersWithPermission(
            pharmacyId: (int) $transfer->pharmacy_id,
            branchId: (int) $transfer->source_branch_id,
            permission: 'stock_transfer.dispatch',
            title: 'Stock transfer ready for dispatch',
            message: 'Transfer ' . $transfer->transfer_no . ' has been approved and needs dispatch.',
            url: route('stock-transfers.show', $transfer),
            type: 'stock_transfer',
            severity: 'warning',
            pendingKey: 'stock_transfer_dispatch_' . $transfer->id,
            meta: [
                'stock_transfer_id' => $transfer->id,
                'source_branch_id' => $transfer->source_branch_id,
                'destination_branch_id' => $transfer->destination_branch_id,
                'status' => $transfer->status,
            ]
        );
    }

    public function notifyStockTransferDispatched(StockTransfer $transfer): void
    {
        $this->notifyBranchUsersWithPermission(
            pharmacyId: (int) $transfer->pharmacy_id,
            branchId: (int) $transfer->destination_branch_id,
            permission: 'stock_transfer.receive',
            title: 'Stock transfer needs receiving',
            message: 'Transfer ' . $transfer->transfer_no . ' has been dispatched to your branch.',
            url: route('stock-transfers.show', $transfer),
            type: 'stock_transfer',
            severity: 'critical',
            pendingKey: 'stock_transfer_receive_' . $transfer->id,
            meta: [
                'stock_transfer_id' => $transfer->id,
                'source_branch_id' => $transfer->source_branch_id,
                'destination_branch_id' => $transfer->destination_branch_id,
                'status' => $transfer->status,
            ]
        );

        $this->notifyOwnersAndAdmins(
            pharmacyId: (int) $transfer->pharmacy_id,
            title: 'Stock transfer dispatched',
            message: 'Transfer ' . $transfer->transfer_no . ' has been dispatched and is waiting for receiving.',
            url: route('stock-transfers.show', $transfer),
            type: 'stock_transfer',
            severity: 'info',
            pendingKey: 'stock_transfer_owner_watch_' . $transfer->id,
            meta: [
                'stock_transfer_id' => $transfer->id,
                'status' => $transfer->status,
            ]
        );
    }

    private function sendOnce(
        Collection $users,
        string $title,
        string $message,
        ?string $url,
        string $type,
        string $severity,
        ?string $pendingKey,
        array $meta
    ): void {
        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            if ($pendingKey && $this->alreadyNotified($user, $pendingKey)) {
                continue;
            }

            $user->notify(new SystemActionNotification(
                title: $title,
                message: $message,
                url: $url,
                type: $type,
                severity: $severity,
                pendingKey: $pendingKey,
                meta: $meta
            ));
        }
    }

    private function alreadyNotified(User $user, string $pendingKey): bool
    {
        return $user->notifications()
            ->whereNull('read_at')
            ->where('data->pending_key', $pendingKey)
            ->exists();
    }

    public function notifyPosExpenseCreated(Expense $expense): void
    {
        $this->notifyOwnersAndAdmins(
            pharmacyId: (int) $expense->pharmacy_id,
            title: 'POS expense recorded',
            message: 'Expense ' . $expense->expense_no . ' was recorded from POS. Amount: ' . number_format((float) $expense->amount, 2),
            url: route('expenses.index', ['search' => $expense->expense_no]),
            type: 'expense',
            severity: 'info',
            pendingKey: 'pos_expense_created_' . $expense->id,
            meta: [
                'expense_id' => $expense->id,
                'branch_id' => $expense->branch_id,
                'amount' => $expense->amount,
                'status' => $expense->status,
            ]
        );
    }

    public function notifyPosExpenseDeleted(Expense $expense): void
    {
        $this->notifyOwnersAndAdmins(
            pharmacyId: (int) $expense->pharmacy_id,
            title: 'POS expense deleted',
            message: 'Expense ' . $expense->expense_no . ' was deleted from POS. Amount: ' . number_format((float) $expense->amount, 2),
            url: route('expenses.index'),
            type: 'expense',
            severity: 'warning',
            pendingKey: 'pos_expense_deleted_' . $expense->id . '_' . now()->timestamp,
            meta: [
                'expense_id' => $expense->id,
                'expense_no' => $expense->expense_no,
                'branch_id' => $expense->branch_id,
                'amount' => $expense->amount,
                'status' => $expense->status,
            ]
        );
    }

    public function notifyAutoExpiryWriteOff(StockAdjustment $adjustment): void
    {
        $this->notifyOwnersAndAdmins(
            pharmacyId: (int) $adjustment->pharmacy_id,
            title: 'Expired stock automatically written off',
            message: 'System automatically adjusted expired stock under adjustment ' . $adjustment->adjustment_no . '. Total cost impact: ' . number_format((float) $adjustment->total_cost, 2),
            url: route('stock-adjustments.show', $adjustment),
            type: 'stock_adjustment',
            severity: 'critical',
            pendingKey: 'auto_expiry_writeoff_' . $adjustment->id,
            meta: [
                'stock_adjustment_id' => $adjustment->id,
                'branch_id' => $adjustment->branch_id,
                'adjustment_type' => $adjustment->adjustment_type,
                'status' => $adjustment->status,
                'total_items' => $adjustment->total_items,
                'total_cost' => $adjustment->total_cost,
            ]
        );
    }
}
