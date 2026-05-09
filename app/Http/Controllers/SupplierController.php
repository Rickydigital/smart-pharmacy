<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:supplier.view', only: ['index']),
            new Middleware('permission:supplier.manage', except: ['index']),
        ];
    }

    public function index(Request $request): View
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
            ->paginate(15)
            ->withQueryString();

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

        return view('suppliers.index', compact('suppliers', 'counts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:180',
                Rule::unique('suppliers', 'name')->where('pharmacy_id', $pharmacy->id),
            ],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

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

        activity()
            ->useLog('supplier')
            ->event('created')
            ->performedOn($supplier)
            ->causedBy(Auth::user())
            ->withProperties([
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
            ])
            ->log('Supplier created');

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->guardPharmacy($supplier);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:180',
                Rule::unique('suppliers', 'name')
                    ->where('pharmacy_id', $supplier->pharmacy_id)
                    ->ignore($supplier->id),
            ],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $old = $supplier->only([
            'name',
            'contact_person',
            'phone',
            'email',
            'address',
            'notes',
            'is_active',
        ]);

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

        activity()
            ->useLog('supplier')
            ->event('updated')
            ->performedOn($supplier)
            ->causedBy(Auth::user())
            ->withProperties([
                'old' => $old,
                'attributes' => $supplier->only([
                    'name',
                    'contact_person',
                    'phone',
                    'email',
                    'address',
                    'notes',
                    'is_active',
                ]),
            ])
            ->log('Supplier updated');

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function toggle(Supplier $supplier): RedirectResponse
    {
        $this->guardPharmacy($supplier);

        $supplier->update([
            'is_active' => ! $supplier->is_active,
        ]);

        activity()
            ->useLog('supplier')
            ->event('status_changed')
            ->performedOn($supplier)
            ->causedBy(Auth::user())
            ->withProperties([
                'supplier_id' => $supplier->id,
                'is_active' => $supplier->is_active,
            ])
            ->log('Supplier status changed');

        return back()->with('success', 'Supplier status updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->guardPharmacy($supplier);

        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'This supplier already has purchases. Deactivate it instead of deleting.');
        }

        activity()
            ->useLog('supplier')
            ->event('deleted')
            ->performedOn($supplier)
            ->causedBy(Auth::user())
            ->withProperties([
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
            ])
            ->log('Supplier deleted');

        Supplier::query()
            ->whereKey($supplier->id)
            ->delete();

        return back()->with('success', 'Supplier deleted successfully.');
    }

    private function generateSupplierCode(int $pharmacyId, string $name): string
    {
        $base = strtoupper(Str::slug($name, '_'));
        $base = preg_replace('/[^A-Z0-9_]/', '', $base) ?: 'SUPPLIER';
        $base = substr($base, 0, 35);

        $code = $base;
        $counter = 1;

        while (Supplier::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('code', $code)
            ->exists()) {
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