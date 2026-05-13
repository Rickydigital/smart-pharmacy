<?php

namespace App\Http\Controllers;

use App\Models\SalesReturn;
use App\Services\SalesReturnService;
use App\Services\SystemNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalesReturnController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sales_return.view', only: ['index', 'show', 'searchSale']),
            new Middleware('permission:sales_return.create', only: ['store']),
            new Middleware('permission:sales_return.approve', only: ['approve']),
            new Middleware('permission:sales_return.reject', only: ['reject']),
            new Middleware('permission:sales_return.cancel', only: ['cancel']),
        ];
    }

    public function index(Request $request, SalesReturnService $service): View
    {
        return view('sales-returns.index', $service->indexData($request->all()));
    }

    public function searchSale(Request $request, SalesReturnService $service)
    {
        $validated = $request->validate([
            'sale_no' => ['required', 'string', 'max:100'],
        ]);

        return response()->json([
            'ok' => true,
            ...$service->searchSale($validated['sale_no']),
        ]);
    }

    public function store(
        Request $request,
        SalesReturnService $service,
        SystemNotificationService $notifier
    ): RedirectResponse {
        $validated = $request->validate($this->storeRules());

        $salesReturn = $service->create($validated);

        $notifier->notifySalesReturnCreated($salesReturn);

        return redirect()
            ->route('sales-returns.show', $salesReturn)
            ->with('success', 'Sales return created. Approval is required before inventory/refund is finalized.');
    }

    public function show(SalesReturn $salesReturn, SalesReturnService $service): View
    {
        $salesReturn = $service->show($salesReturn);

        return view('sales-returns.show', compact('salesReturn'));
    }

    public function approve(SalesReturn $salesReturn, SalesReturnService $service): RedirectResponse
    {
        $service->approve($salesReturn);

        return back()->with('success', 'Sales return approved successfully.');
    }

    public function reject(
        Request $request,
        SalesReturn $salesReturn,
        SalesReturnService $service
    ): RedirectResponse {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $service->reject($salesReturn, $validated['rejection_reason']);

        return back()->with('success', 'Sales return rejected successfully.');
    }

    public function cancel(SalesReturn $salesReturn, SalesReturnService $service): RedirectResponse
    {
        $service->cancel($salesReturn);

        return back()->with('success', 'Sales return cancelled successfully.');
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