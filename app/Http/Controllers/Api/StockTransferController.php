<?php

namespace App\Http\Controllers\Api;

use App\Models\StockTransfer;
use App\Services\Inventory\StockTransferService;
use Illuminate\Http\Request;

class StockTransferController extends ApiController
{
    public function index(Request $request, StockTransferService $service): mixed
    {
        return $this->success($service->list($request));
    }

    public function searchInventory(Request $request, StockTransferService $service): mixed
    {
        return $this->success([
            'items' => $service->searchInventory($request),
        ]);
    }

    public function store(Request $request, StockTransferService $service): mixed
    {
        try {
            return $this->success([
                'transfer' => $service->create($request),
            ], 'Stock transfer created. Approval and dispatch are required before source stock changes.', 201);
        } catch (\Throwable $exception) {
            return $this->error(
                'Stock transfer failed: '.$exception->getMessage(),
                422
            );
        }
    }

    public function show(
        StockTransfer $stockTransfer,
        StockTransferService $service
    ): mixed {
        return $this->success([
            'transfer' => $service->show($stockTransfer),
        ]);
    }

    public function approve(
        StockTransfer $stockTransfer,
        StockTransferService $service
    ): mixed {
        try {
            return $this->success([
                'transfer' => $service->approve($stockTransfer),
            ], 'Stock transfer approved successfully.');
        } catch (\Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    public function reject(
        Request $request,
        StockTransfer $stockTransfer,
        StockTransferService $service
    ): mixed {
        try {
            return $this->success([
                'transfer' => $service->reject($request, $stockTransfer),
            ], 'Stock transfer rejected successfully.');
        } catch (\Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    public function cancel(
        StockTransfer $stockTransfer,
        StockTransferService $service
    ): mixed {
        try {
            return $this->success([
                'transfer' => $service->cancel($stockTransfer),
            ], 'Stock transfer cancelled successfully.');
        } catch (\Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    public function dispatch(
        StockTransfer $stockTransfer,
        StockTransferService $service
    ): mixed {
        try {
            return $this->success([
                'transfer' => $service->dispatch($stockTransfer),
            ], 'Stock transfer dispatched successfully. Source stock has been reduced.');
        } catch (\Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    public function receive(
        StockTransfer $stockTransfer,
        StockTransferService $service
    ): mixed {
        try {
            return $this->success([
                'transfer' => $service->receive($stockTransfer),
            ], 'Stock transfer received successfully. Destination stock has been updated.');
        } catch (\Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }
}