@extends('components.main-layout')

@section('title', 'Inventory Movements')
@section('page-title', 'Inventory Movements')
@section('page-subtitle', 'Audit trail of every inventory increase and decrease')

@section('content')
<style>
    .mov-page { max-width: 100%; overflow-x: hidden; }

    .mov-hero,
    .mov-card,
    .mov-filter-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .07);
    }

    .mov-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .mov-hero .card-body { padding: 22px; }

    .mov-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        font-size: 24px;
        flex: 0 0 auto;
    }

    .mov-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.02em;
    }

    .mov-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 0;
    }

    .mov-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .mov-actions .btn,
    .mov-filter-actions .btn,
    .mov-filter-actions a {
        border-radius: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .mov-filter-card {
        margin-bottom: 16px;
        border: 1px solid #e8edf5;
    }

    .mov-filter-card .card-body { padding: 16px; }

    .mov-label {
        font-size: 11px;
        font-weight: 950;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .mov-filter-card .form-control,
    .mov-filter-card .custom-select,
    .select2-container .select2-selection--single {
        min-height: 42px;
        border-radius: 12px !important;
        border-color: #dbe3ef !important;
        font-size: 13px;
        font-weight: 750;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px;
        color: #334155;
        font-weight: 750;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }

    .mov-card { overflow: hidden; }

    .mov-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .mov-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .mov-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .mov-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .mov-table {
        min-width: 1180px;
        margin-bottom: 0;
    }

    .mov-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .mov-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .mov-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .mov-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 3px;
        font-weight: 700;
    }

    .mov-badge {
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-green { background: #dcfce7; color: #15803d; }
    .badge-red { background: #fee2e2; color: #b91c1c; }
    .badge-blue { background: #eff6ff; color: #1d4ed8; }
    .badge-gray { background: #f1f5f9; color: #475569; }

    .mov-empty {
        padding: 42px 20px;
        text-align: center;
        color: #64748b;
        font-weight: 750;
    }

    @media (max-width: 767.98px) {
        .mov-hero .card-body { padding: 18px 16px; }

        .mov-hero-main { align-items: flex-start !important; }

        .mov-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
        }

        .mov-actions .btn { width: 100%; }

        .mov-filter-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px !important;
        }

        .mov-filter-actions .btn,
        .mov-filter-actions a {
            width: 100%;
            text-align: center;
        }

        .mov-table { min-width: 1050px; }

        .mov-table th,
        .mov-table td {
            padding: 10px;
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid mov-page">
    <div class="card mov-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mov-hero-main" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="mov-icon">
                        <i class="mdi mdi-swap-horizontal-bold"></i>
                    </span>

                    <div>
                        <h4 class="mov-title">Inventory Movements</h4>
                        <p class="mov-subtitle">
                            Every purchase receive, adjustment, expiry, sale and return will appear here.
                        </p>
                    </div>
                </div>

                <div class="mov-actions">
                    @can('stock.view')
                        <a href="{{ route('inventory.index') }}" class="btn btn-light">
                            <i class="mdi mdi-warehouse mr-1"></i>
                            Back to Inventory
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="card mov-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('inventory-movements.index') }}">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="mov-label">Search</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control"
                               placeholder="Movement no, product, reason">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="mov-label">Branch</label>
                        <select name="branch_id" class="custom-select select2-clear" data-placeholder="All branches">
                            <option value="">All branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="mov-label">Movement Type</label>
                        <select name="movement_type" class="custom-select select2-clear" data-placeholder="All types">
                            <option value="">All types</option>
                            @foreach($movementTypes as $movementType)
                                <option value="{{ $movementType }}" {{ request('movement_type') === $movementType ? 'selected' : '' }}>
                                    {{ str_replace('_', ' ', ucfirst($movementType)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-1 col-md-6 mb-3">
                        <label class="mov-label">Direction</label>
                        <select name="direction" class="custom-select">
                            <option value="">All</option>
                            <option value="in" {{ request('direction') === 'in' ? 'selected' : '' }}>In</option>
                            <option value="out" {{ request('direction') === 'out' ? 'selected' : '' }}>Out</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="mov-label">From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="mov-label">To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-end mov-filter-actions" style="gap: 8px;">
                            @if(request()->hasAny(['search', 'branch_id', 'movement_type', 'direction', 'date_from', 'date_to']))
                                <a href="{{ route('inventory-movements.index') }}" class="btn btn-light">
                                    Clear
                                </a>
                            @endif

                            <button class="btn btn-primary" type="submit">
                                Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mov-card">
        <div class="mov-card-header">
            <h5>Movement List</h5>
            <p>This page is the inventory audit trail.</p>
        </div>

        <div class="mov-table-wrap">
            <table class="table mov-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Movement</th>
                        <th>Product</th>
                        <th>Branch</th>
                        <th>Type</th>
                        <th>Direction</th>
                        <th>Quantity</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Reason</th>
                        <th>User</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($movements as $index => $movement)
                        <tr>
                            <td>{{ ($movements->firstItem() ?? 0) + $index }}</td>

                            <td>
                                <div class="mov-main">{{ $movement->movement_no }}</div>
                            </td>

                            <td>
                                <div class="mov-main">{{ $movement->product?->name }}</div>
                                <div class="mov-sub">
                                    Base: {{ $movement->product?->baseUnit?->name ?: '-' }}
                                </div>
                            </td>

                            <td>{{ $movement->branch?->name ?: '-' }}</td>

                            <td>
                                <span class="mov-badge badge-blue">
                                    {{ str_replace('_', ' ', ucfirst($movement->movement_type)) }}
                                </span>
                            </td>

                            <td>
                                <span class="mov-badge {{ $movement->direction === 'in' ? 'badge-green' : 'badge-red' }}">
                                    {{ strtoupper($movement->direction) }}
                                </span>
                            </td>

                            <td>{{ number_format((int) $movement->quantity_base_units) }}</td>
                            <td>{{ number_format((int) $movement->balance_before_base_units) }}</td>
                            <td>{{ number_format((int) $movement->balance_after_base_units) }}</td>

                            <td>{{ $movement->reason ?: '-' }}</td>

                            <td>{{ $movement->creator?->displayName() ?: '-' }}</td>

                            <td>
                                {{ $movement->moved_at?->format('d M Y h:i A') ?: $movement->created_at?->format('d M Y h:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12">
                                <div class="mov-empty">
                                    No inventory movements found.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="p-3 border-top">
                {{ $movements->links('vendor.pagination.bootstrap-5') }}
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