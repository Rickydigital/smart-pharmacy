@extends('components.main-layout')

@section('title', 'Stock Report')
@section('page-title', 'Stock Report')
@section('page-subtitle', 'Current inventory value, quantity, stock risks and expiry status')

@section('content')
@include('reports.partials._styles')

<div class="container-fluid report-page">
    <div class="card report-hero mb-4">
        @include('reports.partials._export_buttons', ['reportKey' => 'center'])
        <div class="card-body">
            <div class="d-flex align-items-center" style="gap: 13px;">
                <span class="report-icon"><i class="mdi mdi-warehouse"></i></span>
                <div>
                    <h4 class="report-title">Stock Report</h4>
                    <p class="report-subtitle">View stock quantity, value, status and risk level.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card report-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.stock') }}">
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

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="report-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All</option>
                            <option value="available" {{ $status === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="depleted" {{ $status === 'depleted' ? 'selected' : '' }}>Depleted</option>
                            <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="blocked" {{ $status === 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="report-label">Risk</label>
                        <select name="risk" class="custom-select">
                            <option value="">All</option>
                            <option value="low" {{ $risk === 'low' ? 'selected' : '' }}>Low Stock</option>
                            <option value="expiring" {{ $risk === 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
                            <option value="expired" {{ $risk === 'expired' ? 'selected' : '' }}>Expired Date</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="d-flex justify-content-end report-filter-actions" style="gap: 8px;">
                            <a href="{{ route('reports.stock') }}" class="btn btn-light">Reset</a>
                            <button class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="report-stat-grid">
        <div class="report-stat">
            <div class="report-stat-label">Inventory Rows</div>
            <div class="report-stat-value">{{ number_format($summary['items']) }}</div>
        </div>
        <div class="report-stat">
            <div class="report-stat-label">Available Qty</div>
            <div class="report-stat-value">{{ number_format($summary['available_qty']) }}</div>
        </div>
        <div class="report-stat">
            <div class="report-stat-label">Stock Value</div>
            <div class="report-stat-value">{{ number_format((float) $summary['stock_value'], 2) }}</div>
        </div>
        <div class="report-stat">
            <div class="report-stat-label">Low Stock</div>
            <div class="report-stat-value">{{ number_format($summary['low_stock']) }}</div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-card-header">
            <h5>Inventory List</h5>
            <p>Stock rows by batch, branch and expiry.</p>
        </div>

        <div class="report-table-wrap">
            <table class="table report-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Branch</th>
                        <th>Batch</th>
                        <th>Expiry</th>
                        <th class="text-right">Available</th>
                        <th class="text-right">Cost/Base</th>
                        <th class="text-right">Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventories as $index => $inventory)
                        <tr>
                            <td>{{ ($inventories->firstItem() ?? 0) + $index }}</td>
                            <td>
                                <div class="report-main">{{ $inventory->product?->name }}</div>
                                <div class="report-sub">Base: {{ $inventory->product?->baseUnit?->name ?: '-' }}</div>
                            </td>
                            <td>{{ $inventory->branch?->name ?: '-' }}</td>
                            <td>{{ $inventory->batch_no ?: '-' }}</td>
                            <td>{{ $inventory->expiry_date?->format('d M Y') ?: '-' }}</td>
                            <td class="text-right">{{ number_format((int) $inventory->available_quantity_base_units) }}</td>
                            <td class="text-right">{{ number_format((float) $inventory->unit_cost_base, 2) }}</td>
                            <td class="text-right"><strong>{{ number_format((float) $inventory->available_quantity_base_units * (float) $inventory->unit_cost_base, 2) }}</strong></td>
                            <td><span class="report-badge badge-blue">{{ ucfirst($inventory->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><div class="report-empty">No stock found.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inventories->hasPages())
            <div class="p-3 border-top">{{ $inventories->links('vendor.pagination.bootstrap-5') }}</div>
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