@extends('components.main-layout')

@section('title', 'Stock Transfer Details')
@section('page-title', 'Stock Transfer Details')
@section('page-subtitle', 'Review transfer items, dispatch status and receiving information')

@section('content')
<style>
    .transfer-show-page { max-width: 100%; overflow-x: hidden; }

    .transfer-show-hero,
    .transfer-show-card,
    .transfer-show-stat {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
    }

    .transfer-show-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .transfer-show-hero .card-body { padding: 22px; }

    .transfer-show-icon {
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

    .transfer-show-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.025em;
    }

    .transfer-show-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 750;
        margin-bottom: 0;
    }

    .transfer-show-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .transfer-show-actions .btn {
        border-radius: 13px;
        font-weight: 850;
        white-space: nowrap;
    }

    .transfer-show-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .transfer-show-stat {
        padding: 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .transfer-show-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 5px;
    }

    .transfer-show-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 21px;
        line-height: 1.1;
    }

    .transfer-show-sub {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 750;
        margin-top: 6px;
    }

    .transfer-show-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .transfer-show-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .transfer-show-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .transfer-show-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .transfer-show-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .transfer-show-table {
        min-width: 1120px;
        margin-bottom: 0;
    }

    .transfer-show-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .transfer-show-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .transfer-show-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .transfer-show-muted {
        color: #64748b;
        font-size: 12px;
        margin-top: 4px;
        font-weight: 700;
    }

    .transfer-show-badge {
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

    .transfer-note-box {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 16px;
        padding: 15px;
        color: #334155;
        font-size: 13px;
        font-weight: 750;
        line-height: 1.55;
    }

    .timeline-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .timeline-step {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #fff;
        padding: 14px;
    }

    .timeline-step.done {
        border-color: #bbf7d0;
        background: #f0fdf4;
    }

    .timeline-title {
        color: #0f172a;
        font-weight: 950;
        margin-bottom: 4px;
    }

    .timeline-sub {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
    }

    @media (max-width: 1199.98px) {
        .transfer-show-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .timeline-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .transfer-show-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .transfer-show-grid,
        .timeline-grid {
            grid-template-columns: 1fr;
        }

        .transfer-show-actions {
            display: grid;
            grid-template-columns: 1fr;
            width: 100%;
        }

        .transfer-show-actions .btn,
        .transfer-show-actions form {
            width: 100%;
        }

        .transfer-show-actions form .btn {
            width: 100%;
        }
    }
</style>

@php
    $statusClass = match ($stockTransfer->status) {
        'received' => 'badge-green',
        'dispatched' => 'badge-purple',
        'approved' => 'badge-yellow',
        'draft' => 'badge-blue',
        'rejected' => 'badge-red',
        'cancelled' => 'badge-gray',
        default => 'badge-gray',
    };
@endphp

<div class="container-fluid transfer-show-page">
    <div class="card transfer-show-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="transfer-show-icon">
                        <i class="mdi mdi-swap-horizontal-bold"></i>
                    </span>

                    <div>
                        <h4 class="transfer-show-title">{{ $stockTransfer->transfer_no }}</h4>
                        <p class="transfer-show-subtitle">
                            {{ $stockTransfer->sourceBranch?->name ?: '-' }}
                            →
                            {{ $stockTransfer->destinationBranch?->name ?: '-' }}
                            • {{ $stockTransfer->transfer_date?->format('d M Y') }}
                        </p>
                    </div>
                </div>

                <div class="transfer-show-actions">
                    <a href="{{ route('stock-transfers.index') }}" class="btn btn-light">
                        <i class="mdi mdi-arrow-left mr-1"></i>
                        Back
                    </a>

                    @if($stockTransfer->isDraft())
                        @can('stock_transfer.approve')
                            <form method="POST" action="{{ route('stock-transfers.approve', $stockTransfer) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-success">
                                    <i class="mdi mdi-check-circle-outline mr-1"></i>
                                    Approve
                                </button>
                            </form>
                        @endcan

                        @can('stock_transfer.cancel')
                            <form method="POST" action="{{ route('stock-transfers.cancel', $stockTransfer) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-outline-secondary">Cancel</button>
                            </form>
                        @endcan
                    @endif

                    @if($stockTransfer->isApproved())
                        @can('stock_transfer.dispatch')
                            <form method="POST" action="{{ route('stock-transfers.dispatch', $stockTransfer) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-warning">
                                    <i class="mdi mdi-truck-fast-outline mr-1"></i>
                                    Dispatch
                                </button>
                            </form>
                        @endcan
                    @endif

                    @if($stockTransfer->isDispatched())
                        @can('stock_transfer.receive')
                            <form method="POST" action="{{ route('stock-transfers.receive', $stockTransfer) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-primary">
                                    <i class="mdi mdi-package-variant-closed-check mr-1"></i>
                                    Receive
                                </button>
                            </form>
                        @endcan
                    @endif
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

    <div class="transfer-show-grid">
        <div class="transfer-show-stat">
            <div class="transfer-show-label">Status</div>
            <div class="transfer-show-value">
                <span class="transfer-show-badge {{ $statusClass }}">{{ ucfirst($stockTransfer->status) }}</span>
            </div>
            <div class="transfer-show-sub">Transfer stage</div>
        </div>

        <div class="transfer-show-stat">
            <div class="transfer-show-label">Items</div>
            <div class="transfer-show-value">{{ number_format((int) $stockTransfer->total_items) }}</div>
            <div class="transfer-show-sub">Transfer lines</div>
        </div>

        <div class="transfer-show-stat">
            <div class="transfer-show-label">Base Qty</div>
            <div class="transfer-show-value">{{ number_format((int) $stockTransfer->total_quantity_base_units) }}</div>
            <div class="transfer-show-sub">Total base units</div>
        </div>

        <div class="transfer-show-stat">
            <div class="transfer-show-label">Cost Value</div>
            <div class="transfer-show-value">{{ number_format((float) $stockTransfer->total_cost, 2) }}</div>
            <div class="transfer-show-sub">Inventory value moved</div>
        </div>

        <div class="transfer-show-stat">
            <div class="transfer-show-label">Created By</div>
            <div class="transfer-show-value" style="font-size: 16px;">
                {{ $stockTransfer->creator?->displayName() ?: ($stockTransfer->creator?->username ?? '-') }}
            </div>
            <div class="transfer-show-sub">{{ $stockTransfer->created_at?->timezone('Africa/Dar_es_Salaam')->format('d M Y h:i A') }}</div>
        </div>
    </div>

    <div class="card transfer-show-card">
        <div class="transfer-show-card-header">
            <h5>Transfer Timeline</h5>
            <p>Source stock reduces on dispatch. Destination stock increases on receive.</p>
        </div>

        <div class="p-3">
            <div class="timeline-grid">
                <div class="timeline-step done">
                    <div class="timeline-title">Created</div>
                    <div class="timeline-sub">
                        {{ $stockTransfer->creator?->displayName() ?: ($stockTransfer->creator?->username ?? '-') }}<br>
                        {{ $stockTransfer->created_at?->timezone('Africa/Dar_es_Salaam')->format('d M Y h:i A') }}
                    </div>
                </div>

                <div class="timeline-step {{ $stockTransfer->approved_at ? 'done' : '' }}">
                    <div class="timeline-title">Approved</div>
                    <div class="timeline-sub">
                        {{ $stockTransfer->approver?->displayName() ?: ($stockTransfer->approver?->username ?? '-') }}<br>
                        {{ $stockTransfer->approved_at?->timezone('Africa/Dar_es_Salaam')->format('d M Y h:i A') ?: '-' }}
                    </div>
                </div>

                <div class="timeline-step {{ $stockTransfer->dispatched_at ? 'done' : '' }}">
                    <div class="timeline-title">Dispatched</div>
                    <div class="timeline-sub">
                        {{ $stockTransfer->dispatcher?->displayName() ?: ($stockTransfer->dispatcher?->username ?? '-') }}<br>
                        {{ $stockTransfer->dispatched_at?->timezone('Africa/Dar_es_Salaam')->format('d M Y h:i A') ?: '-' }}
                    </div>
                </div>

                <div class="timeline-step {{ $stockTransfer->received_at ? 'done' : '' }}">
                    <div class="timeline-title">Received</div>
                    <div class="timeline-sub">
                        {{ $stockTransfer->receiver?->displayName() ?: ($stockTransfer->receiver?->username ?? '-') }}<br>
                        {{ $stockTransfer->received_at?->timezone('Africa/Dar_es_Salaam')->format('d M Y h:i A') ?: '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card transfer-show-card">
        <div class="transfer-show-card-header">
            <h5>Transfer Items</h5>
            <p>Unit quantity is converted into base units before dispatch and receiving.</p>
        </div>

        <div class="transfer-show-table-wrap">
            <table class="table transfer-show-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Batch</th>
                        <th>Unit Qty</th>
                        <th>Base Qty</th>
                        <th>Source Before</th>
                        <th>Source After</th>
                        <th>Destination Before</th>
                        <th>Destination After</th>
                        <th class="text-right">Cost/Base</th>
                        <th class="text-right">Total Cost</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($stockTransfer->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>
                                <div class="transfer-show-main">{{ $item->product?->name ?: '-' }}</div>
                                <div class="transfer-show-muted">Base: {{ $item->product?->baseUnit?->name ?: '-' }}</div>
                            </td>

                            <td>
                                <div class="transfer-show-main">{{ $item->batch_no ?: '-' }}</div>
                                <div class="transfer-show-muted">
                                    Expiry: {{ $item->expiry_date?->format('d M Y') ?: '-' }}
                                </div>
                            </td>

                            <td>
                                <div class="transfer-show-main">
                                    {{ number_format((float) $item->quantity, 2) }}
                                    {{ $item->productUnit?->unit?->name ?: $item->product?->baseUnit?->name }}
                                </div>
                                <div class="transfer-show-muted">
                                    1 unit = {{ number_format((int) $item->quantity_in_base_units) }} base
                                </div>
                            </td>

                            <td>
                                <strong>{{ number_format((int) $item->quantity_base_units) }}</strong>
                            </td>

                            <td>{{ number_format((int) $item->source_balance_before_base_units) }}</td>
                            <td>{{ number_format((int) $item->source_balance_after_base_units) }}</td>
                            <td>{{ number_format((int) $item->destination_balance_before_base_units) }}</td>
                            <td>{{ number_format((int) $item->destination_balance_after_base_units) }}</td>

                            <td class="text-right">{{ number_format((float) $item->unit_cost_base, 2) }}</td>

                            <td class="text-right">
                                <strong>{{ number_format((float) $item->total_cost, 2) }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="text-center text-muted p-4">No transfer items found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="4" class="text-right">Totals</th>
                        <th>{{ number_format((int) $stockTransfer->total_quantity_base_units) }}</th>
                        <th colspan="5"></th>
                        <th class="text-right">{{ number_format((float) $stockTransfer->total_cost, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card transfer-show-card h-100">
                <div class="transfer-show-card-header">
                    <h5>Reason & Notes</h5>
                    <p>Transfer explanation and internal notes.</p>
                </div>

                <div class="p-3">
                    <div class="transfer-note-box mb-3">
                        <strong>Reason:</strong><br>
                        {{ $stockTransfer->reason ?: 'No reason provided.' }}
                    </div>

                    <div class="transfer-note-box">
                        <strong>Notes:</strong><br>
                        {{ $stockTransfer->notes ?: 'No notes provided.' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card transfer-show-card h-100">
                <div class="transfer-show-card-header">
                    <h5>Approval / Rejection</h5>
                    <p>Decision information and rejection reason if any.</p>
                </div>

                <div class="p-3">
                    <div class="transfer-note-box mb-3">
                        <strong>Approved By:</strong><br>
                        {{ $stockTransfer->approver?->displayName() ?: ($stockTransfer->approver?->username ?? '-') }}
                    </div>

                    <div class="transfer-note-box mb-3">
                        <strong>Approved At:</strong><br>
                        {{ $stockTransfer->approved_at?->timezone('Africa/Dar_es_Salaam')->format('d M Y h:i A') ?: '-' }}
                    </div>

                    @if($stockTransfer->rejection_reason)
                        <div class="transfer-note-box text-danger">
                            <strong>Rejection Reason:</strong><br>
                            {{ $stockTransfer->rejection_reason }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection