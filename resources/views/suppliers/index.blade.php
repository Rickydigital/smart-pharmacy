@extends('components.main-layout')

@section('title', 'Suppliers')
@section('page-title', 'Suppliers')
@section('page-subtitle', 'Manage companies and people supplying products to the pharmacy')

@section('content')
<style>
    .supplier-page {
        max-width: 100%;
        overflow-x: hidden;
    }

    .supplier-hero,
    .supplier-card,
    .supplier-filter-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .07);
    }

    .supplier-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .supplier-hero .card-body {
        padding: 22px;
    }

    .supplier-icon {
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

    .supplier-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.02em;
    }

    .supplier-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 0;
    }

    .supplier-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .supplier-actions .btn,
    .supplier-btn-row .btn,
    .supplier-filter-actions .btn,
    .supplier-filter-actions a {
        border-radius: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .supplier-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .supplier-stat {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 14px;
        background: #fff;
    }

    .supplier-stat-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 4px;
    }

    .supplier-stat-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 24px;
        line-height: 1;
    }

    .supplier-filter-card {
        margin-bottom: 16px;
        border: 1px solid #e8edf5;
    }

    .supplier-filter-card .card-body {
        padding: 16px;
    }

    .supplier-label,
    .modal-body label {
        font-size: 11px;
        font-weight: 950;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .supplier-filter-card .form-control,
    .supplier-filter-card .custom-select,
    .modal-body .form-control,
    .modal-body .custom-select {
        min-height: 42px;
        border-radius: 12px;
        border-color: #dbe3ef;
        font-size: 13px;
        font-weight: 750;
    }

    .supplier-card {
        overflow: hidden;
    }

    .supplier-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .supplier-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .supplier-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .supplier-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .supplier-table {
        min-width: 980px;
        margin-bottom: 0;
    }

    .supplier-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .supplier-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .supplier-name {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .supplier-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 3px;
        font-weight: 700;
    }

    .supplier-badge {
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

    .supplier-badge-green {
        background: #dcfce7;
        color: #15803d;
    }

    .supplier-badge-gray {
        background: #f1f5f9;
        color: #475569;
    }

    .supplier-badge-blue {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .supplier-btn-row {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .supplier-empty {
        padding: 42px 20px;
        text-align: center;
        color: #64748b;
        font-weight: 750;
    }

    .modal {
        overflow-y: auto !important;
    }

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
    }

    .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 14px 20px;
    }

    @media (max-width: 767.98px) {
        .supplier-hero .card-body {
            padding: 18px 16px;
        }

        .supplier-hero-main {
            align-items: flex-start !important;
        }

        .supplier-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
        }

        .supplier-actions .btn {
            width: 100%;
        }

        .supplier-stat-grid {
            grid-template-columns: 1fr;
        }

        .supplier-filter-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px !important;
        }

        .supplier-filter-actions .btn,
        .supplier-filter-actions a {
            width: 100%;
            text-align: center;
        }

        .supplier-table {
            min-width: 850px;
        }

        .supplier-table th,
        .supplier-table td {
            padding: 10px;
            font-size: 12px;
        }

        .supplier-btn-row .btn {
            font-size: 11px;
            padding: .32rem .55rem;
        }

        .modal-dialog {
            margin: .65rem;
        }

        .modal-body {
            padding: 15px;
        }

        .modal-header,
        .modal-footer {
            padding: 14px 15px;
        }

        .supplier-mobile-stack {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: 8px !important;
        }

        .supplier-mobile-stack .btn {
            width: 100%;
        }
    }
</style>

<div class="container-fluid supplier-page">
    <div class="card supplier-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between supplier-hero-main" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="supplier-icon">
                        <i class="mdi mdi-truck-delivery-outline"></i>
                    </span>

                    <div>
                        <h4 class="supplier-title">Suppliers</h4>
                        <p class="supplier-subtitle">
                            Manage supplier contacts before recording product purchases and inventory receiving.
                        </p>
                    </div>
                </div>

                @can('supplier.manage')
                    <div class="supplier-actions">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createSupplierModal">
                            <i class="mdi mdi-plus mr-1"></i>
                            Add Supplier
                        </button>
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

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="mdi mdi-alert-circle-outline mr-1"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="supplier-stat-grid">
        <div class="supplier-stat">
            <div class="supplier-stat-label">All Suppliers</div>
            <div class="supplier-stat-value">{{ number_format($counts['all'] ?? 0) }}</div>
        </div>

        <div class="supplier-stat">
            <div class="supplier-stat-label">Active</div>
            <div class="supplier-stat-value">{{ number_format($counts['active'] ?? 0) }}</div>
        </div>

        <div class="supplier-stat">
            <div class="supplier-stat-label">Inactive</div>
            <div class="supplier-stat-value">{{ number_format($counts['inactive'] ?? 0) }}</div>
        </div>
    </div>

    <div class="card supplier-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('suppliers.index') }}">
                <div class="row align-items-end">
                    <div class="col-lg-6 col-md-12 mb-3">
                        <label class="supplier-label">Search</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control"
                               placeholder="Search supplier name, code, contact, phone, email or address">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="supplier-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All suppliers</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active only</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive only</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="d-flex justify-content-end supplier-filter-actions" style="gap: 8px;">
                            @if(request()->hasAny(['search', 'status']))
                                <a href="{{ route('suppliers.index') }}" class="btn btn-light">
                                    <i class="mdi mdi-close mr-1"></i>
                                    Clear
                                </a>
                            @endif

                            <button class="btn btn-primary" type="submit">
                                <i class="mdi mdi-magnify mr-1"></i>
                                Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card supplier-card">
        <div class="supplier-card-header">
            <h5>Supplier List</h5>
            <p>Supplier records used during purchase and inventory receiving.</p>
        </div>

        <div class="supplier-table-wrap">
            <table class="table supplier-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Supplier</th>
                        <th>Contact Person</th>
                        <th>Phone / Email</th>
                        <th>Address</th>
                        <th>Purchases</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($suppliers as $index => $supplier)
                        <tr>
                            <td>{{ ($suppliers->firstItem() ?? 0) + $index }}</td>

                            <td>
                                <div class="supplier-name">{{ $supplier->name }}</div>
                                <div class="supplier-sub">{{ $supplier->code ?: '-' }}</div>
                            </td>

                            <td>{{ $supplier->contact_person ?: '-' }}</td>

                            <td>
                                <div>{{ $supplier->phone ?: '-' }}</div>
                                <div class="supplier-sub">{{ $supplier->email ?: '' }}</div>
                            </td>

                            <td>{{ $supplier->address ?: '-' }}</td>

                            <td>
                                <span class="supplier-badge supplier-badge-blue">
                                    {{ $supplier->purchases_count }} purchases
                                </span>
                            </td>

                            <td>
                                <span class="supplier-badge {{ $supplier->is_active ? 'supplier-badge-green' : 'supplier-badge-gray' }}">
                                    {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="text-right">
                                @can('supplier.manage')
                                    <div class="supplier-btn-row">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editSupplierModal{{ $supplier->id }}">
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('suppliers.toggle', $supplier) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="btn btn-sm btn-outline-{{ $supplier->is_active ? 'danger' : 'success' }}">
                                                {{ $supplier->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>

                                        @if($supplier->purchases_count < 1)
                                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="d-inline" onsubmit="return confirm('Delete this supplier?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                    -
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="supplier-empty">
                                    No suppliers found.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="p-3 border-top">
                {{ $suppliers->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@can('supplier.manage')
    <div class="modal fade" id="createSupplierModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <form method="POST" action="{{ route('suppliers.store') }}" class="modal-content">
                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Add Supplier</h5>
                        <small class="text-muted">Supplier code is generated automatically.</small>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label>Supplier Name</label>
                            <input name="name" class="form-control" value="{{ old('name') }}" placeholder="Example: MSD Tanzania" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Status</label>
                            <div class="custom-control custom-switch mt-2">
                                <input type="checkbox" class="custom-control-input" id="create_supplier_active" name="is_active" value="1" checked>
                                <label class="custom-control-label" for="create_supplier_active">Active</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Contact Person</label>
                            <input name="contact_person" class="form-control" value="{{ old('contact_person') }}" placeholder="Optional">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Phone</label>
                            <input name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Optional">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Optional">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Address</label>
                            <input name="address" class="form-control" value="{{ old('address') }}" placeholder="Optional">
                        </div>

                        <div class="col-12 mb-3">
                            <label>Notes</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Optional notes">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer supplier-mobile-stack">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>

    @foreach($suppliers as $supplier)
        <div class="modal fade" id="editSupplierModal{{ $supplier->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="modal-content">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Edit Supplier</h5>
                            <small class="text-muted">Code: {{ $supplier->code ?: '-' }}</small>
                        </div>

                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label>Supplier Name</label>
                                <input name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Status</label>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox"
                                           class="custom-control-input"
                                           id="edit_supplier_active_{{ $supplier->id }}"
                                           name="is_active"
                                           value="1"
                                           {{ $supplier->is_active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="edit_supplier_active_{{ $supplier->id }}">Active</label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Contact Person</label>
                                <input name="contact_person" class="form-control" value="{{ old('contact_person', $supplier->contact_person) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Phone</label>
                                <input name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Address</label>
                                <input name="address" class="form-control" value="{{ old('address', $supplier->address) }}">
                            </div>

                            <div class="col-12 mb-3">
                                <label>Notes</label>
                                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $supplier->notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer supplier-mobile-stack">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endcan
@endsection