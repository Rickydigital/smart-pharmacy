@extends('components.main-layout')

@section('title', 'Stock Transfers')
@section('page-title', 'Stock Transfers')
@section('page-subtitle', 'Move stock between branches using product units and automatic base-unit calculation')

@section('content')
<style>
    .transfer-page { max-width: 100%; overflow-x: hidden; }

    .transfer-hero,
    .transfer-card,
    .transfer-filter-card,
    .transfer-stat {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
    }

    .transfer-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .transfer-hero .card-body { padding: 22px; }

    .transfer-icon {
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

    .transfer-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.025em;
    }

    .transfer-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 750;
        margin-bottom: 0;
    }

    .transfer-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .transfer-actions .btn,
    .transfer-filter-actions .btn,
    .transfer-filter-actions a,
    .transfer-btn-row .btn {
        border-radius: 13px;
        font-weight: 850;
        white-space: nowrap;
    }

    .transfer-stat-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .transfer-stat {
        padding: 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .transfer-stat-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 5px;
    }

    .transfer-stat-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 23px;
        line-height: 1;
    }

    .transfer-stat-sub {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 750;
        margin-top: 7px;
    }

    .transfer-filter-card {
        margin-bottom: 16px;
        border: 1px solid #e8edf5;
    }

    .transfer-filter-card .card-body { padding: 16px; }

    .transfer-label,
    .modal-body label {
        font-size: 11px;
        font-weight: 950;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .transfer-filter-card .form-control,
    .transfer-filter-card .custom-select,
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

    .transfer-card { overflow: hidden; }

    .transfer-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }

    .transfer-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .transfer-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .transfer-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .transfer-table {
        min-width: 1180px;
        margin-bottom: 0;
    }

    .transfer-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .transfer-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .transfer-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .transfer-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 4px;
        font-weight: 700;
    }

    .transfer-badge {
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

    .transfer-btn-row {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .transfer-empty {
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

    .transfer-box,
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

    .transfer-items-wrap {
        display: grid;
        gap: 12px;
    }

    .transfer-item-row {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #ffffff;
        padding: 14px;
    }

    .transfer-item-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .transfer-item-name {
        font-weight: 950;
        color: #0f172a;
    }

    .transfer-item-meta {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-top: 3px;
    }

    .transfer-item-grid {
        display: grid;
        grid-template-columns: 170px 150px 150px minmax(0, 1fr) 80px;
        gap: 10px;
        align-items: end;
    }

    .transfer-total-box {
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
        .transfer-stat-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .transfer-item-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .transfer-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .transfer-hero .card-body { padding: 18px 16px; }

        .transfer-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
        }

        .transfer-actions .btn { width: 100%; }

        .transfer-stat-grid {
            grid-template-columns: 1fr;
        }

        .transfer-filter-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px !important;
        }

        .transfer-filter-actions .btn,
        .transfer-filter-actions a {
            width: 100%;
            text-align: center;
        }

        .transfer-item-grid {
            grid-template-columns: 1fr;
        }

        .modal-dialog { margin: .65rem; }

        .modal-body {
            padding: 15px;
            max-height: calc(100vh - 165px);
        }

        .transfer-mobile-stack {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: 8px !important;
        }

        .transfer-mobile-stack .btn {
            width: 100%;
        }
    }
</style>

<div class="container-fluid transfer-page">
    <div class="card transfer-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="transfer-icon">
                        <i class="mdi mdi-swap-horizontal-bold"></i>
                    </span>

                    <div>
                        <h4 class="transfer-title">Stock Transfers</h4>
                        <p class="transfer-subtitle">
                            Transfer stock from one branch to another using units like box, strip or pill.
                        </p>
                    </div>
                </div>

                @can('stock_transfer.create')
                    <div class="transfer-actions">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTransferModal">
                            <i class="mdi mdi-plus-circle-outline mr-1"></i>
                            New Transfer
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

    <div class="transfer-stat-grid">
        <div class="transfer-stat">
            <div class="transfer-stat-label">Transfers</div>
            <div class="transfer-stat-value">{{ number_format((int) ($summary['count'] ?? 0)) }}</div>
            <div class="transfer-stat-sub">Selected period</div>
        </div>

        <div class="transfer-stat">
            <div class="transfer-stat-label">Received</div>
            <div class="transfer-stat-value">{{ number_format((int) ($summary['received'] ?? 0)) }}</div>
            <div class="transfer-stat-sub">Completed transfers</div>
        </div>

        <div class="transfer-stat">
            <div class="transfer-stat-label">Items</div>
            <div class="transfer-stat-value">{{ number_format((int) ($summary['items'] ?? 0)) }}</div>
            <div class="transfer-stat-sub">Transfer lines</div>
        </div>

        <div class="transfer-stat">
            <div class="transfer-stat-label">Base Qty</div>
            <div class="transfer-stat-value">{{ number_format((int) ($summary['quantity'] ?? 0)) }}</div>
            <div class="transfer-stat-sub">Total base units</div>
        </div>

        <div class="transfer-stat">
            <div class="transfer-stat-label">Cost Value</div>
            <div class="transfer-stat-value">{{ number_format((float) ($summary['cost'] ?? 0), 2) }}</div>
            <div class="transfer-stat-sub">Inventory cost moved</div>
        </div>
    </div>

    <div class="card transfer-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('stock-transfers.index') }}">
                <div class="row align-items-end">
                    @if($isAdminOrOwner)
                        <div class="col-lg-2 col-md-6 mb-3">
                            <label class="transfer-label">Source</label>
                            <select name="source_branch_id" class="custom-select select2-clear">
                                <option value="">All sources</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (string) $sourceBranchId === (string) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="transfer-label">Destination</label>
                        <select name="destination_branch_id" class="custom-select select2-clear">
                            <option value="">All destinations</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) $destinationBranchId === (string) $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="transfer-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All</option>
                            <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="dispatched" {{ $status === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                            <option value="received" {{ $status === 'received' ? 'selected' : '' }}>Received</option>
                            <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="transfer-label">From</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="transfer-label">To</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="transfer-label">Search</label>
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Transfer no / reason">
                    </div>

                    <div class="col-lg-12 mb-1">
                        <div class="d-flex justify-content-end transfer-filter-actions" style="gap: 8px;">
                            <a href="{{ route('stock-transfers.index') }}" class="btn btn-light">Reset</a>
                            <button class="btn btn-primary" type="submit">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card transfer-card">
        <div class="transfer-card-header">
            <div>
                <h5>Transfer List</h5>
                <p>Draft → Approved → Dispatched → Received. Source stock reduces on dispatch, destination stock increases on receive.</p>
            </div>
        </div>

        <div class="transfer-table-wrap">
            <table class="table transfer-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Transfer</th>
                        <th>Source</th>
                        <th>Destination</th>
                        <th>Items</th>
                        <th>Base Qty</th>
                        <th>Cost Value</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($transfers as $index => $transfer)
                        @php
                            $statusClass = match ($transfer->status) {
                                'received' => 'badge-green',
                                'dispatched' => 'badge-purple',
                                'approved' => 'badge-yellow',
                                'draft' => 'badge-blue',
                                'rejected' => 'badge-red',
                                'cancelled' => 'badge-gray',
                                default => 'badge-gray',
                            };
                        @endphp

                        <tr>
                            <td>{{ ($transfers->firstItem() ?? 0) + $index }}</td>

                            <td>
                                <div class="transfer-main">{{ $transfer->transfer_no }}</div>
                                <div class="transfer-sub">{{ $transfer->transfer_date?->format('d M Y') }}</div>
                            </td>

                            <td>{{ $transfer->sourceBranch?->name ?: '-' }}</td>
                            <td>{{ $transfer->destinationBranch?->name ?: '-' }}</td>
                            <td>{{ number_format((int) $transfer->total_items) }}</td>
                            <td>{{ number_format((int) $transfer->total_quantity_base_units) }}</td>

                            <td>
                                <strong>{{ number_format((float) $transfer->total_cost, 2) }}</strong>
                            </td>

                            <td>
                                <span class="transfer-badge {{ $statusClass }}">
                                    {{ ucfirst($transfer->status) }}
                                </span>
                            </td>

                            <td>{{ $transfer->creator?->displayName() ?: ($transfer->creator?->username ?? '-') }}</td>

                            <td class="text-right">
                                <div class="transfer-btn-row">
                                    <a href="{{ route('stock-transfers.show', $transfer) }}" class="btn btn-sm btn-light">
                                        View
                                    </a>

                                    @if($transfer->isDraft())
                                        @can('stock_transfer.approve')
                                            <form method="POST" action="{{ route('stock-transfers.approve', $transfer) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-success">Approve</button>
                                            </form>
                                        @endcan

                                        @can('stock_transfer.reject')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectTransferModal{{ $transfer->id }}">
                                                Reject
                                            </button>
                                        @endcan

                                        @can('stock_transfer.cancel')
                                            <form method="POST" action="{{ route('stock-transfers.cancel', $transfer) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-secondary">Cancel</button>
                                            </form>
                                        @endcan
                                    @endif

                                    @if($transfer->isApproved())
                                        @can('stock_transfer.dispatch')
                                            <form method="POST" action="{{ route('stock-transfers.dispatch', $transfer) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">Dispatch</button>
                                            </form>
                                        @endcan
                                    @endif

                                    @if($transfer->isDispatched())
                                        @can('stock_transfer.receive')
                                            <form method="POST" action="{{ route('stock-transfers.receive', $transfer) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-primary">Receive</button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="transfer-empty">No stock transfers found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
            <div class="p-3 border-top">
                {{ $transfers->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@can('stock_transfer.create')
    <div class="modal fade" id="createTransferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form method="POST" action="{{ route('stock-transfers.store') }}" class="modal-content" id="stockTransferForm">
                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">New Stock Transfer</h5>
                        <small class="text-muted">Choose product unit and quantity. Base units calculate automatically.</small>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Source Branch</label>
                            <select name="source_branch_id" id="transferSourceBranchId" class="custom-select select2-modal" required>
                                <option value="">Select source</option>
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
                            <label>Destination Branch</label>
                            <select name="destination_branch_id" id="transferDestinationBranchId" class="custom-select select2-modal" required>
                                <option value="">Select destination</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Transfer Date</label>
                            <input type="date"
                                   name="transfer_date"
                                   class="form-control"
                                   value="{{ now()->toDateString() }}"
                                   required>
                        </div>
                    </div>

                    <div class="inventory-search-box">
                        <div class="row align-items-end">
                            <div class="col-md-8 mb-3 mb-md-0">
                                <label>Search Product / Batch / Barcode</label>
                                <input type="text"
                                       id="transferInventorySearchInput"
                                       class="form-control"
                                       placeholder="Example: Panadol, batch no, barcode">
                            </div>

                            <div class="col-md-4">
                                <button type="button" class="btn btn-primary w-100" id="searchTransferInventoryBtn">
                                    <i class="mdi mdi-magnify mr-1"></i>
                                    Search Inventory
                                </button>
                            </div>
                        </div>

                        <div class="inventory-result-list" id="transferInventoryResults">
                            <div class="transfer-sub text-center">Select source branch, then search inventory.</div>
                        </div>
                    </div>

                    <div class="transfer-box">
                        <div class="d-flex justify-content-between align-items-center mb-3" style="gap: 10px;">
                            <div>
                                <strong style="color:#0f172a;">Transfer Items</strong>
                                <div class="transfer-sub">Select unit like box/strip/pill and enter quantity.</div>
                            </div>
                        </div>

                        <div class="transfer-items-wrap" id="transferItemsWrap">
                            <div class="transfer-empty" style="padding: 24px;">No items selected.</div>
                        </div>
                    </div>

                    <div class="transfer-total-box">
                        <span>Total Cost Value</span>
                        <strong id="transferTotalCost">0.00</strong>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <label>Reason</label>
                            <textarea name="reason" rows="3" class="form-control" required placeholder="Why is this transfer needed?"></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Notes</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Optional internal note"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer transfer-mobile-stack">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>

                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save-outline mr-1"></i>
                        Save Transfer Request
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan

@foreach($transfers as $transfer)
    @if($transfer->isDraft())
        @can('stock_transfer.reject')
            <div class="modal fade" id="rejectTransferModal{{ $transfer->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <form method="POST" action="{{ route('stock-transfers.reject', $transfer) }}" class="modal-content">
                        @csrf
                        @method('PATCH')

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Reject Transfer</h5>
                                <small class="text-muted">{{ $transfer->transfer_no }}</small>
                            </div>

                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <label>Rejection Reason</label>
                            <textarea name="rejection_reason"
                                      rows="4"
                                      class="form-control"
                                      required
                                      placeholder="Explain why this stock transfer is rejected"></textarea>
                        </div>

                        <div class="modal-footer transfer-mobile-stack">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Reject Transfer</button>
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
        const searchUrl = @json(route('stock-transfers.search-inventory'));
        const csrf = @json(csrf_token());

        const sourceBranchSelect = document.getElementById('transferSourceBranchId');
        const destinationBranchSelect = document.getElementById('transferDestinationBranchId');
        const searchInput = document.getElementById('transferInventorySearchInput');
        const searchBtn = document.getElementById('searchTransferInventoryBtn');
        const resultsBox = document.getElementById('transferInventoryResults');
        const itemsWrap = document.getElementById('transferItemsWrap');
        const totalCostText = document.getElementById('transferTotalCost');
        const form = document.getElementById('stockTransferForm');

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

        function selectedUnit(item, index) {
            const select = document.querySelector(`[data-transfer-unit="${index}"]`);
            const unitId = select?.value || '';

            const units = item.product_units || [];
            return units.find((unit) => String(unit.id ?? '') === String(unitId))
                || units[0]
                || { id: '', unit_name: item.base_unit || 'Base unit', quantity_in_base_units: 1 };
        }

        function calculateRow(index) {
            const item = selectedItems[index];

            if (!item) {
                return;
            }

            const unit = selectedUnit(item, index);
            const qty = Number(document.querySelector(`[data-transfer-qty="${index}"]`)?.value || 0);
            const baseUnits = Math.round(qty * Number(unit.quantity_in_base_units || 1));
            const lineCost = baseUnits * Number(item.unit_cost_base || 0);

            const baseText = document.querySelector(`[data-transfer-base="${index}"]`);
            const costText = document.querySelector(`[data-transfer-cost="${index}"]`);

            if (baseText) {
                baseText.textContent = `${baseUnits.toLocaleString()} ${item.base_unit || 'base units'}`;
            }

            if (costText) {
                costText.textContent = money(lineCost);
            }

            return { baseUnits, lineCost };
        }

        function calculateTotal() {
            let total = 0;

            selectedItems.forEach((item, index) => {
                const row = calculateRow(index);
                total += Number(row?.lineCost || 0);
            });

            totalCostText.textContent = money(total);
        }

        function unitOptions(item) {
            const units = item.product_units && item.product_units.length
                ? item.product_units
                : [{ id: '', unit_name: item.base_unit || 'Base unit', quantity_in_base_units: 1 }];

            return units.map((unit) => {
                const id = unit.id ?? '';
                const label = `${unit.unit_name} (${Number(unit.quantity_in_base_units || 1)} base)`;
                return `<option value="${escapeHtml(id)}" data-base="${Number(unit.quantity_in_base_units || 1)}">${escapeHtml(label)}</option>`;
            }).join('');
        }

        function renderSelectedItems() {
            if (!selectedItems.length) {
                itemsWrap.innerHTML = `<div class="transfer-empty" style="padding: 24px;">No items selected.</div>`;
                calculateTotal();
                return;
            }

            itemsWrap.innerHTML = selectedItems.map((item, index) => {
                const name = escapeHtml(item.product_name);
                const batch = escapeHtml(item.batch_no);
                const unit = escapeHtml(item.base_unit);
                const max = Number(item.available_quantity_base_units || 0);

                return `
                    <div class="transfer-item-row">
                        <div class="transfer-item-head">
                            <div>
                                <div class="transfer-item-name">${name}</div>
                                <div class="transfer-item-meta">
                                    Batch: ${batch} • Available: ${max.toLocaleString()} ${unit} • Cost/Base: ${money(item.unit_cost_base)}
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-light" data-remove-transfer-item="${index}">
                                Remove
                            </button>
                        </div>

                        <input type="hidden" name="items[${index}][source_inventory_id]" value="${item.inventory_id}">

                        <div class="transfer-item-grid">
                            <div>
                                <label>Unit</label>
                                <select name="items[${index}][product_unit_id]"
                                        class="custom-select transfer-unit"
                                        data-transfer-unit="${index}">
                                    ${unitOptions(item)}
                                </select>
                            </div>

                            <div>
                                <label>Quantity</label>
                                <input type="number"
                                       name="items[${index}][quantity]"
                                       data-transfer-qty="${index}"
                                       class="form-control transfer-qty"
                                       min="0.01"
                                       step="0.01"
                                       value="1"
                                       required>
                            </div>

                            <div>
                                <label>Base Units</label>
                                <div class="transfer-badge badge-blue w-100 justify-content-center" data-transfer-base="${index}">
                                    0 ${unit}
                                </div>
                            </div>

                            <div>
                                <label>Line Cost</label>
                                <div class="form-control bg-light" style="line-height: 30px; font-weight:900;" data-transfer-cost="${index}">
                                    0.00
                                </div>
                            </div>

                            <div>
                                <label>&nbsp;</label>
                                <div class="transfer-badge badge-gray w-100 justify-content-center">
                                    ${unit}
                                </div>
                            </div>
                        </div>

                        <div class="transfer-sub mt-2">
                            System validates against available base quantity before saving.
                        </div>
                    </div>
                `;
            }).join('');

            document.querySelectorAll('.transfer-unit, .transfer-qty').forEach((input) => {
                input.addEventListener('input', calculateTotal);
                input.addEventListener('change', calculateTotal);
            });

            document.querySelectorAll('[data-remove-transfer-item]').forEach((button) => {
                button.addEventListener('click', function () {
                    selectedItems.splice(Number(this.dataset.removeTransferItem), 1);
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
                resultsBox.innerHTML = `<div class="transfer-sub text-center">No available inventory found.</div>`;
                return;
            }

            resultsBox.innerHTML = items.map((item, index) => {
                const unitList = (item.product_units || [])
                    .map((unit) => `${escapeHtml(unit.unit_name)}=${Number(unit.quantity_in_base_units || 1)} base`)
                    .join(' • ');

                return `
                    <div class="inventory-result-item">
                        <div>
                            <div class="inventory-result-name">${escapeHtml(item.product_name)}</div>
                            <div class="inventory-result-meta">
                                Batch: ${escapeHtml(item.batch_no)} • Available: ${item.available_quantity_base_units} ${escapeHtml(item.base_unit)}
                                • Expiry: ${escapeHtml(item.expiry_date || '-')} • Cost/Base: ${money(item.unit_cost_base)}
                            </div>
                            <div class="inventory-result-meta">
                                Units: ${unitList || escapeHtml(item.base_unit)}
                            </div>
                            <div class="mt-2">
                                <span class="transfer-badge badge-green">${escapeHtml(item.status)}</span>
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-primary" data-add-transfer-inventory="${index}">
                            Add
                        </button>
                    </div>
                `;
            }).join('');

            document.querySelectorAll('[data-add-transfer-inventory]').forEach((button) => {
                button.addEventListener('click', function () {
                    addItem(items[Number(this.dataset.addTransferInventory)]);
                });
            });
        }

        searchBtn?.addEventListener('click', function () {
            const sourceBranchId = sourceBranchSelect.value;
            const q = searchInput.value.trim();

            if (!sourceBranchId) {
                alert('Please select source branch first.');
                return;
            }

            searchBtn.disabled = true;
            searchBtn.innerHTML = 'Searching...';

            fetch(`${searchUrl}?source_branch_id=${encodeURIComponent(sourceBranchId)}&q=${encodeURIComponent(q)}`, {
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

        sourceBranchSelect?.addEventListener('change', function () {
            selectedItems = [];
            renderSelectedItems();
            resultsBox.innerHTML = `<div class="transfer-sub text-center">Search inventory from selected source branch.</div>`;
        });

        form?.addEventListener('submit', function (event) {
            if (!sourceBranchSelect.value) {
                event.preventDefault();
                alert('Select source branch.');
                return;
            }

            if (!destinationBranchSelect.value) {
                event.preventDefault();
                alert('Select destination branch.');
                return;
            }

            if (String(sourceBranchSelect.value) === String(destinationBranchSelect.value)) {
                event.preventDefault();
                alert('Source and destination branches cannot be the same.');
                return;
            }

            if (!selectedItems.length) {
                event.preventDefault();
                alert('Add at least one transfer item.');
                return;
            }

            for (let index = 0; index < selectedItems.length; index++) {
                const item = selectedItems[index];
                const row = calculateRow(index);

                if (!row || row.baseUnits <= 0) {
                    event.preventDefault();
                    alert('Invalid transfer quantity.');
                    return;
                }

                if (row.baseUnits > Number(item.available_quantity_base_units || 0)) {
                    event.preventDefault();
                    alert(`${item.product_name} quantity is greater than available stock.`);
                    return;
                }
            }
        });
    });
</script>
@endpush
@endsection