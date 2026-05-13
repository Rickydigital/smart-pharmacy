<?php

namespace App\Http\Controllers\Api;

use App\Models\StockAdjustment;
use App\Services\Inventory\StockAdjustmentService;
use Illuminate\Http\Request;

class StockAdjustmentController extends ApiController
{
    public function index(Request $request, StockAdjustmentService $service): mixed
    {
        return $this->success($service->list($request));
    }

    public function searchInventory(Request $request, StockAdjustmentService $service): mixed
    {
        return $this->success([
            'items' => $service->searchInventory($request),
        ]);
    }

    public function store(Request $request, StockAdjustmentService $service): mixed
    {
        try {
            $adjustment = $service->create($request);

            return $this->success([
                'adjustment' => $adjustment,
            ], 'Stock adjustment created. Approval is required before stock is changed.', 201);
        } catch (\Throwable $exception) {
            return $this->error(
                'Stock adjustment failed: '.$exception->getMessage(),
                422
            );
        }
    }

    public function show(
        StockAdjustment $stockAdjustment,
        StockAdjustmentService $service
    ): mixed {
        return $this->success([
            'adjustment' => $service->show($stockAdjustment),
        ]);
    }

    public function approve(
        StockAdjustment $stockAdjustment,
        StockAdjustmentService $service
    ): mixed {
        try {
            return $this->success([
                'adjustment' => $service->approve($stockAdjustment),
            ], 'Stock adjustment approved and inventory updated successfully.');
        } catch (\Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    public function reject(
        Request $request,
        StockAdjustment $stockAdjustment,
        StockAdjustmentService $service
    ): mixed {
        try {
            return $this->success([
                'adjustment' => $service->reject($request, $stockAdjustment),
            ], 'Stock adjustment rejected successfully.');
        } catch (\Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    public function cancel(
        StockAdjustment $stockAdjustment,
        StockAdjustmentService $service
    ): mixed {
        try {
            return $this->success([
                'adjustment' => $service->cancel($stockAdjustment),
            ], 'Stock adjustment cancelled successfully.');
        } catch (\Throwable $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }
}