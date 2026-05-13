<?php

namespace App\Services\Pos;

use App\Models\DailyClosing;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\Inventory\InventorySellingService;
use App\Services\Sale\SaleNumberService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PosCheckoutService
{
    public function __construct(
        private SaleNumberService $saleNumberService,
        private InventorySellingService $inventorySellingService,
    ) {
    }

    public function checkout(Pharmacy $pharmacy, array $data, ?User $user = null): Sale
    {
        $mobileReference = trim((string) ($data['mobile_reference'] ?? ''));

        if ($mobileReference !== '') {
            $existingSale = Sale::query()
                ->with(['branch', 'creator', 'items.product', 'items.productUnit.unit'])
                ->where('pharmacy_id', $pharmacy->id)
                ->where('mobile_reference', $mobileReference)
                ->first();

            if ($existingSale) {
                return $existingSale;
            }
        }

        $sale = DB::transaction(function () use ($pharmacy, $data, $user) {
            $saleType = $data['sale_type'] ?? 'retail';
            $branchId = (int) $data['branch_id'];

            $preparedItems = [];
            $subtotalAmount = 0;
            $itemDiscountAmount = 0;

            foreach ($data['items'] as $itemData) {
                $product = Product::query()
                    ->where('pharmacy_id', $pharmacy->id)
                    ->where('is_active', true)
                    ->findOrFail($itemData['product_id']);

                $productUnit = ProductUnit::query()
                    ->with(['unit', 'prices'])
                    ->where('pharmacy_id', $pharmacy->id)
                    ->where('product_id', $product->id)
                    ->where('is_active', true)
                    ->where('can_sell_'.$saleType, true)
                    ->findOrFail($itemData['product_unit_id']);

                $quantity = (int) $itemData['quantity'];
                $quantityInBaseUnits = max(1, (int) $productUnit->quantity_in_base_units);
                $totalBaseUnits = $quantity * $quantityInBaseUnits;

                $unitPrice = (float) $itemData['unit_price'];
                $lineDiscount = (float) ($itemData['line_discount'] ?? 0);
                $lineSubtotal = $unitPrice * $quantity;
                $lineTotal = max(0, $lineSubtotal - $lineDiscount);

                $preview = $this->inventorySellingService->previewAllocations(
                    pharmacyId: $pharmacy->id,
                    branchId: $branchId,
                    product: $product,
                    productUnit: $productUnit,
                    quantity: $quantity
                );

                $preparedItems[] = [
                    'product' => $product,
                    'product_unit' => $productUnit,
                    'quantity' => $quantity,
                    'quantity_in_base_units' => $quantityInBaseUnits,
                    'total_base_units' => $totalBaseUnits,
                    'unit_price' => $unitPrice,
                    'line_discount' => $lineDiscount,
                    'line_total' => $lineTotal,
                    'preview_total_cost' => (float) $preview['total_cost'],
                    'preview_cost_per_base_unit' => (float) $preview['cost_per_base_unit'],
                ];

                $subtotalAmount += $lineSubtotal;
                $itemDiscountAmount += $lineDiscount;
            }

            $saleDiscount = (float) ($data['discount_amount'] ?? 0);
            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $discountAmount = $itemDiscountAmount + $saleDiscount;

            $totalAmount = max(0, $subtotalAmount - $discountAmount + $taxAmount);
            $paidAmount = (float) $data['paid_amount'];
            $changeAmount = max(0, $paidAmount - $totalAmount);
            $balanceAmount = max(0, $totalAmount - $paidAmount);

            $paymentStatus = 'paid';

            if ($balanceAmount > 0 && $paidAmount > 0) {
                $paymentStatus = 'partial';
            } elseif ($balanceAmount > 0 && $paidAmount <= 0) {
                $paymentStatus = 'unpaid';
            }

            /** @var Sale $sale */
            $sale = Sale::query()->create([
                'pharmacy_id' => $pharmacy->id,
                'branch_id' => $branchId,
                'sale_no' => $this->saleNumberService->generate(),
                'mobile_reference' => $data['mobile_reference'] ?? null,
                'device_name' => $data['device_name'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'synced_at' => $data['synced_at'] ?? null,
                'offline_created_at' => $data['offline_created_at'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'sale_type' => $saleType,
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'balance_amount' => $balanceAmount,
                'payment_method' => $data['payment_method'],
                'payment_status' => $paymentStatus,
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
                'created_by' => $user?->id,
                'sold_at' => now(),
            ]);

            foreach ($preparedItems as $preparedItem) {
                $sellResult = $this->inventorySellingService->sell(
                    sale: $sale,
                    product: $preparedItem['product'],
                    productUnit: $preparedItem['product_unit'],
                    quantity: $preparedItem['quantity']
                );

                $totalCost = (float) $sellResult['total_cost'];
                $profitAmount = (float) $preparedItem['line_total'] - $totalCost;

                SaleItem::query()->create([
                    'pharmacy_id' => $pharmacy->id,
                    'branch_id' => $branchId,
                    'sale_id' => $sale->id,
                    'product_id' => $preparedItem['product']->id,
                    'product_unit_id' => $preparedItem['product_unit']->id,
                    'quantity' => $preparedItem['quantity'],
                    'quantity_in_base_units' => $preparedItem['quantity_in_base_units'],
                    'total_base_units' => $preparedItem['total_base_units'],
                    'unit_price' => $preparedItem['unit_price'],
                    'line_discount' => $preparedItem['line_discount'],
                    'line_tax' => 0,
                    'line_total' => $preparedItem['line_total'],
                    'cost_per_base_unit' => $sellResult['cost_per_base_unit'],
                    'total_cost' => $totalCost,
                    'profit_amount' => $profitAmount,
                    'inventory_allocations' => $sellResult['allocations'],
                ]);
            }

            activity()
                ->useLog('sale')
                ->event('completed')
                ->performedOn($sale)
                ->causedBy($user)
                ->withProperties([
                    'sale_id' => $sale->id,
                    'sale_no' => $sale->sale_no,
                    'total_amount' => $sale->total_amount,
                    'items_count' => count($preparedItems),
                ])
                ->log('Sale completed');

            return $sale->fresh(['branch', 'creator', 'items.product', 'items.productUnit.unit']);
        });

        $this->markClosingNeedsRecalculation(
            pharmacyId: $pharmacy->id,
            branchId: (int) $sale->branch_id,
            date: $sale->sold_at?->toDateString() ?: now()->toDateString(),
            reason: 'New sale was recorded after verification. Please recalculate and verify again.'
        );

        return $sale;
    }

    public function receiptData(Pharmacy $pharmacy, Sale $sale): array
    {
        if ((int) $sale->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }

        $sale->load([
            'pharmacy.setting',
            'branch',
            'creator',
            'items.product',
            'items.productUnit.unit',
        ]);

        return [
            'sale' => $sale,
            'receipt' => [
                'sale_no' => $sale->sale_no,
                'cashier' => method_exists($sale->creator, 'displayName')
                    ? $sale->creator?->displayName()
                    : ($sale->creator?->name ?? '-'),
                'pharmacy' => $sale->pharmacy,
                'branch' => $sale->branch,
                'items' => $sale->items,
                'totals' => [
                    'subtotal_amount' => (float) $sale->subtotal_amount,
                    'discount_amount' => (float) $sale->discount_amount,
                    'tax_amount' => (float) $sale->tax_amount,
                    'total_amount' => (float) $sale->total_amount,
                    'paid_amount' => (float) $sale->paid_amount,
                    'change_amount' => (float) $sale->change_amount,
                    'balance_amount' => (float) $sale->balance_amount,
                ],
            ],
        ];
    }

    public function existingByMobileReference(Pharmacy $pharmacy, string $mobileReference): ?Sale
    {
        if (trim($mobileReference) === '') {
            return null;
        }

        return Sale::query()
            ->with(['branch', 'creator', 'items.product', 'items.productUnit.unit'])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('mobile_reference', $mobileReference)
            ->first();
    }

    public function markClosingNeedsRecalculation(
        int $pharmacyId,
        int $branchId,
        string $date,
        string $reason
    ): void {
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
}