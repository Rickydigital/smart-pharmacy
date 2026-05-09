<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class InventoryMovementController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:stock.movement.view', only: ['index']),
        ];
    }

    public function index(Request $request): View
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $movements = InventoryMovement::query()
            ->with([
                'branch',
                'product.baseUnit',
                'inventory',
                'creator',
            ])
            ->where('pharmacy_id', $pharmacy->id)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('movement_no', 'like', "%{$search}%")
                        ->orWhere('movement_type', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $query->where('branch_id', $request->input('branch_id'));
            })
            ->when($request->filled('movement_type'), function ($query) use ($request) {
                $query->where('movement_type', $request->input('movement_type'));
            })
            ->when($request->filled('direction'), function ($query) use ($request) {
                $query->where('direction', $request->input('direction'));
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('moved_at', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('moved_at', '<=', $request->input('date_to'));
            })
            ->latest('moved_at')
            ->paginate(20)
            ->withQueryString();

        $branches = Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $movementTypes = InventoryMovement::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->whereNotNull('movement_type')
            ->select('movement_type')
            ->distinct()
            ->orderBy('movement_type')
            ->pluck('movement_type');

        return view('inventory-movements.index', compact(
            'movements',
            'branches',
            'movementTypes'
        ));
    }
}