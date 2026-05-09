@extends('components.main-layout')

@section('title', 'Stock Adjustments')
@section('page-title', 'Stock Adjustments')
@section('page-subtitle', 'Request, approve and audit stock corrections, damages, expiry and physical count changes')

@section('content')
<style>
    .adjust-page { max-width: 100%; overflow-x: hidden; }

    .adjust-hero,
    .adjust-card,
    .adjust-filter-card,
    .adjust-stat {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
    }

    .adjust-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .adjust-hero .card-body { padding: 22px; }

    .adjust-icon {
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

    .adjust-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.025em;
    }

    .adjust-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 750;
        margin-bottom: 0;
    }

    .adjust-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .adjust-actions .btn,
    .adjust-filter-actions .btn,
    .adjust-filter-actions a,
    .adjust-btn-row .btn {
        border-radius: 13px;
        font-weight: 850;
        white-space: nowrap;
    }

    .adjust-stat-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .adjust-stat {
        padding: 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .adjust-stat-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 5px;
    }

    .adjust-stat-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 23px;
        line-height: 1;
    }

    .adjust-stat-sub {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 750;
        margin-top: 7px;
    }

    .adjust-filter-card {
        margin-bottom: 16px;
        border: 1px solid #e8edf5;
    }

    .adjust-filter-card .card-body { padding: 16px; }

    .adjust-label,
    .modal-body label {
        font-size: 11px;
        font-weight: 950;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .adjust-filter-card .form-control,
    .adjust-filter-card .custom-select,
    .modal-body .form-control,
    .modal-body .custom-select,
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

    .adjust-card { overflow: hidden; }

    .adjust-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }

    .adjust-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .adjust-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .adjust-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .adjust-table {
        min-width: 1160px;
        margin-bottom: 0;
    }

    .adjust-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .adjust-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .adjust-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .adjust-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 4px;
        font-weight: 700;
    }

    .adjust-badge {
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

    .adjust-btn-row {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .adjust-empty {
        padding: 42px 20px;
        text-align: center;
        color: #64748b;
        font-weight: 750;
    }

    .modal-content {
        border: 0;
        border-radius: 22px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #eff6ff, #ffffff);
        border-bottom: 1px solid #e5e7eb;
        padding: 18px 20px;
    }

    .modal-title {
        color: #0f172a;
        font-weight: 950;
    }

    .modal-body {
        padding: 20px;
        max-height: calc(100vh - 190px);
        overflow-y: auto;
        background: #f8fafc;
    }

    .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 14px 20px;
        background: #fff;
    }

    .adjust-box,
    .inventory-search-box {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 18px;
        padding: 15px;
        margin-bottom: 14px;
    }

    .inventory-result-list {
        display: grid;
        gap: 10px;
        margin-top: 12px;
    }

    .inventory-result-item {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        padding: 13px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .inventory-result-name {
        font-weight: 950;
        color: #0f172a;
    }

    .inventory-result-meta {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-top: 3px;
    }

    .adjust-items-wrap {
        display: grid;
        gap: 12px;
    }

    .adjust-item-row {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #ffffff;
        padding: 14px;
    }

    .adjust-item-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .adjust-item-name {
        font-weight: 950;
        color: #0f172a;
    }

    .adjust-item-meta {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-top: 3px;
    }

    .adjust-item-grid {
        display: grid;
        grid-template-columns: 150px 180px minmax(0, 1fr) 60px;
        gap: 10px;
        align-items: end;
    }

    .adjust-total-box {
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 16px;
        padding: 13px;
        font-weight: 950;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }

    @media (max-width: 1199.98px) {
        .adjust-stat-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .adjust-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .adjust-item-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .adjust-hero .card-body { padding: 18px 16px; }

        .adjust-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
        }

        .adjust-actions .btn { width: 100%; }

        .adjust-stat-grid {
            grid-template-columns: 1fr;
        }

        .adjust-filter-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px !important;
        }

        .adjust-filter-actions .btn,
        .adjust-filter-actions a {
            width: 100%;
            text-align: center;
        }

        .adjust-item-grid {
            grid-template-columns: 1fr;
        }

        .modal-dialog { margin: .65rem; }

        .modal-body {
            padding: 15px;
            max-height: calc(100vh - 165px);
        }

        .adjust-mobile-stack {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: 8px !important;
        }

        .adjust-mobile-stack .btn {
            width: 100%;
        }
    }
</style>

<div class="container-fluid adjust-page">
    <div class="card adjust-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="adjust-icon">
                        <i class="mdi mdi-clipboard-edit-outline"></i>
                    </span>

                    <div>
                        <h4 class="adjust-title">Stock Adjustments</h4>
                        <p class="adjust-subtitle">
                            Request stock corrections for damaged, expired, lost, found or physical count differences.
                        </p>
                    </div>
                </div>

                @can('stock_adjustment.create')
                    <div class="adjust-actions">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAdjustmentModal">
                            <i class="mdi mdi-plus-circle-outline mr-1"></i>
                            New Adjustment
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

    <div class="adjust-stat-grid">
        <div class="adjust-stat">
            <div class="adjust-stat-label">Adjustments</div>
            <div class="adjust-stat-value">{{ number_format((int) ($summary['count'] ?? 0)) }}</div>
            <div class="adjust-stat-sub">Selected period</div>
        </div>

        <div class="adjust-stat">
            <div class="adjust-stat-label">Items</div>
            <div class="adjust-stat-value">{{ number_format((int) ($summary['items'] ?? 0)) }}</div>
            <div class="adjust-stat-sub">Adjustment lines</div>
        </div>

        <div class="adjust-stat">
            <div class="adjust-stat-label">Base Qty</div>
            <div class="adjust-stat-value">{{ number_format((int) ($summary['quantity'] ?? 0)) }}</div>
            <div class="adjust-stat-sub">Total base units</div>
        </div>

        <div class="adjust-stat">
            <div class="adjust-stat-label">Cost Impact</div>
            <div class="adjust-stat-value">{{ number_format((float) ($summary['cost'] ?? 0), 2) }}</div>
            <div class="adjust-stat-sub">All statuses</div>
        </div>

        <div class="adjust-stat">
            <div class="adjust-stat-label">Approved Impact</div>
            <div class="adjust-stat-value">{{ number_format((float) ($summary['approved_cost'] ?? 0), 2) }}</div>
            <div class="adjust-stat-sub">Approved only</div>
        </div>
    </div>

    <div class="card adjust-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('stock-adjustments.index') }}">
                <div class="row align-items-end">
                    @if($isAdminOrOwner)
                        <div class="col-lg-2 col-md-6 mb-3">
                            <label class="adjust-label">Branch</label>
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
                        <label class="adjust-label">Type</label>
                        <select name="adjustment_type" class="custom-select">
                            <option value="">All</option>
                            <option value="damage" {{ $type === 'damage' ? 'selected' : '' }}>Damage</option>
                            <option value="expiry" {{ $type === 'expiry' ? 'selected' : '' }}>Expiry</option>
                            <option value="physical_count" {{ $type === 'physical_count' ? 'selected' : '' }}>Physical Count</option>
                            <option value="loss" {{ $type === 'loss' ? 'selected' : '' }}>Loss</option>
                            <option value="found_stock" {{ $type === 'found_stock' ? 'selected' : '' }}>Found Stock</option>
                            <option value="correction" {{ $type === 'correction' ? 'selected' : '' }}>Correction</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="adjust-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All</option>
                            <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="adjust-label">From</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="adjust-label">To</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="adjust-label">Search</label>
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Adjustment no / reason">
                    </div>

                    <div class="col-lg-12 mb-1">
                        <div class="d-flex justify-content-end adjust-filter-actions" style="gap: 8px;">
                            <a href="{{ route('stock-adjustments.index') }}" class="btn btn-light">Reset</a>
                            <button class="btn btn-primary" type="submit">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card adjust-card">
        <div class="adjust-card-header">
            <div>
                <h5>Adjustment List</h5>
                <p>Draft adjustments do not change stock until approved by an authorized user.</p>
            </div>
        </div>

        <div class="adjust-table-wrap">
            <table class="table adjust-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Adjustment</th>
                        <th>Branch</th>
                        <th>Type</th>
                        <th>Items</th>
                        <th>Base Qty</th>
                        <th>Cost Impact</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($adjustments as $index => $adjustment)
                        @php
                            $statusClass = match ($adjustment->status) {
                                'approved' => 'badge-green',
                                'draft' => 'badge-blue',
                                'rejected' => 'badge-red',
                                'cancelled' => 'badge-gray',
                                default => 'badge-gray',
                            };

                            $typeClass = match ($adjustment->adjustment_type) {
                                'damage', 'expiry', 'loss' => 'badge-red',
                                'found_stock' => 'badge-green',
                                'physical_count' => 'badge-purple',
                                default => 'badge-yellow',
                            };
                        @endphp

                        <tr>
                            <td>{{ ($adjustments->firstItem() ?? 0) + $index }}</td>

                            <td>
                                <div class="adjust-main">{{ $adjustment->adjustment_no }}</div>
                                <div class="adjust-sub">{{ $adjustment->adjustment_date?->format('d M Y') }}</div>
                            </td>

                            <td>{{ $adjustment->branch?->name ?: '-' }}</td>

                            <td>
                                <span class="adjust-badge {{ $typeClass }}">
                                    {{ $adjustment->displayType() }}
                                </span>
                            </td>

                            <td>{{ number_format((int) $adjustment->total_items) }}</td>
                            <td>{{ number_format((int) $adjustment->total_quantity_base_units) }}</td>

                            <td>
                                <strong>{{ number_format((float) $adjustment->total_cost, 2) }}</strong>
                            </td>

                            <td>
                                <span class="adjust-badge {{ $statusClass }}">
                                    {{ ucfirst($adjustment->status) }}
                                </span>
                            </td>

                            <td>{{ $adjustment->creator?->displayName() ?: ($adjustment->creator?->username ?? '-') }}</td>

                            <td class="text-right">
                                <div class="adjust-btn-row">
                                    <a href="{{ route('stock-adjustments.show', $adjustment) }}" class="btn btn-sm btn-light">
                                        View
                                    </a>

                                    @if($adjustment->isDraft())
                                        @can('stock_adjustment.approve')
                                            <form method="POST" action="{{ route('stock-adjustments.approve', $adjustment) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')

                                                <button class="btn btn-sm btn-outline-success">
                                                    Approve
                                                </button>
                                            </form>
                                        @endcan

                                        @can('stock_adjustment.reject')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectAdjustmentModal{{ $adjustment->id }}">
                                                Reject
                                            </button>
                                        @endcan

                                        @can('stock_adjustment.cancel')
                                            <form method="POST" action="{{ route('stock-adjustments.cancel', $adjustment) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')

                                                <button class="btn btn-sm btn-outline-secondary">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="adjust-empty">No stock adjustments found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($adjustments->hasPages())
            <div class="p-3 border-top">
                {{ $adjustments->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@can('stock_adjustment.create')
    <div class="modal fade" id="createAdjustmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form method="POST" action="{{ route('stock-adjustments.store') }}" class="modal-content" id="stockAdjustmentForm">
                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">New Stock Adjustment</h5>
                        <small class="text-muted">Search inventory batch and add the quantity to adjust.</small>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Branch</label>
                            <select name="branch_id" id="adjustBranchId" class="custom-select select2-modal" required>
                                <option value="">Select branch</option>
                                @foreach($branches as $branch)
                                    @if($isAdminOrOwner || (int) $branch->id === (int) auth()->user()?->branch_id)
                                        <option value="{{ $branch->id }}" {{ ! $isAdminOrOwner && (int) $branch->id === (int) auth()->user()?->branch_id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Adjustment Date</label>
                            <input type="date"
                                   name="adjustment_date"
                                   class="form-control"
                                   value="{{ now()->toDateString() }}"
                                   required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Adjustment Type</label>
                            <select name="adjustment_type" id="adjustmentType" class="custom-select" required>
                                <option value="correction">Correction</option>
                                <option value="damage">Damage</option>
                                <option value="expiry">Expiry</option>
                                <option value="physical_count">Physical Count</option>
                                <option value="loss">Loss</option>
                                <option value="found_stock">Found Stock</option>
                            </select>
                        </div>
                    </div>

                    <div class="inventory-search-box">
                        <div class="row align-items-end">
                            <div class="col-md-8 mb-3 mb-md-0">
                                <label>Search Product / Batch / Barcode</label>
                                <input type="text"
                                       id="inventorySearchInput"
                                       class="form-control"
                                       placeholder="Example: Panadol, batch no, barcode">
                            </div>

                            <div class="col-md-4">
                                <button type="button" class="btn btn-primary w-100" id="searchInventoryBtn">
                                    <i class="mdi mdi-magnify mr-1"></i>
                                    Search Inventory
                                </button>
                            </div>
                        </div>

                        <div class="inventory-result-list" id="inventoryResults">
                            <div class="adjust-sub text-center">Search inventory to add adjustment item.</div>
                        </div>
                    </div>

                    <div class="adjust-box">
                        <div class="d-flex justify-content-between align-items-center mb-3" style="gap: 10px;">
                            <div>
                                <strong style="color:#0f172a;">Adjustment Items</strong>
                                <div class="adjust-sub">Stock changes are applied only after approval.</div>
                            </div>
                        </div>

                        <div class="adjust-items-wrap" id="adjustmentItemsWrap">
                            <div class="adjust-empty" style="padding: 24px;">No items selected.</div>
                        </div>
                    </div>

                    <div class="adjust-total-box">
                        <span>Total Cost Impact</span>
                        <strong id="adjustTotalCost">0.00</strong>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <label>Main Reason</label>
                            <textarea name="reason" rows="3" class="form-control" required placeholder="Why is this adjustment needed?"></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Notes</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Optional internal note"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer adjust-mobile-stack">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>

                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save-outline mr-1"></i>
                        Save Adjustment Request
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan

@foreach($adjustments as $adjustment)
    @if($adjustment->isDraft())
        @can('stock_adjustment.reject')
            <div class="modal fade" id="rejectAdjustmentModal{{ $adjustment->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <form method="POST" action="{{ route('stock-adjustments.reject', $adjustment) }}" class="modal-content">
                        @csrf
                        @method('PATCH')

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Reject Adjustment</h5>
                                <small class="text-muted">{{ $adjustment->adjustment_no }}</small>
                            </div>

                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <label>Rejection Reason</label>
                            <textarea name="rejection_reason"
                                      rows="4"
                                      class="form-control"
                                      required
                                      placeholder="Explain why this stock adjustment is rejected"></textarea>
                        </div>

                        <div class="modal-footer adjust-mobile-stack">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Reject Adjustment</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    @endif
@endforeach

@push('scripts')
<script>
    $(function () {
        $('.select2-clear').select2({
            width: '100%',
            allowClear: true,
            placeholder: 'Select option'
        });

        $('.modal').on('shown.bs.modal', function () {
            $(this).find('.select2-modal').select2({
                width: '100%',
                dropdownParent: $(this),
                allowClear: true,
                placeholder: 'Select option'
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const searchUrl = @json(route('stock-adjustments.search-inventory'));
        const csrf = @json(csrf_token());

        const branchSelect = document.getElementById('adjustBranchId');
        const searchInput = document.getElementById('inventorySearchInput');
        const searchBtn = document.getElementById('searchInventoryBtn');
        const resultsBox = document.getElementById('inventoryResults');
        const itemsWrap = document.getElementById('adjustmentItemsWrap');
        const totalCostText = document.getElementById('adjustTotalCost');
        const form = document.getElementById('stockAdjustmentForm');

        let selectedItems = [];

        function money(value) {
            return Number(value || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function escapeHtml(value) {
            return String(value || '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function calculateTotal() {
            let total = 0;

            selectedItems.forEach((item, index) => {
                const qty = Number(document.querySelector(`[data-adjust-qty="${index}"]`)?.value || 0);
                total += qty * Number(item.unit_cost_base || 0);
            });

            totalCostText.textContent = money(total);
        }

        function renderSelectedItems() {
            if (!selectedItems.length) {
                itemsWrap.innerHTML = `<div class="adjust-empty" style="padding: 24px;">No items selected.</div>`;
                calculateTotal();
                return;
            }

            itemsWrap.innerHTML = selectedItems.map((item, index) => {
                const name = escapeHtml(item.product_name);
                const batch = escapeHtml(item.batch_no);
                const unit = escapeHtml(item.base_unit);
                const max = Number(item.available_quantity_base_units || 0);

                return `
                    <div class="adjust-item-row">
                        <div class="adjust-item-head">
                            <div>
                                <div class="adjust-item-name">${name}</div>
                                <div class="adjust-item-meta">
                                    Batch: ${batch} • Available: ${max} ${unit} • Cost/Base: ${money(item.unit_cost_base)}
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-light" data-remove-adjust-item="${index}">
                                Remove
                            </button>
                        </div>

                        <input type="hidden" name="items[${index}][inventory_id]" value="${item.inventory_id}">

                        <div class="adjust-item-grid">
                            <div>
                                <label>Direction</label>
                                <select name="items[${index}][direction]" class="custom-select adjust-direction" data-direction-index="${index}" required>
                                    <option value="out">Reduce Stock</option>
                                    <option value="in">Increase Stock</option>
                                </select>
                            </div>

                            <div>
                                <label>Base Quantity</label>
                                <input type="number"
                                       name="items[${index}][quantity_base_units]"
                                       data-adjust-qty="${index}"
                                       class="form-control adjust-qty"
                                       min="1"
                                       max="${max}"
                                       step="1"
                                       value="1"
                                       required>
                            </div>

                            <div>
                                <label>Item Reason</label>
                                <input type="text"
                                       name="items[${index}][reason]"
                                       class="form-control"
                                       placeholder="Optional item reason">
                            </div>

                            <div>
                                <label>&nbsp;</label>
                                <div class="adjust-badge badge-blue w-100 justify-content-center">
                                    ${unit}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            document.querySelectorAll('.adjust-qty').forEach((input) => {
                input.addEventListener('input', calculateTotal);
            });

            document.querySelectorAll('.adjust-direction').forEach((select) => {
                select.addEventListener('change', function () {
                    const index = this.dataset.directionIndex;
                    const qtyInput = document.querySelector(`[data-adjust-qty="${index}"]`);
                    const item = selectedItems[index];

                    if (this.value === 'out') {
                        qtyInput.setAttribute('max', item.available_quantity_base_units);
                    } else {
                        qtyInput.removeAttribute('max');
                    }
                });
            });

            document.querySelectorAll('[data-remove-adjust-item]').forEach((button) => {
                button.addEventListener('click', function () {
                    selectedItems.splice(Number(this.dataset.removeAdjustItem), 1);
                    renderSelectedItems();
                });
            });

            calculateTotal();
        }

        function addItem(item) {
            if (selectedItems.some((selected) => Number(selected.inventory_id) === Number(item.inventory_id))) {
                return;
            }

            selectedItems.push(item);
            renderSelectedItems();
        }

        function renderSearchResults(items) {
            if (!items.length) {
                resultsBox.innerHTML = `<div class="adjust-sub text-center">No inventory found.</div>`;
                return;
            }

            resultsBox.innerHTML = items.map((item, index) => {
                const expiredBadge = item.is_expired
                    ? `<span class="adjust-badge badge-red">Expired</span>`
                    : `<span class="adjust-badge badge-green">${escapeHtml(item.status)}</span>`;

                return `
                    <div class="inventory-result-item">
                        <div>
                            <div class="inventory-result-name">${escapeHtml(item.product_name)}</div>
                            <div class="inventory-result-meta">
                                Batch: ${escapeHtml(item.batch_no)} • Available: ${item.available_quantity_base_units} ${escapeHtml(item.base_unit)}
                                • Expiry: ${escapeHtml(item.expiry_date || '-')} • Cost/Base: ${money(item.unit_cost_base)}
                            </div>
                            <div class="mt-2">${expiredBadge}</div>
                        </div>

                        <button type="button" class="btn btn-sm btn-primary" data-add-inventory="${index}">
                            Add
                        </button>
                    </div>
                `;
            }).join('');

            document.querySelectorAll('[data-add-inventory]').forEach((button) => {
                button.addEventListener('click', function () {
                    addItem(items[Number(this.dataset.addInventory)]);
                });
            });
        }

        searchBtn?.addEventListener('click', function () {
            const branchId = branchSelect.value;
            const q = searchInput.value.trim();

            if (!branchId) {
                alert('Please select branch first.');
                return;
            }

            searchBtn.disabled = true;
            searchBtn.innerHTML = 'Searching...';

            fetch(`${searchUrl}?branch_id=${encodeURIComponent(branchId)}&q=${encodeURIComponent(q)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
            })
                .then(async (response) => {
                    const data = await response.json();

                    if (!response.ok || !data.ok) {
                        throw new Error(data.message || 'Unable to search inventory.');
                    }

                    return data;
                })
                .then((data) => {
                    renderSearchResults(data.items || []);
                })
                .catch((error) => {
                    resultsBox.innerHTML = `<div class="text-danger text-center" style="font-weight:850;">${escapeHtml(error.message)}</div>`;
                })
                .finally(() => {
                    searchBtn.disabled = false;
                    searchBtn.innerHTML = '<i class="mdi mdi-magnify mr-1"></i> Search Inventory';
                });
        });

        form?.addEventListener('submit', function (event) {
            if (!selectedItems.length) {
                event.preventDefault();
                alert('Add at least one adjustment item.');
            }
        });
    });
</script>
@endpush
@endsection