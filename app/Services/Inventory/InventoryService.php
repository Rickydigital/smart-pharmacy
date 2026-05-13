<?php

namespace App\Services\Inventory;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InventoryService
{
    public function listInventories(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $inventories = Inventory::query()
            ->with([
                'branch',
                'product.baseUnit',
                'purchase',
                'purchaseItem.productUnit.unit',
            ])
            ->where('pharmacy_id', $pharmacy->id)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('batch_no', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%")
                                ->orWhere('barcode', 'like', "%{$search}%")
                                ->orWhere('generic_name', 'like', "%{$search}%")
                                ->orWhere('brand', 'like', "%{$search}%");
                        })
                        ->orWhereHas('purchase', function ($purchaseQuery) use ($search) {
                            $purchaseQuery->where('purchase_no', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->input('branch_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('expiry_from'), fn ($q) => $q->whereDate('expiry_date', '>=', $request->input('expiry_from')))
            ->when($request->filled('expiry_to'), fn ($q) => $q->whereDate('expiry_date', '<=', $request->input('expiry_to')))
            ->when($request->boolean('low_stock'), fn ($q) => $q->where('available_quantity_base_units', '<=', 10))
            ->orderByRaw("CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END")
            ->orderBy('expiry_date')
            ->latest()
            ->paginate((int) $request->input('per_page', 15))
            ->withQueryString();

        return [
            'inventories' => $inventories,
            'branches' => $this->branches($pharmacy),
            'counts' => $this->counts($pharmacy),
        ];
    }

    public function listMovements(Request $request): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $movements = InventoryMovement::query()
            ->with([
                'branch',
                'product.baseUnit',
                'inventory',
                'creator',
            ])
            ->where('pharmacy_id', $pharmacy->id)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('movement_no', 'like', "%{$search}%")
                        ->orWhere('movement_type', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->input('branch_id')))
            ->when($request->filled('movement_type'), fn ($q) => $q->where('movement_type', $request->input('movement_type')))
            ->when($request->filled('direction'), fn ($q) => $q->where('direction', $request->input('direction')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('moved_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('moved_at', '<=', $request->input('date_to')))
            ->latest('moved_at')
            ->paginate((int) $request->input('per_page', 20))
            ->withQueryString();

        return [
            'movements' => $movements->through(function ($movement) {
                $movement->moved_at_local = $movement->moved_at
                    ? $movement->moved_at->format('d M Y h:i A')
                    : null;

                return $movement;
            }),
            'branches' => $this->branches($pharmacy),
            'movementTypes' => $this->movementTypes($pharmacy),
            'movement_types' => $this->movementTypes($pharmacy),
        ];
    }

    public function adjust(Request $request, Inventory $inventory): Inventory
    {
        $this->guardPharmacy($inventory);

        $validated = $request->validate([
            'direction' => ['required', Rule::in(['in', 'out'])],
            'quantity_base_units' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($inventory, $validated) {
            $inventory->refresh();

            if ($inventory->status === 'blocked') {
                throw new \RuntimeException('Blocked inventory cannot be adjusted.');
            }

            $before = (int) $inventory->available_quantity_base_units;
            $quantity = (int) $validated['quantity_base_units'];

            if ($validated['direction'] === 'out' && $quantity > $before) {
                throw new \RuntimeException('Adjustment quantity cannot be greater than available quantity.');
            }

            $after = $validated['direction'] === 'in'
                ? $before + $quantity
                : $before - $quantity;

            $inventory->update([
                'available_quantity_base_units' => $after,
                'status' => $after > 0 ? 'available' : 'depleted',
            ]);

            InventoryMovement::query()->create([
                'pharmacy_id' => $inventory->pharmacy_id,
                'branch_id' => $inventory->branch_id,
                'product_id' => $inventory->product_id,
                'inventory_id' => $inventory->id,
                'movement_no' => $this->generateMovementNumber(),
                'movement_type' => $validated['direction'] === 'in' ? 'adjustment_in' : 'adjustment_out',
                'direction' => $validated['direction'],
                'quantity_base_units' => $quantity,
                'balance_before_base_units' => $before,
                'balance_after_base_units' => $after,
                'reason' => $validated['reason'],
                'created_by' => Auth::id(),
                'moved_at' => now(),
            ]);

            activity()
                ->useLog('inventory')
                ->event('adjusted')
                ->performedOn($inventory)
                ->causedBy(Auth::user())
                ->withProperties([
                    'direction' => $validated['direction'],
                    'quantity_base_units' => $quantity,
                    'before' => $before,
                    'after' => $after,
                ])
                ->log('Inventory adjusted');
        });

        return $inventory->fresh(['branch', 'product.baseUnit']);
    }

    public function toggleBlock(Inventory $inventory): Inventory
    {
        $this->guardPharmacy($inventory);

        if ($inventory->status === 'blocked') {
            $inventory->update([
                'status' => $inventory->available_quantity_base_units > 0 ? 'available' : 'depleted',
                'is_active' => true,
            ]);

            return $inventory->fresh();
        }

        $inventory->update([
            'status' => 'blocked',
            'is_active' => false,
        ]);

        return $inventory->fresh();
    }

    public function markExpired(Inventory $inventory): Inventory
    {
        $this->guardPharmacy($inventory);

        DB::transaction(function () use ($inventory) {
            $inventory->refresh();

            $before = (int) $inventory->available_quantity_base_units;
            $quantity = $before;

            $inventory->update([
                'status' => 'expired',
                'available_quantity_base_units' => 0,
                'is_active' => false,
            ]);

            if ($quantity > 0) {
                InventoryMovement::query()->create([
                    'pharmacy_id' => $inventory->pharmacy_id,
                    'branch_id' => $inventory->branch_id,
                    'product_id' => $inventory->product_id,
                    'inventory_id' => $inventory->id,
                    'movement_no' => $this->generateMovementNumber(),
                    'movement_type' => 'expired',
                    'direction' => 'out',
                    'quantity_base_units' => $quantity,
                    'balance_before_base_units' => $before,
                    'balance_after_base_units' => 0,
                    'reason' => 'Inventory marked as expired.',
                    'created_by' => Auth::id(),
                    'moved_at' => now(),
                ]);
            }
        });

        return $inventory->fresh();
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

    private function counts(Pharmacy $pharmacy): array
    {
        return [
            'all' => Inventory::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->count(),

            'available' => Inventory::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'available')
                ->count(),

            'low_stock' => Inventory::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('available_quantity_base_units', '<=', 10)
                ->count(),

            'expiring' => Inventory::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', now()->toDateString())
                ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())
                ->count(),

            'expired' => Inventory::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where(function ($query) {
                    $query->where('status', 'expired')
                        ->orWhereDate('expiry_date', '<', now()->toDateString());
                })
                ->count(),
        ];
    }

    private function movementTypes(Pharmacy $pharmacy)
    {
        return InventoryMovement::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->whereNotNull('movement_type')
            ->select('movement_type')
            ->distinct()
            ->orderBy('movement_type')
            ->pluck('movement_type');
    }

    private function guardPharmacy(Inventory $inventory): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $inventory->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }

    private function generateMovementNumber(): string
    {
        do {
            $movementNo = 'MOV-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
        } while (InventoryMovement::query()->where('movement_no', $movementNo)->exists());

        return $movementNo;
    }
}