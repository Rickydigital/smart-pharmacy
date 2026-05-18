<?php

namespace App\Http\Controllers\Api;

use App\Models\Pharmacy;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SupplierController extends ApiController
{
    public function index(Request $request): mixed
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $suppliers = Supplier::query()
            ->withCount('purchases')
            ->where('pharmacy_id', $pharmacy->id)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->input('status') === 'active');
            })
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 15));

        $counts = [
            'all' => Supplier::query()
    ->where('pharmacy_id', $pharmacy->id)
    ->count(),

'active' => Supplier::query()
    ->where('pharmacy_id', $pharmacy->id)
    ->where('is_active', true)
    ->count(),

'inactive' => Supplier::query()
    ->where('pharmacy_id', $pharmacy->id)
    ->where('is_active', false)
    ->count(),
        ];

        return $this->success([
            'suppliers' => $suppliers,
            'counts' => $counts,
        ]);
    }

    public function store(Request $request): mixed
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $this->validateSupplier($request, $pharmacy->id);

        $supplier = Supplier::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'name' => $validated['name'],
            'code' => $this->generateSupplierCode($pharmacy->id, $validated['name']),
            'contact_person' => $validated['contact_person'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->success(
            $supplier->loadCount('purchases'),
            'Supplier created successfully.'
        );
    }

    public function update(Request $request, Supplier $supplier): mixed
    {
        $this->guardPharmacy($supplier);

        $validated = $this->validateSupplier(
            $request,
            $supplier->pharmacy_id,
            $supplier
        );

        $supplier->update([
            'name' => $validated['name'],
            'code' => $supplier->code ?: $this->generateSupplierCode($supplier->pharmacy_id, $validated['name']),
            'contact_person' => $validated['contact_person'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return $this->success(
            $supplier->refresh()->loadCount('purchases'),
            'Supplier updated successfully.'
        );
    }

    public function toggle(Supplier $supplier): mixed
    {
        $this->guardPharmacy($supplier);

        $supplier->update([
            'is_active' => ! $supplier->is_active,
        ]);

        return $this->success(
            $supplier->refresh()->loadCount('purchases'),
            'Supplier status updated successfully.'
        );
    }

    public function destroy(Supplier $supplier): mixed
    {
        $this->guardPharmacy($supplier);

        if ($supplier->purchases()->exists()) {
            return $this->error(
                'This supplier already has purchases. Deactivate it instead of deleting.',
                422
            );
        }

       
        Supplier::query()
            ->whereKey($supplier->id)
            ->delete();


        return $this->success(null, 'Supplier deleted successfully.');
    }

    private function validateSupplier(
        Request $request,
        int $pharmacyId,
        ?Supplier $supplier = null
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:180',
                Rule::unique('suppliers', 'name')
                    ->where('pharmacy_id', $pharmacyId)
                    ->ignore($supplier?->getKey()),
            ],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function generateSupplierCode(int $pharmacyId, string $name): string
    {
        $base = strtoupper(Str::slug($name, '_'));
        $base = preg_replace('/[^A-Z0-9_]/', '', $base) ?: 'SUPPLIER';
        $base = substr($base, 0, 35);

        $code = $base;
        $counter = 1;

        while (
            Supplier::query()
                ->where('pharmacy_id', $pharmacyId)
                ->where('code', $code)
                ->exists()
        ) {
            $code = $base . '_' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $code;
    }

    private function guardPharmacy(Supplier $supplier): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $supplier->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }
}