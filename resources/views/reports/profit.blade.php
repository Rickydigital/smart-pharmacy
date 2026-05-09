@extends('components.main-layout')

@section('title', 'Profit Report')
@section('page-title', 'Profit Report')
@section('page-subtitle', 'Gross profit, expenses, net profit and sale item margin analysis')

@section('content')
@include('reports.partials._styles')

<style>
    .profit-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .profit-stat {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #ffffff;
        padding: 15px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .055);
    }

    .profit-stat-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .profit-stat-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 21px;
        line-height: 1.1;
    }

    .profit-stat-sub {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-top: 6px;
    }

    .profit-green {
        color: #15803d !important;
    }

    .profit-red {
        color: #b91c1c !important;
    }

    .profit-blue {
        color: #1d4ed8 !important;
    }

    .profit-yellow {
        color: #92400e !important;
    }

    .profit-formula-card {
        border: 1px solid #dbeafe;
        border-radius: 20px;
        background: linear-gradient(135deg, #eff6ff, #ffffff);
        padding: 16px;
        margin-bottom: 18px;
        box-shadow: 0 12px 30px rgba(37, 99, 235, .08);
    }

    .profit-formula-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        align-items: stretch;
    }

    .profit-formula-item {
        background: #ffffff;
        border: 1px solid #dbeafe;
        border-radius: 16px;
        padding: 14px;
        text-align: center;
    }

    .profit-formula-sign {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 100%;
        color: #64748b;
        font-weight: 950;
        font-size: 24px;
    }

    .profit-formula-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 5px;
    }

    .profit-formula-value {
        color: #0f172a;
        font-size: 18px;
        font-weight: 950;
        line-height: 1.15;
    }

    .profit-line-profit {
        font-weight: 950;
    }

    .profit-line-loss {
        color: #b91c1c;
        font-weight: 950;
    }

    @media (max-width: 1199.98px) {
        .profit-stat-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .profit-formula-grid {
            grid-template-columns: 1fr;
        }

        .profit-formula-sign {
            min-height: auto;
            padding: 0;
        }
    }

    @media (max-width: 767.98px) {
        .profit-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<div class="container-fluid report-page">
    <div class="card report-hero mb-4">
        @include('reports.partials._export_buttons', ['reportKey' => 'profit'])

        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="report-icon">
                        <i class="mdi mdi-chart-line"></i>
                    </span>

                    <div>
                        <h4 class="report-title">Profit Report</h4>
                        <p class="report-subtitle">
                            Track sales, cost sold, gross profit, expenses and real net profit.
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
            <form method="GET" action="{{ route('reports.profit') }}">
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
                        <label class="report-label">From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="report-label">To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="d-flex justify-content-end report-filter-actions" style="gap: 8px;">
                            <a href="{{ route('reports.profit') }}" class="btn btn-light">Reset</a>
                            <button class="btn btn-primary" type="submit">
                                <i class="mdi mdi-filter-outline mr-1"></i>
                                Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="profit-stat-grid">
        <div class="profit-stat">
            <div class="profit-stat-label">Sales</div>
            <div class="profit-stat-value profit-blue">
                {{ number_format((float) $summary['sales'], 2) }}
            </div>
            <div class="profit-stat-sub">Revenue after approved returns</div>
        </div>

        <div class="profit-stat">
            <div class="profit-stat-label">Sales Returns</div>
            <div class="profit-stat-value profit-yellow">
                {{ number_format((float) ($summary['returns'] ?? 0), 2) }}
            </div>
            <div class="profit-stat-sub">Approved refunds deducted</div>
        </div>

        <div class="profit-stat">
            <div class="profit-stat-label">Cost Sold</div>
            <div class="profit-stat-value">
                {{ number_format((float) $summary['cost'], 2) }}
            </div>
            <div class="profit-stat-sub">Inventory cost of sold items</div>
        </div>

        <div class="profit-stat">
            <div class="profit-stat-label">Returned Cost</div>
            <div class="profit-stat-value profit-yellow">
                {{ number_format((float) ($summary['return_cost'] ?? 0), 2) }}
            </div>
            <div class="profit-stat-sub">Returned item cost deducted</div>
        </div>

        <div class="profit-stat">
            <div class="profit-stat-label">Gross Profit</div>
            <div class="profit-stat-value profit-green">
                {{ number_format((float) $summary['gross_profit'], 2) }}
            </div>
            <div class="profit-stat-sub">
                After reversed profit: {{ number_format((float) ($summary['return_profit'] ?? 0), 2) }}
            </div>
        </div>

        <div class="profit-stat">
            <div class="profit-stat-label">Expenses</div>
            <div class="profit-stat-value profit-red">
                {{ number_format((float) $summary['expenses'], 2) }}
            </div>
            <div class="profit-stat-sub">Operating expenses</div>
        </div>

        <div class="profit-stat">
            <div class="profit-stat-label">Net Profit</div>
            <div class="profit-stat-value {{ ((float) $summary['net_profit']) < 0 ? 'profit-red' : 'profit-green' }}">
                {{ number_format((float) $summary['net_profit'], 2) }}
            </div>
            <div class="profit-stat-sub">Net margin {{ number_format((float) $summary['net_margin'], 2) }}%</div>
        </div>

        <div class="profit-stat">
            <div class="profit-stat-label">Business Result</div>
            <div class="profit-stat-value {{ ((float) $summary['net_profit']) < 0 ? 'profit-red' : 'profit-green' }}">
                {{ ((float) $summary['net_profit']) < 0 ? 'Loss' : 'Profit' }}
            </div>
            <div class="profit-stat-sub">After expenses</div>
        </div>
    </div>

    <div class="profit-formula-card">
        <div class="profit-formula-grid">
            <div class="profit-formula-item">
                <div class="profit-formula-label">Net Sales</div>
                <div class="profit-formula-value">{{ number_format((float) $summary['sales'], 2) }}</div>
            </div>

            <div class="profit-formula-sign">-</div>

            <div class="profit-formula-item">
                <div class="profit-formula-label">Net Cost Sold + Expenses</div>
                <div class="profit-formula-value">
                    {{ number_format(((float) $summary['cost'] + (float) $summary['expenses']), 2) }}
                </div>
            </div>

            <div class="profit-formula-sign">=</div>

            <div class="profit-formula-item">
                <div class="profit-formula-label">Net Profit</div>
                <div class="profit-formula-value {{ ((float) $summary['net_profit']) < 0 ? 'profit-red' : 'profit-green' }}">
                    {{ number_format((float) $summary['net_profit'], 2) }}
                </div>
            </div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-card-header">
            <h5>Profit Lines</h5>
            <p>Sale item profit lines. Summary above deducts approved returns and operating expenses.</p>
        </div>

        <div class="report-table-wrap">
            <table class="table report-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Receipt</th>
                        <th>Product</th>
                        <th>Unit</th>
                        <th>Qty</th>
                        <th class="text-right">Original Sales</th>
                        <th class="text-right">Original Cost</th>
                        <th class="text-right">Original Profit</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $index => $item)
                        @php
                            $lineProfit = (float) $item->profit_amount;
                        @endphp

                        <tr>
                            <td>{{ ($items->firstItem() ?? 0) + $index }}</td>

                            <td>
                                <div class="report-main">{{ $item->sale?->sale_no ?: '-' }}</div>
                                <div class="report-sub">{{ $item->sale?->branch?->name ?: '-' }}</div>
                            </td>

                            <td>
                                <div class="report-main">{{ $item->product?->name ?: '-' }}</div>
                                <div class="report-sub">{{ $item->sale?->sale_type ? ucfirst($item->sale->sale_type) : '-' }}</div>
                            </td>

                            <td>{{ $item->productUnit?->unit?->name ?: '-' }}</td>
                            <td>{{ number_format((int) $item->quantity) }}</td>

                            <td class="text-right">
                                {{ number_format((float) $item->line_total, 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format((float) $item->total_cost, 2) }}
                            </td>

                            <td class="text-right">
                                <strong class="{{ $lineProfit < 0 ? 'profit-line-loss' : 'profit-line-profit' }}">
                                    {{ number_format($lineProfit, 2) }}
                                </strong>
                            </td>

                            <td>{{ $item->sale?->sold_at?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="report-empty">No profit data found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="p-3 border-top">
                {{ $items->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
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