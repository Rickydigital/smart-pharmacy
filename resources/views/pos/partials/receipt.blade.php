@php
    $pharmacy = $sale->pharmacy;
    $branch = $sale->branch;
@endphp

<div class="thermal-receipt">
    <div class="receipt-center">
        <div class="receipt-title">{{ $pharmacy?->name ?? config('app.name') }}</div>

        @if($branch?->name)
            <div class="receipt-muted">{{ $branch->name }}</div>
        @endif

        @if($pharmacy?->phone)
            <div class="receipt-muted">Tel: {{ $pharmacy->phone }}</div>
        @endif

        @if($pharmacy?->address)
            <div class="receipt-muted">{{ $pharmacy->address }}</div>
        @endif
    </div>

    <div class="receipt-line"></div>

    <table class="receipt-meta">
        <tr>
            <td>Receipt</td>
            <td>{{ $sale->sale_no }}</td>
        </tr>
        <tr>
            <td>Date</td>
            <td>{{ $sale->sold_at?->format('d M Y h:i A') ?? $sale->created_at?->format('d M Y h:i A') }}</td>
        </tr>
        <tr>
            <td>Cashier</td>
            <td>{{ $cashierName }}</td>
        </tr>
        <tr>
            <td>Customer</td>
            <td>{{ $sale->displayCustomer() }}</td>
        </tr>
        <tr>
            <td>Payment</td>
            <td>{{ str_replace('_', ' ', ucfirst($sale->payment_method)) }}</td>
        </tr>
    </table>

    <div class="receipt-line"></div>

    <table class="receipt-items">
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td colspan="2">
                        <strong>{{ $item->product?->name }}</strong>
                        <div class="receipt-muted">
                            {{ $item->quantity }} {{ $item->productUnit?->unit?->name }}
                            x {{ number_format((float) $item->unit_price, 2) }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="right">{{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="receipt-line"></div>

    <table class="receipt-totals">
        <tr>
            <td>Subtotal</td>
            <td>{{ number_format((float) $sale->subtotal_amount, 2) }}</td>
        </tr>

        @if((float) $sale->discount_amount > 0)
            <tr>
                <td>Discount</td>
                <td>-{{ number_format((float) $sale->discount_amount, 2) }}</td>
            </tr>
        @endif

        @if((float) $sale->tax_amount > 0)
            <tr>
                <td>Tax</td>
                <td>{{ number_format((float) $sale->tax_amount, 2) }}</td>
            </tr>
        @endif

        <tr class="receipt-grand-total">
            <td>Total</td>
            <td>{{ number_format((float) $sale->total_amount, 2) }}</td>
        </tr>

        <tr>
            <td>Paid</td>
            <td>{{ number_format((float) $sale->paid_amount, 2) }}</td>
        </tr>

        @if((float) $sale->change_amount > 0)
            <tr>
                <td>Change</td>
                <td>{{ number_format((float) $sale->change_amount, 2) }}</td>
            </tr>
        @endif

        @if((float) $sale->balance_amount > 0)
            <tr>
                <td>Balance</td>
                <td>{{ number_format((float) $sale->balance_amount, 2) }}</td>
            </tr>
        @endif
    </table>

    <div class="receipt-line"></div>

    <div class="receipt-center">
        <strong>Thank you!</strong>
        <div class="receipt-muted">Goods once sold are not returnable unless approved.</div>
    </div>
</div>

<style>
    .thermal-receipt {
        width: 80mm;
        max-width: 100%;
        margin: 0 auto;
        background: #ffffff;
        color: #111827;
        font-family: Arial, sans-serif;
        font-size: 12px;
        line-height: 1.35;
        padding: 8px;
    }

    .receipt-center {
        text-align: center;
    }

    .receipt-title {
        font-size: 16px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .receipt-muted {
        color: #4b5563;
        font-size: 11px;
    }

    .receipt-line {
        border-top: 1px dashed #111827;
        margin: 8px 0;
    }

    .receipt-meta,
    .receipt-items,
    .receipt-totals {
        width: 100%;
        border-collapse: collapse;
    }

    .receipt-meta td,
    .receipt-totals td {
        padding: 2px 0;
    }

    .receipt-meta td:first-child {
        color: #4b5563;
    }

    .receipt-meta td:last-child,
    .receipt-totals td:last-child {
        text-align: right;
        font-weight: 700;
    }

    .receipt-items th {
        text-align: left;
        border-bottom: 1px dashed #111827;
        padding-bottom: 4px;
    }

    .receipt-items td {
        padding: 3px 0;
        vertical-align: top;
    }

    .right {
        text-align: right !important;
    }

    .receipt-grand-total td {
        font-size: 14px;
        font-weight: 800;
        border-top: 1px dashed #111827;
        padding-top: 5px;
    }

    @media print {
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            margin: 0;
            background: #ffffff;
        }

        .thermal-receipt {
            width: 80mm;
            margin: 0;
            padding: 6px;
        }
    }
</style>