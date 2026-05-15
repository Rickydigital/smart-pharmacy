<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Inventory\InventoryReceivingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketOrderPurchaseService
{
    public function __construct(
        private InventoryReceivingService $inventoryReceivingService
    ) {}

    public function receiveFromMarketOrder(array $centralOrder, array $mappedItems, int $branchId): Purchase
    {
        return DB::transaction(function () use ($centralOrder, $mappedItems, $branchId) {
            $branch = Branch::query()->findOrFail($branchId);
            $user = Auth::user();

            if (! $user instanceof User) {
                abort(403, 'Authenticated user is required to receive market order.');
            }

            $supplier = $this->firstOrCreateMarketSupplier($centralOrder, (int) $branch->pharmacy_id);

            $purchase = Purchase::query()->create([
                'pharmacy_id' => $branch->pharmacy_id,
                'branch_id' => $branch->id,
                'supplier_id' => $supplier->id,
                'purchase_no' => 'MKTP-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'supplier_invoice_no' => data_get($centralOrder, 'order_no'),
                'purchase_date' => now()->toDateString(),
                'received_date' => null,
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'balance_amount' => 0,
                'payment_status' => 'paid',
                'status' => 'draft',
                'notes' => 'Received from Central market order: ' . data_get($centralOrder, 'order_no', '-'),
                'created_by' => $user->id,
            ]);

            $subtotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;
            $grandTotal = 0;

            foreach ($mappedItems as $item) {
                $product = Product::query()
                    ->where('pharmacy_id', $branch->pharmacy_id)
                    ->where('is_active', true)
                    ->findOrFail($item['product_id']);

                $productUnit = ProductUnit::query()
                    ->where('pharmacy_id', $branch->pharmacy_id)
                    ->where('product_id', $product->id)
                    ->where('is_active', true)
                    ->findOrFail($item['product_unit_id']);

                $quantity = (float) ($item['quantity'] ?? 0);
                $unitCost = (float) ($item['unit_cost'] ?? $item['unit_price'] ?? 0);

                $quantityInBaseUnits = max(1, (int) ($item['quantity_in_base_units'] ?? $productUnit->quantity_in_base_units));
                $totalBaseUnits = (int) round($quantity * $quantityInBaseUnits);

                $lineSubtotal = $quantity * $unitCost;
                $lineDiscount = (float) ($item['line_discount'] ?? 0);
                $lineTax = (float) ($item['line_tax'] ?? 0);
                $lineTotal = max(0, ($lineSubtotal - $lineDiscount) + $lineTax);

                PurchaseItem::query()->create([
                    'pharmacy_id' => $branch->pharmacy_id,
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'product_unit_id' => $productUnit->id,
                    'batch_no' => $item['batch_no'] ?? ('MKT-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4))),
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'quantity' => $quantity,
                    'quantity_in_base_units' => $quantityInBaseUnits,
                    'total_base_units' => $totalBaseUnits,
                    'unit_cost' => $unitCost,
                    'line_discount' => $lineDiscount,
                    'line_tax' => $lineTax,
                    'line_total' => $lineTotal,
                ]);

                $subtotal += $lineSubtotal;
                $totalDiscount += $lineDiscount;
                $totalTax += $lineTax;
                $grandTotal += $lineTotal;
            }

            $purchase->update([
                'subtotal_amount' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'total_amount' => $grandTotal,
                'paid_amount' => $grandTotal,
                'balance_amount' => 0,
                'payment_status' => 'paid',
            ]);

            return $this->inventoryReceivingService->receivePurchase($purchase->fresh(['items.product', 'items.productUnit']), $user);
        });
    }

    private function firstOrCreateMarketSupplier(array $centralOrder, int $pharmacyId): Supplier
    {
        $supplierTenantId = data_get($centralOrder, 'supplier.id');

        if ($supplierTenantId !== null) {
            $supplier = Supplier::query()
                ->where('pharmacy_id', $pharmacyId)
                ->where('supplier_type', 'tenant_pharmacy')
                ->where('central_tenant_id', $supplierTenantId)
                ->first();

            if ($supplier) {
                $supplier->update([
                    'name' => data_get($centralOrder, 'supplier.name', $supplier->name),
                    'phone' => data_get($centralOrder, 'supplier.phone', $supplier->phone),
                    'email' => data_get($centralOrder, 'supplier.email', $supplier->email),
                    'address' => data_get($centralOrder, 'supplier.address', $supplier->address),
                ]);

                return $supplier;
            }
        }

        return Supplier::query()->create([
            'pharmacy_id' => $pharmacyId,
            'name' => data_get($centralOrder, 'supplier.name')
                ?: data_get($centralOrder, 'supplier.pharmacy_name')
                ?: data_get($centralOrder, 'supplier.supplier_name')
                ?: ('Market Supplier #' . data_get($centralOrder, 'supplier.id', 'Unknown')),

            'code' => data_get($centralOrder, 'supplier.code')
                ?: ('MKT-' . data_get($centralOrder, 'supplier.id', strtoupper(Str::random(5)))),

            'contact_person' => data_get($centralOrder, 'supplier.contact_person'),
            'phone' => data_get($centralOrder, 'supplier.phone'),
            'email' => data_get($centralOrder, 'supplier.email'),
            'address' => data_get($centralOrder, 'supplier.address'),

            'notes' => trim(
                'Auto-created from Central market order.' . PHP_EOL .
                    'Central supplier tenant ID: ' . data_get($centralOrder, 'supplier.id', '-') . PHP_EOL .
                    'Central order no: ' . data_get($centralOrder, 'order_no', '-')
            ),

            'supplier_type' => 'tenant_pharmacy',
            'central_tenant_id' => data_get($centralOrder, 'supplier.id'),
            'is_active' => true,
        ]);
    }
}
