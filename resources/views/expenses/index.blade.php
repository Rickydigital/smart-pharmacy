@extends('components.main-layout')

@section('title', 'Expenses')
@section('page-title', 'Expenses')
@section('page-subtitle', 'Track operating expenses, categories, payments and voided records')

@section('content')
<style>
    .expense-page {
        max-width: 100%;
        overflow-x: hidden;
    }

    .expense-hero,
    .expense-card,
    .expense-filter-card,
    .expense-stat {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .07);
    }

    .expense-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .expense-hero .card-body {
        padding: 22px;
    }

    .expense-icon {
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

    .expense-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.02em;
    }

    .expense-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 0;
    }

    .expense-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .expense-actions .btn,
    .expense-filter-actions .btn,
    .expense-filter-actions a,
    .expense-btn-row .btn {
        border-radius: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .expense-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .expense-stat {
        padding: 14px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .expense-stat-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 4px;
    }

    .expense-stat-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 22px;
        line-height: 1;
    }

    .expense-filter-card {
        margin-bottom: 16px;
        border: 1px solid #e8edf5;
    }

    .expense-filter-card .card-body {
        padding: 16px;
    }

    .expense-label,
    .modal-body label {
        font-size: 11px;
        font-weight: 950;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .expense-filter-card .form-control,
    .expense-filter-card .custom-select,
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

    .expense-card {
        overflow: hidden;
    }

    .expense-card-header {
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .expense-card-header h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
    }

    .expense-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .expense-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .expense-table {
        min-width: 1150px;
        margin-bottom: 0;
    }

    .expense-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .expense-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .expense-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .expense-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 3px;
        font-weight: 700;
    }

    .expense-badge {
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

    .badge-green {
        background: #dcfce7;
        color: #15803d;
    }

    .badge-blue {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .badge-gray {
        background: #f1f5f9;
        color: #475569;
    }

    .badge-red {
        background: #fee2e2;
        color: #b91c1c;
    }

    .badge-yellow {
        background: #fef3c7;
        color: #92400e;
    }

    .expense-btn-row {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .expense-empty {
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

    .expense-modal-close {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background-color: #ffffff;
        opacity: 1;
        box-shadow: none;
    }

    .expense-modal-close:hover {
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

    @media (max-width: 767.98px) {
        .expense-hero .card-body {
            padding: 18px 16px;
        }

        .expense-hero-main {
            align-items: flex-start !important;
        }

        .expense-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
        }

        .expense-actions .btn {
            width: 100%;
        }

        .expense-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .expense-filter-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px !important;
        }

        .expense-filter-actions .btn,
        .expense-filter-actions a {
            width: 100%;
            text-align: center;
        }

        .expense-table {
            min-width: 1000px;
        }

        .expense-table th,
        .expense-table td {
            padding: 10px;
            font-size: 12px;
        }

        .expense-btn-row .btn {
            font-size: 11px;
            padding: .32rem .55rem;
        }

        .modal-dialog {
            margin: .65rem;
        }

        .modal-body {
            padding: 15px;
            max-height: calc(100vh - 165px);
        }

        .modal-header,
        .modal-footer {
            padding: 14px 15px;
        }

        .expense-mobile-stack {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: 8px !important;
        }

        .expense-mobile-stack .btn {
            width: 100%;
        }
    }
</style>

<div class="container-fluid expense-page">
    <div class="card expense-hero mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between expense-hero-main"
                style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 13px;">
                    <span class="expense-icon">
                        <i class="mdi mdi-cash-minus"></i>
                    </span>

                    <div>
                        <h4 class="expense-title">Expenses</h4>
                        <p class="expense-subtitle">
                            Record branch expenses, manage categories and track operating costs.
                        </p>
                    </div>
                </div>

                <div class="expense-actions">
                    @can('expense_category.manage')
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#categoryModal">
                        <i class="mdi mdi-shape-outline mr-1"></i>
                        Categories
                    </button>
                    @endcan

                    @can('expense.create')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#createExpenseModal">
                        <i class="mdi mdi-plus-circle-outline mr-1"></i>
                        Add Expense
                    </button>
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

    <div class="expense-stat-grid">
        <div class="expense-stat">
            <div class="expense-stat-label">Records</div>
            <div class="expense-stat-value">{{ number_format($summary['count'] ?? 0) }}</div>
        </div>

        <div class="expense-stat">
            <div class="expense-stat-label">Paid Total</div>
            <div class="expense-stat-value">{{ number_format((float) ($summary['paid_total'] ?? 0), 2) }}</div>
        </div>

        <div class="expense-stat">
            <div class="expense-stat-label">Voided Total</div>
            <div class="expense-stat-value">{{ number_format((float) ($summary['voided_total'] ?? 0), 2) }}</div>
        </div>

        <div class="expense-stat">
            <div class="expense-stat-label">Today</div>
            <div class="expense-stat-value">{{ number_format((float) ($summary['today_total'] ?? 0), 2) }}</div>
        </div>
    </div>

    <div class="card expense-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('expenses.index') }}">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="expense-label">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Expense no, title, reference, category">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="expense-label">Branch</label>
                        <select name="branch_id" class="custom-select select2-clear">
                            <option value="">All branches</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id')==$branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="expense-label">Category</label>
                        <select name="expense_category_id" class="custom-select select2-clear">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('expense_category_id')==$category->id ?
                                'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-1 col-md-6 mb-3">
                        <label class="expense-label">Payment</label>
                        <select name="payment_method" class="custom-select">
                            <option value="">All</option>
                            <option value="cash" {{ request('payment_method')==='cash' ? 'selected' : '' }}>Cash
                            </option>
                            <option value="mobile_money" {{ request('payment_method')==='mobile_money' ? 'selected' : ''
                                }}>Mobile</option>
                            <option value="card" {{ request('payment_method')==='card' ? 'selected' : '' }}>Card
                            </option>
                            <option value="bank" {{ request('payment_method')==='bank' ? 'selected' : '' }}>Bank
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-1 col-md-6 mb-3">
                        <label class="expense-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All</option>
                            <option value="paid" {{ request('status')==='paid' ? 'selected' : '' }}>Paid</option>
                            <option value="voided" {{ request('status')==='voided' ? 'selected' : '' }}>Voided</option>
                        </select>
                    </div>

                    <div class="col-lg-1 col-md-6 mb-3">
                        <label class="expense-label">From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>

                    <div class="col-lg-1 col-md-6 mb-3">
                        <label class="expense-label">To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>

                    <div class="col-lg-1 col-md-6 mb-3">
                        <div class="d-flex justify-content-end expense-filter-actions" style="gap: 8px;">
                            @if(request()->query())
                            <a href="{{ route('expenses.index') }}" class="btn btn-light">Clear</a>
                            @endif

                            <button class="btn btn-primary" type="submit">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card expense-card">
        <div class="expense-card-header">
            <h5>Expense List</h5>
            <p>All branch operating expenses and voided records.</p>
        </div>

        <div class="expense-table-wrap">
            <table class="table expense-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Expense</th>
                        <th>Category</th>
                        <th>Branch</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Recorded By</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($expenses as $index => $expense)
                    @php
                    $statusClass = $expense->status === 'paid' ? 'badge-green' : 'badge-red';

                    $isOwnerOrAdmin = auth()->user()?->hasAnyRole(['Owner', 'Admin']) ?? false;
                    $isTodayExpense = $expense->expense_date?->isToday() ?? false;

                    $canEditExpense = $expense->status === 'paid'
                    && ($isOwnerOrAdmin || $isTodayExpense);

                    $canVoidExpense = $expense->status === 'paid'
                    && ($isOwnerOrAdmin || $isTodayExpense);
                    @endphp

                    <tr>
                        <td>{{ ($expenses->firstItem() ?? 0) + $index }}</td>

                        <td>
                            <div class="expense-main">{{ $expense->expense_no }}</div>
                            <div class="expense-sub">{{ $expense->title }}</div>
                        </td>

                        <td>
                            <div>{{ $expense->category?->name ?: '-' }}</div>
                            <div class="expense-sub">{{ $expense->category?->code ?: '-' }}</div>
                        </td>

                        <td>{{ $expense->branch?->name ?: '-' }}</td>

                        <td>
                            <strong>{{ number_format((float) $expense->amount, 2) }}</strong>
                        </td>

                        <td>{{ str_replace('_', ' ', ucfirst($expense->payment_method)) }}</td>

                        <td>{{ $expense->reference_no ?: '-' }}</td>

                        <td>
                            <span class="expense-badge {{ $statusClass }}">
                                {{ ucfirst($expense->status) }}
                            </span>
                        </td>

                        <td>{{ $expense->expense_date?->format('d M Y') }}</td>

                        <td>{{ $expense->creator?->displayName() ?? $expense->creator?->name ??
                            $expense->creator?->username ?? '-' }}</td>

                        <td class="text-right">
                            <div class="expense-btn-row">
                                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal"
                                    data-bs-target="#viewExpenseModal{{ $expense->id }}">
                                    View
                                </button>

                                @can('expense.update')
                                @if($canEditExpense)
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#editExpenseModal{{ $expense->id }}">
                                    Edit
                                </button>
                                @endif
                                @endcan

                                @can('expense.void')
                                @if($canVoidExpense)
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#voidExpenseModal{{ $expense->id }}">
                                    Void
                                </button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11">
                            <div class="expense-empty">
                                No expenses found.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
        <div class="p-3 border-top">
            {{ $expenses->links('vendor.pagination.bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

@can('expense.create')
<div class="modal fade" id="createExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ route('expenses.store') }}" class="modal-content">
            @csrf

            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Add Expense</h5>
                    <small class="text-muted">Record a paid operating expense</small>
                </div>

                <button type="button" class="btn-close expense-modal-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Branch</label>
                        <select name="branch_id" class="custom-select select2-modal" required>
                            <option value="">Select branch</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Category</label>
                        <select name="expense_category_id" class="custom-select select2-modal" required>
                            <option value="">Select category</option>
                            @foreach($activeCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Date</label>
                        <input type="date" name="expense_date" class="form-control" value="{{ now()->toDateString() }}"
                            required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Payment Method</label>
                        <select name="payment_method" class="custom-select" required>
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="card">Card</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Example: Electricity bill"
                            required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Amount</label>
                        <input type="number" min="0.01" step="0.01" name="amount" class="form-control" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Reference No</label>
                        <input type="text" name="reference_no" class="form-control"
                            placeholder="Optional payment reference">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Notes</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="Optional notes"></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer expense-mobile-stack">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Expense</button>
            </div>
        </form>
    </div>
</div>
@endcan

@can('expense_category.manage')
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Expense Categories</h5>
                    <small class="text-muted">Manage reusable expense categories</small>
                </div>

                <button type="button" class="btn-close expense-modal-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form method="POST" action="{{ route('expenses.categories.store') }}" class="mb-4">
                    @csrf

                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Example: Fuel" required>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label>Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Optional">
                        </div>

                        <div class="col-md-1 mb-3">
                            <label>Active</label>
                            <select name="is_active" class="custom-select">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <button type="submit" class="btn btn-primary w-100">Add</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->code }}</td>
                                <td>{{ $category->description ?: '-' }}</td>
                                <td>
                                    <span
                                        class="expense-badge {{ $category->is_active ? 'badge-green' : 'badge-gray' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('expenses.categories.toggle', $category) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('PATCH')

                                        <button class="btn btn-sm btn-outline-primary">
                                            {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach

                            @if($categories->isEmpty())
                            <tr>
                                <td colspan="5">
                                    <div class="expense-empty">No categories found.</div>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endcan

@foreach($expenses as $expense)
<div class="modal fade" id="viewExpenseModal{{ $expense->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">{{ $expense->expense_no }}</h5>
                    <small class="text-muted">{{ $expense->title }}</small>
                </div>

                <button type="button" class="btn-close expense-modal-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <table class="table table-sm table-bordered mb-0">
                    <tr>
                        <th>Category</th>
                        <td>{{ $expense->category?->name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Branch</th>
                        <td>{{ $expense->branch?->name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <td>{{ $expense->expense_date?->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td>{{ number_format((float) $expense->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Payment</th>
                        <td>{{ str_replace('_', ' ', ucfirst($expense->payment_method)) }}</td>
                    </tr>
                    <tr>
                        <th>Reference</th>
                        <td>{{ $expense->reference_no ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{ ucfirst($expense->status) }}</td>
                    </tr>
                    <tr>
                        <th>Notes</th>
                        <td>{{ $expense->notes ?: '-' }}</td>
                    </tr>

                    @if($expense->isVoided())
                    <tr>
                        <th>Void Reason</th>
                        <td>{{ $expense->void_reason ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Voided At</th>
                        <td>{{ $expense->voided_at?->format('d M Y h:i A') ?: '-' }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@can('expense.update')
 @if($canEditExpense)
<div class="modal fade" id="editExpenseModal{{ $expense->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ route('expenses.update', $expense) }}" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Edit Expense</h5>
                    <small class="text-muted">{{ $expense->expense_no }}</small>
                </div>

                <button type="button" class="btn-close expense-modal-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Branch</label>
                        <select name="branch_id" class="custom-select select2-modal" required>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $expense->branch_id == $branch->id ? 'selected' : ''
                                }}>
                                {{ $branch->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Category</label>
                        <select name="expense_category_id" class="custom-select select2-modal" required>
                            @foreach($activeCategories as $category)
                            <option value="{{ $category->id }}" {{ $expense->expense_category_id == $category->id ?
                                'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Date</label>
                        <input type="date" name="expense_date" class="form-control"
                            value="{{ $expense->expense_date?->toDateString() }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Payment Method</label>
                        <select name="payment_method" class="custom-select" required>
                            <option value="cash" {{ $expense->payment_method === 'cash' ? 'selected' : '' }}>Cash
                            </option>
                            <option value="mobile_money" {{ $expense->payment_method === 'mobile_money' ? 'selected' :
                                '' }}>Mobile Money</option>
                            <option value="card" {{ $expense->payment_method === 'card' ? 'selected' : '' }}>Card
                            </option>
                            <option value="bank" {{ $expense->payment_method === 'bank' ? 'selected' : '' }}>Bank
                            </option>
                        </select>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" value="{{ $expense->title }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Amount</label>
                        <input type="number" min="0.01" step="0.01" name="amount" class="form-control"
                            value="{{ $expense->amount }}" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Reference No</label>
                        <input type="text" name="reference_no" class="form-control"
                            value="{{ $expense->reference_no }}">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Notes</label>
                        <textarea name="notes" rows="3" class="form-control">{{ $expense->notes }}</textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer expense-mobile-stack">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update Expense</button>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

@can('expense.void')
    @if($canVoidExpense)
<div class="modal fade" id="voidExpenseModal{{ $expense->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ route('expenses.void', $expense) }}" class="modal-content">
            @csrf
            @method('PATCH')

            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Void Expense</h5>
                    <small class="text-muted">{{ $expense->expense_no }}</small>
                </div>

                <button type="button" class="btn-close expense-modal-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-warning">
                    Voiding keeps this record for audit but removes it from paid expense totals.
                </div>

                <label>Void Reason</label>
                <textarea name="void_reason" rows="4" class="form-control" required
                    placeholder="Example: wrong entry, duplicate expense, incorrect amount"></textarea>
            </div>

            <div class="modal-footer expense-mobile-stack">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger">Void Expense</button>
            </div>
        </form>
    </div>
</div>
@endif
@endcan
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
</script>
@endpush
@endsection