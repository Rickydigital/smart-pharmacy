<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\PharmacySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PharmacySettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:setting.manage', only: ['update']),
        ];
    }

    public function update(Request $request): RedirectResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'currency' => ['required', 'string', 'max:10'],
            'selling_mode' => ['required', 'in:retail_only,wholesale_only,retail_and_wholesale'],
            'expiry_warning_days' => ['required', 'integer', 'min:1', 'max:365'],
            'block_expired_stock' => ['nullable', 'boolean'],
            'require_prescription_upload' => ['nullable', 'boolean'],
            'require_pharmacist_approval' => ['nullable', 'boolean'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
        ]);

        PharmacySetting::query()->updateOrCreate(
            ['pharmacy_id' => $pharmacy->id],
            [
                'currency' => strtoupper($validated['currency']),
                'selling_mode' => $validated['selling_mode'],
                'expiry_warning_days' => $validated['expiry_warning_days'],
                'block_expired_stock' => $request->boolean('block_expired_stock'),
                'require_prescription_upload' => $request->boolean('require_prescription_upload'),
                'require_pharmacist_approval' => $request->boolean('require_pharmacist_approval'),
                'receipt_footer' => $validated['receipt_footer'] ?? null,
            ]
        );

        return back()->with('success', 'Pharmacy settings updated successfully.');
    }
}