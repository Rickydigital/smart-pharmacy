<?php

namespace App\Http\Controllers\Api;

use App\Models\Inventory;
use App\Models\Product;
use App\Services\MarketBridgeService;
use App\Services\MarketOrderPurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketLocalReceivingController extends ApiController
{
    public function searchProducts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $queryText = trim((string) ($validated['q'] ?? ''));
        $branchId = $validated['branch_id'] ?? null;

        $products = Product::query()
            ->with([
                'category:id,name',
                'baseUnit:id,name,code',
                'productUnits.unit',
            ])
            ->where('is_active', true)
            ->when($queryText !== '', function ($query) use ($queryText) {
                $query->where(function ($query) use ($queryText) {
                    $query->where('name', 'like', "%{$queryText}%")
                        ->orWhere('generic_name', 'like', "%{$queryText}%")
                        ->orWhere('brand', 'like', "%{$queryText}%")
                        ->orWhere('strength', 'like', "%{$queryText}%")
                        ->orWhere('code', 'like', "%{$queryText}%")
                        ->orWhere('barcode', 'like', "%{$queryText}%");
                });
            })
            ->orderBy('name')
            ->limit(25)
            ->get()
            ->map(function (Product $product) use ($branchId) {
                $availableQuantity = null;

                if ($branchId) {
                    $availableQuantity = Inventory::query()
                        ->where('branch_id', $branchId)
                        ->where('product_id', $product->id)
                        ->where('is_active', true)
                        ->where('status', 'available')
                        ->sum('available_quantity_base_units');
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'generic_name' => $product->generic_name,
                    'brand' => $product->brand,
                    'strength' => $product->strength,
                    'category_name' => $product->category?->name,
                    'base_unit' => $product->baseUnit ? [
                        'id' => $product->baseUnit->id,
                        'name' => $product->baseUnit->name,
                        'code' => $product->baseUnit->code,
                    ] : null,
                    'available_quantity_base_units' => $availableQuantity,
                    'units' => $product->productUnits
                        ->where('is_active', true)
                        ->map(function ($productUnit) {
                            return [
                                'id' => $productUnit->id,
                                'unit_id' => $productUnit->unit_id,
                                'unit_name' => $productUnit->unit?->name,
                                'quantity_in_base_units' => (int) $productUnit->quantity_in_base_units,
                                'can_purchase' => (bool) $productUnit->can_purchase,
                                'is_base' => (bool) $productUnit->is_base,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return $this->success([
            'products' => $products,
        ]);
    }

    public function receiveBuyerPurchase(
        Request $request,
        MarketOrderPurchaseService $purchaseService,
        MarketBridgeService $marketBridge
    ): JsonResponse {
        $validated = $request->validate([
            'central_order' => ['required', 'array'],
            'central_order.id' => ['required', 'integer'],
            'central_order.order_no' => ['required', 'string'],

            'branch_id' => ['required', 'integer', 'exists:branches,id'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.market_order_item_id' => ['nullable', 'integer'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.product_unit_id' => ['required', 'integer', 'exists:product_units,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.quantity_in_base_units' => ['nullable', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.batch_no' => ['nullable', 'string', 'max:100'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
        ]);

        $purchase = $purchaseService->receiveFromMarketOrder(
            centralOrder: $validated['central_order'],
            mappedItems: $validated['items'],
            branchId: (int) $validated['branch_id'],
        );

        $marketBridge->updateStatus(
            orderId: (int) $validated['central_order']['id'],
            status: 'received',
            note: 'Buyer received stock and created local purchase #' . $purchase->purchase_no,
        );

        return $this->success([
            'message' => 'Market order received into local stock.',
            'purchase' => $purchase,
        ]);
    }
}