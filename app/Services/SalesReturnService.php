<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\DailyClosing;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesReturnService
{
    public function indexData(array $filters = []): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();
        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        $dateFrom = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $filters['date_to'] ?? now()->toDateString();
        $branchId = $filters['branch_id'] ?? null;
        $status = $filters['status'] ?? null;
        $search = trim((string) ($filters['search'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 20);

        if (! $isAdminOrOwner) {
            $branchId = $user?->branch_id;
        }

        $returns = SalesReturn::query()
            ->with(['branch', 'sale', 'creator', 'approver'])
            ->withCount('items')
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('return_no', 'like', "%{$search}%")
                        ->orWhereHas('sale', fn ($sale) => $sale->where('sale_no', 'like', "%{$search}%"));
                });
            })
            ->whereDate('return_date', '>=', $dateFrom)
            ->whereDate('return_date', '<=', $dateTo)
            ->latest('return_date')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $branches = Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $summaryQuery = SalesReturn::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->whereDate('return_date', '>=', $dateFrom)
            ->whereDate('return_date', '<=', $dateTo);

        return [
            'returns' => $returns,
            'branches' => $branches,
            'summary' => [
                'count' => (clone $summaryQuery)->count(),
                'refund_total' => (float) (clone $summaryQuery)->sum('refund_amount'),
                'approved_total' => (float) (clone $summaryQuery)->where('status', 'approved')->sum('refund_amount'),
                'draft_total' => (float) (clone $summaryQuery)->where('status', 'draft')->sum('refund_amount'),
            ],
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'branchId' => $branchId,
            'status' => $status,
            'search' => $search,
            'isAdminOrOwner' => $isAdminOrOwner,
        ];
    }

    public function searchSale(string $saleNo): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();
        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        $sale = Sale::query()
            ->with([
                'branch',
                'items.product',
                'items.productUnit.unit',
                'items.returnItems.salesReturn',
            ])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('sale_no', $saleNo)
            ->whereIn('status', ['completed', 'partially_returned'])
            ->when(! $isAdminOrOwner, fn ($q) => $q->where('branch_id', $user?->branch_id))
            ->first();

        if (! $sale) {
            throw ValidationException::withMessages([
                'sale_no' => 'Sale receipt not found or not returnable.',
            ]);
        }

        return [
            'sale' => [
                'id' => $sale->id,
                'sale_no' => $sale->sale_no,
                'branch' => $sale->branch?->name ?: '-',
                'branch_id' => $sale->branch_id,
                'customer' => $sale->displayCustomer(),
                'total_amount' => (float) $sale->total_amount,
                'returned_amount' => (float) $sale->returned_amount,
                'status' => $sale->status,
                'sold_at' => $sale->sold_at?->format('d M Y h:i A'),
            ],
            'items' => $sale->items->map(fn (SaleItem $item) => $this->saleItemPayload($item))->values(),
        ];
    }

    public function create(array $data): SalesReturn
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $sale = Sale::query()
            ->with(['items.returnItems.salesReturn'])
            ->where('pharmacy_id', $pharmacy->id)
            ->whereKey($data['sale_id'])
            ->whereIn('status', ['completed', 'partially_returned'])
            ->firstOrFail();

        $this->guardSaleBranch($sale);

        return DB::transaction(function () use ($pharmacy, $sale, $data) {
            $return = SalesReturn::query()->create([
                'pharmacy_id' => $pharmacy->id,
                'branch_id' => $sale->branch_id,
                'sale_id' => $sale->id,
                'return_no' => $this->generateReturnNumber(),
                'return_date' => $data['return_date'],
                'subtotal_amount' => 0,
                'refund_amount' => 0,
                'refund_method' => $data['refund_method'],
                'status' => 'draft',
                'return_type' => $data['return_type'],
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $subtotal = 0.0;

            foreach ($data['items'] as $row) {
                $saleItem = $sale->items->firstWhere('id', (int) $row['sale_item_id']);

                if (! $saleItem) {
                    throw ValidationException::withMessages([
                        'items' => 'Invalid sale item selected.',
                    ]);
                }

                $quantity = (float) $row['quantity'];
                $quantityInBaseUnits = (int) $saleItem->quantity_in_base_units;
                $totalBaseUnits = (int) round($quantity * $quantityInBaseUnits);

                $this->ensureReturnQuantityIsAllowed($saleItem, $totalBaseUnits);

                $refundAmount = $quantity * (float) $saleItem->unit_price;
                $lineCost = (float) $saleItem->cost_per_base_unit * $totalBaseUnits;

                $condition = $row['condition'];
                $restore = (bool) ($row['restore_to_inventory'] ?? false);

                if ($condition !== 'sellable') {
                    $restore = false;
                }

                SalesReturnItem::query()->create([
                    'pharmacy_id' => $pharmacy->id,
                    'branch_id' => $sale->branch_id,
                    'sales_return_id' => $return->id,
                    'sale_id' => $sale->id,
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'product_unit_id' => $saleItem->product_unit_id,
                    'quantity' => $quantity,
                    'quantity_in_base_units' => $quantityInBaseUnits,
                    'total_base_units' => $totalBaseUnits,
                    'unit_price' => (float) $saleItem->unit_price,
                    'refund_amount' => $refundAmount,
                    'cost_per_base_unit' => (float) $saleItem->cost_per_base_unit,
                    'total_cost' => $lineCost,
                    'profit_reversed' => $refundAmount - $lineCost,
                    'condition' => $condition,
                    'restore_to_inventory' => $restore,
                    'inventory_allocations' => $saleItem->inventory_allocations,
                    'reason' => $row['reason'] ?? null,
                ]);

                $subtotal += $refundAmount;
            }

            $return->update([
                'subtotal_amount' => $subtotal,
                'refund_amount' => $data['refund_method'] === 'no_refund' ? 0 : $subtotal,
            ]);

            activity()
                ->useLog('sales_return')
                ->event('created')
                ->performedOn($return)
                ->causedBy(Auth::user())
                ->withProperties([
                    'return_no' => $return->return_no,
                    'sale_no' => $sale->sale_no,
                    'refund_amount' => $return->refund_amount,
                ])
                ->log('Sales return created');

            return $return->fresh(['branch', 'sale', 'items.product', 'items.productUnit.unit']);
        });
    }

    public function show(SalesReturn $salesReturn): SalesReturn
    {
        $this->guardReturn($salesReturn);

        return $salesReturn->load([
            'branch',
            'sale',
            'items.product',
            'items.productUnit.unit',
            'creator',
            'approver',
        ]);
    }

    public function approve(SalesReturn $salesReturn): SalesReturn
    {
        $this->guardReturn($salesReturn);

        if (! $salesReturn->isDraft()) {
            throw ValidationException::withMessages([
                'status' => 'Only draft returns can be approved.',
            ]);
        }

        DB::transaction(function () use ($salesReturn) {
            $salesReturn->load(['items', 'sale']);

            foreach ($salesReturn->items as $item) {
                if ($item->shouldRestoreInventory()) {
                    $this->restoreReturnedInventory($item);
                }
            }

            $sale = Sale::query()->whereKey($salesReturn->sale_id)->lockForUpdate()->firstOrFail();

            $approvedReturnedAmount = (float) SalesReturn::query()
                ->where('sale_id', $sale->id)
                ->where('status', 'approved')
                ->sum('refund_amount');

            $newReturnedAmount = $approvedReturnedAmount + (float) $salesReturn->refund_amount;

            $approvedReturnedBaseUnits = (int) SalesReturnItem::query()
                ->where('sale_id', $sale->id)
                ->whereHas('salesReturn', fn ($q) => $q->where('status', 'approved'))
                ->sum('total_base_units');

            $newReturnedBaseUnits = $approvedReturnedBaseUnits + (int) $salesReturn->items->sum('total_base_units');
            $soldBaseUnits = (int) $sale->items()->sum('total_base_units');

            $sale->update([
                'returned_amount' => $newReturnedAmount,
                'returned_base_units' => $newReturnedBaseUnits,
                'status' => $newReturnedBaseUnits >= $soldBaseUnits ? 'returned' : 'partially_returned',
            ]);

            $salesReturn->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            $this->markClosingNeedsRecalculation(
                pharmacyId: (int) $salesReturn->pharmacy_id,
                branchId: (int) $salesReturn->branch_id,
                date: $salesReturn->return_date?->toDateString() ?: now()->toDateString(),
                reason: 'Sales return was approved after daily closing verification. Please recalculate and verify again.'
            );
        });

        activity()
            ->useLog('sales_return')
            ->event('approved')
            ->performedOn($salesReturn)
            ->causedBy(Auth::user())
            ->log('Sales return approved');

        return $salesReturn->fresh(['branch', 'sale', 'items.product']);
    }

    public function reject(SalesReturn $salesReturn, string $reason): SalesReturn
    {
        $this->guardReturn($salesReturn);

        if (! $salesReturn->isDraft()) {
            throw ValidationException::withMessages([
                'status' => 'Only draft returns can be rejected.',
            ]);
        }

        $salesReturn->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return $salesReturn->fresh();
    }

    public function cancel(SalesReturn $salesReturn): SalesReturn
    {
        $this->guardReturn($salesReturn);

        if (! $salesReturn->isDraft()) {
            throw ValidationException::withMessages([
                'status' => 'Only draft returns can be cancelled.',
            ]);
        }

        $salesReturn->update(['status' => 'cancelled']);

        return $salesReturn->fresh();
    }

    private function saleItemPayload(SaleItem $item): array
    {
        $returnedBaseUnits = (int) $item->returnItems
            ->filter(fn (SalesReturnItem $returnItem) => $returnItem->salesReturn?->status === 'approved')
            ->sum('total_base_units');

        $soldBaseUnits = (int) $item->total_base_units;
        $availableBaseUnits = max(0, $soldBaseUnits - $returnedBaseUnits);

        return [
            'sale_item_id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product?->name ?: '-',
            'product_unit_id' => $item->product_unit_id,
            'unit_name' => $item->productUnit?->unit?->name ?: '-',
            'sold_quantity' => (float) $item->quantity,
            'returned_quantity' => $item->quantity_in_base_units > 0
                ? $returnedBaseUnits / (int) $item->quantity_in_base_units
                : 0,
            'available_quantity' => $item->quantity_in_base_units > 0
                ? $availableBaseUnits / (int) $item->quantity_in_base_units
                : 0,
            'quantity_in_base_units' => (int) $item->quantity_in_base_units,
            'unit_price' => (float) $item->unit_price,
            'line_total' => (float) $item->line_total,
        ];
    }

    private function ensureReturnQuantityIsAllowed(SaleItem $saleItem, int $requestedBaseUnits): void
    {
        $alreadyReturnedBaseUnits = (int) $saleItem->returnItems
            ->filter(fn (SalesReturnItem $item) => $item->salesReturn?->status === 'approved')
            ->sum('total_base_units');

        $availableBaseUnits = max(0, (int) $saleItem->total_base_units - $alreadyReturnedBaseUnits);

        if ($requestedBaseUnits > $availableBaseUnits) {
            throw ValidationException::withMessages([
                'quantity' => 'Returned quantity cannot be greater than the remaining sold quantity.',
            ]);
        }
    }

    private function restoreReturnedInventory(SalesReturnItem $item): void
    {
        $remainingToRestore = (int) $item->total_base_units;

        foreach (collect($item->inventory_allocations ?: [])->filter(fn ($row) => ! empty($row['inventory_id'])) as $allocation) {
            if ($remainingToRestore <= 0) break;

            $restoreQty = min($remainingToRestore, (int) ($allocation['quantity_base_units'] ?? $allocation['quantity'] ?? 0));
            if ($restoreQty <= 0) continue;

            $inventory = Inventory::query()
                ->where('pharmacy_id', $item->pharmacy_id)
                ->where('branch_id', $item->branch_id)
                ->whereKey($allocation['inventory_id'])
                ->lockForUpdate()
                ->first();

            if (! $inventory) continue;

            $before = (int) $inventory->available_quantity_base_units;
            $after = $before + $restoreQty;

            $inventory->update([
                'available_quantity_base_units' => $after,
                'status' => $inventory->status === 'expired' ? 'expired' : 'available',
                'is_active' => $inventory->status !== 'expired',
            ]);

            InventoryMovement::query()->create([
                'pharmacy_id' => $item->pharmacy_id,
                'branch_id' => $item->branch_id,
                'product_id' => $item->product_id,
                'inventory_id' => $inventory->id,
                'movement_no' => $this->generateMovementNumber(),
                'movement_type' => 'sales_return',
                'direction' => 'in',
                'quantity_base_units' => $restoreQty,
                'balance_before_base_units' => $before,
                'balance_after_base_units' => $after,
                'source_type' => SalesReturn::class,
                'source_id' => $item->sales_return_id,
                'reason' => 'Sales return restored to inventory',
                'created_by' => Auth::id(),
                'moved_at' => now(),
            ]);

            $remainingToRestore -= $restoreQty;
        }
    }

    private function guardReturn(SalesReturn $salesReturn): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        abort_if((int) $salesReturn->pharmacy_id !== (int) $pharmacy->id, 403);

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        abort_if(! $isAdminOrOwner && (int) $salesReturn->branch_id !== (int) $user?->branch_id, 403);
    }

    private function guardSaleBranch(Sale $sale): void
    {
        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        abort_if(! $isAdminOrOwner && (int) $sale->branch_id !== (int) $user?->branch_id, 403);
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

    private function generateReturnNumber(): string
    {
        $prefix = 'RET-' . now()->format('Ymd') . '-';

        $last = SalesReturn::query()
            ->where('return_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('return_no');

        $next = $last ? ((int) Str::afterLast($last, '-')) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function generateMovementNumber(): string
    {
        return 'MOV-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(5));
    }
}