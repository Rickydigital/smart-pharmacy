@extends('components.main-layout')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')
@section('page-subtitle', 'View system roles and manage permission access safely')

@section('content')
<style>
    .role-header-card {
        border-radius: 16px;
        border: 0;
    }

    .role-card {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
        transition: .18s ease;
        height: 100%;
    }

    .role-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 36px rgba(15, 23, 42, .08);
    }

    .role-icon {
        width: 46px;
        height: 46px;
        border-radius: 15px;
        background: #eff6ff;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 23px;
        flex-shrink: 0;
    }

    .role-name {
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 2px;
    }

    .role-sub {
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
    }

    .permission-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        color: #475569;
        font-size: 12px;
        font-weight: 800;
    }

    .lock-pill {
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .lock-readonly {
        background: #f1f5f9;
        color: #475569;
    }

    .lock-editable {
        background: #dcfce7;
        color: #15803d;
    }

    @media (max-width: 767.98px) {
        .role-header-card .btn {
            width: 100%;
        }
    }
</style>

<div class="card shadow-sm mb-4 role-header-card">
    <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div>
            <h4 class="mb-1 font-weight-bold text-dark">
                <i class="mdi mdi-shield-key-outline text-primary mr-1"></i>
                Roles & Permissions
            </h4>
            <p class="mb-0 text-muted">
                View roles and control employee permissions without changing protected system roles.
            </p>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">
        <i class="mdi mdi-check-circle-outline mr-1"></i>
        {{ session('success') }}
    </div>
@endif

<div class="row">
    @forelse($roles as $role)
        @php
            $editable = ! in_array($role->name, ['Admin', 'Owner'], true);
        @endphp

        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card role-card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="role-icon">
                            @if($role->name === 'Owner')
                                <i class="mdi mdi-crown-outline"></i>
                            @elseif($role->name === 'Pharmacist')
                                <i class="mdi mdi-medical-bag"></i>
                            @elseif($role->name === 'Cashier')
                                <i class="mdi mdi-cash-register"></i>
                            @elseif($role->name === 'Storekeeper')
                                <i class="mdi mdi-warehouse"></i>
                            @else
                                <i class="mdi mdi-account-key-outline"></i>
                            @endif
                        </div>

                        @if($editable)
                            <span class="lock-pill lock-editable">Editable</span>
                        @else
                            <span class="lock-pill lock-readonly">Read only</span>
                        @endif
                    </div>

                    <h5 class="role-name">{{ $role->name }}</h5>
                    <div class="role-sub mb-3">
                        {{ $editable ? 'Employee role permissions can be updated.' : 'Protected role permissions cannot be edited here.' }}
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="permission-count">
                            <i class="mdi mdi-shield-check-outline"></i>
                            {{ $role->permissions->count() }} permissions
                        </span>

                        <a href="{{ route('roles.show', $role) }}" class="btn btn-sm btn-primary">
                            View
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="mdi mdi-shield-search-outline d-block mb-2" style="font-size: 38px;"></i>
                    No roles found.
                </div>
            </div>
        </div>
    @endforelse
</div>
@endsection