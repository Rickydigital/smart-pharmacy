@props([
    'variant' => 'advanced',
    'title' => 'Advanced Table View',
    'subtitle' => 'A powerful, flexible table for managing and analyzing your data.',
    'count' => '0',
    'addLabel' => 'Add New',
    'createModalId' => 'createRecordModal',
    'exportModalId' => 'exportModal',
])

@once
    @push('styles')
        <style>
            .super-table-page {
                display: grid;
                gap: 22px;
            }

            .super-table-hero {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 18px;
                flex-wrap: wrap;
            }

            .super-table-title {
                margin: 0;
                color: #0f172a;
                font-size: 1.65rem;
                font-weight: 900;
                letter-spacing: -.035em;
            }

            .super-table-subtitle {
                margin: 6px 0 0;
                color: #64748b;
                font-size: .9rem;
                font-weight: 600;
            }

            .super-table-actions {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .super-btn {
                height: 42px;
                border-radius: 14px;
                padding: 0 15px;
                border: 1px solid #e2e8f0;
                background: #fff;
                color: #334155;
                font-size: .82rem;
                font-weight: 850;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                box-shadow: 0 8px 18px rgba(15, 23, 42, .035);
                transition: all .16s ease;
            }

            .super-btn:hover {
                background: #eff6ff;
                border-color: #dbeafe;
                color: #155dfc;
            }

            .super-btn-primary {
                background: #155dfc;
                border-color: #155dfc;
                color: #fff;
                box-shadow: 0 14px 24px rgba(37, 99, 235, .22);
            }

            .super-btn-primary:hover {
                background: #0f4fd8;
                border-color: #0f4fd8;
                color: #fff;
            }

            .super-summary-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 14px;
            }

            .super-summary-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 20px;
                padding: 16px;
                display: flex;
                align-items: center;
                gap: 14px;
                box-shadow: 0 12px 28px rgba(15, 23, 42, .045);
            }

            .super-summary-icon {
                width: 46px;
                height: 46px;
                border-radius: 15px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 21px;
                flex-shrink: 0;
            }

            .super-summary-icon.blue { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
            .super-summary-icon.green { background: linear-gradient(135deg, #22c55e, #16a34a); }
            .super-summary-icon.orange { background: linear-gradient(135deg, #fb923c, #f97316); }
            .super-summary-icon.purple { background: linear-gradient(135deg, #a855f7, #9333ea); }

            .super-summary-label {
                color: #64748b;
                font-size: .75rem;
                font-weight: 800;
            }

            .super-summary-value {
                margin-top: 4px;
                color: #0f172a;
                font-size: 1.28rem;
                font-weight: 950;
                letter-spacing: -.035em;
            }

            .super-summary-change {
                font-size: .72rem;
                font-weight: 900;
            }

            .super-summary-change.up { color: #16a34a; }
            .super-summary-change.down { color: #ef4444; }

            .super-table-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 22px;
                box-shadow: 0 14px 32px rgba(15, 23, 42, .05);
                overflow: hidden;
            }

            .super-filter-shell {
                padding: 18px 18px 0;
                display: grid;
                gap: 12px;
            }

            .super-filter-row {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .super-search {
                position: relative;
                min-width: 260px;
                flex: 1 1 320px;
            }

            .super-search input {
                width: 100%;
                height: 42px;
                border-radius: 14px;
                border: 1px solid #e2e8f0;
                background: #fff;
                padding: 0 42px;
                color: #334155;
                font-size: .82rem;
                font-weight: 700;
                outline: none;
            }

            .super-search input:focus {
                border-color: #bfdbfe;
                box-shadow: 0 0 0 4px rgba(37, 99, 235, .08);
            }

            .super-search i {
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%);
                color: #94a3b8;
                font-size: 18px;
            }

            .super-filter-control {
                height: 42px;
                border-radius: 14px;
                border: 1px solid #e2e8f0;
                background: #fff;
                color: #334155;
                padding: 0 12px;
                font-size: .8rem;
                font-weight: 800;
                min-width: 140px;
            }

            .super-filter-chip {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 11px;
                border-radius: 12px;
                background: #eff6ff;
                color: #1d4ed8;
                font-size: .76rem;
                font-weight: 850;
            }

            .super-tabs {
                display: flex;
                align-items: center;
                gap: 4px;
                border-top: 1px solid #f1f5f9;
                border-bottom: 1px solid #e2e8f0;
                overflow-x: auto;
                padding: 0 18px;
                margin-top: 4px;
            }

            .super-tab {
                min-height: 50px;
                padding: 0 16px;
                border: 0;
                background: transparent;
                color: #475569;
                font-size: .82rem;
                font-weight: 850;
                border-bottom: 3px solid transparent;
                white-space: nowrap;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .super-tab.active {
                color: #155dfc;
                border-bottom-color: #155dfc;
            }

            .super-tab-count {
                padding: 3px 8px;
                border-radius: 999px;
                background: #eef2ff;
                color: #1d4ed8;
                font-size: .7rem;
                font-weight: 900;
            }

            .super-table-toolbar {
                padding: 14px 18px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
            }

            .super-result-count {
                color: #334155;
                font-size: .84rem;
                font-weight: 850;
            }

            .super-toolbar-right {
                display: flex;
                align-items: center;
                gap: 9px;
                flex-wrap: wrap;
            }

            .super-table-wrap {
                overflow-x: auto;
            }

            .super-table {
                width: 100%;
                margin: 0;
                border-collapse: separate;
                border-spacing: 0;
                min-width: 980px;
            }

            .super-table thead th {
                background: #f8fafc;
                color: #475569;
                font-size: .72rem;
                font-weight: 950;
                letter-spacing: .03em;
                text-transform: uppercase;
                padding: 13px 14px;
                border-top: 1px solid #e2e8f0;
                border-bottom: 1px solid #e2e8f0;
                white-space: nowrap;
            }

            .super-table tbody td {
                padding: 13px 14px;
                border-bottom: 1px solid #edf2f7;
                color: #334155;
                font-size: .82rem;
                font-weight: 700;
                vertical-align: middle;
                white-space: nowrap;
            }

            .super-table tbody tr {
                transition: background .14s ease;
            }

            .super-table tbody tr:hover {
                background: #f8fbff;
            }

            .super-checkbox {
                width: 16px;
                height: 16px;
                accent-color: #155dfc;
            }

            .super-item {
                display: flex;
                align-items: center;
                gap: 11px;
                min-width: 240px;
            }

            .super-thumb {
                width: 42px;
                height: 42px;
                border-radius: 13px;
                object-fit: cover;
                background: linear-gradient(135deg, #eff6ff, #dbeafe);
                border: 1px solid #dbeafe;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #155dfc;
                font-size: 18px;
                flex-shrink: 0;
            }

            .super-item-title {
                color: #0f172a;
                font-size: .82rem;
                font-weight: 900;
                line-height: 1.2;
            }

            .super-item-sub {
                color: #64748b;
                font-size: .72rem;
                font-weight: 700;
                margin-top: 3px;
            }

            .super-person {
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .super-avatar {
                width: 30px;
                height: 30px;
                border-radius: 999px;
                object-fit: cover;
                background: #dbeafe;
            }

            .super-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 6px 10px;
                border-radius: 999px;
                font-size: .68rem;
                font-weight: 900;
                line-height: 1;
            }

            .badge-green { background: #dcfce7; color: #15803d; }
            .badge-blue { background: #dbeafe; color: #1d4ed8; }
            .badge-purple { background: #f3e8ff; color: #7e22ce; }
            .badge-orange { background: #ffedd5; color: #c2410c; }
            .badge-gray { background: #f1f5f9; color: #475569; }
            .badge-red { background: #fee2e2; color: #b91c1c; }
            .badge-yellow { background: #fef3c7; color: #b45309; }

            .super-progress {
                width: 86px;
                height: 6px;
                border-radius: 999px;
                background: #e2e8f0;
                overflow: hidden;
            }

            .super-progress span {
                display: block;
                height: 100%;
                border-radius: inherit;
                background: #155dfc;
            }

            .super-action-btn {
                width: 34px;
                height: 34px;
                border-radius: 11px;
                border: 1px solid #e2e8f0;
                background: #fff;
                color: #334155;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .super-action-btn:hover {
                background: #eff6ff;
                color: #155dfc;
                border-color: #dbeafe;
            }

            .super-pagination {
                padding: 14px 18px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                flex-wrap: wrap;
            }

            .super-pagination-left {
                display: flex;
                align-items: center;
                gap: 10px;
                color: #64748b;
                font-size: .82rem;
                font-weight: 800;
            }

            .super-pagination-select {
                height: 38px;
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                background: #fff;
                padding: 0 10px;
                font-weight: 800;
                color: #334155;
            }

            .super-pages {
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .super-page-btn {
                min-width: 36px;
                height: 36px;
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                background: #fff;
                color: #334155;
                font-size: .8rem;
                font-weight: 850;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .super-page-btn.active {
                background: #155dfc;
                color: #fff;
                border-color: #155dfc;
            }

            .super-mobile-list {
                display: none;
                padding: 14px;
                gap: 10px;
            }

            .super-mobile-card {
                border: 1px solid #e2e8f0;
                border-radius: 18px;
                background: #fff;
                padding: 12px;
                display: grid;
                gap: 10px;
                box-shadow: 0 8px 18px rgba(15, 23, 42, .035);
            }

            .super-mobile-card-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
            }

            .super-modal .modal-content {
                border: 0;
                border-radius: 24px;
                box-shadow: 0 24px 70px rgba(15, 23, 42, .20);
                overflow: hidden;
            }

            .super-modal .modal-header {
                border-bottom: 1px solid #edf2f7;
                padding: 18px 20px;
            }

            .super-modal .modal-title {
                color: #0f172a;
                font-size: 1.05rem;
                font-weight: 950;
            }

            .super-modal .modal-body {
                padding: 20px;
            }

            .super-form-label {
                color: #334155;
                font-size: .78rem;
                font-weight: 900;
                margin-bottom: 7px;
            }

            .super-form-control {
                height: 44px;
                border-radius: 14px;
                border: 1px solid #e2e8f0;
                font-size: .86rem;
                font-weight: 700;
                color: #334155;
            }

            .super-form-control:focus {
                border-color: #bfdbfe;
                box-shadow: 0 0 0 4px rgba(37, 99, 235, .08);
            }

            .super-side-panel {
                border: 1px solid #e2e8f0;
                background: #fff;
                border-radius: 22px;
                box-shadow: 0 14px 32px rgba(15, 23, 42, .05);
                padding: 18px;
                height: 100%;
            }

            .super-layout-with-panel {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 310px;
                gap: 18px;
            }

            .payment-amount {
                color: #0f172a;
                font-size: .9rem;
                font-weight: 950;
            }

            @media (max-width: 1199px) {
                .super-layout-with-panel {
                    grid-template-columns: 1fr;
                }

                .super-side-panel {
                    display: none;
                }
            }

            @media (max-width: 991px) {
                .super-summary-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 767px) {
                .super-table-hero {
                    align-items: stretch;
                }

                .super-table-actions {
                    width: 100%;
                }

                .super-table-actions .super-btn,
                .super-btn-primary {
                    flex: 1 1 auto;
                    justify-content: center;
                }

                .super-summary-grid {
                    grid-template-columns: 1fr;
                }

                .super-table-wrap {
                    display: none;
                }

                .super-mobile-list {
                    display: grid;
                }

                .super-pagination {
                    align-items: stretch;
                }

                .super-pages {
                    overflow-x: auto;
                    padding-bottom: 4px;
                }
            }
        </style>
    @endpush
@endonce

<div class="super-table-page super-table-{{ $variant }}">
    <div class="super-table-hero">
        <div>
            <h1 class="super-table-title">{{ $title }}</h1>
            <p class="super-table-subtitle">{{ $subtitle }}</p>
        </div>

        <div class="super-table-actions">
            {{ $actions ?? '' }}

            <button type="button" class="super-btn" data-bs-toggle="modal" data-bs-target="#{{ $exportModalId }}">
                <i class="mdi mdi-export-variant"></i>
                Export
            </button>

            <button type="button" class="super-btn super-btn-primary" data-bs-toggle="modal" data-bs-target="#{{ $createModalId }}">
                <i class="mdi mdi-plus"></i>
                {{ $addLabel }}
            </button>
        </div>
    </div>

    {{ $summary ?? '' }}

    <div class="super-table-card">
        <div class="super-filter-shell">
            {{ $filters ?? '' }}
        </div>

        {{ $tabs ?? '' }}

        <div class="super-table-toolbar">
            <div class="super-result-count">{{ $count }} results</div>

            <div class="super-toolbar-right">
                {{ $toolbar ?? '' }}

                <button type="button" class="super-btn">
                    <i class="mdi mdi-sort"></i>
                    Sort
                </button>

                <button type="button" class="super-btn">
                    <i class="mdi mdi-view-column-outline"></i>
                    Columns
                </button>
            </div>
        </div>

        <div class="super-table-wrap">
            {{ $table }}
        </div>

        <div class="super-mobile-list">
            {{ $mobile ?? '' }}
        </div>

        <div class="super-pagination">
            <div class="super-pagination-left">
                <span>Rows per page</span>
                <select class="super-pagination-select">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
            </div>

            <div class="super-pages">
                <button class="super-page-btn"><i class="mdi mdi-chevron-double-left"></i></button>
                <button class="super-page-btn"><i class="mdi mdi-chevron-left"></i></button>
                <button class="super-page-btn active">1</button>
                <button class="super-page-btn">2</button>
                <button class="super-page-btn">3</button>
                <button class="super-page-btn">4</button>
                <button class="super-page-btn">5</button>
                <button class="super-page-btn">...</button>
                <button class="super-page-btn">125</button>
                <button class="super-page-btn"><i class="mdi mdi-chevron-right"></i></button>
                <button class="super-page-btn"><i class="mdi mdi-chevron-double-right"></i></button>
            </div>
        </div>
    </div>

    {{ $modals ?? '' }}
</div>

<div class="modal fade super-modal" id="{{ $exportModalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Records</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="text-muted fw-semibold mb-3">
                    Choose how you want to export the current filtered table.
                </p>

                <div class="row g-3">
                    <div class="col-6">
                        <a href="javascript:void(0);" class="super-btn w-100 justify-content-center" style="height:56px;">
                            <i class="mdi mdi-file-pdf-box text-danger"></i>
                            PDF
                        </a>
                    </div>

                    <div class="col-6">
                        <a href="javascript:void(0);" class="super-btn w-100 justify-content-center" style="height:56px;">
                            <i class="mdi mdi-file-excel-box text-success"></i>
                            Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>