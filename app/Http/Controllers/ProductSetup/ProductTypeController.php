<?php

namespace App\Http\Controllers\ProductSetup;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\ProductType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;

class ProductTypeController extends Controller implements HasMiddleware
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
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ProductType::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'name' => $validated['name'],
            'code' => $this->generateCode(ProductType::class, $pharmacy->id, $validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('product-setup.index', ['tab' => 'types'])
            ->with('success', 'Product type created successfully.');
    }

    public function update(Request $request, ProductType $productType): RedirectResponse
    {
        $this->guardPharmacy($productType);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $productType->update([
            'name' => $validated['name'],
            'code' => $productType->code ?: $this->generateCode(ProductType::class, $productType->pharmacy_id, $validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('product-setup.index', ['tab' => 'types'])
            ->with('success', 'Product type updated successfully.');
    }

    public function toggle(ProductType $productType): RedirectResponse
    {
        $this->guardPharmacy($productType);

        $productType->update([
            'is_active' => ! $productType->is_active,
        ]);

        return back()->with('success', 'Product type status updated successfully.');
    }

    public function destroy(ProductType $productType): RedirectResponse
    {
        $this->guardPharmacy($productType);

        if ($productType->categories()->exists() || $productType->products()->exists()) {
            return back()->with('error', 'This type is already used. Deactivate it instead of deleting.');
        }

        ProductType::query()
            ->whereKey($productType->id)
            ->delete();

        return back()->with('success', 'Product type deleted successfully.');
    }

    private function generateCode(string $modelClass, int $pharmacyId, string $name): string
    {
        $base = strtoupper(Str::slug($name, '_'));
        $base = preg_replace('/[^A-Z0-9_]/', '', $base) ?: 'TYPE';
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

    private function guardPharmacy(ProductType $productType): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $productType->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }
}