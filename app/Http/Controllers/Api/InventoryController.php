<?php

namespace App\Http\Controllers\Api;

use App\Models\Inventory;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends ApiController
{
    public function index(Request $request, InventoryService $service): mixed
    {
        return $this->success($service->listInventories($request));
    }

    public function adjust(
        Request $request,
        Inventory $inventory,
        InventoryService $service
    ): mixed {
        try {
            $updatedInventory = $service->adjust($request, $inventory);

            return $this->success([
                'inventory' => $updatedInventory,
            ], 'Inventory adjusted successfully.');
        } catch (\Throwable $exception) {
            return $this->error(
                'Adjustment failed: '.$exception->getMessage(),
                422
            );
        }
    }

    public function toggleBlock(
        Inventory $inventory,
        InventoryService $service
    ): mixed {
        $wasBlocked = $inventory->status === 'blocked';

        $updatedInventory = $service->toggleBlock($inventory);

        return $this->success([
            'inventory' => $updatedInventory,
        ], $wasBlocked
            ? 'Inventory unblocked successfully.'
            : 'Inventory blocked successfully.'
        );
    }

    public function markExpired(
        Inventory $inventory,
        InventoryService $service
    ): mixed {
        $updatedInventory = $service->markExpired($inventory);

        return $this->success([
            'inventory' => $updatedInventory,
        ], 'Inventory marked as expired.');
    }

    public function movements(Request $request, InventoryService $service): mixed
    {
        return $this->success($service->listMovements($request));
    }

    public function batches(Request $request, InventoryService $service): mixed
    {
        return $this->success($service->listInventories($request));
    }

    public function stockMovements(Request $request, InventoryService $service): mixed
    {
        return $this->success($service->listMovements($request));
    }
}