@extends('components.main-layout')

@section('title', 'Expense Report')
@section('page-title', 'Expense Report')
@section('page-subtitle', 'Track operating expenses by branch, category and payment method')

@section('content')
@include('reports.partials._styles')

<style>
    .expense-report-hero-icon {
        background: #fff5f5 !important;
        color: #b91c1c !important;
    }

    .expense-report-total {
        color: #b91c1c !important;
    }

    .expense-mini-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .expense-mini-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #ffffff;
        padding: 15px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .055);
    }

    .expense-mini-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .expense-mini-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 22px;
        line-height: 1.1;
    }

    .expense-mini-sub {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-top: 6px;
    }

    .expense-row-title {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.2;
    }

    .expense-row-sub {
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        margin-top: 3px;
    }

    @media (max-width: 991.98px) {
        .expense-mini-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .expense-mini-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid report-page">
    <div class="card report-hero mb-4">
        @include('reports.partials._export_buttons', ['reportKey' => 'expenses'])

        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="report-icon expense-report-hero-icon">
                        <i class="mdi mdi-cash-minus"></i>
                    </span>

                    <div>
                        <h4 class="report-title">Expense Report</h4>
                        <p class="report-subtitle">
                            Monitor operating costs, cash expenses and category spending.
                        </p>
                    </div>
                </div>

                <span class="report-badge badge-red">
                    {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
                    -
                    {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                </span>
            </div>
        </div>
    </div>

    <div class="card report-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.expenses') }}">
                <div class="row align-items-end">
                    <div class="col-lg-2 col-md-6 mb-3">
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

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="report-label">Payment</label>
                        <select name="payment_method" class="custom-select">
                            <option value="">All</option>
                            <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="mobile_money" {{ $paymentMethod === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                            <option value="card" {{ $paymentMethod === 'card' ? 'selected' : '' }}>Card</option>
                            <option value="bank" {{ $paymentMethod === 'bank' ? 'selected' : '' }}>Bank</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="report-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All</option>
                            <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="voided" {{ $status === 'voided' ? 'selected' : '' }}>Voided</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="report-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="report-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <div class="d-flex justify-content-end report-filter-actions" style="gap: 8px;">
                            <a href="{{ route('reports.expenses') }}" class="btn btn-light">Reset</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-filter-outline mr-1"></i>
                                Apply
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="expense-mini-grid">
        <div class="expense-mini-card">
            <div class="expense-mini-label">Expense Total</div>
            <div class="expense-mini-value expense-report-total">
                {{ number_format((float) $summary['total'], 2) }}
            </div>
            <div class="expense-mini-sub">{{ number_format((int) $summary['count']) }} record(s)</div>
        </div>

        <div class="expense-mini-card">
            <div class="expense-mini-label">Cash Expenses</div>
            <div class="expense-mini-value">
                {{ number_format((float) $summary['cash'], 2) }}
            </div>
            <div class="expense-mini-sub">Reduces expected cash</div>
        </div>

        <div class="expense-mini-card">
            <div class="expense-mini-label">Other Payments</div>
            <div class="expense-mini-value">
                {{ number_format((float) $summary['other'], 2) }}
            </div>
            <div class="expense-mini-sub">Mobile money, card and bank</div>
        </div>

        <div class="expense-mini-card">
            <div class="expense-mini-label">Average Expense</div>
            <div class="expense-mini-value">
                {{ ((int) $summary['count']) > 0 ? number_format((float) $summary['total'] / (int) $summary['count'], 2) : '0.00' }}
            </div>
            <div class="expense-mini-sub">Average per expense record</div>
        </div>
    </div>

    <div class="report-grid mb-4">
        <div class="report-card">
            <div class="report-card-header">
                <h5>Category Breakdown</h5>
                <p>Where operating money is going.</p>
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
                        @forelse($categoryBreakdown as $row)
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
                                    <div class="report-empty">No category data.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <div class="report-card-header">
                <h5>Expense Insight</h5>
                <p>Quick interpretation of current selected period.</p>
            </div>

            <div class="p-3">
                <div class="expense-mini-card mb-2">
                    <div class="expense-mini-label">Cash Share</div>
                    <div class="expense-mini-value">
                        @php
                            $cashShare = ((float) $summary['total']) > 0
                                ? (((float) $summary['cash'] / (float) $summary['total']) * 100)
                                : 0;
                        @endphp
                        {{ number_format($cashShare, 2) }}%
                    </div>
                    <div class="expense-mini-sub">Percentage of expenses paid in cash.</div>
                </div>

                <div class="expense-mini-card">
                    <div class="expense-mini-label">Control Note</div>
                    <div class="expense-mini-value" style="font-size: 15px;">
                        {{ ((float) $summary['cash']) > 0 ? 'Cash control needed' : 'No cash pressure' }}
                    </div>
                    <div class="expense-mini-sub">
                        Cash expenses affect daily closing expected cash directly.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-card-header">
            <h5>Expense Records</h5>
            <p>Detailed operating expense transactions.</p>
        </div>

        <div class="report-table-wrap">
            <table class="table report-table">
                <thead>
                    <tr>
                        <th>Expense</th>
                        <th>Branch</th>
                        <th>Category</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-right">Amount</th>
                        <th>Recorded By</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>
                                <div class="expense-row-title">{{ $expense->expense_no }}</div>
                                <div class="expense-row-sub">{{ $expense->title }}</div>
                                @if($expense->reference_no)
                                    <div class="expense-row-sub">Ref: {{ $expense->reference_no }}</div>
                                @endif
                            </td>

                            <td>{{ $expense->branch?->name ?: '-' }}</td>
                            <td>{{ $expense->category?->name ?: '-' }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($expense->payment_method)) }}</td>

                            <td>
                                <span class="report-badge {{ $expense->status === 'paid' ? 'badge-green' : 'badge-red' }}">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>

                            <td class="text-right">
                                <strong>{{ number_format((float) $expense->amount, 2) }}</strong>
                            </td>

                            <td>
                                {{ $expense->creator?->displayName() ?: ($expense->creator?->username ?? '-') }}
                            </td>

                            <td>{{ $expense->expense_date?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="report-empty">No expenses found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-3 border-top">
                {{ $expenses->links('vendor.pagination.bootstrap-5') }}
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