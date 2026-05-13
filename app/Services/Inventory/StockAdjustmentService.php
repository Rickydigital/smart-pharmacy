<?php

namespace App\Services\Inventory;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Services\SystemNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StockAdjustmentService
{
    public function list(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');
        $status = $request->input('status');
        $type = $request->input('adjustment_type');
        $search = trim((string) $request->input('search'));

        if (! $isAdminOrOwner) {
            $branchId = $user?->branch_id;
        }

        $adjustments = StockAdjustment::query()
            ->with(['branch', 'creator', 'approver'])
            ->withCount('items')
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($type, fn ($query) => $query->where('adjustment_type', $type))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('adjustment_no', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->whereDate('adjustment_date', '>=', $dateFrom)
            ->whereDate('adjustment_date', '<=', $dateTo)
            ->latest('adjustment_date')
            ->latest()
            ->paginate((int) $request->input('per_page', 20))
            ->withQueryString();

        $summaryQuery = StockAdjustment::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($type, fn ($query) => $query->where('adjustment_type', $type))
            ->whereDate('adjustment_date', '>=', $dateFrom)
            ->whereDate('adjustment_date', '<=', $dateTo);

        return [
            'adjustments' => $adjustments,
            'branches' => $this->branches($pharmacy),
            'summary' => [
                'count' => (clone $summaryQuery)->count(),
                'items' => (int) (clone $summaryQuery)->sum('total_items'),
                'quantity' => (int) (clone $summaryQuery)->sum('total_quantity_base_units'),
                'cost' => (float) (clone $summaryQuery)->sum('total_cost'),
                'approved_cost' => (float) (clone $summaryQuery)->where('status', 'approved')->sum('total_cost'),
            ],
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'branchId' => $branchId,
            'status' => $status,
            'type' => $type,
            'search' => $search,
            'isAdminOrOwner' => $isAdminOrOwner,
            'is_admin_or_owner' => $isAdminOrOwner,
        ];
    }

    public function searchInventory(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        $branchId = (int) $validated['branch_id'];

        if (! $isAdminOrOwner && (int) $branchId !== (int) $user?->branch_id) {
            abort(403);
        }

        $queryText = trim((string) ($validated['q'] ?? ''));

        return Inventory::query()
            ->with(['branch', 'product.baseUnit', 'purchaseItem.productUnit.unit'])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->when($queryText !== '', function ($query) use ($queryText) {
                $query->where(function ($q) use ($queryText) {
                    $q->where('batch_no', 'like', "%{$queryText}%")
                        ->orWhereHas('product', function ($productQuery) use ($queryText) {
                            $productQuery->where('name', 'like', "%{$queryText}%")
                                ->orWhere('code', 'like', "%{$queryText}%")
                                ->orWhere('barcode', 'like', "%{$queryText}%")
                                ->orWhere('generic_name', 'like', "%{$queryText}%")
                                ->orWhere('brand', 'like', "%{$queryText}%");
                        });
                });
            })
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->limit(30)
            ->get()
            ->map(fn (Inventory $inventory) => [
                'inventory_id' => $inventory->id,
                'product_id' => $inventory->product_id,
                'product_name' => $inventory->product?->name ?: '-',
                'product_code' => $inventory->product?->code,
                'base_unit' => $inventory->product?->baseUnit?->name ?: 'Base unit',
                'branch_name' => $inventory->branch?->name ?: '-',
                'batch_no' => $inventory->batch_no ?: '-',
                'expiry_date' => $inventory->expiry_date?->format('d M Y'),
                'available_quantity_base_units' => (int) $inventory->available_quantity_base_units,
                'unit_cost_base' => (float) $inventory->unit_cost_base,
                'status' => $inventory->status,
                'is_expired' => $inventory->isExpired(),
            ])
            ->values()
            ->all();
    }

    public function create(Request $request): StockAdjustment
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'adjustment_date' => ['required', 'date'],
            'adjustment_type' => ['required', Rule::in([
                'damage',
                'expiry',
                'physical_count',
                'loss',
                'found_stock',
                'correction',
            ])],
            'reason' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_id' => ['required', 'integer', 'exists:inventories,id'],
            'items.*.direction' => ['required', Rule::in(['in', 'out'])],
            'items.*.quantity_base_units' => ['required', 'integer', 'min:1'],
            'items.*.reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        if (! $isAdminOrOwner && (int) $validated['branch_id'] !== (int) $user?->branch_id) {
            abort(403);
        }

        $adjustment = DB::transaction(function () use ($pharmacy, $validated) {
            $adjustment = StockAdjustment::query()->create([
                'pharmacy_id' => $pharmacy->id,
                'branch_id' => $validated['branch_id'],
                'adjustment_no' => $this->generateAdjustmentNumber(),
                'adjustment_date' => $validated['adjustment_date'],
                'adjustment_type' => $validated['adjustment_type'],
                'status' => 'draft',
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $totalItems = 0;
            $totalQuantity = 0;
            $totalCost = 0.0;

            foreach ($validated['items'] as $row) {
                $inventory = Inventory::query()
                    ->where('pharmacy_id', $pharmacy->id)
                    ->where('branch_id', $validated['branch_id'])
                    ->whereKey($row['inventory_id'])
                    ->firstOrFail();

                $direction = $row['direction'];
                $quantity = (int) $row['quantity_base_units'];

                if ($direction === 'out' && $quantity > (int) $inventory->available_quantity_base_units) {
                    abort(422, 'Adjustment quantity cannot be greater than available stock.');
                }

                $unitCost = (float) $inventory->unit_cost_base;
                $lineCost = $unitCost * $quantity;

                $before = (int) $inventory->available_quantity_base_units;
                $after = $direction === 'in'
                    ? $before + $quantity
                    : $before - $quantity;

                StockAdjustmentItem::query()->create([
                    'pharmacy_id' => $pharmacy->id,
                    'branch_id' => $validated['branch_id'],
                    'stock_adjustment_id' => $adjustment->id,
                    'inventory_id' => $inventory->id,
                    'product_id' => $inventory->product_id,
                    'direction' => $direction,
                    'quantity_base_units' => $quantity,
                    'unit_cost_base' => $unitCost,
                    'total_cost' => $lineCost,
                    'balance_before_base_units' => $before,
                    'balance_after_base_units' => $after,
                    'reason' => $row['reason'] ?? null,
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

            activity()
                ->useLog('stock_adjustment')
                ->event('created')
                ->performedOn($adjustment)
                ->causedBy(Auth::user())
                ->withProperties([
                    'adjustment_no' => $adjustment->adjustment_no,
                    'adjustment_type' => $adjustment->adjustment_type,
                    'total_cost' => $adjustment->total_cost,
                ])
                ->log('Stock adjustment created');

            return $adjustment->fresh(['branch', 'creator', 'items.product.baseUnit', 'items.inventory']);
        });

        app(SystemNotificationService::class)->notifyStockAdjustmentCreated($adjustment);

        return $adjustment;
    }

    public function show(StockAdjustment $stockAdjustment): StockAdjustment
    {
        $this->guardAdjustment($stockAdjustment);

        return $stockAdjustment->load([
            'branch',
            'creator',
            'approver',
            'items.inventory',
            'items.product.baseUnit',
        ]);
    }

    public function approve(StockAdjustment $stockAdjustment): StockAdjustment
    {
        $this->guardAdjustment($stockAdjustment);

        if (! $stockAdjustment->isDraft()) {
            abort(422, 'Only draft adjustments can be approved.');
        }

        DB::transaction(function () use ($stockAdjustment) {
            $stockAdjustment->load(['items']);

            foreach ($stockAdjustment->items as $item) {
                $this->applyAdjustmentItem($item);
            }

            $stockAdjustment->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            activity()
                ->useLog('stock_adjustment')
                ->event('approved')
                ->performedOn($stockAdjustment)
                ->causedBy(Auth::user())
                ->withProperties([
                    'adjustment_no' => $stockAdjustment->adjustment_no,
                    'total_cost' => $stockAdjustment->total_cost,
                ])
                ->log('Stock adjustment approved');
        });

        return $stockAdjustment->fresh(['branch', 'creator', 'approver', 'items.product.baseUnit', 'items.inventory']);
    }

    public function reject(Request $request, StockAdjustment $stockAdjustment): StockAdjustment
    {
        $this->guardAdjustment($stockAdjustment);

        if (! $stockAdjustment->isDraft()) {
            abort(422, 'Only draft adjustments can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $stockAdjustment->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        activity()
            ->useLog('stock_adjustment')
            ->event('rejected')
            ->performedOn($stockAdjustment)
            ->causedBy(Auth::user())
            ->log('Stock adjustment rejected');

        return $stockAdjustment->fresh(['branch', 'creator', 'approver', 'items.product.baseUnit', 'items.inventory']);
    }

    public function cancel(StockAdjustment $stockAdjustment): StockAdjustment
    {
        $this->guardAdjustment($stockAdjustment);

        if (! $stockAdjustment->isDraft()) {
            abort(422, 'Only draft adjustments can be cancelled.');
        }

        $stockAdjustment->update([
            'status' => 'cancelled',
        ]);

        return $stockAdjustment->fresh(['branch', 'creator', 'approver', 'items.product.baseUnit', 'items.inventory']);
    }

    private function applyAdjustmentItem(StockAdjustmentItem $item): void
    {
        $inventory = Inventory::query()
            ->where('pharmacy_id', $item->pharmacy_id)
            ->where('branch_id', $item->branch_id)
            ->whereKey($item->inventory_id)
            ->lockForUpdate()
            ->firstOrFail();

        $before = (int) $inventory->available_quantity_base_units;

        if ($item->isOut() && (int) $item->quantity_base_units > $before) {
            abort(422, 'Stock adjustment cannot reduce more than available stock.');
        }

        $after = $item->isIn()
            ? $before + (int) $item->quantity_base_units
            : $before - (int) $item->quantity_base_units;

        $inventory->update([
            'available_quantity_base_units' => $after,
            'status' => $after <= 0
                ? 'depleted'
                : ($inventory->status === 'expired' ? 'expired' : 'available'),
            'is_active' => $after > 0,
        ]);

        InventoryMovement::query()->create([
            'pharmacy_id' => $item->pharmacy_id,
            'branch_id' => $item->branch_id,
            'product_id' => $item->product_id,
            'inventory_id' => $inventory->id,
            'movement_no' => $this->generateMovementNumber(),
            'movement_type' => 'stock_adjustment_' . $item->direction,
            'direction' => $item->direction,
            'quantity_base_units' => (int) $item->quantity_base_units,
            'balance_before_base_units' => $before,
            'balance_after_base_units' => $after,
            'source_type' => StockAdjustment::class,
            'source_id' => $item->stock_adjustment_id,
            'reason' => $item->reason ?: 'Stock adjustment',
            'created_by' => Auth::id(),
            'moved_at' => now(),
        ]);
    }

    private function branches(Pharmacy $pharmacy)
    {
        return Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();
    }

    private function guardAdjustment(StockAdjustment $stockAdjustment): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $stockAdjustment->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        if (! $isAdminOrOwner && (int) $stockAdjustment->branch_id !== (int) $user?->branch_id) {
            abort(403);
        }
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