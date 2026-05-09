@extends('components.main-layout')

@section('title', 'Report Center')
@section('page-title', 'Report Center')
@section('page-subtitle', 'Sales, profit, expenses, purchases, inventory and business health')

@section('content')
@include('reports.partials._styles')

<style>
    .report-stat-grid-premium {
        grid-template-columns: repeat(6, minmax(0, 1fr));
    }

    .report-profit-positive {
        color: #15803d !important;
    }

    .report-profit-negative {
        color: #b91c1c !important;
    }

    .report-money-note {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        padding: 7px 10px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 12px;
        font-weight: 850;
    }

    .report-health-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        padding: 16px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .055);
        margin-bottom: 18px;
    }

    .report-health-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .report-health-item {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #fff;
        padding: 14px;
    }

    .report-health-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 5px;
    }

    .report-health-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 18px;
        line-height: 1.1;
    }

    @media (max-width: 1199.98px) {
        .report-stat-grid-premium {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .report-health-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .report-stat-grid-premium {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .report-health-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid report-page">
    <div class="card report-hero mb-4">
        @include('reports.partials._export_buttons', ['reportKey' => 'center'])

        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="report-icon">
                        <i class="mdi mdi-chart-box-outline"></i>
                    </span>

                    <div>
                        <h4 class="report-title">Report Center</h4>
                        <p class="report-subtitle">
                            Owner dashboard for revenue, expenses, profit, purchases and stock risks.
                        </p>
                    </div>
                </div>

                <span class="report-badge badge-blue">
                    {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
                    -
                    {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                </span>
            </div>
        </div>
    </div>

    <div class="card report-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="report-label">Branch</label>
                        <select name="branch_id" class="custom-select select2-clear">
                            <option value="">All branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) $branchId === (string) $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="report-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="report-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="d-flex justify-content-end report-filter-actions" style="gap: 8px;">
                            <a href="{{ route('reports.index') }}" class="btn btn-light">Reset</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-filter-outline mr-1"></i>
                                Apply Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="report-stat-grid report-stat-grid-premium">
        <div class="report-stat">
            <div class="report-stat-label">Total Sales</div>
            <div class="report-stat-value">{{ number_format((float) $summary['sales_total'], 2) }}</div>
            <div class="report-stat-sub">{{ number_format((int) $summary['sales_count']) }} receipt(s)</div>
        </div>

        <div class="report-stat">
            <div class="report-stat-label">Gross Profit</div>
            <div class="report-stat-value report-profit-positive">
                {{ number_format((float) $summary['profit_total'], 2) }}
            </div>
            <div class="report-stat-sub">Gross margin {{ number_format((float) $summary['gross_margin'], 2) }}%</div>
        </div>

        <div class="report-stat">
            <div class="report-stat-label">Expenses</div>
            <div class="report-stat-value">{{ number_format((float) $summary['expense_total'], 2) }}</div>
            <div class="report-stat-sub">{{ number_format((int) $summary['expense_count']) }} expense(s)</div>
        </div>

        <div class="report-stat">
            <div class="report-stat-label">Net Profit</div>
            <div class="report-stat-value {{ ((float) $summary['net_profit']) < 0 ? 'report-profit-negative' : 'report-profit-positive' }}">
                {{ number_format((float) $summary['net_profit'], 2) }}
            </div>
            <div class="report-stat-sub">Net margin {{ number_format((float) $summary['net_margin'], 2) }}%</div>
        </div>

        <div class="report-stat">
            <div class="report-stat-label">Purchases</div>
            <div class="report-stat-value">{{ number_format((float) $summary['purchase_total'], 2) }}</div>
            <div class="report-stat-sub">{{ number_format((int) $summary['purchase_count']) }} purchase(s)</div>
        </div>

        <div class="report-stat">
            <div class="report-stat-label">Cost Sold</div>
            <div class="report-stat-value">{{ number_format((float) $summary['cost_total'], 2) }}</div>
            <div class="report-stat-sub">Discount {{ number_format((float) $summary['discount_total'], 2) }}</div>
        </div>
    </div>

    <div class="report-health-card">
        <div class="report-health-grid">
            <div class="report-health-item">
                <div class="report-health-label">Sales Received</div>
                <div class="report-health-value">{{ number_format((float) $summary['sales_paid'], 2) }}</div>
                <div class="report-money-note">
                    <i class="mdi mdi-cash-check"></i>
                    Paid sales amount
                </div>
            </div>

            <div class="report-health-item">
                <div class="report-health-label">Cash Expenses</div>
                <div class="report-health-value">{{ number_format((float) $summary['cash_expense_total'], 2) }}</div>
                <div class="report-money-note">
                    <i class="mdi mdi-cash-minus"></i>
                    Reduces expected cash
                </div>
            </div>

            <div class="report-health-item">
                <div class="report-health-label">Purchase Paid</div>
                <div class="report-health-value">{{ number_format((float) $summary['purchase_paid'], 2) }}</div>
                <div class="report-money-note">
                    <i class="mdi mdi-cart-check"></i>
                    Supplier payments
                </div>
            </div>

            <div class="report-health-item">
                <div class="report-health-label">Profit Formula</div>
                <div class="report-health-value">
                    {{ ((float) $summary['net_profit']) < 0 ? 'Loss' : 'Healthy' }}
                </div>
                <div class="report-money-note">
                    <i class="mdi mdi-calculator-variant-outline"></i>
                    Gross profit - Expenses
                </div>
            </div>
        </div>
    </div>

    <div class="report-grid">
        <div class="report-card">
            <div class="report-card-header">
                <h5>Payment Breakdown</h5>
                <p>Sales grouped by payment method.</p>
            </div>

            <div class="report-table-wrap">
                <table class="table report-table">
                    <thead>
                        <tr>
                            <th>Payment</th>
                            <th>Receipts</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($paymentBreakdown as $row)
                            <tr>
                                <td>
                                    <span class="report-badge badge-blue">
                                        {{ str_replace('_', ' ', ucfirst($row->payment_method)) }}
                                    </span>
                                </td>
                                <td>{{ number_format((int) $row->sales_count) }}</td>
                                <td class="text-right">
                                    <strong>{{ number_format((float) $row->total_amount, 2) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="report-empty">No payment data.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <div class="report-card-header">
                <h5>Expense Breakdown</h5>
                <p>Operating expenses grouped by category.</p>
            </div>

            <div class="report-table-wrap">
                <table class="table report-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Records</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($expenseBreakdown as $row)
                            <tr>
                                <td>
                                    <span class="report-badge badge-red">
                                        {{ $row->category?->name ?: 'Uncategorized' }}
                                    </span>
                                </td>
                                <td>{{ number_format((int) $row->expenses_count) }}</td>
                                <td class="text-right">
                                    <strong>{{ number_format((float) $row->total_amount, 2) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="report-empty">No expense data.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <div class="report-card-header">
                <h5>Expense Payment Breakdown</h5>
                <p>Expenses grouped by payment method.</p>
            </div>

            <div class="report-table-wrap">
                <table class="table report-table">
                    <thead>
                        <tr>
                            <th>Payment</th>
                            <th>Records</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($paymentExpenseBreakdown as $row)
                            <tr>
                                <td>
                                    <span class="report-badge badge-yellow">
                                        {{ str_replace('_', ' ', ucfirst($row->payment_method)) }}
                                    </span>
                                </td>
                                <td>{{ number_format((int) $row->expenses_count) }}</td>
                                <td class="text-right">
                                    <strong>{{ number_format((float) $row->total_amount, 2) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="report-empty">No expense payment data.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <div class="report-card-header">
                <h5>Sale Type Breakdown</h5>
                <p>Retail and wholesale performance.</p>
            </div>

            <div class="report-table-wrap">
                <table class="table report-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Receipts</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($saleTypeBreakdown as $row)
                            <tr>
                                <td>
                                    <span class="report-badge {{ $row->sale_type === 'wholesale' ? 'badge-yellow' : 'badge-green' }}">
                                        {{ ucfirst($row->sale_type) }}
                                    </span>
                                </td>
                                <td>{{ number_format((int) $row->sales_count) }}</td>
                                <td class="text-right">
                                    <strong>{{ number_format((float) $row->total_amount, 2) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="report-empty">No sale type data.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <div class="report-card-header">
                <h5>Top Products</h5>
                <p>Best selling products by revenue.</p>
            </div>

            <div class="report-table-wrap">
                <table class="table report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th class="text-right">Sales</th>
                            <th class="text-right">Profit</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($topProducts as $row)
                            <tr>
                                <td>
                                    <div class="report-main">{{ $row->product?->name ?: '-' }}</div>
                                    <div class="report-sub">{{ $row->productUnit?->unit?->name ?: '-' }}</div>
                                </td>
                                <td>{{ number_format((int) $row->quantity_sold) }}</td>
                                <td class="text-right">
                                    <strong>{{ number_format((float) $row->sales_amount, 2) }}</strong>
                                </td>
                                <td class="text-right">
                                    <strong>{{ number_format((float) $row->profit_amount, 2) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="report-empty">No product sales data.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <div class="report-card-header">
                <h5>Stock Risks</h5>
                <p>Low stock and expiring items.</p>
            </div>

            <div class="report-table-wrap">
                <table class="table report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Branch</th>
                            <th>Risk</th>
                            <th class="text-right">Qty</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($lowStock as $inventory)
                            <tr>
                                <td>
                                    <div class="report-main">{{ $inventory->product?->name ?: '-' }}</div>
                                    <div class="report-sub">Base: {{ $inventory->product?->baseUnit?->name ?: '-' }}</div>
                                </td>
                                <td>{{ $inventory->branch?->name ?: '-' }}</td>
                                <td>
                                    <span class="report-badge badge-yellow">Low Stock</span>
                                </td>
                                <td class="text-right">
                                    {{ number_format((int) $inventory->available_quantity_base_units) }}
                                </td>
                            </tr>
                        @endforeach

                        @foreach($expiringSoon as $inventory)
                            <tr>
                                <td>
                                    <div class="report-main">{{ $inventory->product?->name ?: '-' }}</div>
                                    <div class="report-sub">Expiry: {{ $inventory->expiry_date?->format('d M Y') }}</div>
                                </td>
                                <td>{{ $inventory->branch?->name ?: '-' }}</td>
                                <td>
                                    <span class="report-badge badge-red">Expiring</span>
                                </td>
                                <td class="text-right">
                                    {{ number_format((int) $inventory->available_quantity_base_units) }}
                                </td>
                            </tr>
                        @endforeach

                        @if($lowStock->isEmpty() && $expiringSoon->isEmpty())
                            <tr>
                                <td colspan="4">
                                    <div class="report-empty">No stock risks found.</div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <div class="report-card-header">
                <h5>Recent Inventory Movements</h5>
                <p>Latest inventory activities in selected branch.</p>
            </div>

            <div class="report-table-wrap">
                <table class="table report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Branch</th>
                            <th>Type</th>
                            <th>Direction</th>
                            <th class="text-right">Qty</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($recentMovements as $movement)
                            <tr>
                                <td>
                                    <div class="report-main">{{ $movement->product?->name ?: '-' }}</div>
                                    <div class="report-sub">{{ $movement->moved_at?->format('d M Y h:i A') }}</div>
                                </td>
                                <td>{{ $movement->branch?->name ?: '-' }}</td>
                                <td>
                                    <span class="report-badge badge-blue">
                                        {{ str_replace('_', ' ', ucfirst($movement->movement_type)) }}
                                    </span>
                                </td>
                                <td>{{ ucfirst($movement->direction) }}</td>
                                <td class="text-right">
                                    {{ number_format((int) $movement->quantity_base_units) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="report-empty">No inventory movement found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(function () {
        $('.select2-clear').select2({
            width: '100%',
            allowClear: true,
            placeholder: 'Select option'
        });
    });
</script>
@endpush
@endsection