@extends('components.main-layout')

@section('title', 'Sales Report')
@section('page-title', 'Sales Report')
@section('page-subtitle', 'Detailed sales report by date, branch, payment and sale type')

@section('content')
@include('reports.partials._styles')

<div class="container-fluid report-page">
    <div class="card report-hero mb-4">
        @include('reports.partials._export_buttons', ['reportKey' => 'sales'])
        <div class="card-body">
            <div class="d-flex align-items-center" style="gap: 13px;">
                <span class="report-icon"><i class="mdi mdi-receipt-outline"></i></span>
                <div>
                    <h4 class="report-title">Sales Report</h4>
                    <p class="report-subtitle">Track completed and historical sales receipts.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card report-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.sales') }}">
                <div class="row align-items-end">
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="report-label">Branch</label>
                        <select name="branch_id" class="custom-select select2-clear">
                            <option value="">All branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) $branchId === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="report-label">Sale Type</label>
                        <select name="sale_type" class="custom-select">
                            <option value="">All</option>
                            <option value="retail" {{ $saleType === 'retail' ? 'selected' : '' }}>Retail</option>
                            <option value="wholesale" {{ $saleType === 'wholesale' ? 'selected' : '' }}>Wholesale</option>
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
                            <option value="credit" {{ $paymentMethod === 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="report-label">From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="report-label">To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <div class="d-flex justify-content-end report-filter-actions" style="gap: 8px;">
                            <a href="{{ route('reports.sales') }}" class="btn btn-light">Reset</a>
                            <button class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="report-stat-grid">
        <div class="report-stat">
            <div class="report-stat-label">Receipts</div>
            <div class="report-stat-value">{{ number_format($summary['count']) }}</div>
        </div>
        <div class="report-stat">
            <div class="report-stat-label">Total Sales</div>
            <div class="report-stat-value">{{ number_format((float) $summary['total'], 2) }}</div>
        </div>
        <div class="report-stat">
            <div class="report-stat-label">Paid</div>
            <div class="report-stat-value">{{ number_format((float) $summary['paid'], 2) }}</div>
        </div>
        <div class="report-stat">
            <div class="report-stat-label">Discount</div>
            <div class="report-stat-value">{{ number_format((float) $summary['discount'], 2) }}</div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-card-header">
            <h5>Sales List</h5>
            <p>Detailed receipt list.</p>
        </div>

        <div class="report-table-wrap">
            <table class="table report-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Receipt</th>
                        <th>Branch</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-right">Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $index => $sale)
                        <tr>
                            <td>{{ ($sales->firstItem() ?? 0) + $index }}</td>
                            <td>
                                <div class="report-main">{{ $sale->sale_no }}</div>
                                <div class="report-sub">{{ ucfirst($sale->sale_type) }}</div>
                            </td>
                            <td>{{ $sale->branch?->name ?: '-' }}</td>
                            <td>{{ $sale->displayCustomer() }}</td>
                            <td>{{ $sale->items_count }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($sale->payment_method)) }}</td>
                            <td>
                                <span class="report-badge {{ $sale->status === 'completed' ? 'badge-green' : 'badge-gray' }}">
                                    {{ ucfirst($sale->status) }}
                                </span>
                            </td>
                            <td class="text-right"><strong>{{ number_format((float) $sale->total_amount, 2) }}</strong></td>
                            <td>{{ $sale->sold_at?->format('d M Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><div class="report-empty">No sales found.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="p-3 border-top">{{ $sales->links('vendor.pagination.bootstrap-5') }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    $(function () {
        $('.select2-clear').select2({ width: '100%', allowClear: true, placeholder: 'Select option' });
    });
</script>
@endpush
@endsection