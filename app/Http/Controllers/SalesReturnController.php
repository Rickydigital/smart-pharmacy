<?php

namespace App\Http\Controllers;

use App\Models\DailyClosing;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Services\SystemNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalesReturnController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sales_return.view', only: ['index', 'show', 'searchSale']),
            new Middleware('permission:sales_return.create', only: ['store']),
            new Middleware('permission:sales_return.approve', only: ['approve']),
            new Middleware('permission:sales_return.reject', only: ['reject']),
            new Middleware('permission:sales_return.cancel', only: ['cancel']),
        ];
    }

    public function index(Request $request): View
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');
        $status = $request->input('status');
        $search = trim((string) $request->input('search'));

        if (! $isAdminOrOwner) {
            $branchId = $user?->branch_id;
        }

        $returns = SalesReturn::query()
            ->with(['branch', 'sale', 'creator', 'approver'])
            ->withCount('items')
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('return_no', 'like', "%{$search}%")
                        ->orWhereHas('sale', fn ($saleQuery) => $saleQuery->where('sale_no', 'like', "%{$search}%"));
                });
            })
            ->whereDate('return_date', '>=', $dateFrom)
            ->whereDate('return_date', '<=', $dateTo)
            ->latest('return_date')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $branches = \App\Models\Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $summaryQuery = SalesReturn::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->whereDate('return_date', '>=', $dateFrom)
            ->whereDate('return_date', '<=', $dateTo);

        $summary = [
            'count' => (clone $summaryQuery)->count(),
            'refund_total' => (float) (clone $summaryQuery)->sum('refund_amount'),
            'approved_total' => (float) (clone $summaryQuery)->where('status', 'approved')->sum('refund_amount'),
            'draft_total' => (float) (clone $summaryQuery)->where('status', 'draft')->sum('refund_amount'),
        ];

        return view('sales-returns.index', compact(
            'returns',
            'branches',
            'summary',
            'dateFrom',
            'dateTo',
            'branchId',
            'status',
            'search',
            'isAdminOrOwner'
        ));
    }

    public function searchSale(Request $request): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'sale_no' => ['required', 'string', 'max:100'],
        ]);

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
            ->where('sale_no', $validated['sale_no'])
            ->whereIn('status', ['completed', 'partially_returned'])
            ->when(! $isAdminOrOwner, fn ($query) => $query->where('branch_id', $user?->branch_id))
            ->first();

        if (! $sale) {
            return response()->json([
                'ok' => false,
                'message' => 'Sale receipt not found or not returnable.',
            ], 404);
        }

        $items = $sale->items->map(function (SaleItem $item) {
            $returnedBaseUnits = (int) $item->returnItems
                ->filter(fn (SalesReturnItem $returnItem) => $returnItem->salesReturn?->status === 'approved')
                ->sum('total_base_units');

            $soldBaseUnits = (int) $item->total_base_units;
            $availableBaseUnits = max(0, $soldBaseUnits - $returnedBaseUnits);

            $availableQuantity = $item->quantity_in_base_units > 0
                ? $availableBaseUnits / (int) $item->quantity_in_base_units
                : 0;

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
                'available_quantity' => $availableQuantity,
                'quantity_in_base_units' => (int) $item->quantity_in_base_units,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
            ];
        })->values();

        return response()->json([
            'ok' => true,
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
            'items' => $items,
        ]);
    }

    public function store(Request $request, SystemNotificationService $notifier): RedirectResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'sale_id' => [
                'required',
                Rule::exists('sales', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'return_date' => ['required', 'date'],
            'refund_method' => ['required', Rule::in(['cash', 'mobile_money', 'card', 'bank', 'credit_note', 'no_refund'])],
            'return_type' => ['required', Rule::in(['customer_return', 'correction', 'damaged_return'])],
            'reason' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'integer', 'exists:sale_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.condition' => ['required', Rule::in(['sellable', 'damaged', 'expired', 'opened'])],
            'items.*.restore_to_inventory' => ['nullable', 'boolean'],
            'items.*.reason' => ['nullable', 'string', 'max:500'],
        ]);

        $sale = Sale::query()
            ->with(['items.returnItems.salesReturn'])
            ->where('pharmacy_id', $pharmacy->id)
            ->whereKey($validated['sale_id'])
            ->whereIn('status', ['completed', 'partially_returned'])
            ->firstOrFail();

        $this->guardSaleBranch($sale);

        $salesReturn = DB::transaction(function () use ($pharmacy, $sale, $validated) {
            $return = SalesReturn::query()->create([
                'pharmacy_id' => $pharmacy->id,
                'branch_id' => $sale->branch_id,
                'sale_id' => $sale->id,
                'return_no' => $this->generateReturnNumber(),
                'return_date' => $validated['return_date'],
                'subtotal_amount' => 0,
                'refund_amount' => 0,
                'refund_method' => $validated['refund_method'],
                'status' => 'draft',
                'return_type' => $validated['return_type'],
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $subtotal = 0.0;
            $totalCost = 0.0;
            $profitReversed = 0.0;

            foreach ($validated['items'] as $row) {
                /** @var SaleItem $saleItem */
                $saleItem = $sale->items->firstWhere('id', (int) $row['sale_item_id']);

                if (! $saleItem) {
                    abort(422, 'Invalid sale item selected.');
                }

                $quantity = (float) $row['quantity'];
                $quantityInBaseUnits = (int) $saleItem->quantity_in_base_units;
                $totalBaseUnits = (int) round($quantity * $quantityInBaseUnits);

                $this->ensureReturnQuantityIsAllowed($saleItem, $totalBaseUnits);

                $unitPrice = (float) $saleItem->unit_price;
                $refundAmount = $quantity * $unitPrice;
                $costPerBaseUnit = (float) $saleItem->cost_per_base_unit;
                $lineCost = $costPerBaseUnit * $totalBaseUnits;
                $lineProfitReversed = $refundAmount - $lineCost;

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
                    'unit_price' => $unitPrice,
                    'refund_amount' => $refundAmount,
                    'cost_per_base_unit' => $costPerBaseUnit,
                    'total_cost' => $lineCost,
                    'profit_reversed' => $lineProfitReversed,
                    'condition' => $condition,
                    'restore_to_inventory' => $restore,
                    'inventory_allocations' => $saleItem->inventory_allocations,
                    'reason' => $row['reason'] ?? null,
                ]);

                $subtotal += $refundAmount;
                $totalCost += $lineCost;
                $profitReversed += $lineProfitReversed;
                
            }
            

            $return->update([
                'subtotal_amount' => $subtotal,
                'refund_amount' => $validated['refund_method'] === 'no_refund' ? 0 : $subtotal,
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

            return $return;
        });
        $notifier->notifySalesReturnCreated($salesReturn);
        return redirect()
            ->route('sales-returns.show', $salesReturn)
            ->with('success', 'Sales return created. Approval is required before inventory/refund is finalized.');
    }

    public function show(SalesReturn $salesReturn): View
    {
        $this->guardReturn($salesReturn);

        $salesReturn->load([
            'branch',
            'sale',
            'items.product',
            'items.productUnit.unit',
            'creator',
            'approver',
        ]);

        return view('sales-returns.show', compact('salesReturn'));
    }

    public function approve(SalesReturn $salesReturn): RedirectResponse
    {
        $this->guardReturn($salesReturn);

        if (! $salesReturn->isDraft()) {
            return back()->with('error', 'Only draft returns can be approved.');
        }

        DB::transaction(function () use ($salesReturn) {
            $salesReturn->load(['items', 'sale']);

            foreach ($salesReturn->items as $item) {
                if ($item->shouldRestoreInventory()) {
                    $this->restoreReturnedInventory($item);
                }
            }

            $sale = Sale::query()
                ->whereKey($salesReturn->sale_id)
                ->lockForUpdate()
                ->firstOrFail();

            $approvedReturnedAmount = (float) SalesReturn::query()
                ->where('sale_id', $sale->id)
                ->where('status', 'approved')
                ->sum('refund_amount');

            $newReturnedAmount = $approvedReturnedAmount + (float) $salesReturn->refund_amount;

            $approvedReturnedBaseUnits = (int) SalesReturnItem::query()
                ->where('sale_id', $sale->id)
                ->whereHas('salesReturn', fn ($query) => $query->where('status', 'approved'))
                ->sum('total_base_units');

            $newReturnedBaseUnits = $approvedReturnedBaseUnits + (int) $salesReturn->items->sum('total_base_units');

            $soldBaseUnits = (int) $sale->items()->sum('total_base_units');

            $newStatus = $newReturnedBaseUnits >= $soldBaseUnits
                ? 'returned'
                : 'partially_returned';

            $sale->update([
                'returned_amount' => $newReturnedAmount,
                'returned_base_units' => $newReturnedBaseUnits,
                'status' => $newStatus,
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

            activity()
                ->useLog('sales_return')
                ->event('approved')
                ->performedOn($salesReturn)
                ->causedBy(Auth::user())
                ->log('Sales return approved');
        });

        return back()->with('success', 'Sales return approved successfully.');
    }

    public function reject(Request $request, SalesReturn $salesReturn): RedirectResponse
    {
        $this->guardReturn($salesReturn);

        if (! $salesReturn->isDraft()) {
            return back()->with('error', 'Only draft returns can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $salesReturn->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        activity()
            ->useLog('sales_return')
            ->event('rejected')
            ->performedOn($salesReturn)
            ->causedBy(Auth::user())
            ->log('Sales return rejected');

        return back()->with('success', 'Sales return rejected successfully.');
    }

    public function cancel(SalesReturn $salesReturn): RedirectResponse
    {
        $this->guardReturn($salesReturn);

        if (! $salesReturn->isDraft()) {
            return back()->with('error', 'Only draft returns can be cancelled.');
        }

        $salesReturn->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Sales return cancelled successfully.');
    }

    private function restoreReturnedInventory(SalesReturnItem $item): void
{
    $remainingToRestore = (int) $item->total_base_units;

    if ($remainingToRestore <= 0) {
        return;
    }

    $allocations = collect($item->inventory_allocations ?: [])
        ->filter(fn ($row) => ! empty($row['inventory_id']))
        ->values();

    if ($allocations->isEmpty()) {
        return;
    }

    foreach ($allocations as $allocation) {
        if ($remainingToRestore <= 0) {
            break;
        }

        $inventoryId = $allocation['inventory_id'] ?? null;

        if (! $inventoryId) {
            continue;
        }

        $allocatedQty = (int) (
            $allocation['quantity_base_units']
            ?? $allocation['quantity']
            ?? 0
        );

        if ($allocatedQty <= 0) {
            continue;
        }

        $restoreQty = min($remainingToRestore, $allocatedQty);

        $inventory = Inventory::query()
            ->where('pharmacy_id', $item->pharmacy_id)
            ->where('branch_id', $item->branch_id)
            ->whereKey($inventoryId)
            ->lockForUpdate()
            ->first();

        if (! $inventory) {
            continue;
        }

        $before = (int) $inventory->available_quantity_base_units;
        $after = $before + $restoreQty;

        $inventory->update([
            'available_quantity_base_units' => $after,
            'status' => $inventory->status === 'expired' ? 'expired' : 'available',
            'is_active' => $inventory->status === 'expired' ? false : true,
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

    private function ensureReturnQuantityIsAllowed(SaleItem $saleItem, int $requestedBaseUnits): void
    {
        $alreadyReturnedBaseUnits = (int) $saleItem->returnItems
            ->filter(fn (SalesReturnItem $item) => $item->salesReturn?->status === 'approved')
            ->sum('total_base_units');

        $availableBaseUnits = max(0, (int) $saleItem->total_base_units - $alreadyReturnedBaseUnits);

        if ($requestedBaseUnits > $availableBaseUnits) {
            abort(422, 'Returned quantity cannot be greater than the remaining sold quantity.');
        }
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

    private function guardReturn(SalesReturn $salesReturn): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $salesReturn->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        if (! $isAdminOrOwner && (int) $salesReturn->branch_id !== (int) $user?->branch_id) {
            abort(403);
        }
    }

    private function guardSaleBranch(Sale $sale): void
    {
        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        if (! $isAdminOrOwner && (int) $sale->branch_id !== (int) $user?->branch_id) {
            abort(403);
        }
    }

    private function generateReturnNumber(): string
    {
        $prefix = 'RET-' . now()->format('Ymd') . '-';

        $last = SalesReturn::query()
            ->where('return_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('return_no');

        $next = 1;

        if ($last) {
            $number = (int) Str::afterLast($last, '-');
            $next = $number + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function generateMovementNumber(): string
    {
        $prefix = 'MOV-' . now()->format('YmdHis') . '-';

        return $prefix . strtoupper(Str::random(5));
    }
}