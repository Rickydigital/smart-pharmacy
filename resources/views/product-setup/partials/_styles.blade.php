<style>
    html,
    body {
        overflow-y: auto !important;
    }

    .ps-page {
        max-width: 100%;
        overflow-x: hidden;
    }

    .ps-header-card,
    .ps-shell-card,
    .ps-filter-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .07);
    }

    .ps-shell-card {
        overflow: visible !important;
    }

    .ps-header-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 50%, #eef6ff 100%);
    }

    .ps-header-card .card-body {
        padding: 22px;
    }

    .ps-hero-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.02em;
    }

    .ps-hero-text {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 0;
    }

    .ps-header-icon {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 24px;
        flex: 0 0 auto;
    }

    .ps-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .ps-actions .btn,
    .ps-toolbar .btn,
    .ps-action-btn {
        border-radius: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .ps-strip {
        display: flex;
        gap: 8px;
        padding: 16px 18px;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .ps-strip .nav-link {
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        border-radius: 999px;
        font-weight: 850;
        font-size: 13px;
        padding: 9px 14px;
        flex: 0 0 auto;
    }

    .ps-strip .nav-link.active {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
        box-shadow: 0 8px 18px rgba(37, 99, 235, .22);
    }

    .ps-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        padding: 17px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .ps-toolbar h5 {
        margin: 0;
        font-weight: 950;
        color: #0f172a;
        letter-spacing: -.01em;
    }

    .ps-toolbar p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .ps-filter-card {
        margin: 16px 18px;
        background: #ffffff;
        border: 1px solid #e8edf5;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
    }

    .ps-filter-card .card-body {
        padding: 16px;
    }

    .ps-filter label,
    .modal-body label,
    .ps-label {
        font-size: 11px;
        font-weight: 950;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .ps-filter .form-control,
    .ps-filter .custom-select,
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

    .ps-table-wrap {
        width: 100%;
        overflow-x: auto !important;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
    }

    .ps-table {
        min-width: 1040px;
        margin-bottom: 0;
    }

    .ps-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 13px 14px;
    }

    .ps-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 13px 14px;
        border-top: 1px solid #eef2f7;
    }

    .ps-main-name {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .ps-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 3px;
        font-weight: 700;
    }

    .ps-badge {
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

    .ps-badge-blue { background: #eff6ff; color: #1d4ed8; }
    .ps-badge-green { background: #dcfce7; color: #15803d; }
    .ps-badge-gray { background: #f1f5f9; color: #475569; }
    .ps-badge-red { background: #fee2e2; color: #b91c1c; }
    .ps-badge-yellow { background: #fef3c7; color: #92400e; }

    .ps-btn-row {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .ps-btn-row .btn {
        border-radius: 10px;
        font-weight: 850;
        white-space: nowrap;
    }

    .ps-pill-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .ps-empty {
        padding: 44px 20px;
        text-align: center;
        color: #64748b;
        font-weight: 750;
    }

    .ps-detail-box {
        border: 1px solid #e5e7eb;
        border-radius: 15px;
        padding: 14px;
        height: 100%;
        background: #fff;
    }

    .ps-detail-label {
        font-size: 11px;
        color: #64748b;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 5px;
    }

    .ps-detail-value {
        color: #0f172a;
        font-weight: 950;
    }

    .modal {
        overflow-y: auto !important;
    }

    .modal-dialog {
        max-height: calc(100vh - 1.5rem);
    }

    .modal-dialog-scrollable .modal-content {
        max-height: calc(100vh - 1.5rem);
    }

    .modal-dialog-scrollable .modal-body {
        overflow-y: auto;
    }

    .modal-content {
        border: 0;
        border-radius: 20px;
        overflow: visible !important;
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

    .modal-body {
        max-height: calc(100vh - 190px);
        overflow-y: auto;
        padding: 20px;
    }

    .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 14px 20px;
    }

    .ps-alert-info {
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1e40af;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 750;
    }

    .ps-export-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .ps-export-card {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 16px;
        background: #fff;
        text-align: center;
        transition: .18s ease;
        height: 100%;
    }

    .ps-export-card:hover {
        border-color: #2563eb;
        box-shadow: 0 14px 28px rgba(37, 99, 235, .12);
        transform: translateY(-1px);
    }

    .ps-export-card i {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 22px;
        margin-bottom: 10px;
    }

    .ps-export-card h6 {
        margin: 0 0 4px;
        font-weight: 950;
        color: #0f172a;
    }

    .ps-export-card p {
        margin: 0 0 12px;
        font-size: 12px;
        color: #64748b;
        font-weight: 650;
    }

    .ps-import-box {
        border: 1px dashed #bfdbfe;
        border-radius: 18px;
        background: #f8fbff;
        padding: 16px;
    }

    .ps-mobile-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    @media (max-width: 767.98px) {
        .ps-header-card .card-body {
            padding: 18px 16px;
        }

        .ps-header-main {
            align-items: flex-start !important;
        }

        .ps-header-title-wrap {
            width: 100%;
        }

        .ps-actions {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .ps-actions .btn {
            width: 100%;
            padding: 10px 8px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .ps-actions .btn i {
            margin-right: 0 !important;
        }

        .ps-strip {
            padding: 12px;
        }

        .ps-strip .nav-link {
            font-size: 12px;
            padding: 8px 12px;
        }

        .ps-toolbar {
            padding: 14px;
        }

        .ps-toolbar .btn {
            width: 100%;
        }

        .ps-filter-card {
            margin: 12px;
        }

        .ps-filter-card .card-body {
            padding: 14px;
        }

        .ps-filter-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px !important;
        }

        .ps-filter-actions .btn,
        .ps-filter-actions a {
            width: 100%;
            text-align: center;
        }

        .ps-table {
            min-width: 850px;
        }

        .ps-table th,
        .ps-table td {
            padding: 10px;
            font-size: 12px;
        }

        .ps-btn-row .btn {
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

        .ps-export-grid {
            grid-template-columns: 1fr;
        }

        .ps-mobile-stack {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: 8px !important;
        }

        .ps-mobile-stack .btn,
        .ps-mobile-stack input {
            width: 100%;
        }
    }
</style>