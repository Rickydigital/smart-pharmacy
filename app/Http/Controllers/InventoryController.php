<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class InventoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:stock.view', only: ['index']),
            new Middleware('permission:stock.adjust', only: ['adjust', 'toggleBlock', 'markExpired']),
        ];
    }

    public function index(Request $request, InventoryService $service): View
    {
        return view('inventory.index', $service->listInventories($request));
    }

    public function adjust(
        Request $request,
        Inventory $inventory,
        InventoryService $service
    ): RedirectResponse {
        try {
            $service->adjust($request, $inventory);

            return back()->with('success', 'Inventory adjusted successfully.');
        } catch (\Throwable $exception) {
            return back()->with('error', 'Adjustment failed: '.$exception->getMessage());
        }
    }

    public function toggleBlock(
        Inventory $inventory,
        InventoryService $service
    ): RedirectResponse {
        $wasBlocked = $inventory->status === 'blocked';

        $service->toggleBlock($inventory);

        return back()->with(
            'success',
            $wasBlocked
                ? 'Inventory unblocked successfully.'
                : 'Inventory blocked successfully.'
        );
    }

    public function markExpired(
        Inventory $inventory,
        InventoryService $service
    ): RedirectResponse {
        $service->markExpired($inventory);

        return back()->with('success', 'Inventory marked as expired.');
    }
}