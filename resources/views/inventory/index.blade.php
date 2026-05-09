@extends('components.main-layout')

@section('title', 'Inventory')
@section('page-title', 'Inventory')
@section('page-subtitle', 'Track available product quantities by branch, batch, expiry and cost')

@section('content')
<style>
    .inv-page { max-width: 100%; overflow-x: hidden; }

    .inv-hero,
    .inv-card,
    .inv-filter-card,
    .inv-stat {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .07);
    }

    .inv-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .inv-hero .card-body { padding: 22px; }

    .inv-icon {
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

    .inv-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.02em;
    }

    .inv-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 0;
    }

    .inv-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .inv-actions .btn,
    .inv-btn-row .btn,
    .inv-filter-actions .btn,
    .inv-filter-actions a {
        border-radius: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .inv-stat-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .inv-stat {
        padding: 14px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .inv-stat-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 4px;
    }

    .inv-stat-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 24px;
        line-height: 1;
    }

    .inv-filter-card {
        margin-bottom: 16px;
        border: 1px solid #e8edf5;
    }

    .inv-filter-card .card-body { padding: 16px; }

    .inv-label,
    .modal-body label {
        font-size: 11px;
        font-weight: 950;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .inv-filter-card .form-control,
    .inv-filter-card .custom-select,
    .modal-body .form-control,
    .modal-body .custom-select,
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

    .inv-card { overflow: hidden; }

    .inv-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .inv-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .inv-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .inv-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .inv-table {
        min-width: 1250px;
        margin-bottom: 0;
    }

    .inv-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .inv-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .inv-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .inv-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 3px;
        font-weight: 700;
    }

    .inv-badge {
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
    .badge-blue { background: #eff6ff; color: #1d4ed8; }
    .badge-gray { background: #f1f5f9; color: #475569; }
    .badge-red { background: #fee2e2; color: #b91c1c; }
    .badge-yellow { background: #fef3c7; color: #92400e; }

    .inv-btn-row {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .inv-empty {
        padding: 42px 20px;
        text-align: center;
        color: #64748b;
        font-weight: 750;
    }

    .modal { overflow-y: auto !important; }

    .modal-content {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
    }

    .modal-header {
        background: linear-gradient(135deg, #eff6ff, #ffffff);
        border-bottom: 1px solid #e5e7eb;
        padding: 17px 20px;
    }

    .modal-title {
        color: #0f172a;
        font-weight: 950;
    }

    .modal-body {
        padding: 20px;
        max-height: calc(100vh - 190px);
        overflow-y: auto;
    }

    .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 14px 20px;
    }

    .inv-info-box {
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1e40af;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 750;
    }

    @media (max-width: 767.98px) {
        .inv-hero .card-body { padding: 18px 16px; }

        .inv-hero-main { align-items: flex-start !important; }

        .inv-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
        }

        .inv-actions .btn { width: 100%; }

        .inv-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .inv-filter-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px !important;
        }

        .inv-filter-actions .btn,
        .inv-filter-actions a {
            width: 100%;
            text-align: center;
        }

        .inv-table { min-width: 1050px; }

        .inv-table th,
        .inv-table td {
            padding: 10px;
            font-size: 12px;
        }

        .inv-btn-row .btn {
            font-size: 11px;
            padding: .32rem .55rem;
        }

        .modal-dialog { margin: .65rem; }

        .modal-body {
            padding: 15px;
            max-height: calc(100vh - 165px);
        }

        .modal-header,
        .modal-footer {
            padding: 14px 15px;
        }

        .inv-mobile-stack {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: 8px !important;
        }

        .inv-mobile-stack .btn,
        .inv-mobile-stack input {
            width: 100%;
        }
    }
</style>

<div class="container-fluid inv-page">
    <div class="card inv-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between inv-hero-main" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="inv-icon">
                        <i class="mdi mdi-warehouse"></i>
                    </span>

                    <div>
                        <h4 class="inv-title">Inventory</h4>
                        <p class="inv-subtitle">
                            Real available product quantities by branch, batch, expiry and purchase cost.
                        </p>
                    </div>
                </div>

                <div class="inv-actions">
                    @can('stock.movement.view')
                        <a href="{{ route('inventory-movements.index') }}" class="btn btn-light">
                            <i class="mdi mdi-swap-horizontal mr-1"></i>
                            Movements
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            <i class="mdi mdi-check-circle-outline mr-1"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            <i class="mdi mdi-alert-circle-outline mr-1"></i>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="mdi mdi-alert-circle-outline mr-1"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="inv-stat-grid">
        <div class="inv-stat">
            <div class="inv-stat-label">All</div>
            <div class="inv-stat-value">{{ number_format($counts['all'] ?? 0) }}</div>
        </div>

        <div class="inv-stat">
            <div class="inv-stat-label">Available</div>
            <div class="inv-stat-value">{{ number_format($counts['available'] ?? 0) }}</div>
        </div>

        <div class="inv-stat">
            <div class="inv-stat-label">Low Stock</div>
            <div class="inv-stat-value">{{ number_format($counts['low_stock'] ?? 0) }}</div>
        </div>

        <div class="inv-stat">
            <div class="inv-stat-label">Expiring 30 Days</div>
            <div class="inv-stat-value">{{ number_format($counts['expiring'] ?? 0) }}</div>
        </div>

        <div class="inv-stat">
            <div class="inv-stat-label">Expired</div>
            <div class="inv-stat-value">{{ number_format($counts['expired'] ?? 0) }}</div>
        </div>
    </div>

    <div class="card inv-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('inventory.index') }}">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="inv-label">Search</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control"
                               placeholder="Product, code, barcode, batch or purchase no">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="inv-label">Branch</label>
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
                        <label class="inv-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All</option>
                            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="depleted" {{ request('status') === 'depleted' ? 'selected' : '' }}>Depleted</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="inv-label">Expiry From</label>
                        <input type="date" name="expiry_from" value="{{ request('expiry_from') }}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="inv-label">Expiry To</label>
                        <input type="date" name="expiry_to" value="{{ request('expiry_to') }}" class="form-control">
                    </div>

                    <div class="col-lg-1 col-md-6 mb-3">
                        <label class="inv-label">Low</label>
                        <select name="low_stock" class="custom-select">
                            <option value="">No</option>
                            <option value="1" {{ request('low_stock') == '1' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-end inv-filter-actions" style="gap: 8px;">
                            @if(request()->hasAny(['search', 'branch_id', 'status', 'expiry_from', 'expiry_to', 'low_stock']))
                                <a href="{{ route('inventory.index') }}" class="btn btn-light">
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

    <div class="card inv-card">
        <div class="inv-card-header">
            <h5>Inventory List</h5>
            <p>Inventory is created automatically when purchases are received.</p>
        </div>

        <div class="inv-table-wrap">
            <table class="table inv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Branch</th>
                        <th>Batch</th>
                        <th>Expiry</th>
                        <th>Received</th>
                        <th>Available</th>
                        <th>Cost / Base</th>
                        <th>Total Cost</th>
                        <th>Status</th>
                        <th>Purchase</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($inventories as $index => $inventory)
                        @php
                            $statusClass = match ($inventory->status) {
                                'available' => 'badge-green',
                                'depleted' => 'badge-gray',
                                'expired' => 'badge-red',
                                'blocked' => 'badge-yellow',
                                default => 'badge-blue',
                            };

                            $isExpiredByDate = $inventory->expiry_date && $inventory->expiry_date->isPast();
                            $isLowStock = (int) $inventory->available_quantity_base_units <= 10;
                        @endphp

                        <tr>
                            <td>{{ ($inventories->firstItem() ?? 0) + $index }}</td>

                            <td>
                                <div class="inv-main">{{ $inventory->product?->name }}</div>
                                <div class="inv-sub">
                                    {{ $inventory->product?->code }}
                                    • Base: {{ $inventory->product?->baseUnit?->name ?: '-' }}
                                </div>
                            </td>

                            <td>{{ $inventory->branch?->name ?: '-' }}</td>

                            <td>{{ $inventory->batch_no ?: '-' }}</td>

                            <td>
                                @if($inventory->expiry_date)
                                    <div>{{ $inventory->expiry_date->format('d M Y') }}</div>

                                    @if($isExpiredByDate)
                                        <span class="inv-badge badge-red mt-1">Expired Date</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>

                            <td>{{ number_format((int) $inventory->received_quantity_base_units) }}</td>

                            <td>
                                <strong>{{ number_format((int) $inventory->available_quantity_base_units) }}</strong>

                                @if($isLowStock && $inventory->status === 'available')
                                    <div>
                                        <span class="inv-badge badge-yellow mt-1">Low</span>
                                    </div>
                                @endif
                            </td>

                            <td>{{ number_format((float) $inventory->unit_cost_base, 2) }}</td>

                            <td>{{ number_format((float) $inventory->total_cost, 2) }}</td>

                            <td>
                                <span class="inv-badge {{ $statusClass }}">
                                    {{ ucfirst($inventory->status) }}
                                </span>
                            </td>

                            <td>
                                @if($inventory->purchase)
                                    <div class="inv-main">{{ $inventory->purchase->purchase_no }}</div>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="text-right">
                                <div class="inv-btn-row">
                                    <button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#viewInventoryModal{{ $inventory->id }}">
                                        View
                                    </button>

                                    @can('stock.adjust')
                                        @if(! in_array($inventory->status, ['expired', 'blocked'], true))
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#adjustInventoryModal{{ $inventory->id }}">
                                                Adjust
                                            </button>
                                        @endif

                                        <form method="POST" action="{{ route('inventory.toggle-block', $inventory) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="btn btn-sm btn-outline-{{ $inventory->status === 'blocked' ? 'success' : 'danger' }}">
                                                {{ $inventory->status === 'blocked' ? 'Unblock' : 'Block' }}
                                            </button>
                                        </form>

                                        @if($inventory->status !== 'expired')
                                            <form method="POST" action="{{ route('inventory.mark-expired', $inventory) }}" class="d-inline" onsubmit="return confirm('Mark this inventory as expired?')">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Expire
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12">
                                <div class="inv-empty">
                                    No inventory records found. Receive a purchase first.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inventories->hasPages())
            <div class="p-3 border-top">
                {{ $inventories->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@foreach($inventories as $inventory)
    <div class="modal fade" id="viewInventoryModal{{ $inventory->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">{{ $inventory->product?->name }}</h5>
                        <small class="text-muted">
                            {{ $inventory->branch?->name }} • Batch: {{ $inventory->batch_no ?: '-' }}
                        </small>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="inv-info-box mb-3">
                        Inventory is stored in base units. Sale/POS will reduce this quantity using product unit conversion.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <tr>
                                <th>Product</th>
                                <td>{{ $inventory->product?->name }}</td>
                            </tr>
                            <tr>
                                <th>Branch</th>
                                <td>{{ $inventory->branch?->name }}</td>
                            </tr>
                            <tr>
                                <th>Batch No</th>
                                <td>{{ $inventory->batch_no ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Expiry Date</th>
                                <td>{{ $inventory->expiry_date?->format('d M Y') ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Received Base Quantity</th>
                                <td>{{ number_format((int) $inventory->received_quantity_base_units) }}</td>
                            </tr>
                            <tr>
                                <th>Available Base Quantity</th>
                                <td>{{ number_format((int) $inventory->available_quantity_base_units) }}</td>
                            </tr>
                            <tr>
                                <th>Cost Per Base Unit</th>
                                <td>{{ number_format((float) $inventory->unit_cost_base, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Total Cost</th>
                                <td>{{ number_format((float) $inventory->total_cost, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{{ ucfirst($inventory->status) }}</td>
                            </tr>
                            <tr>
                                <th>Purchase</th>
                                <td>{{ $inventory->purchase?->purchase_no ?: '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('stock.adjust')
        <div class="modal fade" id="adjustInventoryModal{{ $inventory->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <form method="POST" action="{{ route('inventory.adjust', $inventory) }}" class="modal-content">
                    @csrf
                    @method('PATCH')

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Adjust Inventory</h5>
                            <small class="text-muted">{{ $inventory->product?->name }}</small>
                        </div>

                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="inv-info-box mb-3">
                            Current available quantity:
                            <strong>{{ number_format((int) $inventory->available_quantity_base_units) }}</strong>
                            {{ $inventory->product?->baseUnit?->name ?: 'base units' }}.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Adjustment Type</label>
                                <select name="direction" class="custom-select" required>
                                    <option value="in">Increase Quantity</option>
                                    <option value="out">Decrease Quantity</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Quantity In Base Units</label>
                                <input type="number"
                                       min="1"
                                       name="quantity_base_units"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label>Reason</label>
                                <textarea name="reason" rows="3" class="form-control" required placeholder="Example: physical count correction, damage, missing stock"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer inv-mobile-stack">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endforeach

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