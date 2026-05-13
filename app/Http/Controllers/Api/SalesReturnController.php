<?php

namespace App\Http\Controllers\Api;

use App\Models\SalesReturn;
use App\Services\SalesReturnService;
use App\Services\SystemNotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalesReturnController extends ApiController
{
    public function index(Request $request, SalesReturnService $service): mixed
    {
        return $this->success($service->indexData($request->all()));
    }

    public function searchSale(Request $request, SalesReturnService $service): mixed
    {
        $validated = $request->validate([
            'sale_no' => ['required', 'string', 'max:100'],
        ]);

        return $this->success($service->searchSale($validated['sale_no']));
    }

    public function store(
        Request $request,
        SalesReturnService $service,
        SystemNotificationService $notifier
    ): mixed {
        $validated = $request->validate($this->storeRules());

        $salesReturn = $service->create($validated);

        $notifier->notifySalesReturnCreated($salesReturn);

        return $this->success([
            'return' => $salesReturn,
            'message' => 'Sales return created. Approval is required before inventory/refund is finalized.',
        ], 201);
    }

    public function show(SalesReturn $salesReturn, SalesReturnService $service): mixed
    {
        return $this->success([
            'return' => $service->show($salesReturn),
        ]);
    }

    public function approve(SalesReturn $salesReturn, SalesReturnService $service): mixed
    {
        return $this->success([
            'return' => $service->approve($salesReturn),
            'message' => 'Sales return approved successfully.',
        ]);
    }

    public function reject(
        Request $request,
        SalesReturn $salesReturn,
        SalesReturnService $service
    ): mixed {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        return $this->success([
            'return' => $service->reject($salesReturn, $validated['rejection_reason']),
            'message' => 'Sales return rejected successfully.',
        ]);
    }

    public function cancel(SalesReturn $salesReturn, SalesReturnService $service): mixed
    {
        return $this->success([
            'return' => $service->cancel($salesReturn),
            'message' => 'Sales return cancelled successfully.',
        ]);
    }

    private function storeRules(): array
    {
        return [
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'return_date' => ['required', 'date'],
            'refund_method' => [
                'required',
                Rule::in(['cash', 'mobile_money', 'card', 'bank', 'credit_note', 'no_refund']),
            ],
            'return_type' => [
                'required',
                Rule::in(['customer_return', 'correction', 'damaged_return']),
            ],
            'reason' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'integer', 'exists:sale_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.condition' => [
                'required',
                Rule::in(['sellable', 'damaged', 'expired', 'opened']),
            ],
            'items.*.restore_to_inventory' => ['nullable', 'boolean'],
            'items.*.reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}