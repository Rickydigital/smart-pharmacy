@extends('components.main-layout')

@section('title', 'Pharmacy Setup')
@section('page-title', 'Pharmacy Setup')
@section('page-subtitle', 'Manage pharmacy profile, branches and system settings')

@section('content')
@php
    $activeBranches = $branches->where('is_active', true)->count();
    $inactiveBranches = $branches->where('is_active', false)->count();
    $mainBranches = $branches->where('is_main', true)->count();

    $logoUrl = $pharmacy->logo_path
        ? asset('storage/' . $pharmacy->logo_path)
        : null;
@endphp

<style>
    .setup-page {
        display: grid;
        gap: 16px;
    }

    .setup-hero {
        border: 1px solid #e2e8f0;
        border-radius: 26px;
        background: linear-gradient(135deg, #ffffff, #f3f8ff);
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .06);
    }

    .setup-hero-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .setup-logo {
        width: 64px;
        height: 64px;
        border-radius: 22px;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        color: #155dfc;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        font-size: 25px;
        font-weight: 950;
        flex-shrink: 0;
    }

    .setup-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .setup-hero h3 {
        margin: 0;
        color: #0f172a;
        font-size: 1.45rem;
        font-weight: 950;
        letter-spacing: -.04em;
    }

    .setup-hero p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
        line-height: 1.45;
    }

    .setup-actions {
        display: flex;
        align-items: center;
        gap: 9px;
        flex-wrap: wrap;
    }

    .setup-btn {
        min-height: 42px;
        border-radius: 14px;
        padding: 9px 14px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #334155;
        font-size: 13px;
        font-weight: 850;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .035);
        transition: .16s ease;
        white-space: nowrap;
    }

    .setup-btn:hover {
        color: #155dfc;
        background: #eff6ff;
        border-color: #bfdbfe;
        text-decoration: none;
    }

    .setup-btn-primary {
        background: #155dfc;
        color: #fff;
        border-color: #155dfc;
        box-shadow: 0 14px 28px rgba(37, 99, 235, .20);
    }

    .setup-btn-primary:hover {
        background: #0f4fd8;
        border-color: #0f4fd8;
        color: #fff;
    }

    .setup-status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 7px 11px;
        font-size: 11px;
        font-weight: 950;
        white-space: nowrap;
    }

    .status-active {
        background: #dcfce7;
        color: #15803d;
    }

    .status-inactive {
        background: #f1f5f9;
        color: #475569;
    }

    .status-suspended {
        background: #fee2e2;
        color: #b91c1c;
    }

    .setup-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .setup-card,
    .branch-panel {
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        background: #fff;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .045);
        overflow: hidden;
        min-width: 0;
    }

    .setup-card-head,
    .branch-panel-head {
        padding: 16px 18px;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .setup-card-title {
        min-width: 0;
    }

    .setup-card-title h5 {
        margin: 0;
        color: #0f172a;
        font-size: 15px;
        font-weight: 950;
    }

    .setup-card-title small {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: 12px;
        font-weight: 650;
    }

    .setup-card-body {
        padding: 16px 18px;
    }

    .setup-info-row {
        display: grid;
        grid-template-columns: 135px minmax(0, 1fr);
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px dashed #e5e7eb;
    }

    .setup-info-row:last-child {
        border-bottom: 0;
    }

    .setup-info-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .setup-info-value {
        color: #0f172a;
        font-size: 13px;
        font-weight: 850;
        text-align: right;
        word-break: break-word;
    }

    .branch-toolbar {
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        border-bottom: 1px solid #edf2f7;
    }

    .branch-search {
        position: relative;
        flex: 1 1 280px;
        min-width: 220px;
    }

    .branch-search input,
    .branch-filter {
        width: 100%;
        min-height: 42px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #334155;
        font-size: 13px;
        font-weight: 750;
        outline: none;
    }

    .branch-search input {
        padding: 0 42px;
    }

    .branch-search i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 18px;
    }

    .branch-filter {
        max-width: 155px;
        padding: 0 12px;
        font-weight: 850;
    }

    .branch-tabs {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding: 12px 18px;
        border-bottom: 1px solid #edf2f7;
    }

    .branch-tab {
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        border-radius: 999px;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
        cursor: pointer;
    }

    .branch-tab.active {
        color: #155dfc;
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .branch-tab-count {
        background: #fff;
        border: 1px solid #dbeafe;
        color: #155dfc;
        border-radius: 999px;
        padding: 2px 7px;
        font-size: 10px;
        font-weight: 950;
    }

    .branch-table-wrap {
        overflow-x: auto;
    }

    .branch-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 760px;
    }

    .branch-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        padding: 13px 16px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .branch-table tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #edf2f7;
        color: #334155;
        font-size: 13px;
        font-weight: 750;
        vertical-align: middle;
    }

    .branch-name-block {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 190px;
    }

    .branch-avatar {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        background: #eff6ff;
        color: #155dfc;
        border: 1px solid #dbeafe;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        flex-shrink: 0;
    }

    .branch-name {
        color: #0f172a;
        font-size: 13px;
        font-weight: 950;
    }

    .branch-sub {
        margin-top: 2px;
        color: #64748b;
        font-size: 12px;
        font-weight: 650;
    }

    .branch-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 950;
        white-space: nowrap;
    }

    .branch-badge-primary {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .branch-badge-light {
        background: #f1f5f9;
        color: #475569;
    }

    .branch-actions {
        display: inline-flex;
        gap: 7px;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .branch-action-btn {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .branch-action-btn:hover {
        background: #eff6ff;
        color: #155dfc;
        border-color: #bfdbfe;
    }

    .branch-mobile-list {
        display: none;
        padding: 14px;
        gap: 12px;
    }

    .branch-mobile-card {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        background: #fff;
        padding: 14px;
        display: grid;
        gap: 12px;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .04);
    }

    .branch-mobile-top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
    }

    .branch-mobile-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .branch-mini-label {
        color: #64748b;
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .branch-mini-value {
        margin-top: 2px;
        color: #0f172a;
        font-size: 12px;
        font-weight: 850;
        word-break: break-word;
    }

    .branch-empty {
        padding: 30px 18px;
        text-align: center;
        color: #64748b;
        font-weight: 700;
    }

    .setup-modal .modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
    }

    .setup-modal .modal-header {
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        padding: 17px 20px;
    }

    .setup-modal .modal-title {
        color: #0f172a;
        font-size: 16px;
        font-weight: 950;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .setup-modal .modal-body {
        padding: 20px;
    }

    .setup-modal .modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #eef2f7;
        background: #fff;
    }

    .form-label {
        font-size: 11px;
        font-weight: 950;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .form-control,
    .custom-select {
        border-radius: 14px;
        border: 1px solid #dbe3ef;
        min-height: 44px;
        font-size: 13px;
        font-weight: 750;
        color: #0f172a;
    }

    .form-control:focus,
    .custom-select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 .18rem rgba(37, 99, 235, .12);
    }

    .custom-control-label {
        font-weight: 850;
        color: #334155;
        font-size: 13px;
    }

    .logo-editor {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        background: #f8fbff;
        margin-bottom: 16px;
    }

    .logo-editor-preview {
        width: 82px;
        height: 82px;
        border-radius: 24px;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        color: #155dfc;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        font-size: 28px;
        font-weight: 950;
        flex-shrink: 0;
    }

    .logo-editor-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .branch-hidden {
        display: none !important;
    }

    @media (max-width: 991.98px) {
        .setup-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .setup-page {
            gap: 12px;
        }

        .setup-hero {
            border-radius: 22px;
            padding: 16px;
            flex-direction: column;
            align-items: stretch;
        }

        .setup-hero-left {
            align-items: flex-start;
        }

        .setup-logo {
            width: 54px;
            height: 54px;
            border-radius: 18px;
        }

        .setup-hero h3 {
            font-size: 1.22rem;
        }

        .setup-hero p {
            font-size: 12.5px;
        }

        .setup-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .setup-actions .setup-btn,
        .setup-actions .setup-status-pill {
            width: 100%;
        }

        .setup-card,
        .branch-panel {
            border-radius: 20px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .035);
        }

        .setup-card-head,
        .branch-panel-head {
            padding: 14px;
        }

        .setup-card-body {
            padding: 14px;
        }

        .setup-card-title small,
        .branch-panel-head small {
            display: none;
        }

        .setup-info-row {
            grid-template-columns: 1fr;
            gap: 4px;
            padding: 9px 0;
        }

        .setup-info-value {
            text-align: left;
        }

        .branch-toolbar {
            padding: 12px;
            gap: 8px;
        }

        .branch-search {
            flex-basis: 100%;
            min-width: 100%;
        }

        .branch-filter {
            max-width: none;
            flex: 1 1 calc(50% - 4px);
        }

        .branch-toolbar .setup-btn {
            width: 100%;
        }

        .branch-tabs {
            padding: 10px 12px;
        }

        .branch-table-wrap {
            display: none;
        }

        .branch-mobile-list {
            display: grid;
        }

        .branch-mobile-meta {
            grid-template-columns: 1fr;
        }

        .branch-actions {
            width: 100%;
        }

        .branch-actions .setup-btn {
            flex: 1 1 auto;
        }

        .setup-modal .modal-dialog {
            margin: 8px;
        }

        .setup-modal .modal-content {
            border-radius: 22px;
            max-height: calc(100vh - 16px);
        }

        .setup-modal .modal-body {
            padding: 16px;
            overflow-y: auto;
        }

        .setup-modal .modal-footer {
            display: grid;
            grid-template-columns: 1fr;
            padding: 14px 16px;
        }

        .setup-modal .modal-footer .setup-btn {
            width: 100%;
        }

        .logo-editor {
            flex-direction: column;
            align-items: flex-start;
        }

        .logo-editor-preview {
            width: 74px;
            height: 74px;
            border-radius: 22px;
        }
    }
</style>

<div class="setup-page">
    <div class="setup-hero">
        <div class="setup-hero-left">
            <div class="setup-logo">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $pharmacy->name }}">
                @else
                    {{ strtoupper(substr($pharmacy->name, 0, 1)) }}
                @endif
            </div>

            <div>
                <h3>{{ $pharmacy->name }}</h3>
                <p>{{ $pharmacy->address ?: 'Manage pharmacy profile, branches and business settings.' }}</p>
            </div>
        </div>

        <div class="setup-actions">
            <span class="setup-status-pill status-{{ $pharmacy->status }}">
                {{ ucfirst($pharmacy->status) }}
            </span>

            @can('setting.manage')
                <button type="button" class="setup-btn setup-btn-primary" data-toggle="modal" data-target="#editPharmacyModal">
                    <i class="mdi mdi-pencil-outline"></i>
                    Edit Profile
                </button>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-0">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mb-0">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-0">{{ $errors->first() }}</div>
    @endif

    <div class="setup-grid">
        <div class="setup-card">
            <div class="setup-card-head">
                <div class="setup-card-title">
                    <h5>Pharmacy Profile</h5>
                    <small>Main pharmacy identity</small>
                </div>

                @can('setting.manage')
                    <button type="button" class="setup-btn" data-toggle="modal" data-target="#editPharmacyModal">Edit</button>
                @endcan
            </div>

            <div class="setup-card-body">
                <div class="setup-info-row">
                    <div class="setup-info-label">Name</div>
                    <div class="setup-info-value">{{ $pharmacy->name }}</div>
                </div>
                <div class="setup-info-row">
                    <div class="setup-info-label">Code</div>
                    <div class="setup-info-value">{{ $pharmacy->code }}</div>
                </div>
                <div class="setup-info-row">
                    <div class="setup-info-label">Phone</div>
                    <div class="setup-info-value">{{ $pharmacy->phone ?: 'Not set' }}</div>
                </div>
                <div class="setup-info-row">
                    <div class="setup-info-label">Email</div>
                    <div class="setup-info-value">{{ $pharmacy->email ?: 'Not set' }}</div>
                </div>
                <div class="setup-info-row">
                    <div class="setup-info-label">Address</div>
                    <div class="setup-info-value">{{ $pharmacy->address ?: 'Not set' }}</div>
                </div>
            </div>
        </div>

        <div class="setup-card">
            <div class="setup-card-head">
                <div class="setup-card-title">
                    <h5>System Settings</h5>
                    <small>Selling, expiry and receipt rules</small>
                </div>

                @can('setting.manage')
                    <button type="button" class="setup-btn" data-toggle="modal" data-target="#editSettingsModal">Edit</button>
                @endcan
            </div>

            <div class="setup-card-body">
                <div class="setup-info-row">
                    <div class="setup-info-label">Currency</div>
                    <div class="setup-info-value">{{ $settings->currency }}</div>
                </div>
                <div class="setup-info-row">
                    <div class="setup-info-label">Selling Mode</div>
                    <div class="setup-info-value">{{ str_replace('_', ' ', ucfirst($settings->selling_mode)) }}</div>
                </div>
                <div class="setup-info-row">
                    <div class="setup-info-label">Expiry Warning</div>
                    <div class="setup-info-value">{{ $settings->expiry_warning_days }} days</div>
                </div>
                <div class="setup-info-row">
                    <div class="setup-info-label">Block Expired</div>
                    <div class="setup-info-value">{{ $settings->block_expired_stock ? 'Yes' : 'No' }}</div>
                </div>
                <div class="setup-info-row">
                    <div class="setup-info-label">Prescription</div>
                    <div class="setup-info-value">{{ $settings->require_prescription_upload ? 'Required' : 'Optional' }}</div>
                </div>
                <div class="setup-info-row">
                    <div class="setup-info-label">Approval</div>
                    <div class="setup-info-value">{{ $settings->require_pharmacist_approval ? 'Required' : 'Optional' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="branch-panel">
        <div class="branch-panel-head">
            <div class="setup-card-title">
                <h5>Branches</h5>
                <small>{{ $branches->count() }} registered branch{{ $branches->count() === 1 ? '' : 'es' }}</small>
            </div>

            @can('setting.manage')
                <button type="button" class="setup-btn setup-btn-primary" data-toggle="modal" data-target="#createBranchModal">
                    <i class="mdi mdi-plus"></i>
                    Add Branch
                </button>
            @endcan
        </div>

        <div class="branch-toolbar">
            <div class="branch-search">
                <i class="mdi mdi-magnify"></i>
                <input type="text" id="branchSearch" placeholder="Search branch...">
            </div>

            <select class="branch-filter" id="branchStatusFilter">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <select class="branch-filter" id="branchTypeFilter">
                <option value="all">All Types</option>
                <option value="main">Main</option>
                <option value="sub">Sub</option>
            </select>
        </div>

        <div class="branch-tabs">
            <button type="button" class="branch-tab active" data-branch-tab="all">
                All <span class="branch-tab-count">{{ $branches->count() }}</span>
            </button>
            <button type="button" class="branch-tab" data-branch-tab="active">
                Active <span class="branch-tab-count">{{ $activeBranches }}</span>
            </button>
            <button type="button" class="branch-tab" data-branch-tab="inactive">
                Inactive <span class="branch-tab-count">{{ $inactiveBranches }}</span>
            </button>
            <button type="button" class="branch-tab" data-branch-tab="main">
                Main <span class="branch-tab-count">{{ $mainBranches }}</span>
            </button>
        </div>

        @if($branches->count())
            <div class="branch-table-wrap">
                <table class="branch-table">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Type</th>
                            <th>Status</th>
                            @can('setting.manage')
                                <th class="text-right">Actions</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branches as $branch)
                            <tr class="branch-record"
                                data-status="{{ $branch->is_active ? 'active' : 'inactive' }}"
                                data-type="{{ $branch->is_main ? 'main' : 'sub' }}"
                                data-search="{{ strtolower($branch->name . ' ' . $branch->code . ' ' . $branch->phone . ' ' . $branch->address) }}">
                                <td>
                                    <div class="branch-name-block">
                                        <span class="branch-avatar"><i class="mdi mdi-store-marker-outline"></i></span>
                                        <div>
                                            <div class="branch-name">{{ $branch->name }}</div>
                                            <div class="branch-sub">{{ $branch->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $branch->phone ?: '-' }}</td>
                                <td>{{ $branch->address ?: '-' }}</td>
                                <td>
                                    <span class="branch-badge {{ $branch->is_main ? 'branch-badge-primary' : 'branch-badge-light' }}">
                                        {{ $branch->is_main ? 'Main' : 'Sub' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="setup-status-pill {{ $branch->is_active ? 'status-active' : 'status-inactive' }}">
                                        {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                @can('setting.manage')
                                    <td class="text-right">
                                        <div class="branch-actions">
                                            <button type="button" class="branch-action-btn" data-toggle="modal" data-target="#editBranchModal{{ $branch->id }}">
                                                <i class="mdi mdi-pencil-outline"></i>
                                            </button>

                                            @if(!$branch->is_main)
                                                <form method="POST" action="{{ route('setup.branches.main', $branch) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="branch-action-btn">
                                                        <i class="mdi mdi-star-outline"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('setup.branches.toggle', $branch) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="branch-action-btn">
                                                    <i class="mdi {{ $branch->is_active ? 'mdi-toggle-switch-off-outline' : 'mdi-toggle-switch-outline' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="branch-mobile-list" id="branchMobileList">
                @foreach($branches as $branch)
                    <div class="branch-mobile-card branch-record"
                         data-status="{{ $branch->is_active ? 'active' : 'inactive' }}"
                         data-type="{{ $branch->is_main ? 'main' : 'sub' }}"
                         data-search="{{ strtolower($branch->name . ' ' . $branch->code . ' ' . $branch->phone . ' ' . $branch->address) }}">
                        <div class="branch-mobile-top">
                            <div class="branch-name-block">
                                <span class="branch-avatar"><i class="mdi mdi-store-marker-outline"></i></span>
                                <div>
                                    <div class="branch-name">{{ $branch->name }}</div>
                                    <div class="branch-sub">{{ $branch->code }}</div>
                                </div>
                            </div>

                            <span class="setup-status-pill {{ $branch->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="branch-mobile-meta">
                            <div>
                                <div class="branch-mini-label">Phone</div>
                                <div class="branch-mini-value">{{ $branch->phone ?: '-' }}</div>
                            </div>
                            <div>
                                <div class="branch-mini-label">Type</div>
                                <div class="branch-mini-value">{{ $branch->is_main ? 'Main Branch' : 'Sub Branch' }}</div>
                            </div>
                            <div>
                                <div class="branch-mini-label">Address</div>
                                <div class="branch-mini-value">{{ $branch->address ?: '-' }}</div>
                            </div>
                        </div>

                        @can('setting.manage')
                            <div class="branch-actions">
                                <button type="button" class="setup-btn" data-toggle="modal" data-target="#editBranchModal{{ $branch->id }}">Edit</button>

                                @if(!$branch->is_main)
                                    <form method="POST" action="{{ route('setup.branches.main', $branch) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="setup-btn">Make Main</button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('setup.branches.toggle', $branch) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="setup-btn">{{ $branch->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            </div>
                        @endcan
                    </div>
                @endforeach
            </div>

            <div class="branch-empty branch-hidden" id="branchEmptyState">
                No matching branches found.
            </div>
        @else
            <div class="branch-empty">
                No branches found.
            </div>
        @endif
    </div>
</div>

@can('setting.manage')
    <div class="modal fade setup-modal" id="editPharmacyModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('setup.pharmacy.update') }}" enctype="multipart/form-data" class="modal-content">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit Pharmacy Profile</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="logo-editor">
                        <div class="logo-editor-preview">
                            @if($logoUrl)
                                <img id="pharmacyLogoPreview" src="{{ $logoUrl }}" alt="{{ $pharmacy->name }}">
                                <span id="pharmacyLogoInitial" class="d-none">{{ strtoupper(substr($pharmacy->name, 0, 1)) }}</span>
                            @else
                                <img id="pharmacyLogoPreview" src="" alt="" class="d-none">
                                <span id="pharmacyLogoInitial">{{ strtoupper(substr($pharmacy->name, 0, 1)) }}</span>
                            @endif
                        </div>

                        <div class="flex-grow-1">
                            <label class="form-label">Pharmacy Logo</label>
                            <input type="file" name="logo" id="pharmacyLogoInput" class="form-control" accept="image/*">
                            <small class="text-muted d-block mt-2">PNG, JPG or WEBP. Max 2MB.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Pharmacy Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $pharmacy->name) }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code', $pharmacy->code) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $pharmacy->phone) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $pharmacy->email) }}">
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $pharmacy->address) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="custom-select" required>
                                <option value="active" {{ old('status', $pharmacy->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $pharmacy->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status', $pharmacy->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="setup-btn" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="setup-btn setup-btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade setup-modal" id="editSettingsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('setup.settings.update') }}" class="modal-content">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit System Settings</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency" class="form-control" value="{{ old('currency', $settings->currency) }}" required>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">Selling Mode</label>
                            <select name="selling_mode" class="custom-select" required>
                                <option value="retail_only" {{ old('selling_mode', $settings->selling_mode) === 'retail_only' ? 'selected' : '' }}>Retail Only</option>
                                <option value="wholesale_only" {{ old('selling_mode', $settings->selling_mode) === 'wholesale_only' ? 'selected' : '' }}>Wholesale Only</option>
                                <option value="retail_and_wholesale" {{ old('selling_mode', $settings->selling_mode) === 'retail_and_wholesale' ? 'selected' : '' }}>Retail and Wholesale</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Expiry Warning Days</label>
                            <input type="number" name="expiry_warning_days" class="form-control" min="1" max="365" value="{{ old('expiry_warning_days', $settings->expiry_warning_days) }}" required>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">Receipt Footer</label>
                            <input type="text" name="receipt_footer" class="form-control" value="{{ old('receipt_footer', $settings->receipt_footer) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="custom-control custom-switch mt-4">
                                <input type="checkbox" class="custom-control-input" id="block_expired_stock" name="block_expired_stock" value="1" {{ old('block_expired_stock', $settings->block_expired_stock) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="block_expired_stock">Block expired stock</label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="custom-control custom-switch mt-4">
                                <input type="checkbox" class="custom-control-input" id="require_prescription_upload" name="require_prescription_upload" value="1" {{ old('require_prescription_upload', $settings->require_prescription_upload) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="require_prescription_upload">Prescription upload</label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="custom-control custom-switch mt-4">
                                <input type="checkbox" class="custom-control-input" id="require_pharmacist_approval" name="require_pharmacist_approval" value="1" {{ old('require_pharmacist_approval', $settings->require_pharmacist_approval) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="require_pharmacist_approval">Pharmacist approval</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="setup-btn" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="setup-btn setup-btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade setup-modal" id="createBranchModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('setup.branches.store') }}" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add Branch</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Branch Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="create_is_main" name="is_main" value="1">
                                <label class="custom-control-label" for="create_is_main">Set as main branch</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="create_is_active" name="is_active" value="1" checked>
                                <label class="custom-control-label" for="create_is_active">Active branch</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="setup-btn" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="setup-btn setup-btn-primary">Save Branch</button>
                </div>
            </form>
        </div>
    </div>

    @foreach($branches as $branch)
        <div class="modal fade setup-modal" id="editBranchModal{{ $branch->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('setup.branches.update', $branch) }}" class="modal-content">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Branch</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Branch Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $branch->name }}" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Code</label>
                                <input type="text" name="code" class="form-control" value="{{ $branch->code }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ $branch->phone }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" value="{{ $branch->address }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="edit_is_main_{{ $branch->id }}" name="is_main" value="1" {{ $branch->is_main ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="edit_is_main_{{ $branch->id }}">Set as main branch</label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="edit_is_active_{{ $branch->id }}" name="is_active" value="1" {{ $branch->is_active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="edit_is_active_{{ $branch->id }}">Active branch</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="setup-btn" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="setup-btn setup-btn-primary">Save Branch</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endcan

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('branchSearch');
        const statusFilter = document.getElementById('branchStatusFilter');
        const typeFilter = document.getElementById('branchTypeFilter');
        const emptyState = document.getElementById('branchEmptyState');
        const logoInput = document.getElementById('pharmacyLogoInput');
        const logoPreview = document.getElementById('pharmacyLogoPreview');
        const logoInitial = document.getElementById('pharmacyLogoInitial');

        let activeTab = 'all';

        function normalize(value) {
            return String(value || '').toLowerCase().trim();
        }

        function getRecords() {
            return Array.from(document.querySelectorAll('.branch-record'));
        }

        function shouldShow(record) {
            const query = normalize(searchInput ? searchInput.value : '');
            const status = statusFilter ? statusFilter.value : 'all';
            const type = typeFilter ? typeFilter.value : 'all';

            if (activeTab !== 'all') {
                if (activeTab === 'main' && record.dataset.type !== 'main') return false;
                if ((activeTab === 'active' || activeTab === 'inactive') && record.dataset.status !== activeTab) return false;
            }

            if (status !== 'all' && record.dataset.status !== status) return false;
            if (type !== 'all' && record.dataset.type !== type) return false;
            if (query && !(record.dataset.search || '').includes(query)) return false;

            return true;
        }

        function applyBranchFilters() {
            const records = getRecords();
            let visible = 0;

            records.forEach(function (record) {
                const show = shouldShow(record);
                record.classList.toggle('branch-hidden', !show);

                if (show && record.classList.contains('branch-mobile-card')) {
                    visible++;
                }
            });

            if (emptyState) {
                emptyState.classList.toggle('branch-hidden', visible > 0 || records.length === 0);
            }
        }

        document.querySelectorAll('.branch-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.branch-tab').forEach(function (item) {
                    item.classList.remove('active');
                });

                tab.classList.add('active');
                activeTab = tab.dataset.branchTab || 'all';
                applyBranchFilters();
            });
        });

        [searchInput, statusFilter, typeFilter].forEach(function (element) {
            if (element) {
                element.addEventListener('input', applyBranchFilters);
                element.addEventListener('change', applyBranchFilters);
            }
        });

        if (logoInput && logoPreview) {
            logoInput.addEventListener('change', function () {
                const file = this.files && this.files[0];

                if (!file) return;

                const reader = new FileReader();

                reader.onload = function (event) {
                    logoPreview.src = event.target.result;
                    logoPreview.classList.remove('d-none');

                    if (logoInitial) {
                        logoInitial.classList.add('d-none');
                    }
                };

                reader.readAsDataURL(file);
            });
        }

        applyBranchFilters();
    });
</script>
@endpush
@endsection