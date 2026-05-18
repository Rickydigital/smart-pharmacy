<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use App\Models\InventoryAlert;
use App\Models\Pharmacy;
use App\Services\InventoryAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryAlertController extends ApiController
{
    public function index(Request $request): mixed
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        $branchId = $request->input('branch_id');

        if (! $isAdminOrOwner) {
            $branchId = $user?->branch_id;
        }

        $alerts = InventoryAlert::query()
            ->with(['branch', 'product.baseUnit', 'inventory'])
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->filled('alert_type'), fn ($q) => $q->where('alert_type', $request->input('alert_type')))
            ->when($request->filled('severity'), fn ($q) => $q->where('severity', $request->input('severity')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->input('search'));

                $q->where(function ($query) use ($search) {
                    $query->where('alert_no', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('inventory', fn ($i) => $i->where('batch_no', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate((int) $request->input('per_page', 20));

        $branches = Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $summaryBase = InventoryAlert::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        return $this->success([
            'alerts' => $alerts,
            'branches' => $branches,
            'summary' => [
                'open' => (clone $summaryBase)->where('status', 'open')->count(),
                'read' => (clone $summaryBase)->where('status', 'read')->count(),
                'resolved' => (clone $summaryBase)->where('status', 'resolved')->count(),
                'critical' => (clone $summaryBase)->whereIn('status', ['open', 'read'])->where('severity', 'critical')->count(),
                'high' => (clone $summaryBase)->whereIn('status', ['open', 'read'])->where('severity', 'high')->count(),
                'low_stock' => (clone $summaryBase)->whereIn('status', ['open', 'read'])->where('alert_type', 'low_stock')->count(),
                'expiring' => (clone $summaryBase)->whereIn('status', ['open', 'read'])->where('alert_type', 'expiring_soon')->count(),
                'expired' => (clone $summaryBase)->whereIn('status', ['open', 'read'])->where('alert_type', 'expired')->count(),
            ],
        ]);
    }

    public function generate(InventoryAlertService $alertService): mixed
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $result = $alertService->generateForPharmacy($pharmacy);

        return $this->success($result, 'Inventory alerts generated.');
    }

    public function markRead(InventoryAlert $inventoryAlert): mixed
    {
        $this->guardAlert($inventoryAlert);

        if (! in_array($inventoryAlert->status, ['open'], true)) {
            return $this->error('Only open alerts can be marked as read.', 422);
        }

        $inventoryAlert->update([
            'status' => 'read',
            'read_at' => now(),
            'read_by' => Auth::id(),
        ]);

        return $this->success(
            $inventoryAlert->fresh(['branch', 'product.baseUnit', 'inventory']),
            'Alert marked as read.'
        );
    }

    public function resolve(InventoryAlert $inventoryAlert): mixed
    {
        $this->guardAlert($inventoryAlert);

        if ($inventoryAlert->status === 'resolved') {
            return $this->error('This alert is already resolved.', 422);
        }

        $inventoryAlert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => Auth::id(),
        ]);

        return $this->success(
            $inventoryAlert->fresh(['branch', 'product.baseUnit', 'inventory']),
            'Alert resolved successfully.'
        );
    }

    public function ignore(InventoryAlert $inventoryAlert): mixed
    {
        $this->guardAlert($inventoryAlert);

        if ($inventoryAlert->status === 'resolved') {
            return $this->error('Resolved alert cannot be ignored.', 422);
        }

        $inventoryAlert->update([
            'status' => 'ignored',
            'resolved_at' => now(),
            'resolved_by' => Auth::id(),
        ]);

        return $this->success(
            $inventoryAlert->fresh(['branch', 'product.baseUnit', 'inventory']),
            'Alert ignored.'
        );
    }

    private function guardAlert(InventoryAlert $inventoryAlert): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $inventoryAlert->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        if (! $isAdminOrOwner && (int) $inventoryAlert->branch_id !== (int) $user?->branch_id) {
            abort(403);
        }
    }
}