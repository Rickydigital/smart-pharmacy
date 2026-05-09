@extends('components.main-layout')

@section('title', 'Sales Return Details')
@section('page-title', 'Sales Return Details')
@section('page-subtitle', 'View return items, refund details and approval status')

@section('content')
<style>
    .return-show-page { max-width: 100%; overflow-x: hidden; }

    .return-show-hero,
    .return-show-card,
    .return-show-stat {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
    }

    .return-show-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .return-show-hero .card-body { padding: 22px; }

    .return-show-icon {
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

    .return-show-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.025em;
    }

    .return-show-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 750;
        margin-bottom: 0;
    }

    .return-show-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .return-show-actions .btn {
        border-radius: 13px;
        font-weight: 850;
        white-space: nowrap;
    }

    .return-show-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .return-show-stat {
        padding: 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .return-show-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 5px;
    }

    .return-show-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 22px;
        line-height: 1.1;
    }

    .return-show-sub {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 750;
        margin-top: 6px;
    }

    .return-show-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .return-show-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .return-show-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .return-show-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .return-show-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .return-show-table {
        min-width: 1050px;
        margin-bottom: 0;
    }

    .return-show-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .return-show-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .return-show-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .return-show-muted {
        color: #64748b;
        font-size: 12px;
        margin-top: 4px;
        font-weight: 700;
    }

    .return-show-badge {
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

    .return-note-box {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 16px;
        padding: 15px;
        color: #334155;
        font-size: 13px;
        font-weight: 750;
        line-height: 1.55;
    }

    @media (max-width: 991.98px) {
        .return-show-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .return-show-grid {
            grid-template-columns: 1fr;
        }

        .return-show-actions {
            display: grid;
            grid-template-columns: 1fr;
            width: 100%;
        }

        .return-show-actions .btn,
        .return-show-actions form {
            width: 100%;
        }

        .return-show-actions form .btn {
            width: 100%;
        }
    }
</style>

@php
    $statusClass = match ($salesReturn->status) {
        'approved' => 'badge-green',
        'draft' => 'badge-blue',
        'rejected' => 'badge-red',
        'cancelled' => 'badge-gray',
        default => 'badge-gray',
    };
@endphp

<div class="container-fluid return-show-page">
    <div class="card return-show-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="return-show-icon">
                        <i class="mdi mdi-backup-restore"></i>
                    </span>

                    <div>
                        <h4 class="return-show-title">{{ $salesReturn->return_no }}</h4>
                        <p class="return-show-subtitle">
                            Receipt: {{ $salesReturn->sale?->sale_no ?: '-' }} •
                            Branch: {{ $salesReturn->branch?->name ?: '-' }}
                        </p>
                    </div>
                </div>

                <div class="return-show-actions">
                    <a href="{{ route('sales-returns.index') }}" class="btn btn-light">
                        <i class="mdi mdi-arrow-left mr-1"></i>
                        Back
                    </a>

                    @if($salesReturn->isDraft())
                        @can('sales_return.approve')
                            <form method="POST" action="{{ route('sales-returns.approve', $salesReturn) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-success">
                                    <i class="mdi mdi-check-circle-outline mr-1"></i>
                                    Approve
                                </button>
                            </form>
                        @endcan

                        @can('sales_return.cancel')
                            <form method="POST" action="{{ route('sales-returns.cancel', $salesReturn) }}">
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

    <div class="return-show-grid">
        <div class="return-show-stat">
            <div class="return-show-label">Status</div>
            <div class="return-show-value">
                <span class="return-show-badge {{ $statusClass }}">{{ ucfirst($salesReturn->status) }}</span>
            </div>
            <div class="return-show-sub">{{ $salesReturn->return_date?->format('d M Y') }}</div>
        </div>

        <div class="return-show-stat">
            <div class="return-show-label">Refund Amount</div>
            <div class="return-show-value">{{ number_format((float) $salesReturn->refund_amount, 2) }}</div>
            <div class="return-show-sub">{{ str_replace('_', ' ', ucfirst($salesReturn->refund_method)) }}</div>
        </div>

        <div class="return-show-stat">
            <div class="return-show-label">Subtotal</div>
            <div class="return-show-value">{{ number_format((float) $salesReturn->subtotal_amount, 2) }}</div>
            <div class="return-show-sub">{{ str_replace('_', ' ', ucfirst($salesReturn->return_type)) }}</div>
        </div>

        <div class="return-show-stat">
            <div class="return-show-label">Created By</div>
            <div class="return-show-value" style="font-size: 17px;">
                {{ $salesReturn->creator?->displayName() ?: ($salesReturn->creator?->username ?? '-') }}
            </div>
            <div class="return-show-sub">{{ $salesReturn->created_at?->timezone('Africa/Dar_es_Salaam')->format('d M Y h:i A') }}</div>
        </div>
    </div>

    <div class="card return-show-card">
        <div class="return-show-card-header">
            <h5>Returned Items</h5>
            <p>Sellable items can be restored to inventory during approval.</p>
        </div>

        <div class="return-show-table-wrap">
            <table class="table return-show-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Unit</th>
                        <th>Qty</th>
                        <th>Base Qty</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Refund</th>
                        <th>Condition</th>
                        <th>Restore</th>
                        <th>Reason</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($salesReturn->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>
                                <div class="return-show-main">{{ $item->product?->name ?: '-' }}</div>
                                <div class="return-show-muted">{{ $item->saleItem?->sale?->sale_no ?? $salesReturn->sale?->sale_no }}</div>
                            </td>

                            <td>{{ $item->productUnit?->unit?->name ?: '-' }}</td>
                            <td>{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>{{ number_format((int) $item->total_base_units) }}</td>

                            <td class="text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="text-right"><strong>{{ number_format((float) $item->refund_amount, 2) }}</strong></td>

                            <td>
                                <span class="return-show-badge {{ $item->condition === 'sellable' ? 'badge-green' : 'badge-yellow' }}">
                                    {{ ucfirst($item->condition) }}
                                </span>
                            </td>

                            <td>
                                <span class="return-show-badge {{ $item->restore_to_inventory ? 'badge-green' : 'badge-gray' }}">
                                    {{ $item->restore_to_inventory ? 'Yes' : 'No' }}
                                </span>
                            </td>

                            <td>{{ $item->reason ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="text-center text-muted p-4">No return items found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="6" class="text-right">Total Refund</th>
                        <th class="text-right">{{ number_format((float) $salesReturn->refund_amount, 2) }}</th>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card return-show-card h-100">
                <div class="return-show-card-header">
                    <h5>Return Notes</h5>
                    <p>Reason and internal return notes.</p>
                </div>

                <div class="p-3">
                    <div class="return-note-box mb-3">
                        <strong>Reason:</strong><br>
                        {{ $salesReturn->reason ?: 'No reason provided.' }}
                    </div>

                    <div class="return-note-box">
                        <strong>Notes:</strong><br>
                        {{ $salesReturn->notes ?: 'No notes provided.' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card return-show-card h-100">
                <div class="return-show-card-header">
                    <h5>Approval Details</h5>
                    <p>Approval, rejection or cancellation information.</p>
                </div>

                <div class="p-3">
                    <div class="return-note-box mb-3">
                        <strong>Approved By:</strong><br>
                        {{ $salesReturn->approver?->displayName() ?: ($salesReturn->approver?->username ?? '-') }}
                    </div>

                    <div class="return-note-box mb-3">
                        <strong>Approved At:</strong><br>
                        {{ $salesReturn->approved_at?->timezone('Africa/Dar_es_Salaam')->format('d M Y h:i A') ?: '-' }}
                    </div>

                    @if($salesReturn->rejection_reason)
                        <div class="return-note-box text-danger">
                            <strong>Rejection Reason:</strong><br>
                            {{ $salesReturn->rejection_reason }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection