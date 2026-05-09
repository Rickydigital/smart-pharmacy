@extends('components.main-layout')

@section('title', 'Stock Adjustment Details')
@section('page-title', 'Stock Adjustment Details')
@section('page-subtitle', 'Review adjustment lines, approval status and inventory movement impact')

@section('content')
<style>
    .adjust-show-page { max-width: 100%; overflow-x: hidden; }

    .adjust-show-hero,
    .adjust-show-card,
    .adjust-show-stat {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
    }

    .adjust-show-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .adjust-show-hero .card-body { padding: 22px; }

    .adjust-show-icon {
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

    .adjust-show-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.025em;
    }

    .adjust-show-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 750;
        margin-bottom: 0;
    }

    .adjust-show-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .adjust-show-actions .btn {
        border-radius: 13px;
        font-weight: 850;
        white-space: nowrap;
    }

    .adjust-show-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .adjust-show-stat {
        padding: 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .adjust-show-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 5px;
    }

    .adjust-show-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 21px;
        line-height: 1.1;
    }

    .adjust-show-sub {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 750;
        margin-top: 6px;
    }

    .adjust-show-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .adjust-show-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .adjust-show-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .adjust-show-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .adjust-show-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .adjust-show-table {
        min-width: 1080px;
        margin-bottom: 0;
    }

    .adjust-show-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .adjust-show-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .adjust-show-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .adjust-show-muted {
        color: #64748b;
        font-size: 12px;
        margin-top: 4px;
        font-weight: 700;
    }

    .adjust-show-badge {
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

    .adjust-note-box {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 16px;
        padding: 15px;
        color: #334155;
        font-size: 13px;
        font-weight: 750;
        line-height: 1.55;
    }

    @media (max-width: 1199.98px) {
        .adjust-show-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .adjust-show-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .adjust-show-grid {
            grid-template-columns: 1fr;
        }

        .adjust-show-actions {
            display: grid;
            grid-template-columns: 1fr;
            width: 100%;
        }

        .adjust-show-actions .btn,
        .adjust-show-actions form {
            width: 100%;
        }

        .adjust-show-actions form .btn {
            width: 100%;
        }
    }
</style>

@php
    $statusClass = match ($stockAdjustment->status) {
        'approved' => 'badge-green',
        'draft' => 'badge-blue',
        'rejected' => 'badge-red',
        'cancelled' => 'badge-gray',
        default => 'badge-gray',
    };

    $typeClass = match ($stockAdjustment->adjustment_type) {
        'damage', 'expiry', 'loss' => 'badge-red',
        'found_stock' => 'badge-green',
        'physical_count' => 'badge-purple',
        default => 'badge-yellow',
    };
@endphp

<div class="container-fluid adjust-show-page">
    <div class="card adjust-show-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="adjust-show-icon">
                        <i class="mdi mdi-clipboard-edit-outline"></i>
                    </span>

                    <div>
                        <h4 class="adjust-show-title">{{ $stockAdjustment->adjustment_no }}</h4>
                        <p class="adjust-show-subtitle">
                            Branch: {{ $stockAdjustment->branch?->name ?: '-' }} •
                            Date: {{ $stockAdjustment->adjustment_date?->format('d M Y') }}
                        </p>
                    </div>
                </div>

                <div class="adjust-show-actions">
                    <a href="{{ route('stock-adjustments.index') }}" class="btn btn-light">
                        <i class="mdi mdi-arrow-left mr-1"></i>
                        Back
                    </a>

                    @if($stockAdjustment->isDraft())
                        @can('stock_adjustment.approve')
                            <form method="POST" action="{{ route('stock-adjustments.approve', $stockAdjustment) }}">
                                @csrf
                                @method('PATCH')

                                <button class="btn btn-success">
                                    <i class="mdi mdi-check-circle-outline mr-1"></i>
                                    Approve
                                </button>
                            </form>
                        @endcan

                        @can('stock_adjustment.cancel')
                            <form method="POST" action="{{ route('stock-adjustments.cancel', $stockAdjustment) }}">
                                @csrf
                                @method('PATCH')

                                <button class="btn btn-outline-secondary">
                                    Cancel
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

    <div class="adjust-show-grid">
        <div class="adjust-show-stat">
            <div class="adjust-show-label">Status</div>
            <div class="adjust-show-value">
                <span class="adjust-show-badge {{ $statusClass }}">{{ ucfirst($stockAdjustment->status) }}</span>
            </div>
            <div class="adjust-show-sub">Approval state</div>
        </div>

        <div class="adjust-show-stat">
            <div class="adjust-show-label">Type</div>
            <div class="adjust-show-value">
                <span class="adjust-show-badge {{ $typeClass }}">{{ $stockAdjustment->displayType() }}</span>
            </div>
            <div class="adjust-show-sub">Adjustment category</div>
        </div>

        <div class="adjust-show-stat">
            <div class="adjust-show-label">Items</div>
            <div class="adjust-show-value">{{ number_format((int) $stockAdjustment->total_items) }}</div>
            <div class="adjust-show-sub">Adjustment lines</div>
        </div>

        <div class="adjust-show-stat">
            <div class="adjust-show-label">Base Qty</div>
            <div class="adjust-show-value">{{ number_format((int) $stockAdjustment->total_quantity_base_units) }}</div>
            <div class="adjust-show-sub">Total base units</div>
        </div>

        <div class="adjust-show-stat">
            <div class="adjust-show-label">Cost Impact</div>
            <div class="adjust-show-value">{{ number_format((float) $stockAdjustment->total_cost, 2) }}</div>
            <div class="adjust-show-sub">Inventory cost value</div>
        </div>
    </div>

    <div class="card adjust-show-card">
        <div class="adjust-show-card-header">
            <h5>Adjustment Items</h5>
            <p>Inventory will be updated only after this adjustment is approved.</p>
        </div>

        <div class="adjust-show-table-wrap">
            <table class="table adjust-show-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Batch</th>
                        <th>Direction</th>
                        <th>Qty</th>
                        <th>Before</th>
                        <th>After</th>
                        <th class="text-right">Cost/Base</th>
                        <th class="text-right">Total Cost</th>
                        <th>Reason</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($stockAdjustment->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>
                                <div class="adjust-show-main">{{ $item->product?->name ?: '-' }}</div>
                                <div class="adjust-show-muted">Base: {{ $item->product?->baseUnit?->name ?: '-' }}</div>
                            </td>

                            <td>
                                <div class="adjust-show-main">{{ $item->inventory?->batch_no ?: '-' }}</div>
                                <div class="adjust-show-muted">
                                    Expiry: {{ $item->inventory?->expiry_date?->format('d M Y') ?: '-' }}
                                </div>
                            </td>

                            <td>
                                <span class="adjust-show-badge {{ $item->isIn() ? 'badge-green' : 'badge-red' }}">
                                    {{ $item->isIn() ? 'Increase' : 'Reduce' }}
                                </span>
                            </td>

                            <td>{{ number_format((int) $item->quantity_base_units) }}</td>
                            <td>{{ number_format((int) $item->balance_before_base_units) }}</td>
                            <td>{{ number_format((int) $item->balance_after_base_units) }}</td>

                            <td class="text-right">
                                {{ number_format((float) $item->unit_cost_base, 2) }}
                            </td>

                            <td class="text-right">
                                <strong>{{ number_format((float) $item->total_cost, 2) }}</strong>
                            </td>

                            <td>{{ $item->reason ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="text-center text-muted p-4">No adjustment items found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="4" class="text-right">Totals</th>
                        <th>{{ number_format((int) $stockAdjustment->total_quantity_base_units) }}</th>
                        <th colspan="3"></th>
                        <th class="text-right">{{ number_format((float) $stockAdjustment->total_cost, 2) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card adjust-show-card h-100">
                <div class="adjust-show-card-header">
                    <h5>Reason & Notes</h5>
                    <p>Adjustment explanation and internal notes.</p>
                </div>

                <div class="p-3">
                    <div class="adjust-note-box mb-3">
                        <strong>Reason:</strong><br>
                        {{ $stockAdjustment->reason ?: 'No reason provided.' }}
                    </div>

                    <div class="adjust-note-box">
                        <strong>Notes:</strong><br>
                        {{ $stockAdjustment->notes ?: 'No notes provided.' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card adjust-show-card h-100">
                <div class="adjust-show-card-header">
                    <h5>Approval Details</h5>
                    <p>Approval, rejection or cancellation details.</p>
                </div>

                <div class="p-3">
                    <div class="adjust-note-box mb-3">
                        <strong>Created By:</strong><br>
                        {{ $stockAdjustment->creator?->displayName() ?: ($stockAdjustment->creator?->username ?? '-') }}
                    </div>

                    <div class="adjust-note-box mb-3">
                        <strong>Approved By:</strong><br>
                        {{ $stockAdjustment->approver?->displayName() ?: ($stockAdjustment->approver?->username ?? '-') }}
                    </div>

                    <div class="adjust-note-box mb-3">
                        <strong>Approved At:</strong><br>
                        {{ $stockAdjustment->approved_at?->timezone('Africa/Dar_es_Salaam')->format('d M Y h:i A') ?: '-' }}
                    </div>

                    @if($stockAdjustment->rejection_reason)
                        <div class="adjust-note-box text-danger">
                            <strong>Rejection Reason:</strong><br>
                            {{ $stockAdjustment->rejection_reason }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection