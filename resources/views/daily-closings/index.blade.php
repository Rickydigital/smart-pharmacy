@extends('components.main-layout')

@section('title', 'Daily Closings')
@section('page-title', 'Daily Closings')
@section('page-subtitle', 'Close cashier day, verify cash and lock branch accounting')

@section('content')
<style>
    .closing-page { max-width: 100%; overflow-x: hidden; }

    .closing-hero,
    .closing-card,
    .closing-filter-card,
    .closing-stat {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .07);
    }

    .closing-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .closing-hero .card-body { padding: 22px; }

    .closing-icon {
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

    .closing-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.02em;
    }

    .closing-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 0;
    }

    .closing-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .closing-actions .btn,
    .closing-filter-actions .btn,
    .closing-filter-actions a,
    .closing-btn-row .btn {
        border-radius: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .closing-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .closing-stat {
        padding: 14px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .closing-stat-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 4px;
    }

    .closing-stat-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 22px;
        line-height: 1;
    }

    .closing-filter-card {
        margin-bottom: 16px;
        border: 1px solid #e8edf5;
    }

    .closing-filter-card .card-body { padding: 16px; }

    .closing-label,
    .modal-body label {
        font-size: 11px;
        font-weight: 950;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .closing-filter-card .form-control,
    .closing-filter-card .custom-select,
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

    .closing-card { overflow: hidden; }

    .closing-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .closing-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .closing-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .closing-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .closing-table {
        min-width: 1180px;
        margin-bottom: 0;
    }

    .closing-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .closing-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .closing-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .closing-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 3px;
        font-weight: 700;
    }

    .closing-badge {
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

    .closing-btn-row {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .closing-empty {
        padding: 42px 20px;
        text-align: center;
        color: #64748b;
        font-weight: 750;
    }

    .closing-calculation-box {
        border: 1px solid #dbeafe;
        background: #eff6ff;
        border-radius: 16px;
        padding: 14px;
        margin-bottom: 14px;
    }

    .closing-calc-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .closing-calc-item {
        background: #fff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 12px;
    }

    .closing-calc-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .closing-calc-value {
        margin-top: 5px;
        color: #0f172a;
        font-weight: 950;
        font-size: 18px;
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

    .closing-modal-close {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background-color: #ffffff;
        opacity: 1;
        box-shadow: none;
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

    @media (max-width: 767.98px) {
        .closing-hero .card-body { padding: 18px 16px; }

        .closing-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
        }

        .closing-actions .btn { width: 100%; }

        .closing-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .closing-calc-grid {
            grid-template-columns: 1fr;
        }

        .closing-filter-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px !important;
        }

        .closing-filter-actions .btn,
        .closing-filter-actions a {
            width: 100%;
            text-align: center;
        }

        .closing-table { min-width: 1080px; }

        .closing-btn-row .btn {
            font-size: 11px;
            padding: .32rem .55rem;
        }

        .modal-dialog { margin: .65rem; }

        .modal-body {
            padding: 15px;
            max-height: calc(100vh - 165px);
        }

        .closing-mobile-stack {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: 8px !important;
        }

        .closing-mobile-stack .btn {
            width: 100%;
        }
    }
</style>

<div class="container-fluid closing-page">
    <div class="card closing-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="closing-icon">
                        <i class="mdi mdi-calendar-check-outline"></i>
                    </span>

                    <div>
                        <h4 class="closing-title">Daily Closings</h4>
                        <p class="closing-subtitle">
                            Compare expected cash with physical cash and verify daily accounting.
                        </p>
                    </div>
                </div>

                @can('daily_closing.create')
                    <div class="closing-actions">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createClosingModal">
                            <i class="mdi mdi-plus-circle-outline mr-1"></i>
                            New Closing
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

    <div class="closing-stat-grid">
        <div class="closing-stat">
            <div class="closing-stat-label">Closings</div>
            <div class="closing-stat-value">{{ number_format($summary['count'] ?? 0) }}</div>
        </div>

        <div class="closing-stat">
            <div class="closing-stat-label">Expected Cash</div>
            <div class="closing-stat-value">{{ number_format((float) ($summary['expected_cash'] ?? 0), 2) }}</div>
        </div>

        <div class="closing-stat">
            <div class="closing-stat-label">Counted Cash</div>
            <div class="closing-stat-value">{{ number_format((float) ($summary['counted_cash'] ?? 0), 2) }}</div>
        </div>

        <div class="closing-stat">
            <div class="closing-stat-label">Difference</div>
            <div class="closing-stat-value">{{ number_format((float) ($summary['difference'] ?? 0), 2) }}</div>
        </div>
    </div>

    <div class="card closing-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('daily-closings.index') }}">
                <div class="row align-items-end">
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="closing-label">Branch</label>
                        <select name="branch_id" class="custom-select select2-clear">
                            <option value="">All branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) $branchId === (string) $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="closing-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All</option>
                            <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="submitted" {{ $status === 'submitted' ? 'selected' : '' }}>Submitted</option>
                            <option value="verified" {{ $status === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="needs_recalculation" {{ $status === 'needs_recalculation' ? 'selected' : '' }}>
                                Needs Recalculation
                            </option>
                            <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="closing-label">From</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="closing-label">To</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <div class="d-flex justify-content-end closing-filter-actions" style="gap: 8px;">
                            <a href="{{ route('daily-closings.index') }}" class="btn btn-light">Reset</a>
                            <button class="btn btn-primary" type="submit">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

        <div class="card closing-card">
        <div class="closing-card-header">
            <h5>Closing List</h5>
            <p>
                Drafts can be submitted. Submitted closings can be verified or rejected by Owner.
                If sales, expenses or approved returns happen after verification, the closing will need recalculation.
            </p>
        </div>

        <div class="closing-table-wrap">
            <table class="table closing-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Branch</th>
                        <th>Closed by</th>
                        <th>Sales</th>
                        <th>Expenses</th>
                        <th>Expected Cash</th>
                        <th>Counted Cash</th>
                        <th>Difference</th>
                        <th>Result</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($closings as $index => $closing)
                        @php
                            $statusClass = match ($closing->status) {
                                'verified' => 'badge-green',
                                'submitted' => 'badge-blue',
                                'needs_recalculation' => 'badge-yellow',
                                'rejected' => 'badge-red',
                                default => 'badge-gray',
                            };

                            $resultClass = match ($closing->closing_result) {
                                'balanced' => 'badge-green',
                                'short' => 'badge-red',
                                'over' => 'badge-yellow',
                                default => 'badge-gray',
                            };
                        @endphp

                        <tr>
                            <td>{{ ($closings->firstItem() ?? 0) + $index }}</td>

                            <td>
                                <div class="closing-main">{{ $closing->closing_date?->format('d M Y') }}</div>
                                <div class="closing-sub">{{ $closing->created_at?->timezone('Africa/Dar_es_Salaam')->format('h:i A') }}</div>
                            </td>

                            <td>{{ $closing->branch?->name ?: '-' }}</td>

                            <td>
                                {{ $closing->creator?->displayName() ?: ($closing->creator?->username ?? '-') }}
                            </td>

                            <td>
                                <strong>{{ number_format((float) $closing->total_sales_amount, 2) }}</strong>
                                <div class="closing-sub">Cash: {{ number_format((float) $closing->cash_sales_amount, 2) }}</div>
                            </td>

                            <td>
                                <strong>{{ number_format((float) $closing->total_expenses_amount, 2) }}</strong>
                                <div class="closing-sub">Cash: {{ number_format((float) $closing->cash_expenses_amount, 2) }}</div>
                            </td>

                            <td><strong>{{ number_format((float) $closing->expected_cash_amount, 2) }}</strong></td>
                            <td>{{ number_format((float) $closing->counted_cash_amount, 2) }}</td>
                            <td>{{ number_format((float) $closing->difference_amount, 2) }}</td>

                            <td>
                                <span class="closing-badge {{ $resultClass }}">
                                    {{ ucfirst($closing->closing_result) }}
                                </span>
                            </td>

                            <td>
                                <span class="closing-badge {{ $statusClass }}">
                                    {{ str($closing->status)->replace('_', ' ')->title() }}

                                </span>
                                    @if($closing->status === 'needs_recalculation')
                                        <div class="closing-sub text-warning" style="font-weight: 850;">
                                            Sales/expenses/returns changed after verification
                                        </div>
                                    @endif
                                
                            </td>

                            <td class="text-right">
                                <div class="closing-btn-row">
                                    <button type="button"
                                            class="btn btn-sm btn-light"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewClosingModal{{ $closing->id }}">
                                        View
                                    </button>

                                    @if($isAdminOrOwner && $closing->status === 'needs_recalculation')
                                        @can('daily_closing.verify')
                                            <form method="POST" action="{{ route('daily-closings.recalculate', $closing) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')

                                                <button class="btn btn-sm btn-outline-warning">
                                                    Recalculate
                                                </button>
                                            </form>
                                        @endcan
                                    @endif

                                    @can('daily_closing.submit')
                                        @if(($closing->isDraft() || $closing->isRejected()) && ((int) $closing->created_by === (int) auth()->id() || $isAdminOrOwner))
                                            <form method="POST" action="{{ route('daily-closings.submit', $closing) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')

                                                <button class="btn btn-sm btn-outline-primary">
                                                    Submit
                                                </button>
                                            </form>
                                        @endif
                                    @endcan

                                    @if($isAdminOrOwner && $closing->isSubmitted())
                                        @can('daily_closing.verify')
                                            <form method="POST" action="{{ route('daily-closings.verify', $closing) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')

                                                <button class="btn btn-sm btn-outline-success">
                                                    Verify
                                                </button>
                                            </form>
                                        @endcan

                                        @can('daily_closing.reject')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectClosingModal{{ $closing->id }}">
                                                Reject
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12">
                                <div class="closing-empty">No daily closings found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($closings->hasPages())
            <div class="p-3 border-top">
                {{ $closings->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@can('daily_closing.create')
    <div class="modal fade" id="createClosingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form method="POST" action="{{ route('daily-closings.store') }}" class="modal-content" id="dailyClosingForm">
                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">New Daily Closing</h5>
                        <small class="text-muted">System will calculate sales and expenses automatically.</small>
                    </div>

                    <button type="button" class="btn-close closing-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Branch</label>
                            <select name="branch_id" id="closingBranchId" class="custom-select select2-modal" required>
                                <option value="">Select branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                       

                        <div class="col-md-6 mb-3">
                            <label>Closing Date</label>
                            <input type="date"
                                   name="closing_date"
                                   id="closingDate"
                                   value="{{ now()->toDateString() }}"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                    <div class="closing-calculation-box">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3" style="gap: 10px;">
                            <div>
                                <strong>System Calculation</strong>
                                <div class="closing-sub">Sales, cash refunds, expenses and expected cash are calculated automatically.</div>
                            </div>

                            <button type="button" class="btn btn-sm btn-primary" id="calculateClosingBtn">
                                Calculate
                            </button>
                        </div>

                        <div class="closing-calc-grid">
                            <div class="closing-calc-item">
                                <div class="closing-calc-label">Cash Sales</div>
                                <div class="closing-calc-value" id="calcCashSales">0.00</div>
                            </div>

                            <div class="closing-calc-item">
                                <div class="closing-calc-label">Total Sales</div>
                                <div class="closing-calc-value" id="calcTotalSales">0.00</div>
                            </div>

                            <div class="closing-calc-item">
                                <div class="closing-calc-label">Cash Expenses</div>
                                <div class="closing-calc-value" id="calcCashExpenses">0.00</div>
                            </div>

                            <div class="closing-calc-item">
                                <div class="closing-calc-label">Total Expenses</div>
                                <div class="closing-calc-value" id="calcTotalExpenses">0.00</div>
                            </div>

                            <div class="closing-calc-item">
                                <div class="closing-calc-label">Expected Cash</div>
                                <div class="closing-calc-value" id="calcExpectedCash">0.00</div>
                            </div>

                            <div class="closing-calc-item">
                                <div class="closing-calc-label">Difference</div>
                                <div class="closing-calc-value" id="calcDifference">0.00</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Counted Cash</label>
                            <input type="number"
                                   name="counted_cash_amount"
                                   id="countedCashAmount"
                                   min="0"
                                   step="0.01"
                                   value="0"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label>Notes</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Optional closing notes"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer closing-mobile-stack">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>

                    <button type="submit" name="action" value="draft" class="btn btn-outline-primary">
                        Save Draft
                    </button>

                    <button type="submit" name="action" value="submit" class="btn btn-primary">
                        Submit Closing
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan

@foreach($closings as $closing)
    <div class="modal fade" id="viewClosingModal{{ $closing->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Closing Details</h5>
                        <small class="text-muted">{{ $closing->closing_date?->format('d M Y') }} • {{ $closing->branch?->name }}</small>
                    </div>

                    <button type="button" class="btn-close closing-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="closing-calc-grid">
                        <div class="closing-calc-item"><div class="closing-calc-label">Cash Sales</div><div class="closing-calc-value">{{ number_format((float) $closing->cash_sales_amount, 2) }}</div></div>
                        <div class="closing-calc-item"><div class="closing-calc-label">Mobile Money</div><div class="closing-calc-value">{{ number_format((float) $closing->mobile_money_sales_amount, 2) }}</div></div>
                        <div class="closing-calc-item"><div class="closing-calc-label">Card</div><div class="closing-calc-value">{{ number_format((float) $closing->card_sales_amount, 2) }}</div></div>
                        <div class="closing-calc-item"><div class="closing-calc-label">Bank</div><div class="closing-calc-value">{{ number_format((float) $closing->bank_sales_amount, 2) }}</div></div>
                        <div class="closing-calc-item"><div class="closing-calc-label">Total Sales</div><div class="closing-calc-value">{{ number_format((float) $closing->total_sales_amount, 2) }}</div></div>
                        <div class="closing-calc-item">
                            <div class="closing-calc-label">Returns</div>
                            <div class="closing-calc-value">Deducted</div>
                        </div>
                        <div class="closing-calc-item"><div class="closing-calc-label">Discount</div><div class="closing-calc-value">{{ number_format((float) $closing->total_discount_amount, 2) }}</div></div>
                        <div class="closing-calc-item"><div class="closing-calc-label">Cash Expenses</div><div class="closing-calc-value">{{ number_format((float) $closing->cash_expenses_amount, 2) }}</div></div>
                        <div class="closing-calc-item"><div class="closing-calc-label">Total Expenses</div><div class="closing-calc-value">{{ number_format((float) $closing->total_expenses_amount, 2) }}</div></div>
                        <div class="closing-calc-item"><div class="closing-calc-label">Expected Cash</div><div class="closing-calc-value">{{ number_format((float) $closing->expected_cash_amount, 2) }}</div></div>
                        <div class="closing-calc-item"><div class="closing-calc-label">Counted Cash</div><div class="closing-calc-value">{{ number_format((float) $closing->counted_cash_amount, 2) }}</div></div>
                        <div class="closing-calc-item"><div class="closing-calc-label">Difference</div><div class="closing-calc-value">{{ number_format((float) $closing->difference_amount, 2) }}</div></div>
                        <div class="closing-calc-item"><div class="closing-calc-label">Result</div><div class="closing-calc-value">{{ ucfirst($closing->closing_result) }}</div></div>
                    </div>

                    @if($closing->notes)
                        <hr>
                        <strong>Notes</strong>
                        <p class="mb-0">{{ $closing->notes }}</p>
                    @endif

                    @if($closing->rejection_reason)
                        <hr>
                        <strong>Rejection Reason</strong>
                        <p class="mb-0 text-danger">{{ $closing->rejection_reason }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($isAdminOrOwner && $closing->isSubmitted())
        @can('daily_closing.reject')
            <div class="modal fade" id="rejectClosingModal{{ $closing->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <form method="POST" action="{{ route('daily-closings.reject', $closing) }}" class="modal-content">
                        @csrf
                        @method('PATCH')

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Reject Closing</h5>
                                <small class="text-muted">{{ $closing->closing_date?->format('d M Y') }}</small>
                            </div>

                            <button type="button" class="btn-close closing-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <label>Reason</label>
                            <textarea name="rejection_reason" rows="4" class="form-control" required placeholder="Explain why this closing is rejected"></textarea>
                        </div>

                        <div class="modal-footer closing-mobile-stack">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Reject Closing</button>
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

        const calculateUrl = @json(route('daily-closings.calculate'));
        const csrf = @json(csrf_token());

        let expectedCash = 0;

        function money(value) {
            return Number(value || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function updateDifference() {
            const counted = Number($('#countedCashAmount').val() || 0);
            const diff = counted - expectedCash;
            $('#calcDifference').text(money(diff));
        }

        $('#countedCashAmount').on('input', updateDifference);

        $('#calculateClosingBtn').on('click', function () {
            const branchId = $('#closingBranchId').val();
            const closingDate = $('#closingDate').val();

            if (!branchId || !closingDate) {
                alert('Please select branch and date first.');
                alert('Unable to calculate closing.');
                return;
            }

            const button = $(this);
            button.prop('disabled', true).text('Calculating...');

            fetch(calculateUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    branch_id: branchId,
                    closing_date: closingDate,
                }),
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.ok) {
                        alert('Unable to calculate closing.');
                        return;
                    }

                    const totals = data.totals;

                    expectedCash = Number(totals.expected_cash_amount || 0);

                    $('#calcCashSales').text(money(totals.cash_sales_amount));
                    $('#calcTotalSales').text(money(totals.total_sales_amount));
                    $('#calcCashExpenses').text(money(totals.cash_expenses_amount));
                    $('#calcTotalExpenses').text(money(totals.total_expenses_amount));
                    $('#calcExpectedCash').text(money(totals.expected_cash_amount));

                    updateDifference();
                })
                .catch(() => {
                    alert('Unable to calculate closing.');
                })
                .finally(() => {
                    button.prop('disabled', false).text('Calculate');
                });
        });
    });
</script>
@endpush
@endsection