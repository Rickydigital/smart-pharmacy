<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\PharmacySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PharmacyProfileController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:setting.view', only: ['index']),
            new Middleware('permission:setting.manage', only: ['update']),
        ];
    }

    public function index(Request $request): View
    {
        $pharmacy = Pharmacy::query()
            ->with([
                'setting',
                'branches' => fn ($query) => $query->orderByDesc('is_main')->orderBy('name'),
            ])
            ->firstOrFail();

        $settings = $pharmacy->setting
            ?: PharmacySetting::query()->firstOrCreate(
                ['pharmacy_id' => $pharmacy->id],
                [
                    'currency' => 'TZS',
                    'selling_mode' => 'retail_and_wholesale',
                    'expiry_warning_days' => 30,
                    'block_expired_stock' => true,
                    'require_prescription_upload' => false,
                    'require_pharmacist_approval' => false,
                    'receipt_footer' => 'Thank you for buying from us.',
                ]
            );

        return view('setup.index', [
            'pharmacy' => $pharmacy,
            'branches' => $pharmacy->branches,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:30', 'unique:pharmacies,code,' . $pharmacy->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($validated['logo']);

        if ($request->hasFile('logo')) {
            if ($pharmacy->logo_path && Storage::disk('public')->exists($pharmacy->logo_path)) {
                Storage::disk('public')->delete($pharmacy->logo_path);
            }

            $validated['logo_path'] = $request->file('logo')->store('pharmacies/logos', 'public');
        }

        $validated['code'] = strtoupper($validated['code']);

        $pharmacy->update($validated);

        return back()->with('success', 'Pharmacy profile updated successfully.');
    }
}