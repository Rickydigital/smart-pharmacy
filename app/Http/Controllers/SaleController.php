<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SaleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sale.view', only: ['index', 'show', 'receipt']),
            new Middleware('permission:sale.cancel', only: ['cancel']),
        ];
    }

    public function index(Request $request): View
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        $sales = Sale::query()
            ->with(['branch', 'creator'])
            ->withCount('items')
            ->where('pharmacy_id', $pharmacy->id)
            ->when(! $isAdminOrOwner, function ($query) use ($user) {
                $query->where('created_by', $user?->id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('sale_no', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $query->where('branch_id', $request->input('branch_id'));
            })
            ->when($request->filled('sale_type'), function ($query) use ($request) {
                $query->where('sale_type', $request->input('sale_type'));
            })
            ->when($request->filled('payment_method'), function ($query) use ($request) {
                $query->where('payment_method', $request->input('payment_method'));
            })
            ->when($request->filled('payment_status'), function ($query) use ($request) {
                $query->where('payment_status', $request->input('payment_status'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('sold_at', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('sold_at', '<=', $request->input('date_to'));
            })
            ->latest('sold_at')
            ->paginate(20)
            ->withQueryString();

        $summaryQuery = Sale::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->when(! $isAdminOrOwner, function ($query) use ($user) {
                $query->where('created_by', $user?->id);
            })
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $query->where('branch_id', $request->input('branch_id'));
            })
            ->when($request->filled('sale_type'), function ($query) use ($request) {
                $query->where('sale_type', $request->input('sale_type'));
            })
            ->when($request->filled('payment_method'), function ($query) use ($request) {
                $query->where('payment_method', $request->input('payment_method'));
            })
            ->when($request->filled('payment_status'), function ($query) use ($request) {
                $query->where('payment_status', $request->input('payment_status'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('sold_at', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('sold_at', '<=', $request->input('date_to'));
            });

        $summary = [
            'sales_count' => (clone $summaryQuery)->count(),
            'total_sales' => (float) (clone $summaryQuery)->where('status', 'completed')->sum('total_amount'),
            'total_paid' => (float) (clone $summaryQuery)->where('status', 'completed')->sum('paid_amount'),
            'total_profit' => (float) (clone $summaryQuery)
                ->where('status', 'completed')
                ->withSum('items as profit_sum', 'profit_amount')
                ->get()
                ->sum('profit_sum'),
        ];

        $branches = Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        return view('sales.index', compact(
            'sales',
            'branches',
            'summary',
            'isAdminOrOwner'
        ));
    }

    public function show(Sale $sale): JsonResponse
    {
        $this->guardPharmacy($sale);

        $sale->load([
            'branch',
            'creator',
            'items.product',
            'items.productUnit.unit',
        ]);

        $items = $sale->items->map(function ($item) {
            return [
                'product' => $item->product?->name,
                'unit' => $item->productUnit?->unit?->name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_discount' => (float) $item->line_discount,
                'line_total' => (float) $item->line_total,
                'total_cost' => (float) $item->total_cost,
                'profit_amount' => (float) $item->profit_amount,
            ];
        });

        return response()->json([
            'ok' => true,
            'sale' => [
                'id' => $sale->id,
                'sale_no' => $sale->sale_no,
                'branch' => $sale->branch?->name,
                'customer' => $sale->displayCustomer(),
                'customer_phone' => $sale->customer_phone,
                'cashier' => $this->userLabel($sale->creator),
                'sale_type' => ucfirst($sale->sale_type),
                'payment_method' => str_replace('_', ' ', ucfirst($sale->payment_method)),
                'payment_status' => ucfirst($sale->payment_status),
                'status' => ucfirst($sale->status),
                'subtotal_amount' => (float) $sale->subtotal_amount,
                'discount_amount' => (float) $sale->discount_amount,
                'tax_amount' => (float) $sale->tax_amount,
                'total_amount' => (float) $sale->total_amount,
                'paid_amount' => (float) $sale->paid_amount,
                'change_amount' => (float) $sale->change_amount,
                'balance_amount' => (float) $sale->balance_amount,
                'sold_at' => $sale->sold_at?->format('d M Y h:i A'),
                'items' => $items,
                'receipt_url' => route('sales.receipt', $sale),
            ],
        ]);
    }

    public function receipt(Sale $sale): JsonResponse
    {
        $this->guardPharmacy($sale);

        $sale->load([
            'pharmacy',
            'branch',
            'creator',
            'items.product',
            'items.productUnit.unit',
        ]);

        $html = view('pos.partials.receipt', [
            'sale' => $sale,
            'cashierName' => $this->userLabel($sale->creator),
        ])->render();

        return response()->json([
            'ok' => true,
            'sale_no' => $sale->sale_no,
            'html' => $html,
        ]);
    }

    public function cancel(Request $request, Sale $sale): RedirectResponse
{
    $this->guardPharmacy($sale);

    if (! $sale->isCompleted()) {
        return back()->with('error', 'Only completed sales can be cancelled.');
    }

    $validated = $request->validate([
        'reason' => ['required', 'string', 'max:500'],
    ]);

    try {
        DB::transaction(function () use ($sale, $validated) {
            $sale->load('items');

            foreach ($sale->items as $item) {
                $allocations = is_array($item->inventory_allocations)
                    ? $item->inventory_allocations
                    : [];

                foreach ($allocations as $allocation) {
                    $inventoryId = $allocation['inventory_id'] ?? null;
                    $quantity = (int) ($allocation['quantity_base_units'] ?? 0);

                    if (! $inventoryId || $quantity <= 0) {
                        continue;
                    }

                    /** @var \App\Models\Inventory|null $inventory */
                    $inventory = Inventory::query()
                        ->where('pharmacy_id', $sale->pharmacy_id)
                        ->where('branch_id', $sale->branch_id)
                        ->whereKey($inventoryId)
                        ->lockForUpdate()
                        ->first();

                    if (! $inventory) {
                        continue;
                    }

                    $before = (int) $inventory->available_quantity_base_units;
                    $after = $before + $quantity;

                    $wasExpired = $inventory->status === 'expired';

                    $inventory->forceFill([
                        'available_quantity_base_units' => $after,
                        'status' => $wasExpired ? 'expired' : 'available',
                        'is_active' => ! $wasExpired,
                    ])->save();

                    InventoryMovement::query()->create([
                        'pharmacy_id' => $sale->pharmacy_id,
                        'branch_id' => $sale->branch_id,
                        'product_id' => $item->product_id,
                        'inventory_id' => $inventory->id,
                        'movement_no' => $this->generateMovementNumber(),
                        'movement_type' => 'sale_cancel_return',
                        'direction' => 'in',
                        'quantity_base_units' => $quantity,
                        'balance_before_base_units' => $before,
                        'balance_after_base_units' => $after,
                        'source_type' => Sale::class,
                        'source_id' => $sale->id,
                        'reason' => $validated['reason'],
                        'created_by' => Auth::id(),
                        'moved_at' => now(),
                    ]);
                }
            }

            $sale->forceFill([
                'status' => 'cancelled',
                'notes' => trim(($sale->notes ? $sale->notes . "\n" : '') . 'Cancelled: ' . $validated['reason']),
            ])->save();

            activity()
                ->useLog('sale')
                ->event('cancelled')
                ->performedOn($sale)
                ->causedBy(Auth::user())
                ->withProperties([
                    'sale_id' => $sale->id,
                    'sale_no' => $sale->sale_no,
                    'reason' => $validated['reason'],
                ])
                ->log('Sale cancelled and inventory restored');
        });

        return back()->with('success', 'Sale cancelled and inventory restored successfully.');
    } catch (\Throwable $exception) {
        return back()->with('error', 'Sale cancellation failed: ' . $exception->getMessage());
    }
}

    private function guardPharmacy(Sale $sale): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $sale->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }

    private function userLabel($user): string
    {
        if (! $user) {
            return '-';
        }

        if (method_exists($user, 'displayName')) {
            return $user->displayName();
        }

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        return $name ?: ($user->name ?? $user->username ?? $user->email ?? 'User');
    }

    private function generateMovementNumber(): string
    {
        do {
            $movementNo = 'MOV-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
        } while (InventoryMovement::query()->where('movement_no', $movementNo)->exists());

        return $movementNo;
    }
}