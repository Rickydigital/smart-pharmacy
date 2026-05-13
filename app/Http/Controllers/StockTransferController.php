<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Services\Inventory\StockTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class StockTransferController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:stock_transfer.view', only: ['index', 'show', 'searchInventory']),
            new Middleware('permission:stock_transfer.create', only: ['store']),
            new Middleware('permission:stock_transfer.approve', only: ['approve']),
            new Middleware('permission:stock_transfer.reject', only: ['reject']),
            new Middleware('permission:stock_transfer.cancel', only: ['cancel']),
            new Middleware('permission:stock_transfer.dispatch', only: ['dispatch']),
            new Middleware('permission:stock_transfer.receive', only: ['receive']),
        ];
    }

    public function index(Request $request, StockTransferService $service): View
    {
        return view('stock-transfers.index', $service->list($request));
    }

    public function searchInventory(
        Request $request,
        StockTransferService $service
    ): JsonResponse {
        return response()->json([
            'ok' => true,
            'items' => $service->searchInventory($request),
        ]);
    }

    public function store(
        Request $request,
        StockTransferService $service
    ): RedirectResponse {
        try {
            $stockTransfer = $service->create($request);

            return redirect()
                ->route('stock-transfers.show', $stockTransfer)
                ->with('success', 'Stock transfer created. Approval and dispatch are required before source stock changes.');
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->with('error', 'Stock transfer failed: '.$exception->getMessage());
        }
    }

    public function show(
        StockTransfer $stockTransfer,
        StockTransferService $service
    ): View {
        return view('stock-transfers.show', [
            'stockTransfer' => $service->show($stockTransfer),
        ]);
    }

    public function approve(
        StockTransfer $stockTransfer,
        StockTransferService $service
    ): RedirectResponse {
        try {
            $service->approve($stockTransfer);

            return back()->with('success', 'Stock transfer approved successfully.');
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function reject(
        Request $request,
        StockTransfer $stockTransfer,
        StockTransferService $service
    ): RedirectResponse {
        try {
            $service->reject($request, $stockTransfer);

            return back()->with('success', 'Stock transfer rejected successfully.');
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function cancel(
        StockTransfer $stockTransfer,
        StockTransferService $service
    ): RedirectResponse {
        try {
            $service->cancel($stockTransfer);

            return back()->with('success', 'Stock transfer cancelled successfully.');
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function dispatch(
        StockTransfer $stockTransfer,
        StockTransferService $service
    ): RedirectResponse {
        try {
            $service->dispatch($stockTransfer);

            return back()->with('success', 'Stock transfer dispatched successfully. Source stock has been reduced.');
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function receive(
        StockTransfer $stockTransfer,
        StockTransferService $service
    ): RedirectResponse {
        try {
            $service->receive($stockTransfer);

            return back()->with('success', 'Stock transfer received successfully. Destination stock has been updated.');
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}