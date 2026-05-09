@extends('components.main-layout')

@section('title', 'Purchase Report')
@section('page-title', 'Purchase Report')
@section('page-subtitle', 'Supplier purchase totals, payment status and received stock costs')

@section('content')
@include('reports.partials._styles')

<div class="container-fluid report-page">
    <div class="card report-hero mb-4">
        @include('reports.partials._export_buttons', ['reportKey' => 'center'])
        <div class="card-body">
            <div class="d-flex align-items-center" style="gap: 13px;">
                <span class="report-icon"><i class="mdi mdi-cart-arrow-down"></i></span>
                <div>
                    <h4 class="report-title">Purchase Report</h4>
                    <p class="report-subtitle">Track purchase totals, supplier invoices and payment status.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card report-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.purchases') }}">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="report-label">Branch</label>
                        <select name="branch_id" class="custom-select select2-clear">
                            <option value="">All branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) $branchId === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="report-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All</option>
                            <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="received" {{ $status === 'received' ? 'selected' : '' }}>Received</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="d-flex justify-content-end report-filter-actions" style="gap: 8px;">
                            <a href="{{ route('reports.purchases') }}" class="btn btn-light">Reset</a>
                            <button class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="report-stat-grid">
        <div class="report-stat">
            <div class="report-stat-label">Purchases</div>
            <div class="report-stat-value">{{ number_format($summary['count']) }}</div>
        </div>
        <div class="report-stat">
            <div class="report-stat-label">Total</div>
            <div class="report-stat-value">{{ number_format((float) $summary['total'], 2) }}</div>
        </div>
        <div class="report-stat">
            <div class="report-stat-label">Paid</div>
            <div class="report-stat-value">{{ number_format((float) $summary['paid'], 2) }}</div>
        </div>
        <div class="report-stat">
            <div class="report-stat-label">Balance</div>
            <div class="report-stat-value">{{ number_format((float) $summary['balance'], 2) }}</div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-card-header">
            <h5>Purchase List</h5>
            <p>Supplier purchase records.</p>
        </div>

        <div class="report-table-wrap">
            <table class="table report-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Purchase</th>
                        <th>Supplier</th>
                        <th>Branch</th>
                        <th>Items</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Balance</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $index => $purchase)
                        <tr>
                            <td>{{ ($purchases->firstItem() ?? 0) + $index }}</td>
                            <td>
                                <div class="report-main">{{ $purchase->purchase_no }}</div>
                                <div class="report-sub">{{ $purchase->supplier_invoice_no ?: '-' }}</div>
                            </td>
                            <td>{{ $purchase->supplier?->name ?: '-' }}</td>
                            <td>{{ $purchase->branch?->name ?: '-' }}</td>
                            <td>{{ $purchase->items_count }}</td>
                            <td class="text-right"><strong>{{ number_format((float) $purchase->total_amount, 2) }}</strong></td>
                            <td class="text-right">{{ number_format((float) $purchase->paid_amount, 2) }}</td>
                            <td class="text-right">{{ number_format((float) $purchase->balance_amount, 2) }}</td>
                            <td><span class="report-badge badge-blue">{{ ucfirst($purchase->status) }}</span></td>
                            <td>{{ $purchase->purchase_date?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><div class="report-empty">No purchases found.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
            <div class="p-3 border-top">{{ $purchases->links('vendor.pagination.bootstrap-5') }}</div>
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