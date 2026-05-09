@extends('components.main-layout')

@section('title', 'Purchases')
@section('page-title', 'Purchases')
@section('page-subtitle', 'Create supplier purchases and receive products into inventory')

@section('content')
<style>
    .purchase-page { max-width: 100%; overflow-x: hidden; }

    .purchase-hero,
    .purchase-card,
    .purchase-filter-card,
    .purchase-stat {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .07);
    }

    .purchase-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .purchase-hero .card-body { padding: 22px; }

    .purchase-icon {
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

    .purchase-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.02em;
    }

    .purchase-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 0;
    }

    .purchase-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .purchase-actions .btn,
    .purchase-btn-row .btn,
    .purchase-filter-actions .btn,
    .purchase-filter-actions a {
        border-radius: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .purchase-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .purchase-stat {
        padding: 14px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .purchase-stat-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 4px;
    }

    .purchase-stat-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 24px;
        line-height: 1;
    }

    .purchase-filter-card {
        margin-bottom: 16px;
        border: 1px solid #e8edf5;
    }

    .purchase-filter-card .card-body { padding: 16px; }

    .purchase-label,
    .modal-body label {
        font-size: 11px;
        font-weight: 950;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .purchase-filter-card .form-control,
    .purchase-filter-card .custom-select,
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

    .purchase-card { overflow: hidden; }

    .purchase-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .purchase-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .purchase-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .purchase-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .purchase-table {
        min-width: 1180px;
        margin-bottom: 0;
    }

    .purchase-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .purchase-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .purchase-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .purchase-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 3px;
        font-weight: 700;
    }

    .purchase-badge {
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

    .purchase-btn-row {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .purchase-empty {
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

    .purchase-info-box {
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1e40af;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 750;
    }

    .purchase-preview-box {
        border: 1px solid #dbeafe;
        background: #f8fbff;
        border-radius: 16px;
        padding: 14px;
        height: 100%;
    }

    .purchase-preview-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 5px;
    }

    .purchase-preview-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 16px;
    }

    .is-invalid {
        border-color: #dc2626 !important;
        background: #fff7f7 !important;
    }

    @media (max-width: 767.98px) {
        .purchase-hero .card-body { padding: 18px 16px; }

        .purchase-hero-main { align-items: flex-start !important; }

        .purchase-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
        }

        .purchase-actions .btn { width: 100%; }

        .purchase-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .purchase-filter-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px !important;
        }

        .purchase-filter-actions .btn,
        .purchase-filter-actions a {
            width: 100%;
            text-align: center;
        }

        .purchase-table { min-width: 980px; }

        .purchase-table th,
        .purchase-table td {
            padding: 10px;
            font-size: 12px;
        }

        .purchase-btn-row .btn {
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

        .purchase-mobile-stack {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: 8px !important;
        }

        .purchase-mobile-stack .btn,
        .purchase-mobile-stack input {
            width: 100%;
        }
    }
</style>

<div class="container-fluid purchase-page">
    <div class="card purchase-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between purchase-hero-main" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="purchase-icon">
                        <i class="mdi mdi-cart-arrow-down"></i>
                    </span>

                    <div>
                        <h4 class="purchase-title">Purchases</h4>
                        <p class="purchase-subtitle">
                            Create supplier purchases, add purchased items, and receive them into inventory.
                        </p>
                    </div>
                </div>

                @can('purchase.manage')
                    <div class="purchase-actions">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createPurchaseModal">
                            <i class="mdi mdi-plus mr-1"></i>
                            New Purchase
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

    <div class="purchase-stat-grid">
        <div class="purchase-stat">
            <div class="purchase-stat-label">All</div>
            <div class="purchase-stat-value">{{ number_format($counts['all'] ?? 0) }}</div>
        </div>

        <div class="purchase-stat">
            <div class="purchase-stat-label">Draft</div>
            <div class="purchase-stat-value">{{ number_format($counts['draft'] ?? 0) }}</div>
        </div>

        <div class="purchase-stat">
            <div class="purchase-stat-label">Received</div>
            <div class="purchase-stat-value">{{ number_format($counts['received'] ?? 0) }}</div>
        </div>

        <div class="purchase-stat">
            <div class="purchase-stat-label">Cancelled</div>
            <div class="purchase-stat-value">{{ number_format($counts['cancelled'] ?? 0) }}</div>
        </div>
    </div>

    <div class="card purchase-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('purchases.index') }}">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="purchase-label">Search</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control"
                               placeholder="Purchase no, invoice, supplier">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="purchase-label">Supplier</label>
                        <select name="supplier_id" class="custom-select select2-clear" data-placeholder="All suppliers">
                            <option value="">All suppliers</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="purchase-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="purchase-label">From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="purchase-label">To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>

                    <div class="col-lg-1 col-md-6 mb-3">
                        <div class="d-flex justify-content-end purchase-filter-actions" style="gap: 8px;">
                            @if(request()->hasAny(['search', 'supplier_id', 'status', 'date_from', 'date_to']))
                                <a href="{{ route('purchases.index') }}" class="btn btn-light">
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

    <div class="card purchase-card">
        <div class="purchase-card-header">
            <h5>Purchase List</h5>
            <p>Draft purchases can be edited. Received purchases are locked and update inventory.</p>
        </div>

        <div class="purchase-table-wrap">
            <table class="table purchase-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Purchase</th>
                        <th>Supplier</th>
                        <th>Branch</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Paid / Balance</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($purchases as $index => $purchase)
                        <tr>
                            <td>{{ ($purchases->firstItem() ?? 0) + $index }}</td>

                            <td>
                                <div class="purchase-main">{{ $purchase->purchase_no }}</div>
                                <div class="purchase-sub">
                                    {{ $purchase->purchase_date?->format('d M Y') }}
                                    @if($purchase->supplier_invoice_no)
                                        • Inv: {{ $purchase->supplier_invoice_no }}
                                    @endif
                                </div>
                            </td>

                            <td>{{ $purchase->supplier?->name ?: 'No supplier' }}</td>

                            <td>{{ $purchase->branch?->name ?: '-' }}</td>

                            <td>
                                <span class="purchase-badge badge-blue">
                                    {{ $purchase->items_count }} items
                                </span>
                            </td>

                            <td>
                                <strong>{{ number_format((float) $purchase->total_amount, 2) }}</strong>
                            </td>

                            <td>
                                <div>Paid: {{ number_format((float) $purchase->paid_amount, 2) }}</div>
                                <div class="purchase-sub">Balance: {{ number_format((float) $purchase->balance_amount, 2) }}</div>
                            </td>

                            <td>
                                @php
                                    $paymentClass = match ($purchase->payment_status) {
                                        'paid' => 'badge-green',
                                        'partial' => 'badge-yellow',
                                        default => 'badge-gray',
                                    };
                                @endphp

                                <span class="purchase-badge {{ $paymentClass }}">
                                    {{ ucfirst($purchase->payment_status) }}
                                </span>
                            </td>

                            <td>
                                @php
                                    $statusClass = match ($purchase->status) {
                                        'received' => 'badge-green',
                                        'cancelled' => 'badge-red',
                                        default => 'badge-blue',
                                    };
                                @endphp

                                <span class="purchase-badge {{ $statusClass }}">
                                    {{ ucfirst($purchase->status) }}
                                </span>
                            </td>

                            <td class="text-right">
                                <div class="purchase-btn-row">
                                    <button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#viewPurchaseModal{{ $purchase->id }}">
                                        View
                                    </button>

                                    @can('purchase.manage')
                                        @if($purchase->isDraft())
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editPurchaseModal{{ $purchase->id }}">
                                                Edit
                                            </button>

                                            <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#addItemModal{{ $purchase->id }}">
                                                Items
                                            </button>

                                            <form method="POST" action="{{ route('purchases.receive', $purchase) }}" class="d-inline" onsubmit="return confirm('Receive this purchase into inventory?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    Receive
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('purchases.cancel', $purchase) }}" class="d-inline" onsubmit="return confirm('Cancel this purchase?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="purchase-empty">No purchases found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
            <div class="p-3 border-top">
                {{ $purchases->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@can('purchase.manage')
    <div class="modal fade" id="createPurchaseModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <form method="POST" action="{{ route('purchases.store') }}" class="modal-content">
                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">New Purchase</h5>
                        <small class="text-muted">Create purchase header first, then add items.</small>
                    </div>

                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Branch</label>
                            <select name="branch_id" class="custom-select select2-modal" data-placeholder="Select branch" required>
                                <option value=""></option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Supplier</label>
                            <select name="supplier_id" class="custom-select select2-modal" data-placeholder="Select supplier">
                                <option value=""></option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Supplier Invoice No</label>
                            <input name="supplier_invoice_no" class="form-control" placeholder="Optional">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Paid / Allowed Item Amount</label>
                            <input type="number" step="0.01" min="0" name="paid_amount" class="form-control" value="0">
                            <small class="text-muted d-block mt-1">
                                Item totals cannot exceed this amount.
                            </small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer purchase-mobile-stack">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Purchase</button>
                </div>
            </form>
        </div>
    </div>
@endcan

@foreach($purchases as $purchase)
    @php
        $purchaseItemsTotal = (float) $purchase->items->sum('line_total');
        $remainingPurchaseAmount = max(0, (float) $purchase->paid_amount - $purchaseItemsTotal);
    @endphp

    <div class="modal fade" id="viewPurchaseModal{{ $purchase->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">{{ $purchase->purchase_no }}</h5>
                        <small class="text-muted">
                            {{ $purchase->supplier?->name ?: 'No supplier' }} • {{ $purchase->branch?->name }}
                        </small>
                    </div>

                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="purchase-info-box mb-3">
                        Batch number is generated automatically when the purchase is received.
                        Cost per base unit is calculated from item amount ÷ total base quantity.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Purchase Unit</th>
                                    <th>Generated Batch</th>
                                    <th>Expiry</th>
                                    <th>Qty Bought</th>
                                    <th>Total Base Qty</th>
                                    <th>Cost / Purchase Unit</th>
                                    <th>Cost / Base Unit</th>
                                    <th>Item Amount</th>
                                    @can('purchase.manage')
                                        @if($purchase->isDraft())
                                            <th>Actions</th>
                                        @endif
                                    @endcan
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($purchase->items as $item)
                                    @php
                                        $baseUnitCost = (int) $item->total_base_units > 0
                                            ? (float) $item->line_total / (int) $item->total_base_units
                                            : 0;
                                    @endphp

                                    <tr>
                                        <td>
                                            <strong>{{ $item->product?->name }}</strong>
                                            <div class="purchase-sub">{{ $item->product?->code }}</div>
                                        </td>
                                        <td>{{ $item->productUnit?->unit?->name }}</td>
                                        <td>{{ $item->batch_no ?: 'Auto on receive' }}</td>
                                        <td>{{ $item->expiry_date?->format('d M Y') ?: '-' }}</td>
                                        <td>{{ number_format((int) $item->quantity) }}</td>
                                        <td>{{ number_format((int) $item->total_base_units) }}</td>
                                        <td>{{ number_format((float) $item->unit_cost, 2) }}</td>
                                        <td>{{ number_format($baseUnitCost, 2) }}</td>
                                        <td>{{ number_format((float) $item->line_total, 2) }}</td>

                                        @can('purchase.manage')
                                            @if($purchase->isDraft())
                                                <td>
                                                    <div class="purchase-btn-row">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editItemModal{{ $item->id }}">
                                                            Edit
                                                        </button>

                                                        <form method="POST" action="{{ route('purchases.items.destroy', $item) }}" onsubmit="return confirm('Remove this item?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                Remove
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            @endif
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">No items added.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th colspan="8" class="text-right">Total Items</th>
                                    <th>{{ number_format((float) $purchase->total_amount, 2) }}</th>
                                    @can('purchase.manage')
                                        @if($purchase->isDraft())
                                            <th></th>
                                        @endif
                                    @endcan
                                </tr>
                                <tr>
                                    <th colspan="8" class="text-right">Remaining Allowed</th>
                                    <th>{{ number_format($remainingPurchaseAmount, 2) }}</th>
                                    @can('purchase.manage')
                                        @if($purchase->isDraft())
                                            <th></th>
                                        @endif
                                    @endcan
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('purchase.manage')
        @if($purchase->isDraft())
            <div class="modal fade" id="editPurchaseModal{{ $purchase->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                    <form method="POST" action="{{ route('purchases.update', $purchase) }}" class="modal-content">
                        @csrf
                        @method('PUT')

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Edit Purchase</h5>
                                <small class="text-muted">{{ $purchase->purchase_no }}</small>
                            </div>

                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Branch</label>
                                    <select name="branch_id" class="custom-select select2-modal" data-placeholder="Select branch" required>
                                        <option value=""></option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $purchase->branch_id == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Supplier</label>
                                    <select name="supplier_id" class="custom-select select2-modal" data-placeholder="Select supplier">
                                        <option value=""></option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ $purchase->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Purchase Date</label>
                                    <input type="date" name="purchase_date" class="form-control" value="{{ $purchase->purchase_date?->toDateString() }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Supplier Invoice No</label>
                                    <input name="supplier_invoice_no" class="form-control" value="{{ $purchase->supplier_invoice_no }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Paid / Allowed Item Amount</label>
                                    <input type="number" step="0.01" min="0" name="paid_amount" class="form-control" value="{{ $purchase->paid_amount }}">
                                    <small class="text-muted d-block mt-1">
                                        Item totals cannot exceed this amount.
                                    </small>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label>Notes</label>
                                    <textarea name="notes" class="form-control" rows="3">{{ $purchase->notes }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer purchase-mobile-stack">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Purchase</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal fade" id="addItemModal{{ $purchase->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                    <form method="POST" action="{{ route('purchases.items.store', $purchase) }}" class="modal-content js-purchase-item-form">
                        @csrf

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Add Purchase Item</h5>
                                <small class="text-muted">{{ $purchase->purchase_no }}</small>
                            </div>

                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>

                        <div class="modal-body">
                            <div class="purchase-info-box mb-3">
                                Remaining allowed amount:
                                <strong>{{ number_format($remainingPurchaseAmount, 2) }}</strong>.
                                Item amount cannot exceed this remaining amount.
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Product</label>
                                    <select name="product_id"
                                            class="custom-select select2-modal js-purchase-product"
                                            data-placeholder="Select product"
                                            required>
                                        <option value=""></option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">
                                                {{ $product->name }} - Base: {{ $product->baseUnit?->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Purchase Unit</label>
                                    <select name="product_unit_id"
                                            class="custom-select select2-modal js-purchase-unit"
                                            data-placeholder="Select unit"
                                            required>
                                        <option value=""></option>
                                        @foreach($productUnits as $productUnit)
                                            <option value="{{ $productUnit->id }}"
                                                    data-product-id="{{ $productUnit->product_id }}"
                                                    data-base-qty="{{ $productUnit->quantity_in_base_units }}">
                                                {{ $productUnit->unit?->name }}
                                                - {{ number_format((int) $productUnit->quantity_in_base_units) }} base units
                                            </option>
                                        @endforeach
                                    </select>

                                    <small class="text-muted d-block mt-1 js-unit-help">
                                        Select product first.
                                    </small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Quantity Bought</label>
                                    <input type="number"
                                           min="1"
                                           name="quantity"
                                           class="form-control js-purchase-quantity"
                                           value="1"
                                           required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Expiry Date</label>
                                    <input type="date" name="expiry_date" class="form-control">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Item Amount</label>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           max="{{ $remainingPurchaseAmount }}"
                                           name="item_amount"
                                           class="form-control js-item-amount"
                                           value="0"
                                           data-max-amount="{{ $remainingPurchaseAmount }}"
                                           required>
                                    <small class="text-muted d-block mt-1 js-amount-help">
                                        Maximum: {{ number_format($remainingPurchaseAmount, 2) }}
                                    </small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Base Cost Preview</label>
                                    <input type="text"
                                           class="form-control js-base-cost-preview"
                                           value="0.00 per base unit"
                                           readonly>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="purchase-preview-box">
                                        <div class="purchase-preview-label">Total Base Quantity</div>
                                        <div class="purchase-preview-value js-total-base-preview">0</div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="purchase-preview-box">
                                        <div class="purchase-preview-label">Batch Number</div>
                                        <div class="purchase-preview-value">Auto generated on receive</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer purchase-mobile-stack">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary js-save-item-btn" {{ $remainingPurchaseAmount <= 0 ? 'disabled' : '' }}>
                                Save Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach($purchase->items as $item)
                @php
                    $remainingEditAmount = max(
                        0,
                        (float) $purchase->paid_amount - ((float) $purchase->items->sum('line_total') - (float) $item->line_total)
                    );
                @endphp

                <div class="modal fade" id="editItemModal{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                        <form method="POST" action="{{ route('purchases.items.update', $item) }}" class="modal-content js-purchase-item-form">
                            @csrf
                            @method('PUT')

                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title">Edit Purchase Item</h5>
                                    <small class="text-muted">{{ $item->product?->name }}</small>
                                </div>

                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>

                            <div class="modal-body">
                                <div class="purchase-info-box mb-3">
                                    Maximum allowed for this item:
                                    <strong>{{ number_format($remainingEditAmount, 2) }}</strong>.
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Purchase Unit</label>
                                        <select name="product_unit_id"
                                                class="custom-select select2-modal js-purchase-unit"
                                                data-placeholder="Select unit"
                                                required>
                                            <option value=""></option>
                                            @foreach($productUnits->where('product_id', $item->product_id) as $productUnit)
                                                <option value="{{ $productUnit->id }}"
                                                        data-product-id="{{ $productUnit->product_id }}"
                                                        data-base-qty="{{ $productUnit->quantity_in_base_units }}"
                                                        {{ $item->product_unit_id == $productUnit->id ? 'selected' : '' }}>
                                                    {{ $productUnit->unit?->name }}
                                                    - {{ number_format((int) $productUnit->quantity_in_base_units) }} base units
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label>Quantity Bought</label>
                                        <input type="number"
                                               min="1"
                                               name="quantity"
                                               class="form-control js-purchase-quantity"
                                               value="{{ $item->quantity }}"
                                               required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label>Expiry Date</label>
                                        <input type="date"
                                               name="expiry_date"
                                               class="form-control"
                                               value="{{ $item->expiry_date?->toDateString() }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label>Item Amount</label>
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               max="{{ $remainingEditAmount }}"
                                               name="item_amount"
                                               class="form-control js-item-amount"
                                               value="{{ $item->line_total }}"
                                               data-max-amount="{{ $remainingEditAmount }}"
                                               required>
                                        <small class="text-muted d-block mt-1 js-amount-help">
                                            Maximum: {{ number_format($remainingEditAmount, 2) }}
                                        </small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label>Base Cost Preview</label>
                                        <input type="text"
                                               class="form-control js-base-cost-preview"
                                               value="0.00 per base unit"
                                               readonly>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label>Total Base Quantity</label>
                                        <input type="text"
                                               class="form-control js-total-base-preview"
                                               value="0"
                                               readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer purchase-mobile-stack">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary js-save-item-btn">
                                    Update Item
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif
    @endcan
@endforeach

@push('scripts')
<script>
    $(function () {
        const unitOptions = [];

        $('.js-purchase-unit:first option').each(function () {
            const $option = $(this);

            unitOptions.push({
                value: $option.attr('value') || '',
                text: $option.text(),
                productId: $option.data('product-id') ? String($option.data('product-id')) : '',
                baseQty: $option.data('base-qty') ? String($option.data('base-qty')) : '',
            });
        });

        function initSelect2($modal) {
            $modal.find('.select2-modal').each(function () {
                const $select = $(this);

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    width: '100%',
                    allowClear: true,
                    dropdownParent: $modal,
                    placeholder: $select.data('placeholder') || 'Select option'
                });
            });
        }

        $('.select2-clear').select2({
            width: '100%',
            allowClear: true,
            placeholder: 'Select option'
        });

        $('.modal').on('shown.bs.modal', function () {
            const $modal = $(this);

            initSelect2($modal);
            calculatePurchaseItemPreview($modal);
        });

        $(document).on('change', '.js-purchase-product', function () {
            const productId = String($(this).val() || '');
            const $modal = $(this).closest('.modal');
            const $unitSelect = $modal.find('.js-purchase-unit');

            rebuildUnitSelect($unitSelect, productId);

            const count = $unitSelect.find('option[value!=""]').length;

            $modal.find('.js-unit-help').text(
                count > 0
                    ? count + ' purchase unit(s) available for this product.'
                    : 'No purchase units configured for this product.'
            );

            calculatePurchaseItemPreview($modal);
        });

        $(document).on('change keyup', '.js-purchase-unit, .js-purchase-quantity, .js-item-amount', function () {
            calculatePurchaseItemPreview($(this).closest('.modal'));
        });

        $(document).on('submit', '.js-purchase-item-form', function (event) {
            const $form = $(this);
            const amount = parseFloat($form.find('.js-item-amount').val() || 0);
            const maxAmount = parseFloat($form.find('.js-item-amount').data('max-amount') || 0);

            if (maxAmount >= 0 && amount > maxAmount) {
                event.preventDefault();

                $form.find('.js-item-amount').addClass('is-invalid');
                alert('Item amount cannot exceed the remaining allowed amount of ' + maxAmount.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + '.');
            }
        });

        function rebuildUnitSelect($unitSelect, productId) {
            const selectedBefore = String($unitSelect.val() || '');

            $unitSelect.empty();
            $unitSelect.append(new Option('', ''));

            unitOptions.forEach(function (item) {
                if (!item.value) {
                    return;
                }

                if (item.productId === productId) {
                    const option = new Option(item.text, item.value, false, selectedBefore === String(item.value));
                    $(option).attr('data-product-id', item.productId);
                    $(option).attr('data-base-qty', item.baseQty);

                    $unitSelect.append(option);
                }
            });

            if ($unitSelect.find('option[value="' + selectedBefore + '"]').length === 0) {
                $unitSelect.val('');
            } else {
                $unitSelect.val(selectedBefore);
            }

            $unitSelect.trigger('change.select2');
        }

        function calculatePurchaseItemPreview($modal) {
            const $selectedUnit = $modal.find('.js-purchase-unit option:selected');

            const baseQty = parseFloat($selectedUnit.data('base-qty') || 0);
            const quantity = parseFloat($modal.find('.js-purchase-quantity').val() || 0);
            const amount = parseFloat($modal.find('.js-item-amount').val() || 0);
            const maxAmount = parseFloat($modal.find('.js-item-amount').data('max-amount') || 0);

            const totalBaseUnits = baseQty * quantity;
            const baseCost = totalBaseUnits > 0 ? amount / totalBaseUnits : 0;

            const baseCostText = baseCost.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' per base unit';

            const totalBaseText = totalBaseUnits.toLocaleString(undefined, {
                maximumFractionDigits: 0
            });

            $modal.find('.js-base-cost-preview').val(baseCostText);
            $modal.find('.js-total-base-preview').each(function () {
                if ($(this).is('input')) {
                    $(this).val(totalBaseText);
                } else {
                    $(this).text(totalBaseText);
                }
            });

            if (maxAmount >= 0 && amount > maxAmount) {
                $modal.find('.js-item-amount').addClass('is-invalid');
                $modal.find('.js-save-item-btn').prop('disabled', true);
                $modal.find('.js-amount-help').text(
                    'Amount exceeds maximum allowed: ' + maxAmount.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                );
            } else {
                $modal.find('.js-item-amount').removeClass('is-invalid');

                if (maxAmount > 0 || amount > 0) {
                    $modal.find('.js-save-item-btn').prop('disabled', false);
                }

                $modal.find('.js-amount-help').text(
                    'Maximum: ' + maxAmount.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                );
            }
        }
    });
</script>
@endpush
@endsection