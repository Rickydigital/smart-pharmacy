<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Pharmacy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class BranchSetupController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:setting.manage'),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('branches', 'code')->where('pharmacy_id', $pharmacy->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],

            'street_id' => ['nullable', 'integer', 'exists:streets,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            'opens_at' => ['nullable', 'date_format:H:i'],
            'closes_at' => ['nullable', 'date_format:H:i'],
            'is_24_hours' => ['nullable', 'boolean'],

            'is_main' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $makeMain = $request->boolean('is_main');

        if ($makeMain) {
            Branch::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->update(['is_main' => false]);
        }

        Branch::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'phone' => $validated['phone'] ?? null,
            'whatsapp' => $validated['whatsapp'] ?? null,
            'address' => $validated['address'] ?? null,
            'street_id' => $validated['street_id'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'opens_at' => $request->boolean('is_24_hours') ? null : ($validated['opens_at'] ?? null),
            'closes_at' => $request->boolean('is_24_hours') ? null : ($validated['closes_at'] ?? null),
            'is_24_hours' => $request->boolean('is_24_hours'),
            'is_main' => $makeMain,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Branch created successfully.');
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('branches', 'code')
                    ->where('pharmacy_id', $branch->pharmacy_id)
                    ->ignore($branch->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],

            'street_id' => ['nullable', 'integer', 'exists:streets,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            'opens_at' => ['nullable', 'date_format:H:i'],
            'closes_at' => ['nullable', 'date_format:H:i'],
            'is_24_hours' => ['nullable', 'boolean'],

            'is_main' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $makeMain = $request->boolean('is_main');

        if ($makeMain) {
            Branch::query()
                ->where('pharmacy_id', $branch->pharmacy_id)
                ->where('id', '!=', $branch->id)
                ->update(['is_main' => false]);
        }

        $branch->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'phone' => $validated['phone'] ?? null,
            'whatsapp' => $validated['whatsapp'] ?? null,
            'address' => $validated['address'] ?? null,
            'street_id' => $validated['street_id'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'opens_at' => $request->boolean('is_24_hours') ? null : ($validated['opens_at'] ?? null),
            'closes_at' => $request->boolean('is_24_hours') ? null : ($validated['closes_at'] ?? null),
            'is_24_hours' => $request->boolean('is_24_hours'),
            'is_main' => $makeMain ?: $branch->is_main,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Branch updated successfully.');
    }

    public function makeMain(Branch $branch): RedirectResponse
    {
        Branch::query()
            ->where('pharmacy_id', $branch->pharmacy_id)
            ->update(['is_main' => false]);

        $branch->update([
            'is_main' => true,
            'is_active' => true,
        ]);

        return back()->with('success', 'Main branch updated successfully.');
    }

    public function toggle(Branch $branch): RedirectResponse
    {
        if ($branch->is_main && $branch->is_active) {
            return back()->with('error', 'Main branch cannot be deactivated.');
        }

        $branch->update([
            'is_active' => ! $branch->is_active,
        ]);

        return back()->with('success', 'Branch status updated successfully.');
    }
}