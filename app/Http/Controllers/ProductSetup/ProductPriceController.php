<?php

namespace App\Http\Controllers\ProductSetup;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\ProductPrice;
use App\Models\ProductUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class ProductPriceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:product.manage'),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'product_unit_id' => [
                'required',
                Rule::exists('product_units', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'price_type' => ['required', 'in:retail,wholesale'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $productUnit = ProductUnit::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->findOrFail($validated['product_unit_id']);

        if ($validated['price_type'] === 'retail' && ! $productUnit->can_sell_retail) {
            return back()->with('error', 'This unit is not allowed for retail sale.');
        }

        if ($validated['price_type'] === 'wholesale' && ! $productUnit->can_sell_wholesale) {
            return back()->with('error', 'This unit is not allowed for wholesale sale.');
        }

        ProductPrice::query()->updateOrCreate(
            [
                'product_unit_id' => $productUnit->id,
                'price_type' => $validated['price_type'],
            ],
            [
                'pharmacy_id' => $pharmacy->id,
                'product_id' => $productUnit->product_id,
                'price' => $validated['price'],
                'currency' => $validated['currency'] ?? 'TZS',
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        return redirect()
            ->route('product-setup.index', ['tab' => 'prices'])
            ->with('success', 'Product price saved successfully.');
    }

    public function update(Request $request, ProductPrice $productPrice): RedirectResponse
    {
        $this->guardPharmacy($productPrice);

        $validated = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $productPrice->update([
            'price' => $validated['price'],
            'currency' => $validated['currency'] ?? 'TZS',
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('product-setup.index', ['tab' => 'prices'])
            ->with('success', 'Product price updated successfully.');
    }

    public function toggle(ProductPrice $productPrice): RedirectResponse
    {
        $this->guardPharmacy($productPrice);

        $productPrice->update([
            'is_active' => ! $productPrice->is_active,
        ]);

        return back()->with('success', 'Product price status updated successfully.');
    }

    public function destroy(ProductPrice $productPrice): RedirectResponse
    {
        $this->guardPharmacy($productPrice);

        ProductPrice::query()
            ->whereKey($productPrice->id)
            ->delete();

        return back()->with('success', 'Product price deleted successfully.');
    }

    private function guardPharmacy(ProductPrice $productPrice): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $productPrice->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }
}