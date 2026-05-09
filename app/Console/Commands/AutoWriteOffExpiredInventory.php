<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Services\SystemNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AutoWriteOffExpiredInventory extends Command
{
    protected $signature = 'inventory:auto-writeoff-expired {--pharmacy_id=} {--date=}';

    protected $description = 'Automatically write off inventory whose expiry date has reached today or passed';

    public function handle(SystemNotificationService $notifier): int
    {
        $date = $this->option('date') ?: now()->toDateString();

        $pharmacies = Pharmacy::query()
            ->when($this->option('pharmacy_id'), fn ($query) => $query->whereKey($this->option('pharmacy_id')))
            ->get();

        if ($pharmacies->isEmpty()) {
            $this->warn('No pharmacies found.');
            return self::SUCCESS;
        }

        foreach ($pharmacies as $pharmacy) {
            $this->info('Checking expired inventory for: ' . $pharmacy->name);

            $inventories = Inventory::query()
                ->with(['branch', 'product.baseUnit'])
                ->where('pharmacy_id', $pharmacy->id)
                ->where('is_active', true)
                ->where('available_quantity_base_units', '>', 0)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', $date)
                ->orderBy('branch_id')
                ->orderBy('expiry_date')
                ->get()
                ->groupBy('branch_id');

            if ($inventories->isEmpty()) {
                $this->line('No expired inventory found.');
                continue;
            }

            foreach ($inventories as $branchId => $branchInventories) {
                $adjustment = DB::transaction(function () use ($pharmacy, $branchId, $branchInventories, $date) {
                    $adjustment = StockAdjustment::query()->create([
                        'pharmacy_id' => $pharmacy->id,
                        'branch_id' => $branchId,
                        'adjustment_no' => $this->generateAdjustmentNumber(),
                        'adjustment_date' => now()->toDateString(),
                        'adjustment_type' => 'expiry',
                        'status' => 'approved',
                        'reason' => 'Automatic expiry write-off for stock expiring on or before ' . $date,
                        'notes' => 'AUTO_EXPIRY_WRITEOFF_' . $date,
                        'created_by' => null,
                        'approved_by' => null,
                        'approved_at' => now(),
                    ]);

                    $totalItems = 0;
                    $totalQuantity = 0;
                    $totalCost = 0.0;

                    foreach ($branchInventories as $inventory) {
                        $lockedInventory = Inventory::query()
                            ->whereKey($inventory->id)
                            ->where('pharmacy_id', $pharmacy->id)
                            ->where('branch_id', $branchId)
                            ->lockForUpdate()
                            ->first();

                        if (! $lockedInventory) {
                            continue;
                        }

                        $before = (int) $lockedInventory->available_quantity_base_units;

                        if ($before <= 0) {
                            continue;
                        }

                        $quantity = $before;
                        $after = 0;

                        $unitCost = (float) $lockedInventory->unit_cost_base;
                        $lineCost = $unitCost * $quantity;

                        $item = StockAdjustmentItem::query()->create([
                            'pharmacy_id' => $pharmacy->id,
                            'branch_id' => $branchId,
                            'stock_adjustment_id' => $adjustment->id,
                            'inventory_id' => $lockedInventory->id,
                            'product_id' => $lockedInventory->product_id,
                            'direction' => 'out',
                            'quantity_base_units' => $quantity,
                            'unit_cost_base' => $unitCost,
                            'total_cost' => $lineCost,
                            'balance_before_base_units' => $before,
                            'balance_after_base_units' => $after,
                            'reason' => 'Automatic expiry write-off. Expiry date: ' . optional($lockedInventory->expiry_date)->toDateString(),
                        ]);

                        $lockedInventory->update([
                            'available_quantity_base_units' => 0,
                            'status' => 'expired',
                            'is_active' => false,
                        ]);

                        InventoryMovement::query()->create([
                            'pharmacy_id' => $pharmacy->id,
                            'branch_id' => $branchId,
                            'product_id' => $lockedInventory->product_id,
                            'inventory_id' => $lockedInventory->id,
                            'movement_no' => $this->generateMovementNumber(),
                            'movement_type' => 'auto_expiry_writeoff',
                            'direction' => 'out',
                            'quantity_base_units' => $quantity,
                            'balance_before_base_units' => $before,
                            'balance_after_base_units' => $after,
                            'source_type' => StockAdjustment::class,
                            'source_id' => $adjustment->id,
                            'reason' => $item->reason,
                            'created_by' => null,
                            'moved_at' => now(),
                        ]);

                        $totalItems++;
                        $totalQuantity += $quantity;
                        $totalCost += $lineCost;
                    }

                    $adjustment->update([
                        'total_items' => $totalItems,
                        'total_quantity_base_units' => $totalQuantity,
                        'total_cost' => $totalCost,
                    ]);

                    if ($totalItems <= 0) {
                        StockAdjustment::destroy($adjustment->id);

                        return null;
                    }

                    activity()
                        ->useLog('stock_adjustment')
                        ->event('auto_expiry_writeoff')
                        ->performedOn($adjustment)
                        ->withProperties([
                            'adjustment_no' => $adjustment->adjustment_no,
                            'branch_id' => $branchId,
                            'total_items' => $totalItems,
                            'total_quantity_base_units' => $totalQuantity,
                            'total_cost' => $totalCost,
                        ])
                        ->log('Expired inventory automatically written off');

                    return $adjustment;
                });

                if ($adjustment) {
                    $notifier->notifyAutoExpiryWriteOff($adjustment);

                    $this->line('Written off adjustment: ' . $adjustment->adjustment_no);
                }
            }
        }

        $this->info('Expired inventory write-off completed.');

        return self::SUCCESS;
    }

    private function generateAdjustmentNumber(): string
    {
        $prefix = 'ADJ-' . now()->format('Ymd') . '-';

        $last = StockAdjustment::query()
            ->where('adjustment_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('adjustment_no');

        $next = 1;

        if ($last) {
            $number = (int) Str::afterLast($last, '-');
            $next = $number + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function generateMovementNumber(): string
    {
        return 'MOV-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(5));
    }
}