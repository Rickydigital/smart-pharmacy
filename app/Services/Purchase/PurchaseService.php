<?php

namespace App\Services\Purchase;

use App\Models\Branch;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Inventory\InventoryReceivingService;
use App\Services\SystemNotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseService
{
    public function __construct(
        private PurchaseNumberService $purchaseNumberService,
        private SystemNotificationService $notifier,
        private InventoryReceivingService $receivingService,
    ) {
    }

    public function listData(Pharmacy $pharmacy, array $filters = []): array
    {
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
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $search = trim((string) $filters['search']);

                $query->where(function ($q) use ($search) {
                    $q->where('purchase_no', 'like', "%{$search}%")
                        ->orWhere('supplier_invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                            $supplierQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['payment_status']), fn ($query) => $query->where('payment_status', $filters['payment_status']))
            ->when(! empty($filters['supplier_id']), fn ($query) => $query->where('supplier_id', $filters['supplier_id']))
            ->when(! empty($filters['date_from']), fn ($query) => $query->whereDate('purchase_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($query) => $query->whereDate('purchase_date', '<=', $filters['date_to']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return [
            'purchases' => $purchases,
            'suppliers' => $this->suppliers($pharmacy),
            'branches' => $this->branches($pharmacy),
            'products' => $this->products($pharmacy),
            'productUnits' => $this->productUnits($pharmacy),
            'counts' => $this->counts($pharmacy),
        ];
    }

    public function create(Pharmacy $pharmacy, array $data, ?User $user = null): Purchase
    {
        $purchase = Purchase::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'branch_id' => $data['branch_id'],
            'supplier_id' => $data['supplier_id'] ?? null,
            'purchase_no' => $this->purchaseNumberService->generate(),
            'supplier_invoice_no' => $data['supplier_invoice_no'] ?? null,
            'purchase_date' => $data['purchase_date'],
            'received_date' => null,
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'paid_amount' => $data['paid_amount'] ?? 0,
            'balance_amount' => 0,
            'payment_status' => 'unpaid',
            'status' => 'draft',
            'notes' => $data['notes'] ?? null,
            'created_by' => $user?->id,
        ]);

        $this->recalculateTotals($purchase);

        activity()
            ->useLog('purchase')
            ->event('created')
            ->performedOn($purchase)
            ->causedBy($user)
            ->withProperties([
                'purchase_id' => $purchase->id,
                'purchase_no' => $purchase->purchase_no,
            ])
            ->log('Purchase created');

        $this->notifier->notifyPurchaseCreated($purchase);

        return $purchase->fresh([
            'branch',
            'supplier',
            'items.product',
            'items.productUnit.unit',
        ]);
    }

    public function update(Purchase $purchase, array $data, ?User $user = null): Purchase
    {
        $this->guardPharmacy($purchase);

        if (! $purchase->isDraft()) {
            throw new RuntimeException('Only draft purchases can be edited.');
        }

        $purchase->update([
            'branch_id' => $data['branch_id'],
            'supplier_id' => $data['supplier_id'] ?? null,
            'supplier_invoice_no' => $data['supplier_invoice_no'] ?? null,
            'purchase_date' => $data['purchase_date'],
            'paid_amount' => $data['paid_amount'] ?? 0,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->recalculateTotals($purchase);

        activity()
            ->useLog('purchase')
            ->event('updated')
            ->performedOn($purchase)
            ->causedBy($user)
            ->log('Purchase updated');

        return $purchase->fresh([
            'branch',
            'supplier',
            'items.product',
            'items.productUnit.unit',
        ]);
    }

    public function addItem(Purchase $purchase, array $data): PurchaseItem
    {
        $this->guardPharmacy($purchase);

        if (! $purchase->isDraft()) {
            throw new RuntimeException('Items can only be added to draft purchases.');
        }

        $productUnit = ProductUnit::query()
            ->where('pharmacy_id', $purchase->pharmacy_id)
            ->where('product_id', $data['product_id'])
            ->where('can_purchase', true)
            ->findOrFail($data['product_unit_id']);

        $quantity = (int) $data['quantity'];
        $quantityInBaseUnits = max(1, (int) $productUnit->quantity_in_base_units);
        $totalBaseUnits = $quantity * $quantityInBaseUnits;
        $itemAmount = (float) $data['item_amount'];

        $this->ensureAllowedAmount($purchase, $itemAmount);

        $item = PurchaseItem::query()->create([
            'pharmacy_id' => $purchase->pharmacy_id,
            'purchase_id' => $purchase->id,
            'product_id' => $data['product_id'],
            'product_unit_id' => $productUnit->id,
            'batch_no' => null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'quantity' => $quantity,
            'quantity_in_base_units' => $quantityInBaseUnits,
            'total_base_units' => $totalBaseUnits,
            'unit_cost' => $quantity > 0 ? $itemAmount / $quantity : 0,
            'line_discount' => 0,
            'line_tax' => 0,
            'line_total' => $itemAmount,
        ]);

        $this->recalculateTotals($purchase);

        return $item->fresh(['product', 'productUnit.unit']);
    }

    public function updateItem(PurchaseItem $purchaseItem, array $data): PurchaseItem
    {
        $purchaseItem->load('purchase');

        $purchase = $purchaseItem->purchase;

        $this->guardPharmacy($purchase);

        if (! $purchase->isDraft()) {
            throw new RuntimeException('Items can only be edited while purchase is draft.');
        }

        $productUnit = ProductUnit::query()
            ->where('pharmacy_id', $purchase->pharmacy_id)
            ->where('product_id', $purchaseItem->product_id)
            ->where('can_purchase', true)
            ->findOrFail($data['product_unit_id']);

        $quantity = (int) $data['quantity'];
        $quantityInBaseUnits = max(1, (int) $productUnit->quantity_in_base_units);
        $totalBaseUnits = $quantity * $quantityInBaseUnits;
        $itemAmount = (float) $data['item_amount'];

        $this->ensureAllowedAmount($purchase, $itemAmount, $purchaseItem);

        $purchaseItem->update([
            'product_unit_id' => $productUnit->id,
            'batch_no' => null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'quantity' => $quantity,
            'quantity_in_base_units' => $quantityInBaseUnits,
            'total_base_units' => $totalBaseUnits,
            'unit_cost' => $quantity > 0 ? $itemAmount / $quantity : 0,
            'line_discount' => 0,
            'line_tax' => 0,
            'line_total' => $itemAmount,
        ]);

        $this->recalculateTotals($purchase);

        return $purchaseItem->fresh(['product', 'productUnit.unit']);
    }

    public function deleteItem(PurchaseItem $purchaseItem): Purchase
    {
        $purchaseItem->load('purchase');

        $purchase = $purchaseItem->purchase;

        $this->guardPharmacy($purchase);

        if (! $purchase->isDraft()) {
            throw new RuntimeException('Items can only be removed while purchase is draft.');
        }

         PurchaseItem::query()
            ->whereKey($purchaseItem->id)
            ->delete();

        $this->recalculateTotals($purchase);

        return $purchase->fresh([
            'branch',
            'supplier',
            'items.product',
            'items.productUnit.unit',
        ]);
    }

    public function receive(Purchase $purchase, ?User $user = null): Purchase
    {
        $this->guardPharmacy($purchase);

        $this->receivingService->receivePurchase($purchase, $user);

        return $purchase->fresh([
            'branch',
            'supplier',
            'items.product',
            'items.productUnit.unit',
        ]);
    }

    public function cancel(Purchase $purchase, ?User $user = null): Purchase
    {
        $this->guardPharmacy($purchase);

        if (! $purchase->isDraft()) {
            throw new RuntimeException('Only draft purchases can be cancelled.');
        }

        $purchase->update([
            'status' => 'cancelled',
        ]);

        activity()
            ->useLog('purchase')
            ->event('cancelled')
            ->performedOn($purchase)
            ->causedBy($user)
            ->log('Purchase cancelled');

        return $purchase->fresh([
            'branch',
            'supplier',
            'items.product',
            'items.productUnit.unit',
        ]);
    }

    public function delete(Purchase $purchase): void
    {
        $this->guardPharmacy($purchase);

        if (! $purchase->isDraft() && ! $purchase->isCancelled()) {
            throw new RuntimeException('Received purchases cannot be deleted.');
        }

         Purchase::query()
            ->whereKey($purchase->id)
            ->delete();
    }

    public function recalculateTotals(Purchase $purchase): void
    {
        $purchase->load('items');

        $subtotal = $purchase->items->sum(fn (PurchaseItem $item) => (float) $item->unit_cost * (int) $item->quantity);
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

    private function ensureAllowedAmount(
        Purchase $purchase,
        float $itemAmount,
        ?PurchaseItem $exceptItem = null
    ): void {
        $query = $purchase->items();

        if ($exceptItem) {
            $query->where('id', '!=', $exceptItem->id);
        }

        $currentItemsTotal = (float) $query->sum('line_total');
        $allowedAmount = (float) $purchase->paid_amount;
        $newItemsTotal = $currentItemsTotal + $itemAmount;

        if ($allowedAmount > 0 && $newItemsTotal > $allowedAmount) {
            throw new RuntimeException(
                'Item amount is too high. Total purchase items cannot exceed the paid amount of '
                . number_format($allowedAmount, 2) . '.'
            );
        }
    }

    public function guardPharmacy(Purchase $purchase): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $purchase->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }

    public function suppliers(Pharmacy $pharmacy): Collection
    {
        return Supplier::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function branches(Pharmacy $pharmacy): Collection
    {
        return Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();
    }

    public function products(Pharmacy $pharmacy): Collection
    {
        return Product::query()
            ->with(['baseUnit', 'units.unit'])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function productUnits(Pharmacy $pharmacy): Collection
    {
        return ProductUnit::query()
            ->with(['unit', 'product'])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->where('can_purchase', true)
            ->orderBy('product_id')
            ->orderBy('quantity_in_base_units')
            ->get();
    }

    public function counts(Pharmacy $pharmacy): array
    {
        return [
            'all' => Purchase::query()->where('pharmacy_id', $pharmacy->id)->count(),
            'draft' => Purchase::query()->where('pharmacy_id', $pharmacy->id)->where('status', 'draft')->count(),
            'received' => Purchase::query()->where('pharmacy_id', $pharmacy->id)->where('status', 'received')->count(),
            'cancelled' => Purchase::query()->where('pharmacy_id', $pharmacy->id)->where('status', 'cancelled')->count(),
        ];
    }
}