@extends('components.main-layout')

@section('title', 'Inventory Alert Center')
@section('page-title', 'Inventory Alert Center')
@section('page-subtitle', 'Low stock, out of stock, expiring soon and expired inventory alerts')

@section('content')
<style>
    .alert-page { max-width: 100%; overflow-x: hidden; }

    .alert-hero,
    .alert-card,
    .alert-filter-card,
    .alert-stat {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
    }

    .alert-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .alert-hero .card-body { padding: 22px; }

    .alert-icon {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        font-size: 26px;
        flex: 0 0 auto;
    }

    .alert-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.025em;
    }

    .alert-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 750;
        margin-bottom: 0;
    }

    .alert-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .alert-actions .btn,
    .alert-filter-actions .btn,
    .alert-filter-actions a,
    .alert-btn-row .btn {
        border-radius: 13px;
        font-weight: 850;
        white-space: nowrap;
    }

    .alert-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .alert-stat {
        padding: 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .alert-stat-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 5px;
    }

    .alert-stat-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 24px;
        line-height: 1;
    }

    .alert-stat-sub {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 750;
        margin-top: 7px;
    }

    .alert-filter-card {
        margin-bottom: 16px;
        border: 1px solid #e8edf5;
    }

    .alert-filter-card .card-body { padding: 16px; }

    .alert-label {
        font-size: 11px;
        font-weight: 950;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .alert-filter-card .form-control,
    .alert-filter-card .custom-select,
    .select2-container .select2-selection--single {
        min-height: 44px;
        border-radius: 13px !important;
        border-color: #dbe3ef !important;
        font-size: 13px;
        font-weight: 750;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px;
        color: #334155;
        font-weight: 750;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
    }

    .alert-card { overflow: hidden; }

    .alert-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }

    .alert-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .alert-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .alert-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .alert-table {
        min-width: 1180px;
        margin-bottom: 0;
    }

    .alert-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .alert-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .alert-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .alert-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 4px;
        font-weight: 700;
    }

    .alert-badge {
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
    .badge-purple { background: #f3e8ff; color: #7e22ce; }

    .alert-btn-row {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .alert-empty {
        padding: 42px 20px;
        text-align: center;
        color: #64748b;
        font-weight: 750;
    }

    @media (max-width: 1199.98px) {
        .alert-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .alert-hero .card-body { padding: 18px 16px; }

        .alert-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
        }

        .alert-actions .btn,
        .alert-actions form,
        .alert-actions form button {
            width: 100%;
        }

        .alert-stat-grid {
            grid-template-columns: 1fr;
        }

        .alert-filter-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px !important;
        }

        .alert-filter-actions .btn,
        .alert-filter-actions a {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="container-fluid alert-page">
    <div class="card alert-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="alert-icon">
                        <i class="mdi mdi-bell-alert-outline"></i>
                    </span>

                    <div>
                        <h4 class="alert-title">Inventory Alert Center</h4>
                        <p class="alert-subtitle">
                            Alerts are generated from inventory and sent to Owner/Admin through database notifications.
                        </p>
                    </div>
                </div>

                @can('inventory_alert.generate')
                    <div class="alert-actions">
                        <form method="POST" action="{{ route('inventory-alerts.generate') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-refresh mr-1"></i>
                                Generate Alerts
                            </button>
                        </form>
                    </div>
                @endcan
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

    <div class="alert-stat-grid">
        <div class="alert-stat">
            <div class="alert-stat-label">Open Alerts</div>
            <div class="alert-stat-value">{{ number_format((int) $summary['open']) }}</div>
            <div class="alert-stat-sub">Needs attention</div>
        </div>

        <div class="alert-stat">
            <div class="alert-stat-label">Critical</div>
            <div class="alert-stat-value text-danger">{{ number_format((int) $summary['critical']) }}</div>
            <div class="alert-stat-sub">Expired / out of stock</div>
        </div>

        <div class="alert-stat">
            <div class="alert-stat-label">Low Stock</div>
            <div class="alert-stat-value">{{ number_format((int) $summary['low_stock']) }}</div>
            <div class="alert-stat-sub">Below threshold</div>
        </div>

        <div class="alert-stat">
            <div class="alert-stat-label">Expiring / Expired</div>
            <div class="alert-stat-value">
                {{ number_format((int) $summary['expiring'] + (int) $summary['expired']) }}
            </div>
            <div class="alert-stat-sub">Expiry risk</div>
        </div>
    </div>

    <div class="card alert-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('inventory-alerts.index') }}">
                <div class="row align-items-end">
                    @if($isAdminOrOwner)
                        <div class="col-lg-2 col-md-6 mb-3">
                            <label class="alert-label">Branch</label>
                            <select name="branch_id" class="custom-select select2-clear">
                                <option value="">All branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (string) $branchId === (string) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="alert-label">Type</label>
                        <select name="alert_type" class="custom-select">
                            <option value="">All</option>
                            <option value="low_stock" {{ $alertType === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out_of_stock" {{ $alertType === 'out_of_stock' ? 'selected' : '' }}>Out Of Stock</option>
                            <option value="expiring_soon" {{ $alertType === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                            <option value="expired" {{ $alertType === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="alert-label">Severity</label>
                        <select name="severity" class="custom-select">
                            <option value="">All</option>
                            <option value="critical" {{ $severity === 'critical' ? 'selected' : '' }}>Critical</option>
                            <option value="high" {{ $severity === 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ $severity === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ $severity === 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="alert-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All</option>
                            <option value="open" {{ $status === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="read" {{ $status === 'read' ? 'selected' : '' }}>Read</option>
                            <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="ignored" {{ $status === 'ignored' ? 'selected' : '' }}>Ignored</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="alert-label">Search</label>
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Product / batch / alert">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <div class="d-flex justify-content-end alert-filter-actions" style="gap: 8px;">
                            <a href="{{ route('inventory-alerts.index') }}" class="btn btn-light">Reset</a>
                            <button class="btn btn-primary" type="submit">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card alert-card">
        <div class="alert-card-header">
            <div>
                <h5>Alert List</h5>
                <p>Open alerts are active. Resolve only after stock issue has been handled.</p>
            </div>
        </div>

        <div class="alert-table-wrap">
            <table class="table alert-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Alert</th>
                        <th>Product</th>
                        <th>Branch</th>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>Qty</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th>Notified</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($alerts as $index => $alert)
                        @php
                            $typeClass = match ($alert->alert_type) {
                                'expired', 'out_of_stock' => 'badge-red',
                                'expiring_soon' => 'badge-yellow',
                                'low_stock' => 'badge-blue',
                                default => 'badge-gray',
                            };

                            $severityClass = match ($alert->severity) {
                                'critical' => 'badge-red',
                                'high' => 'badge-yellow',
                                'medium' => 'badge-blue',
                                default => 'badge-gray',
                            };

                            $statusClass = match ($alert->status) {
                                'open' => 'badge-red',
                                'read' => 'badge-yellow',
                                'resolved' => 'badge-green',
                                'ignored' => 'badge-gray',
                                default => 'badge-gray',
                            };
                        @endphp

                        <tr>
                            <td>{{ ($alerts->firstItem() ?? 0) + $index }}</td>

                            <td>
                                <div class="alert-main">{{ $alert->title }}</div>
                                <div class="alert-sub">{{ $alert->alert_no }}</div>
                                <div class="alert-sub">{{ $alert->message }}</div>
                            </td>

                            <td>
                                <div class="alert-main">{{ $alert->product?->name ?: ($alert->meta['product_name'] ?? '-') }}</div>
                                <div class="alert-sub">
                                    Batch: {{ $alert->inventory?->batch_no ?: ($alert->meta['batch_no'] ?? '-') }}
                                </div>
                            </td>

                            <td>{{ $alert->branch?->name ?: ($alert->meta['branch_name'] ?? '-') }}</td>

                            <td>
                                <span class="alert-badge {{ $typeClass }}">
                                    {{ $alert->displayType() }}
                                </span>
                            </td>

                            <td>
                                <span class="alert-badge {{ $severityClass }}">
                                    {{ ucfirst($alert->severity) }}
                                </span>
                            </td>

                            <td>
                                {{ number_format((int) $alert->available_quantity_base_units) }}
                                <div class="alert-sub">{{ $alert->meta['base_unit'] ?? $alert->product?->baseUnit?->name }}</div>
                            </td>

                            <td>
                                {{ $alert->expiry_date?->format('d M Y') ?: '-' }}
                                @if(! is_null($alert->days_to_expiry))
                                    <div class="alert-sub">{{ $alert->days_to_expiry }} day(s)</div>
                                @endif
                            </td>

                            <td>
                                <span class="alert-badge {{ $statusClass }}">
                                    {{ ucfirst($alert->status) }}
                                </span>
                            </td>

                            <td>
                                {{ $alert->notified_at?->timezone('Africa/Dar_es_Salaam')->format('d M Y h:i A') ?: '-' }}
                                @if($alert->channels_sent)
                                    <div class="alert-sub">
                                        {{ implode(', ', $alert->channels_sent) }}
                                    </div>
                                @endif
                            </td>

                            <td class="text-right">
                                <div class="alert-btn-row">
                                    @can('inventory_alert.manage')
                                        @if($alert->status === 'open')
                                            <form method="POST" action="{{ route('inventory-alerts.read', $alert) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">Read</button>
                                            </form>
                                        @endif

                                        @if(in_array($alert->status, ['open', 'read'], true))
                                            <form method="POST" action="{{ route('inventory-alerts.resolve', $alert) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-success">Resolve</button>
                                            </form>

                                            <form method="POST" action="{{ route('inventory-alerts.ignore', $alert) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-secondary">Ignore</button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="alert-empty">No inventory alerts found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($alerts->hasPages())
            <div class="p-3 border-top">
                {{ $alerts->links('vendor.pagination.bootstrap-5') }}
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