@extends('components.main-layout')

@section('title', 'Sales Returns')
@section('page-title', 'Sales Returns')
@section('page-subtitle', 'Manage customer returns, refunds and inventory restoration')

@section('content')
<style>
    .returns-page { max-width: 100%; overflow-x: hidden; }

    .returns-hero,
    .returns-card,
    .returns-filter-card,
    .returns-stat {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
    }

    .returns-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .returns-hero .card-body { padding: 22px; }

    .returns-icon {
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

    .returns-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.025em;
    }

    .returns-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 750;
        margin-bottom: 0;
    }

    .returns-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .returns-actions .btn,
    .returns-filter-actions .btn,
    .returns-filter-actions a,
    .returns-btn-row .btn {
        border-radius: 13px;
        font-weight: 850;
        white-space: nowrap;
    }

    .returns-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .returns-stat {
        padding: 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .returns-stat-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 5px;
    }

    .returns-stat-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 24px;
        line-height: 1;
    }

    .returns-stat-sub {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 750;
        margin-top: 7px;
    }

    .returns-filter-card {
        margin-bottom: 16px;
        border: 1px solid #e8edf5;
    }

    .returns-filter-card .card-body { padding: 16px; }

    .returns-label,
    .modal-body label {
        font-size: 11px;
        font-weight: 950;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .returns-filter-card .form-control,
    .returns-filter-card .custom-select,
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

    .returns-card { overflow: hidden; }

    .returns-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }

    .returns-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .returns-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .returns-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .returns-table {
        min-width: 1120px;
        margin-bottom: 0;
    }

    .returns-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .returns-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .returns-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .returns-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 4px;
        font-weight: 700;
    }

    .returns-badge {
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

    .returns-btn-row {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .returns-empty {
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

    .return-sale-box,
    .return-item-box {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 18px;
        padding: 15px;
        margin-bottom: 14px;
    }

    .return-sale-box {
        display: none;
    }

    .return-sale-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 3px;
    }

    .return-sale-meta {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
    }

    .return-items-wrap {
        display: grid;
        gap: 12px;
    }

    .return-item-row {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #ffffff;
        padding: 14px;
    }

    .return-item-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .return-item-name {
        font-weight: 950;
        color: #0f172a;
    }

    .return-item-meta {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-top: 3px;
    }

    .return-item-grid {
        display: grid;
        grid-template-columns: 140px 160px 180px minmax(0, 1fr);
        gap: 10px;
        align-items: end;
    }

    .return-check {
        width: 20px;
        height: 20px;
    }

    .return-total-box {
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

    @media (max-width: 991.98px) {
        .returns-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .return-item-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .returns-hero .card-body { padding: 18px 16px; }

        .returns-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
        }

        .returns-actions .btn { width: 100%; }

        .returns-stat-grid {
            grid-template-columns: 1fr;
        }

        .returns-filter-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px !important;
        }

        .returns-filter-actions .btn,
        .returns-filter-actions a {
            width: 100%;
            text-align: center;
        }

        .return-item-grid {
            grid-template-columns: 1fr;
        }

        .modal-dialog { margin: .65rem; }

        .modal-body {
            padding: 15px;
            max-height: calc(100vh - 165px);
        }

        .returns-mobile-stack {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: 8px !important;
        }

        .returns-mobile-stack .btn {
            width: 100%;
        }
    }
</style>

<div class="container-fluid returns-page">
    <div class="card returns-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="returns-icon">
                        <i class="mdi mdi-backup-restore"></i>
                    </span>

                    <div>
                        <h4 class="returns-title">Sales Returns</h4>
                        <p class="returns-subtitle">
                            Search receipts, create return requests, approve refunds and restore sellable stock.
                        </p>
                    </div>
                </div>

                @can('sales_return.create')
                    <div class="returns-actions">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createReturnModal">
                            <i class="mdi mdi-plus-circle-outline mr-1"></i>
                            New Return
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

    <div class="returns-stat-grid">
        <div class="returns-stat">
            <div class="returns-stat-label">Returns</div>
            <div class="returns-stat-value">{{ number_format((int) ($summary['count'] ?? 0)) }}</div>
            <div class="returns-stat-sub">Selected period</div>
        </div>

        <div class="returns-stat">
            <div class="returns-stat-label">Refund Total</div>
            <div class="returns-stat-value">{{ number_format((float) ($summary['refund_total'] ?? 0), 2) }}</div>
            <div class="returns-stat-sub">All statuses</div>
        </div>

        <div class="returns-stat">
            <div class="returns-stat-label">Approved Refunds</div>
            <div class="returns-stat-value">{{ number_format((float) ($summary['approved_total'] ?? 0), 2) }}</div>
            <div class="returns-stat-sub">Finalized returns</div>
        </div>

        <div class="returns-stat">
            <div class="returns-stat-label">Draft Refunds</div>
            <div class="returns-stat-value">{{ number_format((float) ($summary['draft_total'] ?? 0), 2) }}</div>
            <div class="returns-stat-sub">Awaiting approval</div>
        </div>
    </div>

    <div class="card returns-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('sales-returns.index') }}">
                <div class="row align-items-end">
                    @if($isAdminOrOwner)
                        <div class="col-lg-2 col-md-6 mb-3">
                            <label class="returns-label">Branch</label>
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
                        <label class="returns-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All</option>
                            <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="returns-label">From</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="returns-label">To</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="returns-label">Search</label>
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Return no / receipt">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <div class="d-flex justify-content-end returns-filter-actions" style="gap: 8px;">
                            <a href="{{ route('sales-returns.index') }}" class="btn btn-light">Reset</a>
                            <button class="btn btn-primary" type="submit">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card returns-card">
        <div class="returns-card-header">
            <div>
                <h5>Return List</h5>
                <p>Draft returns need approval before stock and sale totals are updated.</p>
            </div>
        </div>

        <div class="returns-table-wrap">
            <table class="table returns-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Return</th>
                        <th>Sale</th>
                        <th>Branch</th>
                        <th>Items</th>
                        <th>Refund</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($returns as $index => $return)
                        @php
                            $statusClass = match ($return->status) {
                                'approved' => 'badge-green',
                                'draft' => 'badge-blue',
                                'rejected' => 'badge-red',
                                'cancelled' => 'badge-gray',
                                default => 'badge-gray',
                            };
                        @endphp

                        <tr>
                            <td>{{ ($returns->firstItem() ?? 0) + $index }}</td>

                            <td>
                                <div class="returns-main">{{ $return->return_no }}</div>
                                <div class="returns-sub">{{ $return->return_date?->format('d M Y') }}</div>
                            </td>

                            <td>
                                <div class="returns-main">{{ $return->sale?->sale_no ?: '-' }}</div>
                                <div class="returns-sub">{{ $return->sale?->displayCustomer() ?: 'Walk-in Customer' }}</div>
                            </td>

                            <td>{{ $return->branch?->name ?: '-' }}</td>
                            <td>{{ number_format((int) $return->items_count) }}</td>

                            <td>
                                <strong>{{ number_format((float) $return->refund_amount, 2) }}</strong>
                            </td>

                            <td>{{ str_replace('_', ' ', ucfirst($return->refund_method)) }}</td>

                            <td>
                                <span class="returns-badge {{ $statusClass }}">
                                    {{ ucfirst($return->status) }}
                                </span>
                            </td>

                            <td>{{ $return->creator?->displayName() ?: ($return->creator?->username ?? '-') }}</td>

                            <td class="text-right">
                                <div class="returns-btn-row">
                                    <a href="{{ route('sales-returns.show', $return) }}" class="btn btn-sm btn-light">
                                        View
                                    </a>

                                    @if($return->isDraft())
                                        @can('sales_return.approve')
                                            <form method="POST" action="{{ route('sales-returns.approve', $return) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-success">
                                                    Approve
                                                </button>
                                            </form>
                                        @endcan

                                        @can('sales_return.reject')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectReturnModal{{ $return->id }}">
                                                Reject
                                            </button>
                                        @endcan

                                        @can('sales_return.cancel')
                                            <form method="POST" action="{{ route('sales-returns.cancel', $return) }}" class="d-inline">
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
                                <div class="returns-empty">No sales returns found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div class="p-3 border-top">
                {{ $returns->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@can('sales_return.create')
    <div class="modal fade" id="createReturnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form method="POST" action="{{ route('sales-returns.store') }}" class="modal-content" id="salesReturnForm">
                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">New Sales Return</h5>
                        <small class="text-muted">Search receipt number and select items to return.</small>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="sale_id" id="returnSaleId">

                    <div class="return-item-box">
                        <div class="row align-items-end">
                            <div class="col-md-8 mb-3 mb-md-0">
                                <label>Receipt Number</label>
                                <input type="text"
                                       id="returnSaleNo"
                                       class="form-control"
                                       placeholder="Example: SAL-20260507-0001">
                            </div>

                            <div class="col-md-4">
                                <button type="button" class="btn btn-primary w-100" id="searchReturnSaleBtn">
                                    <i class="mdi mdi-magnify mr-1"></i>
                                    Search Sale
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="return-sale-box" id="returnSaleBox">
                        <div class="d-flex flex-column flex-md-row justify-content-between" style="gap: 12px;">
                            <div>
                                <div class="return-sale-title" id="returnSaleTitle">-</div>
                                <div class="return-sale-meta" id="returnSaleMeta">-</div>
                            </div>

                            <div class="text-md-right">
                                <div class="return-sale-title" id="returnSaleAmount">0.00</div>
                                <div class="return-sale-meta">Sale total</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Return Date</label>
                            <input type="date"
                                   name="return_date"
                                   class="form-control"
                                   value="{{ now()->toDateString() }}"
                                   required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Refund Method</label>
                            <select name="refund_method" class="custom-select" required>
                                <option value="cash">Cash</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="card">Card</option>
                                <option value="bank">Bank</option>
                                <option value="credit_note">Credit Note</option>
                                <option value="no_refund">No Refund</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Return Type</label>
                            <select name="return_type" class="custom-select" required>
                                <option value="customer_return">Customer Return</option>
                                <option value="correction">Correction</option>
                                <option value="damaged_return">Damaged Return</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Expected Refund</label>
                            <input type="text" id="expectedRefundText" class="form-control" value="0.00" readonly>
                        </div>
                    </div>

                    <div class="return-items-wrap" id="returnItemsWrap">
                        <div class="return-item-box text-center text-muted" style="font-weight: 750;">
                            Search sale to load returnable items.
                        </div>
                    </div>

                    <div class="return-total-box mt-3">
                        <span>Total Refund</span>
                        <strong id="returnGrandTotal">0.00</strong>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <label>Reason</label>
                            <textarea name="reason" rows="3" class="form-control" placeholder="Why is this item returned?"></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Notes</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Optional internal note"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer returns-mobile-stack">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>

                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save-outline mr-1"></i>
                        Save Return Request
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan

@foreach($returns as $return)
    @if($return->isDraft())
        @can('sales_return.reject')
            <div class="modal fade" id="rejectReturnModal{{ $return->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <form method="POST" action="{{ route('sales-returns.reject', $return) }}" class="modal-content">
                        @csrf
                        @method('PATCH')

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Reject Return</h5>
                                <small class="text-muted">{{ $return->return_no }}</small>
                            </div>

                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <label>Rejection Reason</label>
                            <textarea name="rejection_reason"
                                      rows="4"
                                      class="form-control"
                                      required
                                      placeholder="Explain why this return is rejected"></textarea>
                        </div>

                        <div class="modal-footer returns-mobile-stack">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Reject Return</button>
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
    });

    document.addEventListener('DOMContentLoaded', function () {
        const searchUrl = @json(route('sales-returns.search-sale'));
        const csrf = @json(csrf_token());

        const saleNoInput = document.getElementById('returnSaleNo');
        const saleIdInput = document.getElementById('returnSaleId');
        const searchBtn = document.getElementById('searchReturnSaleBtn');
        const saleBox = document.getElementById('returnSaleBox');
        const saleTitle = document.getElementById('returnSaleTitle');
        const saleMeta = document.getElementById('returnSaleMeta');
        const saleAmount = document.getElementById('returnSaleAmount');
        const itemsWrap = document.getElementById('returnItemsWrap');
        const grandTotal = document.getElementById('returnGrandTotal');
        const expectedRefundText = document.getElementById('expectedRefundText');

        let loadedItems = [];

        function money(value) {
            return Number(value || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function calculateTotal() {
            let total = 0;

            document.querySelectorAll('.return-line-check').forEach((check) => {
                if (!check.checked) return;

                const index = check.dataset.index;
                const qtyInput = document.querySelector(`[data-return-qty="${index}"]`);
                const item = loadedItems[index];

                const qty = Number(qtyInput?.value || 0);
                total += qty * Number(item.unit_price || 0);
            });

            grandTotal.textContent = money(total);
            expectedRefundText.value = money(total);
        }

        function renderItems(items) {
            loadedItems = items || [];

            if (!loadedItems.length) {
                itemsWrap.innerHTML = `<div class="return-item-box text-center text-muted" style="font-weight:750;">No returnable items found.</div>`;
                calculateTotal();
                return;
            }

            itemsWrap.innerHTML = loadedItems.map((item, index) => {
                const disabled = Number(item.available_quantity) <= 0 ? 'disabled' : '';

                return `
                    <div class="return-item-row">
                        <div class="return-item-head">
                            <div>
                                <div class="return-item-name">${item.product_name}</div>
                                <div class="return-item-meta">
                                    Unit: ${item.unit_name} • Sold: ${money(item.sold_quantity)} • Returned: ${money(item.returned_quantity)} • Available: ${money(item.available_quantity)}
                                </div>
                            </div>

                            <label style="display:flex; align-items:center; gap:8px; font-weight:900; color:#0f172a;">
                                <input type="checkbox" class="return-check return-line-check" data-index="${index}" ${disabled}>
                                Return
                            </label>
                        </div>

                        <input type="hidden" name="items[${index}][sale_item_id]" value="${item.sale_item_id}" ${disabled}>

                        <div class="return-item-grid">
                            <div>
                                <label>Quantity</label>
                                <input type="number"
                                       name="items[${index}][quantity]"
                                       data-return-qty="${index}"
                                       class="form-control return-line-qty"
                                       min="0.01"
                                       max="${item.available_quantity}"
                                       step="0.01"
                                       value="1"
                                       ${disabled}>
                            </div>

                            <div>
                                <label>Condition</label>
                                <select name="items[${index}][condition]" class="custom-select return-condition" data-condition-index="${index}" ${disabled}>
                                    <option value="sellable">Sellable</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="expired">Expired</option>
                                    <option value="opened">Opened</option>
                                </select>
                            </div>

                            <div>
                                <label>Restore Stock?</label>
                                <select name="items[${index}][restore_to_inventory]" class="custom-select return-restore" data-restore-index="${index}" ${disabled}>
                                    <option value="1">Yes, restore</option>
                                    <option value="0">No, do not restore</option>
                                </select>
                            </div>

                            <div>
                                <label>Item Reason</label>
                                <input type="text"
                                       name="items[${index}][reason]"
                                       class="form-control"
                                       placeholder="Optional reason"
                                       ${disabled}>
                            </div>
                        </div>

                        <div class="returns-sub mt-2">
                            Unit price: ${money(item.unit_price)} • Refund line updates automatically
                        </div>
                    </div>
                `;
            }).join('');

            document.querySelectorAll('.return-line-check, .return-line-qty').forEach((el) => {
                el.addEventListener('input', calculateTotal);
                el.addEventListener('change', calculateTotal);
            });

            document.querySelectorAll('.return-condition').forEach((select) => {
                select.addEventListener('change', function () {
                    const index = this.dataset.conditionIndex;
                    const restore = document.querySelector(`[data-restore-index="${index}"]`);

                    if (this.value !== 'sellable') {
                        restore.value = '0';
                        restore.setAttribute('disabled', 'disabled');
                    } else {
                        restore.removeAttribute('disabled');
                    }
                });
            });

            calculateTotal();
        }

        searchBtn?.addEventListener('click', function () {
            const saleNo = saleNoInput.value.trim();

            if (!saleNo) {
                alert('Enter receipt number first.');
                return;
            }

            searchBtn.disabled = true;
            searchBtn.innerHTML = 'Searching...';

            fetch(`${searchUrl}?sale_no=${encodeURIComponent(saleNo)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
            })
                .then(async (response) => {
                    const data = await response.json();

                    if (!response.ok || !data.ok) {
                        throw new Error(data.message || 'Sale not found.');
                    }

                    return data;
                })
                .then((data) => {
                    saleIdInput.value = data.sale.id;
                    saleBox.style.display = 'block';

                    saleTitle.textContent = `${data.sale.sale_no} • ${data.sale.customer}`;
                    saleMeta.textContent = `${data.sale.branch} • ${data.sale.sold_at} • Status: ${data.sale.status}`;
                    saleAmount.textContent = money(data.sale.total_amount);

                    renderItems(data.items);
                })
                .catch((error) => {
                    saleIdInput.value = '';
                    saleBox.style.display = 'none';
                    itemsWrap.innerHTML = `<div class="return-item-box text-center text-danger" style="font-weight:850;">${error.message}</div>`;
                    calculateTotal();
                })
                .finally(() => {
                    searchBtn.disabled = false;
                    searchBtn.innerHTML = '<i class="mdi mdi-magnify mr-1"></i> Search Sale';
                });
        });

        document.getElementById('salesReturnForm')?.addEventListener('submit', function (event) {
            const selected = document.querySelectorAll('.return-line-check:checked');

            if (!saleIdInput.value) {
                event.preventDefault();
                alert('Search and select a sale first.');
                return;
            }

            if (!selected.length) {
                event.preventDefault();
                alert('Select at least one item to return.');
                return;
            }

            document.querySelectorAll('.return-line-check').forEach((check) => {
                const index = check.dataset.index;
                const inputs = document.querySelectorAll(`[name^="items[${index}]"]`);

                inputs.forEach((input) => {
                    if (!check.checked) {
                        input.setAttribute('disabled', 'disabled');
                    } else {
                        input.removeAttribute('disabled');
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection