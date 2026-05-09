<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\ProductUnit;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
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

class StockTransferController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:stock_transfer.view', only: ['index', 'show', 'searchInventory']),
            new Middleware('permission:stock_transfer.create', only: ['store']),
            new Middleware('permission:stock_transfer.approve', only: ['approve']),
            new Middleware('permission:stock_transfer.reject', only: ['reject']),
            new Middleware('permission:stock_transfer.cancel', only: ['cancel']),
            new Middleware('permission:stock_transfer.dispatch', only: ['dispatch']),
            new Middleware('permission:stock_transfer.receive', only: ['receive']),
        ];
    }

    public function index(Request $request): View
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $sourceBranchId = $request->input('source_branch_id');
        $destinationBranchId = $request->input('destination_branch_id');
        $status = $request->input('status');
        $search = trim((string) $request->input('search'));

        if (! $isAdminOrOwner) {
            $sourceBranchId = $user?->branch_id;
        }

        $transfers = StockTransfer::query()
            ->with(['sourceBranch', 'destinationBranch', 'creator', 'approver', 'dispatcher', 'receiver'])
            ->withCount('items')
            ->where('pharmacy_id', $pharmacy->id)
            ->when($sourceBranchId, fn ($query) => $query->where('source_branch_id', $sourceBranchId))
            ->when($destinationBranchId, fn ($query) => $query->where('destination_branch_id', $destinationBranchId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('transfer_no', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->whereDate('transfer_date', '>=', $dateFrom)
            ->whereDate('transfer_date', '<=', $dateTo)
            ->latest('transfer_date')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $branches = Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $summaryQuery = StockTransfer::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->when($sourceBranchId, fn ($query) => $query->where('source_branch_id', $sourceBranchId))
            ->when($destinationBranchId, fn ($query) => $query->where('destination_branch_id', $destinationBranchId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->whereDate('transfer_date', '>=', $dateFrom)
            ->whereDate('transfer_date', '<=', $dateTo);

        $summary = [
            'count' => (clone $summaryQuery)->count(),
            'items' => (int) (clone $summaryQuery)->sum('total_items'),
            'quantity' => (int) (clone $summaryQuery)->sum('total_quantity_base_units'),
            'cost' => (float) (clone $summaryQuery)->sum('total_cost'),
            'received' => (clone $summaryQuery)->where('status', 'received')->count(),
        ];

        return view('stock-transfers.index', compact(
            'transfers',
            'branches',
            'summary',
            'dateFrom',
            'dateTo',
            'sourceBranchId',
            'destinationBranchId',
            'status',
            'search',
            'isAdminOrOwner'
        ));
    }

    public function searchInventory(Request $request): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'source_branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        $sourceBranchId = (int) $validated['source_branch_id'];

        if (! $isAdminOrOwner && (int) $sourceBranchId !== (int) $user?->branch_id) {
            abort(403);
        }

        $queryText = trim((string) ($validated['q'] ?? ''));

        $inventories = Inventory::query()
            ->with([
                'branch',
                'product.baseUnit',
                'product.productUnits.unit',
            ])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('branch_id', $sourceBranchId)
            ->where('is_active', true)
            ->where('status', 'available')
            ->where('available_quantity_base_units', '>', 0)
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
            ->get();

        $items = $inventories->map(function (Inventory $inventory) {
            $productUnits = $inventory->product?->productUnits
                ? $inventory->product->productUnits
                    ->sortBy('quantity_in_base_units')
                    ->map(fn (ProductUnit $productUnit) => [
                        'id' => $productUnit->id,
                        'unit_name' => $productUnit->unit?->name ?: '-',
                        'quantity_in_base_units' => (int) $productUnit->quantity_in_base_units,
                    ])
                    ->values()
                : collect();

            if ($productUnits->isEmpty() && $inventory->product?->baseUnit) {
                $productUnits = collect([
                    [
                        'id' => null,
                        'unit_name' => $inventory->product->baseUnit->name,
                        'quantity_in_base_units' => 1,
                    ],
                ]);
            }

            return [
                'inventory_id' => $inventory->id,
                'product_id' => $inventory->product_id,
                'product_name' => $inventory->product?->name ?: '-',
                'product_code' => $inventory->product?->code,
                'base_unit' => $inventory->product?->baseUnit?->name ?: 'Base unit',
                'source_branch_name' => $inventory->branch?->name ?: '-',
                'batch_no' => $inventory->batch_no ?: '-',
                'expiry_date' => $inventory->expiry_date?->format('d M Y'),
                'available_quantity_base_units' => (int) $inventory->available_quantity_base_units,
                'unit_cost_base' => (float) $inventory->unit_cost_base,
                'status' => $inventory->status,
                'product_units' => $productUnits,
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'items' => $items,
        ]);
    }

    public function store(Request $request, SystemNotificationService $notifier): RedirectResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'source_branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'destination_branch_id' => [
                'required',
                'different:source_branch_id',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'transfer_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.source_inventory_id' => ['required', 'integer', 'exists:inventories,id'],
            'items.*.product_unit_id' => ['nullable', 'integer', 'exists:product_units,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ]);

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        if (! $isAdminOrOwner && (int) $validated['source_branch_id'] !== (int) $user?->branch_id) {
            abort(403);
        }

        $stockTransfer = DB::transaction(function () use ($pharmacy, $validated) {
            $transfer = StockTransfer::query()->create([
                'pharmacy_id' => $pharmacy->id,
                'source_branch_id' => $validated['source_branch_id'],
                'destination_branch_id' => $validated['destination_branch_id'],
                'transfer_no' => $this->generateTransferNumber(),
                'transfer_date' => $validated['transfer_date'],
                'status' => 'draft',
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $totalItems = 0;
            $totalQuantityBaseUnits = 0;
            $totalCost = 0.0;

            foreach ($validated['items'] as $row) {
                $inventory = Inventory::query()
                    ->with(['product.baseUnit'])
                    ->where('pharmacy_id', $pharmacy->id)
                    ->where('branch_id', $validated['source_branch_id'])
                    ->whereKey($row['source_inventory_id'])
                    ->firstOrFail();

                $productUnit = null;
                $quantityInBaseUnits = 1;

                if (! empty($row['product_unit_id'])) {
                    $productUnit = ProductUnit::query()
                        ->where('product_id', $inventory->product_id)
                        ->whereKey($row['product_unit_id'])
                        ->firstOrFail();

                    $quantityInBaseUnits = max(1, (int) $productUnit->quantity_in_base_units);
                }

                $quantity = (float) $row['quantity'];
                $quantityBaseUnits = (int) round($quantity * $quantityInBaseUnits);

                if ($quantityBaseUnits <= 0) {
                    abort(422, 'Transfer quantity is invalid.');
                }

                if ($quantityBaseUnits > (int) $inventory->available_quantity_base_units) {
                    abort(422, 'Transfer quantity cannot be greater than available stock.');
                }

                $unitCost = (float) $inventory->unit_cost_base;
                $lineCost = $unitCost * $quantityBaseUnits;

                $sourceBefore = (int) $inventory->available_quantity_base_units;
                $sourceAfter = $sourceBefore - $quantityBaseUnits;

                StockTransferItem::query()->create([
                    'pharmacy_id' => $pharmacy->id,
                    'stock_transfer_id' => $transfer->id,
                    'source_branch_id' => $validated['source_branch_id'],
                    'destination_branch_id' => $validated['destination_branch_id'],
                    'source_inventory_id' => $inventory->id,
                    'product_id' => $inventory->product_id,
                    'product_unit_id' => $productUnit?->id,
                    'batch_no' => $inventory->batch_no,
                    'expiry_date' => $inventory->expiry_date,
                    'quantity' => $quantity,
                    'quantity_in_base_units' => $quantityInBaseUnits,
                    'quantity_base_units' => $quantityBaseUnits,
                    'unit_cost_base' => $unitCost,
                    'total_cost' => $lineCost,
                    'source_balance_before_base_units' => $sourceBefore,
                    'source_balance_after_base_units' => $sourceAfter,
                    'destination_balance_before_base_units' => 0,
                    'destination_balance_after_base_units' => 0,
                ]);

                $totalItems++;
                $totalQuantityBaseUnits += $quantityBaseUnits;
                $totalCost += $lineCost;
            }

            $transfer->update([
                'total_items' => $totalItems,
                'total_quantity_base_units' => $totalQuantityBaseUnits,
                'total_cost' => $totalCost,
            ]);

            activity()
                ->useLog('stock_transfer')
                ->event('created')
                ->performedOn($transfer)
                ->causedBy(Auth::user())
                ->withProperties([
                    'transfer_no' => $transfer->transfer_no,
                    'source_branch_id' => $transfer->source_branch_id,
                    'destination_branch_id' => $transfer->destination_branch_id,
                    'total_cost' => $transfer->total_cost,
                ])
                ->log('Stock transfer created');

            return $transfer;
        });

        $notifier->notifyStockTransferCreated($stockTransfer);

        return redirect()
            ->route('stock-transfers.show', $stockTransfer)
            ->with('success', 'Stock transfer created. Approval and dispatch are required before source stock changes.');
    }

    public function show(StockTransfer $stockTransfer): View
    {
        $this->guardTransfer($stockTransfer);

        $stockTransfer->load([
            'sourceBranch',
            'destinationBranch',
            'creator',
            'approver',
            'dispatcher',
            'receiver',
            'items.product.baseUnit',
            'items.productUnit.unit',
            'items.sourceInventory',
            'items.destinationInventory',
        ]);

        return view('stock-transfers.show', compact('stockTransfer'));
    }

    public function approve(StockTransfer $stockTransfer, SystemNotificationService $notifier): RedirectResponse
    {
        $this->guardTransfer($stockTransfer);

        if (! $stockTransfer->isDraft()) {
            return back()->with('error', 'Only draft transfers can be approved.');
        }

        $stockTransfer->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        activity()
            ->useLog('stock_transfer')
            ->event('approved')
            ->performedOn($stockTransfer)
            ->causedBy(Auth::user())
            ->log('Stock transfer approved');

        $notifier->notifyStockTransferApproved($stockTransfer);

        return back()->with('success', 'Stock transfer approved successfully.');
    }

    public function dispatch(StockTransfer $stockTransfer, SystemNotificationService $notifier): RedirectResponse
    {
        $this->guardTransfer($stockTransfer);

        if (! $stockTransfer->isApproved()) {
            return back()->with('error', 'Only approved transfers can be dispatched.');
        }

        DB::transaction(function () use ($stockTransfer) {
            $stockTransfer->load('items');

            foreach ($stockTransfer->items as $item) {
                $this->dispatchItem($item);
            }

            $stockTransfer->update([
                'status' => 'dispatched',
                'dispatched_by' => Auth::id(),
                'dispatched_at' => now(),
            ]);

            activity()
                ->useLog('stock_transfer')
                ->event('dispatched')
                ->performedOn($stockTransfer)
                ->causedBy(Auth::user())
                ->log('Stock transfer dispatched and source stock reduced');
        });
        $notifier->notifyStockTransferDispatched($stockTransfer);

        return back()->with('success', 'Stock transfer dispatched successfully. Source stock has been reduced.');
    }

    public function receive(StockTransfer $stockTransfer): RedirectResponse
    {
        $this->guardTransfer($stockTransfer, checkDestination: true);

        if (! $stockTransfer->isDispatched()) {
            return back()->with('error', 'Only dispatched transfers can be received.');
        }

        DB::transaction(function () use ($stockTransfer) {
            $stockTransfer->load('items');

            foreach ($stockTransfer->items as $item) {
                $this->receiveItem($item);
            }

            $stockTransfer->update([
                'status' => 'received',
                'received_by' => Auth::id(),
                'received_at' => now(),
            ]);

            activity()
                ->useLog('stock_transfer')
                ->event('received')
                ->performedOn($stockTransfer)
                ->causedBy(Auth::user())
                ->log('Stock transfer received and destination stock increased');
        });

        return back()->with('success', 'Stock transfer received successfully. Destination stock has been updated.');
    }

    public function reject(Request $request, StockTransfer $stockTransfer): RedirectResponse
    {
        $this->guardTransfer($stockTransfer);

        if (! $stockTransfer->isDraft()) {
            return back()->with('error', 'Only draft transfers can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $stockTransfer->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        activity()
            ->useLog('stock_transfer')
            ->event('rejected')
            ->performedOn($stockTransfer)
            ->causedBy(Auth::user())
            ->log('Stock transfer rejected');

        return back()->with('success', 'Stock transfer rejected successfully.');
    }

    public function cancel(StockTransfer $stockTransfer): RedirectResponse
    {
        $this->guardTransfer($stockTransfer);

        if (! $stockTransfer->isDraft()) {
            return back()->with('error', 'Only draft transfers can be cancelled.');
        }

        $stockTransfer->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Stock transfer cancelled successfully.');
    }

    private function dispatchItem(StockTransferItem $item): void
    {
        $inventory = Inventory::query()
            ->where('pharmacy_id', $item->pharmacy_id)
            ->where('branch_id', $item->source_branch_id)
            ->whereKey($item->source_inventory_id)
            ->lockForUpdate()
            ->firstOrFail();

        $before = (int) $inventory->available_quantity_base_units;
        $quantity = (int) $item->quantity_base_units;

        if ($quantity > $before) {
            abort(422, 'Transfer quantity cannot be greater than available source stock.');
        }

        $after = $before - $quantity;

        $inventory->update([
            'available_quantity_base_units' => $after,
            'status' => $after <= 0
                ? 'depleted'
                : ($inventory->status === 'expired' ? 'expired' : 'available'),
            'is_active' => $after > 0,
        ]);

        $item->update([
            'source_balance_before_base_units' => $before,
            'source_balance_after_base_units' => $after,
        ]);

        InventoryMovement::query()->create([
            'pharmacy_id' => $item->pharmacy_id,
            'branch_id' => $item->source_branch_id,
            'product_id' => $item->product_id,
            'inventory_id' => $inventory->id,
            'movement_no' => $this->generateMovementNumber(),
            'movement_type' => 'stock_transfer_dispatch',
            'direction' => 'out',
            'quantity_base_units' => $quantity,
            'balance_before_base_units' => $before,
            'balance_after_base_units' => $after,
            'source_type' => StockTransfer::class,
            'source_id' => $item->stock_transfer_id,
            'reason' => 'Stock transfer dispatched',
            'created_by' => Auth::id(),
            'moved_at' => now(),
        ]);
    }

    private function receiveItem(StockTransferItem $item): void
    {
        $destinationInventory = Inventory::query()
            ->where('pharmacy_id', $item->pharmacy_id)
            ->where('branch_id', $item->destination_branch_id)
            ->where('product_id', $item->product_id)
            ->where('batch_no', $item->batch_no)
            ->whereDate('expiry_date', $item->expiry_date)
            ->where('unit_cost_base', $item->unit_cost_base)
            ->lockForUpdate()
            ->first();

        if (! $destinationInventory) {
            $destinationInventory = Inventory::query()->create([
                'pharmacy_id' => $item->pharmacy_id,
                'branch_id' => $item->destination_branch_id,
                'product_id' => $item->product_id,
                'purchase_id' => null,
                'purchase_item_id' => null,
                'batch_no' => $item->batch_no,
                'expiry_date' => $item->expiry_date,
                'received_quantity_base_units' => 0,
                'available_quantity_base_units' => 0,
                'unit_cost_base' => $item->unit_cost_base,
                'status' => 'available',
                'is_active' => true,
            ]);
        }

        $before = (int) $destinationInventory->available_quantity_base_units;
        $quantity = (int) $item->quantity_base_units;
        $after = $before + $quantity;

        $destinationInventory->update([
            'received_quantity_base_units' => (int) $destinationInventory->received_quantity_base_units + $quantity,
            'available_quantity_base_units' => $after,
            'status' => $destinationInventory->expiry_date && $destinationInventory->expiry_date->isPast()
                ? 'expired'
                : 'available',
            'is_active' => true,
        ]);

        $item->update([
            'destination_inventory_id' => $destinationInventory->id,
            'destination_balance_before_base_units' => $before,
            'destination_balance_after_base_units' => $after,
        ]);

        InventoryMovement::query()->create([
            'pharmacy_id' => $item->pharmacy_id,
            'branch_id' => $item->destination_branch_id,
            'product_id' => $item->product_id,
            'inventory_id' => $destinationInventory->id,
            'movement_no' => $this->generateMovementNumber(),
            'movement_type' => 'stock_transfer_receive',
            'direction' => 'in',
            'quantity_base_units' => $quantity,
            'balance_before_base_units' => $before,
            'balance_after_base_units' => $after,
            'source_type' => StockTransfer::class,
            'source_id' => $item->stock_transfer_id,
            'reason' => 'Stock transfer received',
            'created_by' => Auth::id(),
            'moved_at' => now(),
        ]);
    }

    private function guardTransfer(StockTransfer $stockTransfer, bool $checkDestination = false): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $stockTransfer->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        if ($isAdminOrOwner) {
            return;
        }

        $userBranchId = (int) $user?->branch_id;

        if ($checkDestination) {
            if ((int) $stockTransfer->destination_branch_id !== $userBranchId) {
                abort(403);
            }

            return;
        }

        if (
            (int) $stockTransfer->source_branch_id !== $userBranchId
            && (int) $stockTransfer->destination_branch_id !== $userBranchId
        ) {
            abort(403);
        }
    }

    private function generateTransferNumber(): string
    {
        $prefix = 'TRF-' . now()->format('Ymd') . '-';

        $last = StockTransfer::query()
            ->where('transfer_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('transfer_no');

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