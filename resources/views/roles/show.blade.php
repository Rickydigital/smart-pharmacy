@extends('components.main-layout')

@section('title', 'Role Permissions')
@section('page-title', 'Role Permissions')
@section('page-subtitle', 'View and manage permission access for employee roles')

@section('content')
<style>
    .role-detail-card {
        border-radius: 16px;
        border: 0;
    }

    .permission-group-card {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
        overflow: hidden;
        height: 100%;
    }

    .permission-group-header {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .permission-group-title {
        margin: 0;
        font-weight: 900;
        color: #0f172a;
        text-transform: capitalize;
    }

    .permission-list {
        padding: 14px 16px;
    }

    .permission-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px dashed #eef2f7;
    }

    .permission-item:last-child {
        border-bottom: 0;
    }

    .permission-name {
        font-weight: 700;
        color: #334155;
        font-size: 13px;
    }

    .permission-code {
        color: #64748b;
        font-size: 12px;
    }

    .permission-checkbox {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    .readonly-note {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 14px;
        font-weight: 700;
        font-size: 13px;
    }

    .editable-note {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        border-radius: 14px;
        padding: 12px 14px;
        font-weight: 700;
        font-size: 13px;
    }

    @media (max-width: 767.98px) {
        .role-actions {
            width: 100%;
        }

        .role-actions .btn {
            width: 100%;
            margin-top: 8px;
        }
    }
</style>

<div class="card shadow-sm mb-4 role-detail-card">
    <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div>
            <h4 class="mb-1 font-weight-bold text-dark">
                <i class="mdi mdi-shield-key-outline text-primary mr-1"></i>
                {{ $role->name }} Permissions
            </h4>

            <p class="mb-0 text-muted">
                @if($canEditPermissions)
                    You can update permissions for this employee role.
                @else
                    This role is protected and can only be viewed.
                @endif
            </p>
        </div>

        <div class="role-actions mt-3 mt-md-0">
            <a href="{{ route('roles.index') }}" class="btn btn-light">
                <i class="mdi mdi-arrow-left mr-1"></i>
                Back
            </a>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">
        <i class="mdi mdi-check-circle-outline mr-1"></i>
        {{ session('success') }}
    </div>
@endif

@if($canEditPermissions)
    <div class="editable-note mb-4">
        <i class="mdi mdi-information-outline mr-1"></i>
        Only permissions are editable. Role name and protected roles are not editable from this screen.
    </div>
@else
    <div class="readonly-note mb-4">
        <i class="mdi mdi-lock-outline mr-1"></i>
        This role is read-only. Permissions are shown for visibility only.
    </div>
@endif

<form method="POST" action="{{ route('roles.permissions.update', $role) }}">
    @csrf
    @method('PUT')

    <div class="row">
        @foreach($permissions as $group => $items)
            <div class="col-xl-4 col-lg-6 mb-4">
                <div class="permission-group-card">
                    <div class="permission-group-header">
                        <h6 class="permission-group-title">{{ str_replace('_', ' ', $group) }}</h6>
                        <span class="badge badge-light">{{ $items->count() }}</span>
                    </div>

                    <div class="permission-list">
                        @foreach($items as $permission)
                            <label class="permission-item mb-0">
                                <input type="checkbox"
                                       class="permission-checkbox"
                                       name="permissions[]"
                                       value="{{ $permission->name }}"
                                       {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                       {{ $canEditPermissions ? '' : 'disabled' }}>

                                <span>
                                    <span class="permission-name d-block">
                                        {{ str($permission->name)->after('.')->replace('_', ' ')->title() }}
                                    </span>
                                    <span class="permission-code">
                                        {{ $permission->name }}
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($canEditPermissions)
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <div class="text-muted font-weight-bold mb-3 mb-md-0">
                    Save changes only after confirming this role should receive these permissions.
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="mdi mdi-content-save-outline mr-1"></i>
                    Save Permissions
                </button>
            </div>
        </div>
    @endif
</form>
@endsection