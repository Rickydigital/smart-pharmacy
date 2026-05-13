<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\Purchase\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class PurchaseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:purchase.view', only: ['index']),
            new Middleware('permission:purchase.manage', except: ['index']),
        ];
    }

    public function index(Request $request, PurchaseService $purchaseService): View
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

        return view('purchases.index', $data);
    }

    public function store(Request $request, PurchaseService $purchaseService): RedirectResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $this->validatePurchase($request, $pharmacy->id);

        $purchaseService->create($pharmacy, $validated, $request->user());

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase created successfully. Add purchase items now.');
    }

    public function update(Request $request, Purchase $purchase, PurchaseService $purchaseService): RedirectResponse
    {
        try {
            $validated = $this->validatePurchase($request, $purchase->pharmacy_id);

            $purchaseService->update($purchase, $validated, $request->user());

            return back()->with('success', 'Purchase updated successfully.');
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function storeItem(Request $request, Purchase $purchase, PurchaseService $purchaseService): RedirectResponse
    {
        try {
            $validated = $this->validatePurchaseItem($request, $purchase->pharmacy_id);

            $purchaseService->addItem($purchase, $validated);

            return back()->with('success', 'Purchase item added successfully.');
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function updateItem(Request $request, PurchaseItem $purchaseItem, PurchaseService $purchaseService): RedirectResponse
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

            $purchaseService->updateItem($purchaseItem, $validated);

            return back()->with('success', 'Purchase item updated successfully.');
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function destroyItem(PurchaseItem $purchaseItem, PurchaseService $purchaseService): RedirectResponse
    {
        try {
            $purchaseService->deleteItem($purchaseItem);

            return back()->with('success', 'Purchase item removed successfully.');
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function receive(Purchase $purchase, PurchaseService $purchaseService): RedirectResponse
    {
        try {
            $purchaseService->receive($purchase, request()->user());

            return back()->with('success', 'Purchase received successfully and inventory updated.');
        } catch (Throwable $exception) {
            return back()->with('error', 'Receiving failed: ' . $exception->getMessage());
        }
    }

    public function cancel(Purchase $purchase, PurchaseService $purchaseService): RedirectResponse
    {
        try {
            $purchaseService->cancel($purchase, request()->user());

            return back()->with('success', 'Purchase cancelled successfully.');
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function destroy(Purchase $purchase, PurchaseService $purchaseService): RedirectResponse
    {
        try {
            $purchaseService->delete($purchase);

            return back()->with('success', 'Purchase deleted successfully.');
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
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