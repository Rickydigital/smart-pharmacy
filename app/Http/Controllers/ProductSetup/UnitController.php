<?php

namespace App\Http\Controllers\ProductSetup;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;

class UnitController extends Controller implements HasMiddleware
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

        Unit::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'name' => $validated['name'],
            'code' => $this->generateCode($pharmacy->id, $validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('product-setup.index', ['tab' => 'units'])
            ->with('success', 'Unit created successfully.');
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $this->guardPharmacy($unit);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $unit->update([
            'name' => $validated['name'],
            'code' => $unit->code ?: $this->generateCode($unit->pharmacy_id, $validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('product-setup.index', ['tab' => 'units'])
            ->with('success', 'Unit updated successfully.');
    }

    public function toggle(Unit $unit): RedirectResponse
    {
        $this->guardPharmacy($unit);

        $unit->update([
            'is_active' => ! $unit->is_active,
        ]);

        return back()->with('success', 'Unit status updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $this->guardPharmacy($unit);

        if ($unit->productUnits()->exists()) {
            return back()->with('error', 'This unit is already used by products. Deactivate it instead of deleting.');
        }

        Unit::query()
            ->whereKey($unit->id)
            ->delete();

        return back()->with('success', 'Unit deleted successfully.');
    }

    private function generateCode(int $pharmacyId, string $name): string
    {
        $base = strtoupper(Str::slug($name, '_'));
        $base = preg_replace('/[^A-Z0-9_]/', '', $base) ?: 'UNIT';
        $base = substr($base, 0, 35);

        $code = $base;
        $counter = 1;

        while (Unit::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('code', $code)
            ->exists()) {
            $code = $base . '_' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $code;
    }

    private function guardPharmacy(Unit $unit): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $unit->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }
}