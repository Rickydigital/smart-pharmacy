<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\InventoryAlert;
use App\Models\Pharmacy;
use App\Services\InventoryAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryAlertController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:inventory_alert.view', only: ['index']),
            new Middleware('permission:inventory_alert.manage', only: ['markRead', 'resolve', 'ignore']),
            new Middleware('permission:inventory_alert.generate', only: ['generate']),
        ];
    }

    public function index(Request $request): View
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $user = Auth::user();
        $isAdminOrOwner = $user?->hasAnyRole(['Admin', 'Owner']) ?? false;

        $branchId = $request->input('branch_id');
        $alertType = $request->input('alert_type');
        $severity = $request->input('severity');
        $status = $request->input('status', 'open');
        $search = trim((string) $request->input('search'));

        if (! $isAdminOrOwner) {
            $branchId = $user?->branch_id;
        }

        $alerts = InventoryAlert::query()
            ->with(['branch', 'product.baseUnit', 'inventory', 'reader', 'resolver'])
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($alertType, fn ($query) => $query->where('alert_type', $alertType))
            ->when($severity, fn ($query) => $query->where('severity', $severity))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('alert_no', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('inventory', fn ($i) => $i->where('batch_no', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $branches = Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $summaryBase = InventoryAlert::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));

        $summary = [
            'open' => (clone $summaryBase)->where('status', 'open')->count(),
            'read' => (clone $summaryBase)->where('status', 'read')->count(),
            'resolved' => (clone $summaryBase)->where('status', 'resolved')->count(),
            'critical' => (clone $summaryBase)->whereIn('status', ['open', 'read'])->where('severity', 'critical')->count(),
            'high' => (clone $summaryBase)->whereIn('status', ['open', 'read'])->where('severity', 'high')->count(),
            'low_stock' => (clone $summaryBase)->whereIn('status', ['open', 'read'])->where('alert_type', 'low_stock')->count(),
            'expiring' => (clone $summaryBase)->whereIn('status', ['open', 'read'])->where('alert_type', 'expiring_soon')->count(),
            'expired' => (clone $summaryBase)->whereIn('status', ['open', 'read'])->where('alert_type', 'expired')->count(),
        ];

        return view('inventory-alerts.index', compact(
            'alerts',
            'branches',
            'summary',
            'branchId',
            'alertType',
            'severity',
            'status',
            'search',
            'isAdminOrOwner'
        ));
    }

    public function generate(InventoryAlertService $alertService): RedirectResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $result = $alertService->generateForPharmacy($pharmacy);

        return back()->with('success', 'Inventory alerts generated. New alerts: ' . $result['created']);
    }

    public function markRead(InventoryAlert $inventoryAlert): RedirectResponse
    {
        $this->guardAlert($inventoryAlert);

        if (! in_array($inventoryAlert->status, ['open'], true)) {
            return back()->with('error', 'Only open alerts can be marked as read.');
        }

        $inventoryAlert->update([
            'status' => 'read',
            'read_at' => now(),
            'read_by' => Auth::id(),
        ]);

        return back()->with('success', 'Alert marked as read.');
    }

    public function resolve(InventoryAlert $inventoryAlert): RedirectResponse
    {
        $this->guardAlert($inventoryAlert);

        if ($inventoryAlert->status === 'resolved') {
            return back()->with('error', 'This alert is already resolved.');
        }

        $inventoryAlert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => Auth::id(),
        ]);

        return back()->with('success', 'Alert resolved successfully.');
    }

    public function ignore(InventoryAlert $inventoryAlert): RedirectResponse
    {
        $this->guardAlert($inventoryAlert);

        if ($inventoryAlert->status === 'resolved') {
            return back()->with('error', 'Resolved alert cannot be ignored.');
        }

        $inventoryAlert->update([
            'status' => 'ignored',
            'resolved_at' => now(),
            'resolved_by' => Auth::id(),
        ]);

        return back()->with('success', 'Alert ignored.');
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