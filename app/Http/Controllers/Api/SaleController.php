<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\DelegatesToWebController;
use App\Http\Controllers\SaleController as WebSaleController;
use App\Models\Pharmacy;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends ApiController
{
    use DelegatesToWebController;

    protected string $webController = WebSaleController::class;

    public function index(): mixed
    {
        return $this->fromWeb('index');
    }

    public function show(Sale $sale): mixed
    {
        return app(WebSaleController::class)->show($sale);
    }

    public function cancel(Request $request, Sale $sale): mixed
    {
        app(WebSaleController::class)->cancel($request, $sale);

        return $this->success([
            'sale' => $sale->fresh(),
            'message' => 'Sale cancelled and inventory restored successfully.',
        ]);
    }

    public function receipt(Sale $sale): mixed
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $sale->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }

        $sale->load([
            'pharmacy.setting',
            'branch',
            'creator',
            'items.product',
            'items.productUnit.unit',
        ]);

        return $this->success([
            'sale' => $sale,
            'cashier' => $sale->creator?->displayName(),
        ]);
    }
}