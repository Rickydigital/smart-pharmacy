<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:stock.view', only: ['index']),
            new Middleware('permission:stock.adjust', only: ['adjust', 'toggleBlock', 'markExpired']),
        ];
    }

    public function index(Request $request): View
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
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $query->where('branch_id', $request->input('branch_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('expiry_from'), function ($query) use ($request) {
                $query->whereDate('expiry_date', '>=', $request->input('expiry_from'));
            })
            ->when($request->filled('expiry_to'), function ($query) use ($request) {
                $query->whereDate('expiry_date', '<=', $request->input('expiry_to'));
            })
            ->when($request->boolean('low_stock'), function ($query) {
                $query->where('available_quantity_base_units', '<=', 10);
            })
            ->orderByRaw("CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END")
            ->orderBy('expiry_date')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $counts = [
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

        return view('inventory.index', compact(
            'inventories',
            'branches',
            'counts'
        ));
    }

    public function adjust(Request $request, Inventory $inventory): RedirectResponse
    {
        $this->guardPharmacy($inventory);

        $validated = $request->validate([
            'direction' => ['required', Rule::in(['in', 'out'])],
            'quantity_base_units' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
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

            return back()->with('success', 'Inventory adjusted successfully.');
        } catch (\Throwable $exception) {
            return back()->with('error', 'Adjustment failed: ' . $exception->getMessage());
        }
    }

    public function toggleBlock(Inventory $inventory): RedirectResponse
    {
        $this->guardPharmacy($inventory);

        if ($inventory->status === 'blocked') {
            $inventory->update([
                'status' => $inventory->available_quantity_base_units > 0 ? 'available' : 'depleted',
                'is_active' => true,
            ]);

            return back()->with('success', 'Inventory unblocked successfully.');
        }

        $inventory->update([
            'status' => 'blocked',
            'is_active' => false,
        ]);

        return back()->with('success', 'Inventory blocked successfully.');
    }

    public function markExpired(Inventory $inventory): RedirectResponse
    {
        $this->guardPharmacy($inventory);

        if ((int) $inventory->available_quantity_base_units <= 0) {
            $inventory->update([
                'status' => 'expired',
                'available_quantity_base_units' => 0,
            ]);

            return back()->with('success', 'Inventory marked as expired.');
        }

        DB::transaction(function () use ($inventory) {
            $inventory->refresh();

            $before = (int) $inventory->available_quantity_base_units;
            $quantity = $before;

            $inventory->update([
                'status' => 'expired',
                'available_quantity_base_units' => 0,
                'is_active' => false,
            ]);

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
        });

        return back()->with('success', 'Inventory marked as expired.');
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