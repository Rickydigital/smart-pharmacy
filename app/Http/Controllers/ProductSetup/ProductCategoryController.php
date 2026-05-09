<?php

namespace App\Http\Controllers\ProductSetup;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller implements HasMiddleware
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
            'product_type_id' => [
                'required',
                Rule::exists('product_types', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ProductCategory::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'product_type_id' => $validated['product_type_id'],
            'name' => $validated['name'],
            'code' => $this->generateCode(ProductCategory::class, $pharmacy->id, $validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('product-setup.index', ['tab' => 'categories'])
            ->with('success', 'Product category created successfully.');
    }

    public function update(Request $request, ProductCategory $productCategory): RedirectResponse
    {
        $this->guardPharmacy($productCategory);

        $validated = $request->validate([
            'product_type_id' => [
                'required',
                Rule::exists('product_types', 'id')->where('pharmacy_id', $productCategory->pharmacy_id),
            ],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $productCategory->update([
            'product_type_id' => $validated['product_type_id'],
            'name' => $validated['name'],
            'code' => $productCategory->code ?: $this->generateCode(ProductCategory::class, $productCategory->pharmacy_id, $validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('product-setup.index', ['tab' => 'categories'])
            ->with('success', 'Product category updated successfully.');
    }

    public function toggle(ProductCategory $productCategory): RedirectResponse
    {
        $this->guardPharmacy($productCategory);

        $productCategory->update([
            'is_active' => ! $productCategory->is_active,
        ]);

        return back()->with('success', 'Product category status updated successfully.');
    }

    public function destroy(ProductCategory $productCategory): RedirectResponse
    {
        $this->guardPharmacy($productCategory);

        if ($productCategory->products()->exists()) {
            return back()->with('error', 'This category is already used by products. Deactivate it instead of deleting.');
        }

        ProductCategory::query()
            ->whereKey($productCategory->id)
            ->delete();

        return back()->with('success', 'Product category deleted successfully.');
    }

    private function generateCode(string $modelClass, int $pharmacyId, string $name): string
    {
        $base = strtoupper(Str::slug($name, '_'));
        $base = preg_replace('/[^A-Z0-9_]/', '', $base) ?: 'CATEGORY';
        $base = substr($base, 0, 35);

        $code = $base;
        $counter = 1;

        while ($modelClass::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('code', $code)
            ->exists()) {
            $code = $base . '_' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $code;
    }

    private function guardPharmacy(ProductCategory $productCategory): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $productCategory->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }
}