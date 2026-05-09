@extends('components.main-layout')

@section('title', 'Users')
@section('page-title', 'Users')
@section('page-subtitle', 'Manage pharmacy employees and access permissions')

@section('content')
@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $users */
@endphp

<style>
    .users-page {
        --line: #e9edf5;
        --soft: #f8fafc;
        --text: #0f172a;
        --muted: #64748b;
        --primary: #2563eb;
        --primary-soft: #eff6ff;
        --shadow: 0 14px 34px rgba(15, 23, 42, 0.06);
    }

    .users-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid var(--line);
        border-radius: 22px;
        padding: 22px 24px;
        margin-bottom: 18px;
        box-shadow: var(--shadow);
    }

    .users-hero-title {
        margin: 0;
        color: var(--text);
        font-weight: 900;
        letter-spacing: -.03em;
        font-size: 1.35rem;
    }

    .users-hero-subtitle {
        margin: 6px 0 0;
        color: var(--muted);
        font-size: 14px;
        font-weight: 600;
    }

    .users-panel {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 22px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .users-panel-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--line);
        background: linear-gradient(180deg, #ffffff, #fbfcfe);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .users-panel-title {
        margin: 0;
        color: var(--text);
        font-weight: 900;
        font-size: 1.03rem;
    }

    .users-panel-desc {
        margin: 4px 0 0;
        color: var(--muted);
        font-size: 13px;
        font-weight: 600;
    }

    .users-toolbar {
        padding: 18px 22px 10px;
        border-bottom: 1px solid var(--line);
        background: #fff;
    }

    .users-toolbar .form-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #475569;
        font-weight: 900;
        margin-bottom: 7px;
    }

    .users-toolbar .form-control,
    .users-toolbar .custom-select {
        min-height: 44px;
        border-radius: 14px;
        border-color: #d9e2ef;
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
    }

    .users-toolbar .form-control:focus,
    .users-toolbar .custom-select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 .18rem rgba(37, 99, 235, .12);
    }

    .users-toolbar .btn {
        min-height: 44px;
        border-radius: 14px;
        font-weight: 800;
        white-space: nowrap;
    }

    .users-summary {
        padding: 0 22px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        background: #fff;
    }

    .summary-chip-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .summary-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid var(--line);
        border-radius: 999px;
        padding: 8px 12px;
        background: #fff;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
    }

    .summary-chip i {
        color: var(--primary);
        font-size: 16px;
    }

    .table-wrap {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        background: #fff;
    }

    .data-grid {
        width: 100%;
        min-width: 1080px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .data-grid thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8fafc;
        border-top: 0;
        border-bottom: 1px solid var(--line);
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 900;
        white-space: nowrap;
        padding: 14px 14px;
    }

    .data-grid tbody td {
        border-top: 0;
        border-bottom: 1px solid #eef2f7;
        padding: 14px 14px;
        vertical-align: middle;
        font-size: 13px;
        color: #334155;
        font-weight: 700;
        background: #fff;
    }

    .data-grid tbody tr:hover td {
        background: #fbfdff;
    }

    .emp-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 220px;
    }

    .emp-avatar {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: var(--primary-soft);
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 13px;
        flex-shrink: 0;
        border: 1px solid #dbeafe;
    }

    .emp-name {
        font-weight: 900;
        color: var(--text);
        line-height: 1.15;
    }

    .emp-sub {
        font-size: 12px;
        color: var(--muted);
        margin-top: 3px;
        font-weight: 700;
    }

    .soft-badge {
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .soft-role {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .soft-active {
        background: #dcfce7;
        color: #15803d;
    }

    .soft-inactive {
        background: #f1f5f9;
        color: #475569;
    }

    .soft-blocked {
        background: #fee2e2;
        color: #b91c1c;
    }

    .actions-bar {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex-wrap: nowrap;
    }

    .actions-bar .btn {
        border-radius: 12px;
        font-weight: 800;
        white-space: nowrap;
        padding: .4rem .7rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .actions-bar .btn i {
        font-size: 15px;
    }

    .empty-state {
        padding: 46px 20px;
        text-align: center;
        color: var(--muted);
    }

    .empty-state i {
        display: block;
        font-size: 36px;
        margin-bottom: 10px;
        color: #94a3b8;
    }

    .pagination-zone {
        padding: 16px 22px;
        border-top: 1px solid var(--line);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .pagination-zone .small {
        font-weight: 700;
        color: var(--muted);
    }

    .pagination-zone .pagination {
        margin-bottom: 0;
    }

    .pagination-zone nav {
        max-width: 100%;
    }

    .modal-content {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
    }

    .modal-header {
        background: linear-gradient(135deg, #eff6ff, #ffffff);
        border-bottom: 1px solid var(--line);
        padding: 18px 22px;
    }

    .modal-title {
        font-weight: 900;
        color: var(--text);
    }

    .modal-body {
        padding: 22px;
    }

    .modal-footer {
        border-top: 1px solid var(--line);
        padding: 16px 22px;
    }

    .modal-body .form-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 900;
        color: #475569;
    }

    .modal-body .form-control,
    .modal-body .custom-select {
        min-height: 44px;
        border-radius: 14px;
        border-color: #d9e2ef;
        font-weight: 700;
    }

    .info-alert {
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1e40af;
        border-radius: 16px;
        padding: 12px 14px;
        font-weight: 700;
        font-size: 13px;
    }

    .light-alert {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #334155;
        border-radius: 16px;
        padding: 12px 14px;
        font-weight: 700;
        font-size: 13px;
    }

    .warn-alert {
        border: 1px solid #fde68a;
        background: #fffbeb;
        color: #92400e;
        border-radius: 16px;
        padding: 12px 14px;
        font-weight: 700;
        font-size: 13px;
    }

    @media (max-width: 991.98px) {
        .users-hero,
        .users-panel-header,
        .users-toolbar,
        .users-summary,
        .pagination-zone {
            padding-left: 16px;
            padding-right: 16px;
        }

        .data-grid {
            min-width: 980px;
        }
    }

    @media (max-width: 767.98px) {
        .users-hero {
            padding: 18px 16px;
            border-radius: 18px;
        }

        .users-hero-title {
            font-size: 1.15rem;
        }

        .users-panel {
            border-radius: 18px;
        }

        .users-panel-header {
            padding: 16px;
        }

        .users-panel-header .btn {
            width: 100%;
        }

        .users-toolbar {
            padding: 14px 16px 8px;
        }

        .users-summary {
            padding: 0 16px 12px;
        }

        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-grid {
            min-width: 900px;
        }

        .data-grid thead th,
        .data-grid tbody td {
            padding: 10px 10px;
        }

        .data-grid thead th {
            font-size: 10px;
        }

        .data-grid tbody td {
            font-size: 12px;
        }

        .emp-avatar {
            width: 34px;
            height: 34px;
            border-radius: 11px;
            font-size: 11px;
        }

        .emp-cell {
            gap: 9px;
            min-width: 190px;
        }

        .emp-name {
            font-size: 12px;
        }

        .emp-sub {
            font-size: 11px;
        }

        .soft-badge {
            padding: 5px 8px;
            font-size: 10px;
        }

        .actions-bar .btn {
            font-size: 11px;
            padding: .32rem .52rem;
            gap: 4px;
            border-radius: 10px;
        }

        .actions-bar .btn i {
            font-size: 13px;
        }

        .pagination-zone {
            align-items: flex-start;
        }

        .pagination-zone nav {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .modal-dialog {
            margin: .75rem;
        }
    }

     .employee-header-card {
        border-radius: 16px;
    }

    .employee-header-card .card-body {
        padding: 20px 22px;
    }

    @media (max-width: 767.98px) {
        .employee-header-card .btn {
            width: 100%;
        }
    }
</style>

<div class="users-page">
 <div class="card border-0 shadow-sm mb-4 employee-header-card">
    <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div>
            <h4 class="mb-1 font-weight-bold text-dark">
                <i class="mdi mdi-account-group-outline text-primary mr-1"></i>
                Employee Management
            </h4>
            <p class="mb-0 text-muted">
                Manage pharmacy employees, roles, branches, and account access.
            </p>
        </div>

        @can('user.manage')
            <button type="button"
                    class="btn btn-primary mt-3 mt-md-0"
                    data-toggle="modal"
                    data-target="#createUserModal">
                <i class="mdi mdi-plus mr-1"></i>
                Add Employee
            </button>
        @endcan
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

    <div class="users-panel">

        <div class="users-toolbar">
            <form method="GET" action="{{ route('users.index') }}">
                <div class="row">
                    <div class="col-lg-5 col-md-12 mb-3">
                        <label class="form-label">Search</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control"
                               placeholder="Search by name, username, email or phone">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="custom-select">
                            <option value="">All roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="custom-select">
                            <option value="">All statuses</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-12 mb-3 d-flex align-items-end">
                        <div class="w-100 d-flex" style="gap:8px;">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="mdi mdi-filter-outline mr-1"></i>
                                Filter
                            </button>

                            <a href="{{ route('users.index') }}" class="btn btn-light flex-fill">
                                Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>


        <div class="table-wrap">
            <table class="table data-grid">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email / Phone</th>
                        <th>Branch</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $index => $user)
                        @php
                            $roleName = $user->roles->first()?->name ?? 'No Role';
                            $initials = strtoupper(substr($user->first_name ?? 'U', 0, 1) . substr($user->last_name ?? '', 0, 1));
                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>
                                <div class="emp-cell">
                                    <span class="emp-avatar">{{ $initials }}</span>
                                    <div>
                                        <div class="emp-name">{{ $user->displayName() }}</div>
                                        <div class="emp-sub">{{ $user->username }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div>{{ $user->email }}</div>
                                <div class="emp-sub">{{ $user->phone ?: 'No phone' }}</div>
                            </td>

                            <td>{{ $user->branch?->name ?: 'No branch' }}</td>

                            <td>
                                <span class="soft-badge soft-role">{{ $roleName }}</span>
                            </td>

                            <td>
                                @if($user->status === 'active')
                                    <span class="soft-badge soft-active">Active</span>
                                @elseif($user->status === 'blocked')
                                    <span class="soft-badge soft-blocked">Blocked</span>
                                @else
                                    <span class="soft-badge soft-inactive">Inactive</span>
                                @endif
                            </td>

                            <td>{{ $user->created_at?->format('M d, Y') }}</td>

                            <td class="text-right">
                                <div class="actions-bar">
                                    <button type="button"
                                            class="btn btn-light btn-sm"
                                            data-toggle="modal"
                                            data-target="#showUserModal{{ $user->id }}">
                                        <i class="mdi mdi-eye-outline"></i>
                                        <span>View</span>
                                    </button>

                                    @can('user.manage')
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-toggle="modal"
                                                data-target="#editUserModal{{ $user->id }}">
                                            <i class="mdi mdi-pencil-outline"></i>
                                            <span>Edit</span>
                                        </button>

                                        <button type="button"
                                                class="btn btn-outline-warning btn-sm"
                                                data-toggle="modal"
                                                data-target="#resetPasswordModal{{ $user->id }}">
                                            <i class="mdi mdi-lock-reset"></i>
                                            <span>Reset</span>
                                        </button>

                                        <form method="POST" action="{{ route('users.toggle', $user) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="btn btn-outline-{{ $user->status === 'active' ? 'danger' : 'success' }} btn-sm">
                                                <i class="mdi {{ $user->status === 'active' ? 'mdi-account-off-outline' : 'mdi-account-check-outline' }}"></i>
                                                <span>{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}</span>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="mdi mdi-account-search-outline"></i>
                                    No employee users found.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-zone">
            <small class="small">
                Paginated employee listing
            </small>

            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can('user.manage')
    <div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('users.store') }}" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-account-plus-outline mr-1"></i>
                        Create Employee
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="info-alert mb-3">
                        <i class="mdi mdi-information-outline mr-1"></i>
                        Username and password will be generated automatically and sent to the employee email.
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="custom-select">
                                <option value="">No branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="custom-select" required>
                                <option value="">Select role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-0">
                            <label class="form-label">Status</label>
                            <select name="status" class="custom-select" required>
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="blocked" {{ old('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save-outline mr-1"></i>
                        Save Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan

@foreach($users as $user)
    @php
        $roleName = $user->roles->first()?->name ?? 'No Role';
        $initials = strtoupper(substr($user->first_name ?? 'U', 0, 1) . substr($user->last_name ?? '', 0, 1));
    @endphp

    <div class="modal fade" id="showUserModal{{ $user->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Employee Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="emp-cell mb-3">
                        <span class="emp-avatar">{{ $initials }}</span>
                        <div>
                            <div class="emp-name">{{ $user->displayName() }}</div>
                            <div class="emp-sub">{{ $user->username }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <small class="text-muted font-weight-bold">Email</small>
                            <div class="font-weight-bold">{{ $user->email }}</div>
                        </div>

                        <div class="col-6 mb-3">
                            <small class="text-muted font-weight-bold">Phone</small>
                            <div class="font-weight-bold">{{ $user->phone ?: 'No phone' }}</div>
                        </div>

                        <div class="col-6 mb-3">
                            <small class="text-muted font-weight-bold">Branch</small>
                            <div class="font-weight-bold">{{ $user->branch?->name ?: 'No branch' }}</div>
                        </div>

                        <div class="col-6 mb-3">
                            <small class="text-muted font-weight-bold">Role</small>
                            <div><span class="soft-badge soft-role">{{ $roleName }}</span></div>
                        </div>

                        <div class="col-6 mb-0">
                            <small class="text-muted font-weight-bold">Status</small>
                            <div>
                                @if($user->status === 'active')
                                    <span class="soft-badge soft-active">Active</span>
                                @elseif($user->status === 'blocked')
                                    <span class="soft-badge soft-blocked">Blocked</span>
                                @else
                                    <span class="soft-badge soft-inactive">Inactive</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-6 mb-0">
                            <small class="text-muted font-weight-bold">Created</small>
                            <div class="font-weight-bold">{{ $user->created_at?->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('user.manage')
        <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('users.update', $user) }}" class="modal-content">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Employee</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="light-alert mb-3">
                            <i class="mdi mdi-account-key-outline mr-1"></i>
                            Username: <strong>{{ $user->username }}</strong>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Branch</label>
                                <select name="branch_id" class="custom-select">
                                    <option value="">No branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="custom-select" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('role', $roleName) == $role->name ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-0">
                                <label class="form-label">Status</label>
                                <select name="status" class="custom-select" required>
                                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="blocked" {{ old('status', $user->status) == 'blocked' ? 'selected' : '' }}>Blocked</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline mr-1"></i>
                            Update Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="resetPasswordModal{{ $user->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('users.password', $user) }}" class="modal-content">
                    @csrf
                    @method('PATCH')

                    <div class="modal-header">
                        <h5 class="modal-title">Reset Password</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-2">
                            Generate a new password for <strong>{{ $user->displayName() }}</strong>?
                        </p>

                        <div class="warn-alert mb-0">
                            <i class="mdi mdi-alert-outline mr-1"></i>
                            The new login details will be sent to the employee email.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Reset & Send</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endforeach
@endsection