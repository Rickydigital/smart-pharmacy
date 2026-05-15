<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\Inventory\InventorySellingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketOrderSaleService
{
    public function __construct(
        private InventorySellingService $inventorySellingService
    ) {
    }

    public function createSaleFromMarketOrder(
        array $centralOrder,
        array $mappedItems,
        int $branchId,
        string $paymentMethod = 'credit',
        float $paidAmount = 0
    ): Sale {
        return DB::transaction(function () use (
            $centralOrder,
            $mappedItems,
            $branchId,
            $paymentMethod,
            $paidAmount
        ) {
            $branch = Branch::query()->findOrFail($branchId);

            $sale = Sale::query()->create([
                'pharmacy_id' => $branch->pharmacy_id,
                'branch_id' => $branch->id,
                'sale_no' => 'MKTS-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'customer_name' => data_get($centralOrder, 'buyer.name', 'Market Buyer'),
                'customer_phone' => data_get($centralOrder, 'buyer.phone'),
                'sale_type' => 'market_order',

                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'change_amount' => 0,
                'balance_amount' => 0,

                'payment_method' => $paymentMethod,
                'payment_status' => 'unpaid',
                'status' => 'pending',

                'notes' => trim(
                    'Central market order: ' . data_get($centralOrder, 'order_no', '-') .
                    PHP_EOL .
                    (string) data_get($centralOrder, 'supplier_note', '')
                ),
                'sold_at' => now(),
            ]);

            $subtotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;
            $grandTotal = 0;
            $totalPaid = max(0, $paidAmount);

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
                $unitPrice = (float) ($item['unit_price'] ?? 0);

                $quantityInBaseUnits = max(
                    1,
                    (int) ($item['quantity_in_base_units'] ?? $productUnit->quantity_in_base_units)
                );

                $totalBaseUnits = (int) round($quantity * $quantityInBaseUnits);

                if ($quantity <= 0 || $totalBaseUnits <= 0) {
                    abort(422, 'Invalid quantity for market order item.');
                }

                $lineSubtotal = $quantity * $unitPrice;
                $lineDiscount = (float) ($item['line_discount'] ?? 0);
                $lineTax = (float) ($item['line_tax'] ?? 0);
                $lineTotal = max(0, ($lineSubtotal - $lineDiscount) + $lineTax);

                $sellResult = $this->inventorySellingService->sell(
                    sale: $sale,
                    product: $product,
                    productUnit: $productUnit,
                    quantity: $quantity
                );

                $totalCost = (float) ($sellResult['total_cost'] ?? 0);
                $costPerBaseUnit = (float) ($sellResult['cost_per_base_unit'] ?? 0);
                $profitAmount = $lineTotal - $totalCost;

                SaleItem::query()->create([
                    'pharmacy_id' => $branch->pharmacy_id,
                    'branch_id' => $branch->id,
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_unit_id' => $productUnit->id,

                    'quantity' => $quantity,
                    'quantity_in_base_units' => $quantityInBaseUnits,
                    'total_base_units' => $totalBaseUnits,

                    'unit_price' => $unitPrice,
                    'line_discount' => $lineDiscount,
                    'line_tax' => $lineTax,
                    'line_total' => $lineTotal,

                    'cost_per_base_unit' => $costPerBaseUnit,
                    'total_cost' => $totalCost,
                    'profit_amount' => $profitAmount,

                    'inventory_allocations' => [
                        'source' => 'market_order',
                        'central_order_id' => data_get($centralOrder, 'id'),
                        'central_order_no' => data_get($centralOrder, 'order_no'),
                        'market_order_item_id' => $item['market_order_item_id'] ?? null,
                        'allocations' => $sellResult['allocations'] ?? [],
                    ],
                ]);

                $subtotal += $lineSubtotal;
                $totalDiscount += $lineDiscount;
                $totalTax += $lineTax;
                $grandTotal += $lineTotal;
            }

            $totalPaid = $grandTotal;
            $balance = 0;
            $paymentStatus = 'paid';

            $sale->update([
                'subtotal_amount' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'total_amount' => $grandTotal,
                'paid_amount' => $totalPaid,
                'change_amount' => 0,
                'balance_amount' => $balance,
                'payment_status' => $paymentStatus,
                'status' => $paymentStatus === 'paid' ? 'completed' : 'pending',
            ]);

            return $sale->fresh(['items.product', 'items.productUnit.unit']);
        });
    }
}