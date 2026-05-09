<?php

namespace App\Http\Controllers\ProductSetup;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class ProductUnitController extends Controller implements HasMiddleware
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
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'unit_id' => [
                'required',
                Rule::exists('units', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'quantity_in_base_units' => ['required', 'integer', 'min:1'],
            'can_purchase' => ['nullable', 'boolean'],
            'can_sell_retail' => ['nullable', 'boolean'],
            'can_sell_wholesale' => ['nullable', 'boolean'],
            'is_base' => ['nullable', 'boolean'],
            'is_default_sale_unit' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $product = Product::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->findOrFail($validated['product_id']);

        if ($request->boolean('is_base')) {
            ProductUnit::query()
                ->where('product_id', $product->id)
                ->update(['is_base' => false]);
        }

        if ($request->boolean('is_default_sale_unit')) {
            ProductUnit::query()
                ->where('product_id', $product->id)
                ->update(['is_default_sale_unit' => false]);
        }

        ProductUnit::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'unit_id' => $validated['unit_id'],
            ],
            [
                'pharmacy_id' => $pharmacy->id,
                'quantity_in_base_units' => $validated['quantity_in_base_units'],
                'can_purchase' => $request->boolean('can_purchase', true),
                'can_sell_retail' => $request->boolean('can_sell_retail', true),
                'can_sell_wholesale' => $request->boolean('can_sell_wholesale', true),
                'is_base' => $request->boolean('is_base'),
                'is_default_sale_unit' => $request->boolean('is_default_sale_unit'),
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        return redirect()
            ->route('product-setup.index', ['tab' => 'structure'])
            ->with('success', 'Product unit saved successfully.');
    }

    public function update(Request $request, ProductUnit $productUnit): RedirectResponse
    {
        $this->guardPharmacy($productUnit);

        $validated = $request->validate([
            'quantity_in_base_units' => ['required', 'integer', 'min:1'],
            'can_purchase' => ['nullable', 'boolean'],
            'can_sell_retail' => ['nullable', 'boolean'],
            'can_sell_wholesale' => ['nullable', 'boolean'],
            'is_base' => ['nullable', 'boolean'],
            'is_default_sale_unit' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_base')) {
            ProductUnit::query()
                ->where('product_id', $productUnit->product_id)
                ->where('id', '!=', $productUnit->id)
                ->update(['is_base' => false]);
        }

        if ($request->boolean('is_default_sale_unit')) {
            ProductUnit::query()
                ->where('product_id', $productUnit->product_id)
                ->where('id', '!=', $productUnit->id)
                ->update(['is_default_sale_unit' => false]);
        }

        $productUnit->update([
            'quantity_in_base_units' => $validated['quantity_in_base_units'],
            'can_purchase' => $request->boolean('can_purchase'),
            'can_sell_retail' => $request->boolean('can_sell_retail'),
            'can_sell_wholesale' => $request->boolean('can_sell_wholesale'),
            'is_base' => $request->boolean('is_base'),
            'is_default_sale_unit' => $request->boolean('is_default_sale_unit'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('product-setup.index', ['tab' => 'structure'])
            ->with('success', 'Product unit updated successfully.');
    }

    public function toggle(ProductUnit $productUnit): RedirectResponse
    {
        $this->guardPharmacy($productUnit);

        $productUnit->update([
            'is_active' => ! $productUnit->is_active,
        ]);

        return back()->with('success', 'Product unit status updated successfully.');
    }

    public function makeDefaultSaleUnit(ProductUnit $productUnit): RedirectResponse
    {
        $this->guardPharmacy($productUnit);

        ProductUnit::query()
            ->where('product_id', $productUnit->product_id)
            ->update(['is_default_sale_unit' => false]);

        $productUnit->update([
            'is_default_sale_unit' => true,
        ]);

        return back()->with('success', 'Default sale unit updated successfully.');
    }

    public function destroy(ProductUnit $productUnit): RedirectResponse
    {
        $this->guardPharmacy($productUnit);

        if ($productUnit->prices()->exists()) {
            return back()->with('error', 'This product unit has prices. Delete/deactivate prices first.');
        }

        ProductUnit::query()
            ->whereKey($productUnit->id)
            ->delete();

        return back()->with('success', 'Product unit deleted successfully.');
    }

    private function guardPharmacy(ProductUnit $productUnit): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $productUnit->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }
}