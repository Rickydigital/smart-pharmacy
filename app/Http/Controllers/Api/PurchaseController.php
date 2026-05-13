<?php

namespace App\Http\Controllers\Api;

use App\Models\Pharmacy;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\Purchase\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class PurchaseController extends ApiController
{
    public function index(Request $request, PurchaseService $purchaseService): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $data = $purchaseService->listData($pharmacy, $request->only([
            'search',
            'status',
            'payment_status',
            'supplier_id',
            'date_from',
            'date_to',
        ]));

        return $this->success($data);
    }

    public function store(Request $request, PurchaseService $purchaseService): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $this->validatePurchase($request, $pharmacy->id);

        $purchase = $purchaseService->create(
            pharmacy: $pharmacy,
            data: $validated,
            user: $request->user(),
        );

        return $this->success([
            'id' => $purchase->id,
            'purchase_id' => $purchase->id,
            'purchase_no' => $purchase->purchase_no,
            'purchase' => $purchase,
        ], 'Purchase created successfully. Add purchase items now.', 201);
    }

    public function update(Request $request, Purchase $purchase, PurchaseService $purchaseService): JsonResponse
    {
        try {
            $validated = $this->validatePurchase($request, $purchase->pharmacy_id);

            $updated = $purchaseService->update(
                purchase: $purchase,
                data: $validated,
                user: $request->user(),
            );

            return $this->success([
                'id' => $updated->id,
                'purchase_id' => $updated->id,
                'purchase_no' => $updated->purchase_no,
                'purchase' => $updated,
            ], 'Purchase updated successfully.');
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    public function storeItem(Request $request, Purchase $purchase, PurchaseService $purchaseService): JsonResponse
    {
        try {
            $validated = $this->validatePurchaseItem($request, $purchase->pharmacy_id);

            $item = $purchaseService->addItem($purchase, $validated);

            return $this->success([
                'purchase_id' => $purchase->id,
                'item_id' => $item->id,
                'item' => $item,
                'purchase' => $purchase->fresh([
                    'branch',
                    'supplier',
                    'items.product',
                    'items.productUnit.unit',
                ]),
            ], 'Purchase item added successfully.', 201);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    public function updateItem(Request $request, PurchaseItem $purchaseItem, PurchaseService $purchaseService): JsonResponse
    {
        try {
            $purchaseItem->load('purchase');

            $validated = $request->validate([
                'product_unit_id' => [
                    'required',
                    Rule::exists('product_units', 'id')
                        ->where('pharmacy_id', $purchaseItem->purchase->pharmacy_id),
                ],
                'expiry_date' => ['nullable', 'date'],
                'quantity' => ['required', 'integer', 'min:1'],
                'item_amount' => ['required', 'numeric', 'min:0'],
            ]);

            $item = $purchaseService->updateItem($purchaseItem, $validated);

            return $this->success([
                'purchase_id' => $purchaseItem->purchase_id,
                'item_id' => $item->id,
                'item' => $item,
                'purchase' => $purchaseItem->purchase->fresh([
                    'branch',
                    'supplier',
                    'items.product',
                    'items.productUnit.unit',
                ]),
            ], 'Purchase item updated successfully.');
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    public function destroyItem(PurchaseItem $purchaseItem, PurchaseService $purchaseService): JsonResponse
    {
        try {
            $purchase = $purchaseService->deleteItem($purchaseItem);

            return $this->success([
                'purchase_id' => $purchase->id,
                'purchase' => $purchase,
            ], 'Purchase item removed successfully.');
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    public function receive(Purchase $purchase, PurchaseService $purchaseService): JsonResponse
    {
        try {
            $received = $purchaseService->receive($purchase, request()->user());

            return $this->success([
                'purchase_id' => $received->id,
                'purchase_no' => $received->purchase_no,
                'purchase' => $received,
            ], 'Purchase received successfully and inventory updated.');
        } catch (Throwable $exception) {
            return $this->error('Receiving failed: ' . $exception->getMessage(), 422);
        }
    }

    public function cancel(Purchase $purchase, PurchaseService $purchaseService): JsonResponse
    {
        try {
            $cancelled = $purchaseService->cancel($purchase, request()->user());

            return $this->success([
                'purchase_id' => $cancelled->id,
                'purchase_no' => $cancelled->purchase_no,
                'purchase' => $cancelled,
            ], 'Purchase cancelled successfully.');
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    public function destroy(Purchase $purchase, PurchaseService $purchaseService): JsonResponse
    {
        try {
            $purchaseId = $purchase->id;

            $purchaseService->delete($purchase);

            return $this->success([
                'purchase_id' => $purchaseId,
            ], 'Purchase deleted successfully.');
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    private function validatePurchase(Request $request, int $pharmacyId): array
    {
        return $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacyId),
            ],
            'supplier_id' => [
                'nullable',
                Rule::exists('suppliers', 'id')->where('pharmacy_id', $pharmacyId),
            ],
            'supplier_invoice_no' => ['nullable', 'string', 'max:120'],
            'purchase_date' => ['required', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function validatePurchaseItem(Request $request, int $pharmacyId): array
    {
        return $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where('pharmacy_id', $pharmacyId),
            ],
            'product_unit_id' => [
                'required',
                Rule::exists('product_units', 'id')->where('pharmacy_id', $pharmacyId),
            ],
            'expiry_date' => ['nullable', 'date'],
            'quantity' => ['required', 'integer', 'min:1'],
            'item_amount' => ['required', 'numeric', 'min:0'],
        ]);
    }
}