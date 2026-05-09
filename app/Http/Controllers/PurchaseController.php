<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Services\Inventory\InventoryReceivingService;
use App\Services\Purchase\PurchaseNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Services\SystemNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PurchaseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:purchase.view', only: ['index']),
            new Middleware('permission:purchase.manage', except: ['index']),
        ];
    }

    public function index(Request $request): View
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $purchases = Purchase::query()
            ->with([
                'branch',
                'supplier',
                'creator',
                'receiver',
                'items.product',
                'items.productUnit.unit',
            ])
            ->withCount('items')
            ->where('pharmacy_id', $pharmacy->id)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('purchase_no', 'like', "%{$search}%")
                        ->orWhere('supplier_invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                            $supplierQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('payment_status'), function ($query) use ($request) {
                $query->where('payment_status', $request->input('payment_status'));
            })
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('supplier_id', $request->input('supplier_id'));
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('purchase_date', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('purchase_date', '<=', $request->input('date_to'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with(['baseUnit', 'units.unit'])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $productUnits = ProductUnit::query()
            ->with(['unit', 'product'])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->where('can_purchase', true)
            ->orderBy('product_id')
            ->orderBy('quantity_in_base_units')
            ->get();

        $counts = [
            'all' => Purchase::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->count(),

            'draft' => Purchase::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'draft')
                ->count(),

            'received' => Purchase::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'received')
                ->count(),

            'cancelled' => Purchase::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'cancelled')
                ->count(),
        ];

        return view('purchases.index', compact(
            'purchases',
            'suppliers',
            'branches',
            'products',
            'productUnits',
            'counts'
        ));
    }

    public function store(Request $request, PurchaseNumberService $purchaseNumberService, SystemNotificationService $notifier): RedirectResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'supplier_id' => [
                'nullable',
                Rule::exists('suppliers', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'supplier_invoice_no' => ['nullable', 'string', 'max:120'],
            'purchase_date' => ['required', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $purchase = Purchase::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'branch_id' => $validated['branch_id'],
            'supplier_id' => $validated['supplier_id'] ?? null,
            'purchase_no' => $purchaseNumberService->generate(),
            'supplier_invoice_no' => $validated['supplier_invoice_no'] ?? null,
            'purchase_date' => $validated['purchase_date'],
            'received_date' => null,
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'paid_amount' => $validated['paid_amount'] ?? 0,
            'balance_amount' => 0,
            'payment_status' => 'unpaid',
            'status' => 'draft',
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $this->recalculatePurchaseTotals($purchase);

        activity()
            ->useLog('purchase')
            ->event('created')
            ->performedOn($purchase)
            ->causedBy(Auth::user())
            ->withProperties([
                'purchase_id' => $purchase->id,
                'purchase_no' => $purchase->purchase_no,
            ])
            ->log('Purchase created');
        $notifier->notifyPurchaseCreated($purchase);

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase created successfully. Add purchase items now.');
    }

    public function update(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->guardPharmacy($purchase);

        if (! $purchase->isDraft()) {
            return back()->with('error', 'Only draft purchases can be edited.');
        }

        $validated = $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $purchase->pharmacy_id),
            ],
            'supplier_id' => [
                'nullable',
                Rule::exists('suppliers', 'id')->where('pharmacy_id', $purchase->pharmacy_id),
            ],
            'supplier_invoice_no' => ['nullable', 'string', 'max:120'],
            'purchase_date' => ['required', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $purchase->update([
            'branch_id' => $validated['branch_id'],
            'supplier_id' => $validated['supplier_id'] ?? null,
            'supplier_invoice_no' => $validated['supplier_invoice_no'] ?? null,
            'purchase_date' => $validated['purchase_date'],
            'paid_amount' => $validated['paid_amount'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->recalculatePurchaseTotals($purchase);

        activity()
            ->useLog('purchase')
            ->event('updated')
            ->performedOn($purchase)
            ->causedBy(Auth::user())
            ->log('Purchase updated');

        return back()->with('success', 'Purchase updated successfully.');
    }

    public function storeItem(Request $request, Purchase $purchase): RedirectResponse
{
    $this->guardPharmacy($purchase);

    if (! $purchase->isDraft()) {
        return back()->with('error', 'Items can only be added to draft purchases.');
    }

    $validated = $request->validate([
        'product_id' => [
            'required',
            Rule::exists('products', 'id')->where('pharmacy_id', $purchase->pharmacy_id),
        ],
        'product_unit_id' => [
            'required',
            Rule::exists('product_units', 'id')->where('pharmacy_id', $purchase->pharmacy_id),
        ],
        'expiry_date' => ['nullable', 'date'],
        'quantity' => ['required', 'integer', 'min:1'],
        'item_amount' => ['required', 'numeric', 'min:0'],
    ]);

    $productUnit = ProductUnit::query()
        ->where('pharmacy_id', $purchase->pharmacy_id)
        ->where('product_id', $validated['product_id'])
        ->where('can_purchase', true)
        ->findOrFail($validated['product_unit_id']);

    $quantity = (int) $validated['quantity'];
    $quantityInBaseUnits = max(1, (int) $productUnit->quantity_in_base_units);
    $totalBaseUnits = $quantity * $quantityInBaseUnits;

    $itemAmount = (float) $validated['item_amount'];
    $currentItemsTotal = (float) $purchase->items()->sum('line_total');
    $allowedAmount = (float) $purchase->paid_amount;
    $newItemsTotal = $currentItemsTotal + $itemAmount;

    if ($allowedAmount > 0 && $newItemsTotal > $allowedAmount) {
        return back()->with('error', 'Item amount is too high. Total purchase items cannot exceed the paid amount of ' . number_format($allowedAmount, 2) . '.');
    }

    $unitCost = $quantity > 0
        ? $itemAmount / $quantity
        : 0;

    PurchaseItem::query()->create([
        'pharmacy_id' => $purchase->pharmacy_id,
        'purchase_id' => $purchase->id,
        'product_id' => $validated['product_id'],
        'product_unit_id' => $productUnit->id,

        // Batch number is generated later when receiving inventory.
        'batch_no' => null,

        'expiry_date' => $validated['expiry_date'] ?? null,
        'quantity' => $quantity,
        'quantity_in_base_units' => $quantityInBaseUnits,
        'total_base_units' => $totalBaseUnits,

        // Unit cost here means cost per selected purchase unit.
        // Example: amount 100,000 / 10 boxes = 10,000 per box.
        'unit_cost' => $unitCost,

        'line_discount' => 0,
        'line_tax' => 0,
        'line_total' => $itemAmount,
    ]);

    $this->recalculatePurchaseTotals($purchase);

    return back()->with('success', 'Purchase item added successfully.');
}
    public function updateItem(Request $request, PurchaseItem $purchaseItem): RedirectResponse
{
    $purchaseItem->load('purchase');

    $purchase = $purchaseItem->purchase;

    $this->guardPharmacy($purchase);

    if (! $purchase->isDraft()) {
        return back()->with('error', 'Items can only be edited while purchase is draft.');
    }

    $validated = $request->validate([
        'product_unit_id' => [
            'required',
            Rule::exists('product_units', 'id')->where('pharmacy_id', $purchase->pharmacy_id),
        ],
        'expiry_date' => ['nullable', 'date'],
        'quantity' => ['required', 'integer', 'min:1'],
        'item_amount' => ['required', 'numeric', 'min:0'],
    ]);

    $productUnit = ProductUnit::query()
        ->where('pharmacy_id', $purchase->pharmacy_id)
        ->where('product_id', $purchaseItem->product_id)
        ->where('can_purchase', true)
        ->findOrFail($validated['product_unit_id']);

    $quantity = (int) $validated['quantity'];
    $quantityInBaseUnits = max(1, (int) $productUnit->quantity_in_base_units);
    $totalBaseUnits = $quantity * $quantityInBaseUnits;

    $itemAmount = (float) $validated['item_amount'];
    $currentItemsTotal = (float) $purchase->items()
        ->where('id', '!=', $purchaseItem->id)
        ->sum('line_total');

    $allowedAmount = (float) $purchase->paid_amount;
    $newItemsTotal = $currentItemsTotal + $itemAmount;

    if ($allowedAmount > 0 && $newItemsTotal > $allowedAmount) {
        return back()->with('error', 'Item amount is too high. Total purchase items cannot exceed the paid amount of ' . number_format($allowedAmount, 2) . '.');
    }

    $unitCost = $quantity > 0
        ? $itemAmount / $quantity
        : 0;

    $purchaseItem->update([
        'product_unit_id' => $productUnit->id,

        // Batch number remains system-generated.
        'batch_no' => null,

        'expiry_date' => $validated['expiry_date'] ?? null,
        'quantity' => $quantity,
        'quantity_in_base_units' => $quantityInBaseUnits,
        'total_base_units' => $totalBaseUnits,
        'unit_cost' => $unitCost,
        'line_discount' => 0,
        'line_tax' => 0,
        'line_total' => $itemAmount,
    ]);

    $this->recalculatePurchaseTotals($purchase);

    return back()->with('success', 'Purchase item updated successfully.');
}

    public function destroyItem(PurchaseItem $purchaseItem): RedirectResponse
    {
        $purchaseItem->load('purchase');
        $purchase = $purchaseItem->purchase;

        $this->guardPharmacy($purchase);

        if (! $purchase->isDraft()) {
            return back()->with('error', 'Items can only be removed while purchase is draft.');
        }

        PurchaseItem::query()
            ->whereKey($purchaseItem->id)
            ->delete();

        $this->recalculatePurchaseTotals($purchase);

        return back()->with('success', 'Purchase item removed successfully.');
    }

    public function receive(Purchase $purchase, InventoryReceivingService $receivingService): RedirectResponse
    {
        $this->guardPharmacy($purchase);

        try {
            $receivingService->receivePurchase($purchase, Auth::user());

            return back()->with('success', 'Purchase received successfully and inventory updated.');
        } catch (\Throwable $exception) {
            return back()->with('error', 'Receiving failed: ' . $exception->getMessage());
        }
    }

    public function cancel(Purchase $purchase): RedirectResponse
    {
        $this->guardPharmacy($purchase);

        if (! $purchase->isDraft()) {
            return back()->with('error', 'Only draft purchases can be cancelled.');
        }

        $purchase->update([
            'status' => 'cancelled',
        ]);

        activity()
            ->useLog('purchase')
            ->event('cancelled')
            ->performedOn($purchase)
            ->causedBy(Auth::user())
            ->log('Purchase cancelled');

        return back()->with('success', 'Purchase cancelled successfully.');
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        $this->guardPharmacy($purchase);

        if (! $purchase->isDraft() && ! $purchase->isCancelled()) {
            return back()->with('error', 'Received purchases cannot be deleted.');
        }

        Purchase::query()
            ->whereKey($purchase->id)
            ->delete();

        return back()->with('success', 'Purchase deleted successfully.');
    }

    private function recalculatePurchaseTotals(Purchase $purchase): void
    {
        $purchase->load('items');

        $subtotal = $purchase->items->sum(function (PurchaseItem $item) {
            return (float) $item->unit_cost * (int) $item->quantity;
        });

        $discount = $purchase->items->sum('line_discount');
        $tax = $purchase->items->sum('line_tax');
        $total = $purchase->items->sum('line_total');
        $paid = (float) $purchase->paid_amount;
        $balance = max(0, $total - $paid);

        $paymentStatus = 'unpaid';

        if ($paid >= $total && $total > 0) {
            $paymentStatus = 'paid';
        } elseif ($paid > 0 && $paid < $total) {
            $paymentStatus = 'partial';
        }

        $purchase->update([
            'subtotal_amount' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'balance_amount' => $balance,
            'payment_status' => $paymentStatus,
        ]);
    }

    private function guardPharmacy(Purchase $purchase): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $purchase->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }
}