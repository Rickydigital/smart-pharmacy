@extends('components.main-layout')

@section('title', 'Sales History')
@section('page-title', 'Sales History')
@section('page-subtitle', 'Review sales, reprint receipts, and track sales performance')

@section('content')
<style>
    .sales-page { max-width: 100%; overflow-x: hidden; }

    .sales-hero,
    .sales-card,
    .sales-filter-card,
    .sales-stat {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .07);
    }

    .sales-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .sales-hero .card-body { padding: 22px; }

    .sales-icon {
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

    .sales-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.02em;
    }

    .sales-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 0;
    }

    .sales-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .sales-actions .btn,
    .sales-filter-actions .btn,
    .sales-filter-actions a,
    .sales-btn-row .btn {
        border-radius: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .sales-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .sales-stat {
        padding: 14px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .sales-stat-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 4px;
    }

    .sales-stat-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 22px;
        line-height: 1;
    }

    .sales-filter-card {
        margin-bottom: 16px;
        border: 1px solid #e8edf5;
    }

    .sales-filter-card .card-body { padding: 16px; }

    .sales-label,
    .modal-body label {
        font-size: 11px;
        font-weight: 950;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .sales-filter-card .form-control,
    .sales-filter-card .custom-select,
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

    .sales-card { overflow: hidden; }

    .sales-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .sales-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .sales-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .sales-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .sales-table {
        min-width: 1120px;
        margin-bottom: 0;
    }

    .sales-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .sales-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .sales-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .sales-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 3px;
        font-weight: 700;
    }

    .sales-badge {
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

    .sales-btn-row {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .sales-empty {
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

    .sales-modal-close {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background-color: #ffffff;
        opacity: 1;
        box-shadow: none;
    }

    .sales-modal-close:hover {
        background-color: #eff6ff;
        opacity: 1;
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

    .receipt-preview-box {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        padding: 14px;
        overflow-x: auto;
    }

    @media (max-width: 767.98px) {
        .sales-hero .card-body { padding: 18px 16px; }

        .sales-hero-main { align-items: flex-start !important; }

        .sales-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
        }

        .sales-actions .btn { width: 100%; }

        .sales-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .sales-filter-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px !important;
        }

        .sales-filter-actions .btn,
        .sales-filter-actions a {
            width: 100%;
            text-align: center;
        }

        .sales-table { min-width: 980px; }

        .sales-table th,
        .sales-table td {
            padding: 10px;
            font-size: 12px;
        }

        .sales-btn-row .btn {
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

        .sales-mobile-stack {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: 8px !important;
        }

        .sales-mobile-stack .btn {
            width: 100%;
        }
    }
</style>

<div class="container-fluid sales-page">
    <div class="card sales-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between sales-hero-main" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="sales-icon">
                        <i class="mdi mdi-receipt-text-check-outline"></i>
                    </span>

                    <div>
                        <h4 class="sales-title">Sales History</h4>
                        <p class="sales-subtitle">
                            Review sales, reprint receipts, and track sales performance.
                        </p>
                    </div>
                </div>

                <div class="sales-actions">
                    @can('pos.use')
                        @if(Route::has('pos.index'))
                            <a href="{{ route('pos.index') }}" class="btn btn-primary">
                                <i class="mdi mdi-cash-register mr-1"></i>
                                Open POS
                            </a>
                        @endif
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

    <div class="sales-stat-grid">
        <div class="sales-stat">
            <div class="sales-stat-label">Sales Count</div>
            <div class="sales-stat-value">{{ number_format($summary['sales_count'] ?? 0) }}</div>
        </div>

        <div class="sales-stat">
            <div class="sales-stat-label">Total Sales</div>
            <div class="sales-stat-value">{{ number_format((float) ($summary['total_sales'] ?? 0), 2) }}</div>
        </div>

        <div class="sales-stat">
            <div class="sales-stat-label">Paid Amount</div>
            <div class="sales-stat-value">{{ number_format((float) ($summary['total_paid'] ?? 0), 2) }}</div>
        </div>

        <div class="sales-stat">
            <div class="sales-stat-label">Profit</div>
            <div class="sales-stat-value">{{ number_format((float) ($summary['total_profit'] ?? 0), 2) }}</div>
        </div>
    </div>

    <div class="card sales-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('sales.index') }}">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="sales-label">Search</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control"
                               placeholder="Receipt no, customer or phone">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="sales-label">Branch</label>
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
                        <label class="sales-label">Sale Type</label>
                        <select name="sale_type" class="custom-select">
                            <option value="">All</option>
                            <option value="retail" {{ request('sale_type') === 'retail' ? 'selected' : '' }}>Retail</option>
                            <option value="wholesale" {{ request('sale_type') === 'wholesale' ? 'selected' : '' }}>Wholesale</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="sales-label">Payment</label>
                        <select name="payment_method" class="custom-select">
                            <option value="">All</option>
                            <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="mobile_money" {{ request('payment_method') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                            <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                            <option value="bank" {{ request('payment_method') === 'bank' ? 'selected' : '' }}>Bank</option>
                            <option value="credit" {{ request('payment_method') === 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                    </div>

                    <div class="col-lg-1 col-md-6 mb-3">
                        <label class="sales-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                        </select>
                    </div>

                    <div class="col-lg-1 col-md-6 mb-3">
                        <label class="sales-label">From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>

                    <div class="col-lg-1 col-md-6 mb-3">
                        <label class="sales-label">To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-end sales-filter-actions" style="gap: 8px;">
                            @if(request()->hasAny(['search', 'branch_id', 'sale_type', 'payment_method', 'payment_status', 'status', 'date_from', 'date_to']))
                                <a href="{{ route('sales.index') }}" class="btn btn-light">
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

    <div class="card sales-card">
        <div class="sales-card-header">
            <h5>Sales List</h5>
            <p>Use view for item breakdown and receipt for reprint.</p>
        </div>

        <div class="sales-table-wrap">
            <table class="table sales-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Receipt</th>
                        <th>Customer</th>
                        <th>Branch</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Cashier</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($sales as $index => $sale)
                        @php
                            $statusClass = match ($sale->status) {
                                'completed' => 'badge-green',
                                'cancelled' => 'badge-red',
                                'returned' => 'badge-yellow',
                                default => 'badge-gray',
                            };

                            $paymentClass = match ($sale->payment_status) {
                                'paid' => 'badge-green',
                                'partial' => 'badge-yellow',
                                'unpaid' => 'badge-red',
                                default => 'badge-gray',
                            };
                        @endphp

                        <tr>
                            <td>{{ ($sales->firstItem() ?? 0) + $index }}</td>

                            <td>
                                <div class="sales-main">{{ $sale->sale_no }}</div>
                                <div class="sales-sub">{{ ucfirst($sale->sale_type) }}</div>
                            </td>

                            <td>
                                <div>{{ $sale->displayCustomer() }}</div>
                                <div class="sales-sub">{{ $sale->customer_phone ?: '-' }}</div>
                            </td>

                            <td>{{ $sale->branch?->name ?: '-' }}</td>

                            <td>
                                <span class="sales-badge badge-blue">
                                    {{ $sale->items_count }} items
                                </span>
                            </td>

                            <td>
                                <strong>{{ number_format((float) $sale->total_amount, 2) }}</strong>
                            </td>

                            <td>
                                <span class="sales-badge {{ $paymentClass }}">
                                    {{ ucfirst($sale->payment_status) }}
                                </span>
                                <div class="sales-sub">
                                    {{ str_replace('_', ' ', ucfirst($sale->payment_method)) }}
                                </div>
                            </td>

                            <td>{{ $sale->creator?->displayName() ?? $sale->creator?->name ?? $sale->creator?->username ?? '-' }}</td>

                            <td>
                                <span class="sales-badge {{ $statusClass }}">
                                    {{ ucfirst($sale->status) }}
                                </span>
                            </td>

                            <td>
                                {{ $sale->sold_at?->format('d M Y') }}
                                <div class="sales-sub">{{ $sale->sold_at?->format('h:i A') }}</div>
                            </td>

                            <td class="text-right">
                                <div class="sales-btn-row">
                                    <button type="button"
                                            class="btn btn-sm btn-light js-view-sale"
                                            data-url="{{ route('sales.show', $sale) }}">
                                        View
                                    </button>

                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary js-print-receipt"
                                            data-url="{{ route('sales.receipt', $sale) }}"
                                            data-sale-no="{{ $sale->sale_no }}">
                                        Receipt
                                    </button>

                                    {{-- Cancel hidden for now. --}}
                                    {{-- 
                                    @can('sale.cancel')
                                        @if($sale->status === 'completed')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#cancelSaleModal{{ $sale->id }}">
                                                Cancel
                                            </button>
                                        @endif
                                    @endcan
                                    --}}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="sales-empty">
                                    No sales found.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="p-3 border-top">
                {{ $sales->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="saleDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="saleDetailsTitle">Sale Details</h5>
                    <small class="text-muted">Full sale breakdown</small>
                </div>

                <button type="button" class="btn-close sales-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="saleDetailsBody">
                <div class="sales-empty">Loading sale details...</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="receiptModalTitle">Receipt</h5>
                    <small class="text-muted">Preview and print receipt</small>
                </div>

                <button type="button" class="btn-close sales-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div id="receiptPreview" class="receipt-preview-box">
                    <div class="sales-empty">Receipt preview will appear here.</div>
                </div>
            </div>

            <div class="modal-footer sales-mobile-stack">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" id="printReceiptBtn" class="btn btn-primary">
                    <i class="mdi mdi-printer mr-1"></i>
                    Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Cancel sale modal hidden for now. --}}
{{-- 
@can('sale.cancel')
    @foreach($sales as $sale)
        @if($sale->status === 'completed')
            <div class="modal fade" id="cancelSaleModal{{ $sale->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <form method="POST" action="{{ route('sales.cancel', $sale) }}" class="modal-content">
                        @csrf
                        @method('PATCH')

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Cancel Sale</h5>
                                <small class="text-muted">{{ $sale->sale_no }}</small>
                            </div>

                            <button type="button" class="btn-close sales-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-warning">
                                Cancelling this sale will restore sold quantities back to inventory using the original sale allocations.
                            </div>

                            <label>Reason</label>
                            <textarea name="reason" rows="4" class="form-control" required placeholder="Example: wrong product selected, duplicate receipt, customer changed order"></textarea>
                        </div>

                        <div class="modal-footer sales-mobile-stack">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">
                                Cancel Sale & Restore Inventory
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
@endcan
--}}

@push('scripts')
<script>
    $(function () {
        $('.select2-clear').select2({
            width: '100%',
            allowClear: true,
            placeholder: 'Select option'
        });

        $('.js-view-sale').on('click', function () {
            const url = $(this).data('url');

            $('#saleDetailsTitle').text('Sale Details');
            $('#saleDetailsBody').html('<div class="sales-empty">Loading sale details...</div>');

            showSalesModal('saleDetailsModal');

            fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.ok) {
                        $('#saleDetailsBody').html('<div class="sales-empty">Unable to load sale details.</div>');
                        return;
                    }

                    renderSaleDetails(data.sale);
                })
                .catch(() => {
                    $('#saleDetailsBody').html('<div class="sales-empty">Unable to load sale details.</div>');
                });
        });

        $('.js-print-receipt').on('click', function () {
            const url = $(this).data('url');
            const saleNo = $(this).data('sale-no');

            openReceipt(url, saleNo);
        });

        $('#printReceiptBtn').on('click', function () {
            printReceipt();
        });

        $(document).on('click', '[data-bs-dismiss="modal"]', function () {
            const modalElement = $(this).closest('.modal')[0];

            if (!modalElement) {
                return;
            }

            hideSalesModal(modalElement.id);
        });
    });

    function showSalesModal(id) {
        const element = document.getElementById(id);

        if (!element) {
            return;
        }

        if (window.bootstrap && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(element);
            modal.show();
            return;
        }

        if (window.jQuery && typeof $('#' + id).modal === 'function') {
            $('#' + id).modal('show');
            return;
        }

        element.classList.add('show');
        element.style.display = 'block';
        element.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
    }

    function hideSalesModal(id) {
        const element = document.getElementById(id);

        if (!element) {
            return;
        }

        if (window.bootstrap && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(element);
            modal.hide();
            return;
        }

        if (window.jQuery && typeof $('#' + id).modal === 'function') {
            $('#' + id).modal('hide');
            return;
        }

        element.classList.remove('show');
        element.style.display = 'none';
        element.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    }

    function money(value) {
        return Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function renderSaleDetails(sale) {
        $('#saleDetailsTitle').text(sale.sale_no);

        const rows = sale.items.map(item => `
            <tr>
                <td>${escapeHtml(item.product || '-')}</td>
                <td>${escapeHtml(item.unit || '-')}</td>
                <td>${item.quantity}</td>
                <td>${money(item.unit_price)}</td>
                <td>${money(item.line_discount)}</td>
                <td>${money(item.line_total)}</td>
                <td>${money(item.total_cost)}</td>
                <td>${money(item.profit_amount)}</td>
            </tr>
        `).join('');

        $('#saleDetailsBody').html(`
            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <small class="text-muted d-block">Customer</small>
                    <strong>${escapeHtml(sale.customer || '-')}</strong>
                </div>

                <div class="col-md-3 mb-2">
                    <small class="text-muted d-block">Branch</small>
                    <strong>${escapeHtml(sale.branch || '-')}</strong>
                </div>

                <div class="col-md-3 mb-2">
                    <small class="text-muted d-block">Cashier</small>
                    <strong>${escapeHtml(sale.cashier || '-')}</strong>
                </div>

                <div class="col-md-3 mb-2">
                    <small class="text-muted d-block">Date</small>
                    <strong>${escapeHtml(sale.sold_at || '-')}</strong>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Total</th>
                            <th>Cost</th>
                            <th>Profit</th>
                        </tr>
                    </thead>

                    <tbody>
                        ${rows || '<tr><td colspan="8" class="text-center text-muted">No items.</td></tr>'}
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right">Subtotal</th>
                            <th>${money(sale.subtotal_amount)}</th>
                            <th></th>
                            <th></th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-right">Discount</th>
                            <th>${money(sale.discount_amount)}</th>
                            <th></th>
                            <th></th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-right">Total</th>
                            <th>${money(sale.total_amount)}</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `);
    }

    function openReceipt(url, saleNo) {
        $('#receiptModalTitle').text(saleNo || 'Receipt');
        $('#receiptPreview').html('<div class="sales-empty">Loading receipt...</div>');

        showSalesModal('receiptModal');

        fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (!data.ok) {
                    $('#receiptPreview').html('<div class="sales-empty">Unable to load receipt.</div>');
                    return;
                }

                $('#receiptModalTitle').text(data.sale_no);
                $('#receiptPreview').html(data.html);
            })
            .catch(() => {
                $('#receiptPreview').html('<div class="sales-empty">Unable to load receipt.</div>');
            });
    }

    function printReceipt() {
        const receiptHtml = document.getElementById('receiptPreview').innerHTML;

        if (!receiptHtml || receiptHtml.includes('Receipt preview will appear here')) {
            return;
        }

        const printWindow = window.open('', '_blank', 'width=420,height=650');

        printWindow.document.open();
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Receipt</title>
            </head>
            <body>
                ${receiptHtml}
                <script>
                    window.onload = function () {
                        window.focus();
                        window.print();
                        setTimeout(function () {
                            window.close();
                        }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);
        printWindow.document.close();
    }

    function escapeHtml(value) {
        return String(value || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
</script>
@endpush
@endsection