<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use App\Models\Pharmacy;
use App\Models\PharmacySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SetupController extends ApiController
{
    public function index(): JsonResponse
    {
        $pharmacy = Pharmacy::query()
            ->with([
                'setting',
                'branches' => fn ($query) => $query
                    ->with('street.ward.district.region.country')
                    ->orderByDesc('is_main')
                    ->orderBy('name'),
            ])
            ->firstOrFail();

        $settings = $pharmacy->setting ?: PharmacySetting::query()->firstOrCreate(
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

        $pharmacy->load([
            'branches' => fn ($query) => $query
                ->with('street.ward.district.region.country')
                ->orderByDesc('is_main')
                ->orderBy('name'),
        ]);

        return $this->success([
            'pharmacy' => $this->pharmacyPayload($pharmacy),
            'settings' => $settings,
            'branches' => $pharmacy->branches->map(fn ($branch) => $this->branchPayload($branch))->values(),
            'summary' => [
                'total_branches' => $pharmacy->branches->count(),
                'active_branches' => $pharmacy->branches->where('is_active', true)->count(),
                'inactive_branches' => $pharmacy->branches->where('is_active', false)->count(),
                'main_branches' => $pharmacy->branches->where('is_main', true)->count(),
            ],
        ], 'Setup loaded successfully.');
    }

    public function updatePharmacy(Request $request): JsonResponse
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

            $validated['logo_path'] = $request->file('logo')
                ->store('pharmacies/logos', 'public');
        }

        $validated['code'] = strtoupper($validated['code']);

        $pharmacy->update($validated);

        return $this->success(
            $this->pharmacyPayload($pharmacy->fresh()),
            'Pharmacy profile updated successfully.'
        );
    }

   public function storeBranch(Request $request): JsonResponse
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

    $branch = Branch::query()->create([
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

    return $this->success(
        $this->branchPayload($branch->fresh()->load('street.ward.district.region.country')),
        'Branch created successfully.'
    );
}

    public function updatePharmacyLogo(Request $request): JsonResponse
{
    $pharmacy = Pharmacy::query()->firstOrFail();

    $validated = $request->validate([
        'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ]);

    if ($pharmacy->logo_path && Storage::disk('public')->exists($pharmacy->logo_path)) {
        Storage::disk('public')->delete($pharmacy->logo_path);
    }

    $pharmacy->update([
        'logo_path' => $request->file('logo')->store('pharmacies/logos', 'public'),
    ]);

    return $this->success(
        $this->pharmacyPayload($pharmacy->fresh()),
        'Pharmacy logo updated successfully.'
    );
}
  public function updateBranch(Request $request, Branch $branch): JsonResponse
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

    return $this->success(
        $this->branchPayload($branch->fresh()->load('street.ward.district.region.country')),
        'Branch updated successfully.'
    );
}

private function branchPayload(Branch $branch): array
{
    $branch->loadMissing('street.ward.district.region.country');

    $street = $branch->street;
    $ward = $street?->ward;
    $district = $ward?->district;
    $region = $district?->region;
    $country = $region?->country;

    $fullLocation = collect([
        $country?->name,
        $region?->name,
        $district?->name,
        $ward?->name,
        $street?->name,
    ])->filter()->implode(', ');

    return [
        'id' => $branch->id,
        'pharmacy_id' => $branch->pharmacy_id,
        'name' => $branch->name,
        'code' => $branch->code,
        'phone' => $branch->phone,
        'whatsapp' => $branch->whatsapp,
        'address' => $branch->address,

        'street_id' => $branch->street_id,
        'latitude' => $branch->latitude,
        'longitude' => $branch->longitude,

        'opens_at' => $branch->opens_at?->format('H:i'),
        'closes_at' => $branch->closes_at?->format('H:i'),
        'is_24_hours' => (bool) $branch->is_24_hours,

        'is_main' => (bool) $branch->is_main,
        'is_active' => (bool) $branch->is_active,

        'location' => [
            'country' => $country ? [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
            ] : null,
            'region' => $region ? [
                'id' => $region->id,
                'name' => $region->name,
                'code' => $region->code,
            ] : null,
            'district' => $district ? [
                'id' => $district->id,
                'name' => $district->name,
                'code' => $district->code,
            ] : null,
            'ward' => $ward ? [
                'id' => $ward->id,
                'name' => $ward->name,
                'code' => $ward->code,
            ] : null,
            'street' => $street ? [
                'id' => $street->id,
                'name' => $street->name,
                'code' => $street->code,
            ] : null,
            'full_name' => $fullLocation,
        ],

        'created_at' => $branch->created_at,
        'updated_at' => $branch->updated_at,
    ];
}

    public function makeMainBranch(Branch $branch): JsonResponse
    {
        Branch::query()
            ->where('pharmacy_id', $branch->pharmacy_id)
            ->update(['is_main' => false]);

        $branch->update([
            'is_main' => true,
            'is_active' => true,
        ]);

        return $this->success(
            $this->branchPayload($branch->fresh()->load('street.ward.district.region.country')),
            'Main branch updated successfully.'
        );
    }

    public function toggleBranch(Branch $branch): JsonResponse
    {
        if ($branch->is_main && $branch->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Main branch cannot be deactivated.',
                'data' => [],
            ], 422);
        }

        $branch->update([
            'is_active' => ! $branch->is_active,
        ]);

        return $this->success(
            $this->branchPayload($branch->fresh()->load('street.ward.district.region.country')),
            'Branch status updated successfully.'
        );
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'currency' => ['required', 'string', 'max:10'],
            'selling_mode' => [
                'required',
                'in:retail_only,wholesale_only,retail_and_wholesale',
            ],
            'expiry_warning_days' => ['required', 'integer', 'min:1', 'max:365'],
            'block_expired_stock' => ['nullable', 'boolean'],
            'require_prescription_upload' => ['nullable', 'boolean'],
            'require_pharmacist_approval' => ['nullable', 'boolean'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = PharmacySetting::query()->updateOrCreate(
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

        return $this->success(
            $settings,
            'Pharmacy settings updated successfully.'
        );
    }

    private function pharmacyPayload(Pharmacy $pharmacy): array
    {
        $logoUrl = $pharmacy->logo_path
            ? asset('storage/' . $pharmacy->logo_path)
            : null;

        return [
            'id' => $pharmacy->id,
            'name' => $pharmacy->name,
            'code' => $pharmacy->code,
            'phone' => $pharmacy->phone,
            'email' => $pharmacy->email,
            'address' => $pharmacy->address,
            'status' => $pharmacy->status,
            'logo_path' => $pharmacy->logo_path,
            'logo_url' => $logoUrl,
            'created_at' => $pharmacy->created_at,
            'updated_at' => $pharmacy->updated_at,
        ];
    }
}