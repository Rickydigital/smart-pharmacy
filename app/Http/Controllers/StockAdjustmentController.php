<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Services\Inventory\StockAdjustmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class StockAdjustmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:stock_adjustment.view', only: ['index', 'show', 'searchInventory']),
            new Middleware('permission:stock_adjustment.create', only: ['store']),
            new Middleware('permission:stock_adjustment.approve', only: ['approve']),
            new Middleware('permission:stock_adjustment.reject', only: ['reject']),
            new Middleware('permission:stock_adjustment.cancel', only: ['cancel']),
        ];
    }

    public function index(Request $request, StockAdjustmentService $service): View
    {
        return view('stock-adjustments.index', $service->list($request));
    }

    public function searchInventory(
        Request $request,
        StockAdjustmentService $service
    ): JsonResponse {
        return response()->json([
            'ok' => true,
            'items' => $service->searchInventory($request),
        ]);
    }

    public function store(
        Request $request,
        StockAdjustmentService $service
    ): RedirectResponse {
        try {
            $stockAdjustment = $service->create($request);

            return redirect()
                ->route('stock-adjustments.show', $stockAdjustment)
                ->with('success', 'Stock adjustment created. Approval is required before stock is changed.');
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->with('error', 'Stock adjustment failed: '.$exception->getMessage());
        }
    }

    public function show(
        StockAdjustment $stockAdjustment,
        StockAdjustmentService $service
    ): View {
        return view('stock-adjustments.show', [
            'stockAdjustment' => $service->show($stockAdjustment),
        ]);
    }

    public function approve(
        StockAdjustment $stockAdjustment,
        StockAdjustmentService $service
    ): RedirectResponse {
        try {
            $service->approve($stockAdjustment);

            return back()->with('success', 'Stock adjustment approved and inventory updated successfully.');
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function reject(
        Request $request,
        StockAdjustment $stockAdjustment,
        StockAdjustmentService $service
    ): RedirectResponse {
        try {
            $service->reject($request, $stockAdjustment);

            return back()->with('success', 'Stock adjustment rejected successfully.');
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function cancel(
        StockAdjustment $stockAdjustment,
        StockAdjustmentService $service
    ): RedirectResponse {
        try {
            $service->cancel($stockAdjustment);

            return back()->with('success', 'Stock adjustment cancelled successfully.');
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}