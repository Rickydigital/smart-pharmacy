<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketOrderSaleService
{
    public function createSaleFromMarketOrder(array $centralOrder, array $mappedItems, int $branchId): Sale
    {
        return DB::transaction(function () use ($centralOrder, $mappedItems, $branchId) {
            $branch = Branch::query()->findOrFail($branchId);

            $sale = Sale::query()->create([
                'pharmacy_id' => $branch->pharmacy_id,
                'branch_id' => $branch->id,
                'sale_no' => 'MKTS-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'customer_name' => data_get($centralOrder, 'buyer.name', 'Market Buyer'),
                'customer_phone' => data_get($centralOrder, 'buyer.phone'),
                'sale_type' => 'market_order',
                'payment_status' => 'unpaid',
                'status' => 'pending',
                'total_amount' => 0,
                'discount_amount' => 0,
                'paid_amount' => 0,
                'balance_amount' => 0,
                'notes' => 'Central market order: ' . data_get($centralOrder, 'order_no', '-'),
                'sold_at' => now(),
            ]);

            $total = 0;

            foreach ($mappedItems as $item) {
                $product = Product::query()
                    ->where('pharmacy_id', $branch->pharmacy_id)
                    ->findOrFail($item['product_id']);

                $unit = null;

                if (! empty($item['product_unit_id'])) {
                    $unit = ProductUnit::query()
                        ->where('product_id', $product->id)
                        ->find($item['product_unit_id']);
                }

                $quantity = (float) $item['quantity'];
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $lineTotal = $quantity * $unitPrice;

                SaleItem::query()->create([
                    'pharmacy_id' => $branch->pharmacy_id,
                    'branch_id' => $branch->id,
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_unit_id' => $unit?->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                    'discount_amount' => 0,
                    'market_order_item_id' => $item['market_order_item_id'] ?? null,
                    'note' => $item['note'] ?? null,
                ]);

                $total += $lineTotal;
            }

            $sale->update([
                'total_amount' => $total,
                'balance_amount' => $total,
            ]);

            return $sale->fresh(['items.product']);
        });
    }
}
