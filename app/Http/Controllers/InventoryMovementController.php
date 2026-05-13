<?php

namespace App\Http\Controllers;

use App\Services\Inventory\InventoryService;
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

    public function index(Request $request, InventoryService $service): View
    {
        return view('inventory-movements.index', $service->listMovements($request));
    }
}