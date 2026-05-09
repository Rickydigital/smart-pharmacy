<style>
    .report-page { max-width: 100%; overflow-x: hidden; }

    .report-hero,
    .report-card,
    .report-filter-card,
    .report-stat {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .07);
    }

    .report-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eff6ff 100%);
    }

    .report-hero .card-body { padding: 22px; }

    .report-icon {
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

    .report-title {
        font-weight: 950;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -.02em;
    }

    .report-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 0;
    }

    .report-filter-card {
        margin-bottom: 16px;
        border: 1px solid #e8edf5;
    }

    .report-filter-card .card-body { padding: 16px; }

    .report-label {
        font-size: 11px;
        font-weight: 950;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 7px;
    }

    .report-filter-card .form-control,
    .report-filter-card .custom-select,
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

    .report-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .report-stat {
        padding: 15px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .report-stat-label {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 950;
        margin-bottom: 6px;
    }

    .report-stat-value {
        color: #0f172a;
        font-weight: 950;
        font-size: 22px;
        line-height: 1;
    }

    .report-stat-sub {
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        margin-top: 7px;
    }

    .report-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .report-card {
        background: #ffffff;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        margin-bottom: 16px;
    }

    .report-card-header {
        padding: 16px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #ffffff;
    }

    .report-card-header h5 {
        margin: 0;
        color: #0f172a;
        font-weight: 950;
    }

    .report-card-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .report-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .report-table {
        min-width: 760px;
        margin-bottom: 0;
    }

    .report-table th {
        background: #f8fafc;
        color: #64748b;
        border-top: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
        padding: 12px 14px;
    }

    .report-table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 720;
        color: #334155;
        padding: 12px 14px;
        border-top: 1px solid #eef2f7;
    }

    .report-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.15;
    }

    .report-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 3px;
        font-weight: 700;
    }

    .report-badge {
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
    .badge-red { background: #fee2e2; color: #b91c1c; }
    .badge-yellow { background: #fef3c7; color: #92400e; }
    .badge-gray { background: #f1f5f9; color: #475569; }

    .report-empty {
        padding: 36px 20px;
        text-align: center;
        color: #64748b;
        font-weight: 750;
    }

    @media (max-width: 991.98px) {
        .report-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .report-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .report-hero .card-body { padding: 18px 16px; }

        .report-stat-grid {
            grid-template-columns: 1fr;
        }

        .report-filter-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 8px !important;
        }

        .report-filter-actions .btn,
        .report-filter-actions a {
            width: 100%;
        }

        .report-table {
            min-width: 700px;
        }
    }
</style>