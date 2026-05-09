<style>
    #posExpensesModal .pos-expense-modal {
        width: min(1180px, 96vw) !important;
        max-width: 1180px !important;
        max-height: 94vh !important;
        border-radius: 28px;
        overflow: hidden;
    }

    #posExpensesModal .pos-expense-head {
        flex-shrink: 0;
        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 58%, #f8fafc 100%);
        padding: 22px 26px;
    }

    #posExpensesModal .pos-expense-head-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    #posExpensesModal .pos-expense-head-icon {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #155dfc;
        color: #ffffff;
        font-size: 27px;
        box-shadow: 0 16px 28px rgba(37, 99, 235, .25);
        flex: 0 0 auto;
    }

    #posExpensesModal .pos-expense-head h4 {
        margin: 0;
        color: #0f172a;
        font-weight: 950;
        letter-spacing: -.035em;
        font-size: 24px;
        line-height: 1.1;
    }

    #posExpensesModal .pos-expense-head p {
        margin: 6px 0 0;
        color: #64748b;
        font-weight: 750;
        font-size: 14px;
        line-height: 1.35;
    }

    #posExpensesModal .pos-expense-tabs {
        display: flex;
        gap: 10px;
        padding: 14px 24px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
        flex-shrink: 0;
    }

    #posExpensesModal .pos-expense-tab {
        height: 46px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
        color: #64748b;
        padding: 0 18px;
        font-size: 13px;
        font-weight: 950;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: .18s ease;
        white-space: nowrap;
    }

    #posExpensesModal .pos-expense-tab i {
        font-size: 18px;
    }

    #posExpensesModal .pos-expense-tab.active {
        background: linear-gradient(135deg, #155dfc, #0f3fbf);
        border-color: #155dfc;
        color: #ffffff;
        box-shadow: 0 14px 24px rgba(37, 99, 235, .24);
    }

    #posExpensesModal .pos-expense-body {
        padding: 22px 24px;
        overflow-y: auto;
        overflow-x: hidden !important;
        background: #f8fafc;
        width: 100%;
        box-sizing: border-box;
    }

    #posExpensesModal .pos-expense-tab-panel {
        display: none;
        width: 100%;
        min-width: 0;
    }

    #posExpensesModal .pos-expense-tab-panel.active {
        display: block;
    }

    #posExpensesModal .pos-expense-summary-card,
    #posExpensesModal .pos-expense-filter-card,
    #posExpensesModal .pos-expense-list-shell,
    #posExpensesModal .pos-expense-form-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .045);
        width: 100%;
        box-sizing: border-box;
        min-width: 0;
    }

    #posExpensesModal .pos-expense-summary-card {
        padding: 18px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    #posExpensesModal .pos-expense-summary-card h5,
    #posExpensesModal .pos-expense-form-title h5 {
        margin: 0;
        color: #0f172a;
        font-weight: 950;
        letter-spacing: -.025em;
        font-size: 18px;
        line-height: 1.2;
    }

    #posExpensesModal .pos-expense-summary-card p,
    #posExpensesModal .pos-expense-form-title p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 750;
        line-height: 1.35;
    }

    #posExpensesModal .pos-expense-summary-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    #posExpensesModal .pos-expense-summary-actions span {
        display: inline-flex;
        align-items: center;
        background: #eef5ff;
        color: #155dfc;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        padding: 8px 12px;
        white-space: nowrap;
    }

    #posExpensesModal .pos-expense-filter-card {
        padding: 16px;
        margin-bottom: 14px;
    }

    #posExpensesModal .pos-expense-filters {
        display: grid;
        grid-template-columns: 180px minmax(220px, 1fr) 140px;
        gap: 12px;
        align-items: end;
    }

    #posExpensesModal .pos-expense-field label,
    #posExpensesModal .pos-expense-filters label {
        display: block;
        color: #475569;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin-bottom: 7px;
    }

    #posExpensesModal .pos-expense-field input,
    #posExpensesModal .pos-expense-field select,
    #posExpensesModal .pos-expense-field textarea,
    #posExpensesModal .pos-expense-filters input,
    #posExpensesModal .pos-expense-filters select {
        width: 100%;
        border: 1px solid #dbe3ef;
        border-radius: 15px;
        padding: 0 14px;
        color: #0f172a;
        font-size: 14px;
        font-weight: 850;
        outline: 0;
        background: #ffffff;
        box-sizing: border-box;
    }

    #posExpensesModal .pos-expense-field input,
    #posExpensesModal .pos-expense-field select,
    #posExpensesModal .pos-expense-filters input,
    #posExpensesModal .pos-expense-filters select {
        height: 48px;
    }

    #posExpensesModal .pos-expense-field textarea {
        padding-top: 12px;
        min-height: 96px;
        resize: vertical;
    }

    #posExpensesModal .pos-expense-field input:focus,
    #posExpensesModal .pos-expense-field select:focus,
    #posExpensesModal .pos-expense-field textarea:focus,
    #posExpensesModal .pos-expense-filters input:focus,
    #posExpensesModal .pos-expense-filters select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 5px rgba(37, 99, 235, .08);
    }

    #posExpensesModal .pos-expense-all-btn {
        width: 100%;
        height: 48px;
    }

    #posExpensesModal .pos-expense-list-shell {
        padding: 0;
        overflow: hidden;
    }

    #posExpensesModal .pos-expense-list {
        max-height: 455px;
        overflow-y: auto;
        overflow-x: hidden !important;
        padding: 10px;
        width: 100%;
        box-sizing: border-box;
    }

    #posExpensesModal .pos-expense-row {
        display: grid;
        grid-template-columns: minmax(320px, 1fr) 170px 150px 110px;
        gap: 14px;
        align-items: center;
        padding: 15px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        margin-bottom: 10px;
        background: #ffffff;
        transition: .18s ease;
        width: 100%;
        box-sizing: border-box;
        min-width: 0;
    }

    #posExpensesModal .pos-expense-row:hover {
        border-color: #bfdbfe;
        box-shadow: 0 10px 24px rgba(37, 99, 235, .08);
    }

    #posExpensesModal .pos-expense-main {
        color: #0f172a;
        font-weight: 950;
        line-height: 1.25;
        word-break: normal !important;
        overflow-wrap: anywhere;
        white-space: normal;
    }

    #posExpensesModal .pos-expense-sub {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-top: 5px;
        line-height: 1.35;
        word-break: normal !important;
        overflow-wrap: anywhere;
        white-space: normal;
    }

    #posExpensesModal .pos-expense-amount {
        color: #155dfc;
        font-weight: 950;
        text-align: right;
        white-space: nowrap;
        font-size: 15px;
    }

    #posExpensesModal .pos-expense-delete {
        height: 40px;
        border: 1px solid #fecaca;
        background: #fff5f5;
        color: #b91c1c;
        border-radius: 13px;
        padding: 0 13px;
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
    }

    #posExpensesModal .pos-expense-delete:hover {
        background: #fee2e2;
    }

    #posExpensesModal .pos-expense-locked {
        height: 40px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
        border-radius: 13px;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 950;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    #posExpensesModal .pos-expense-form-wrap {
        padding: 22px;
    }

    #posExpensesModal .pos-expense-form-title {
        margin-bottom: 18px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
    }

    #posExpensesModal .pos-expense-form-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
        color: #15803d;
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
    }

    #posExpensesModal .pos-expense-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    #posExpensesModal .pos-expense-field {
        margin-bottom: 0;
        min-width: 0;
    }

    #posExpensesModal .pos-expense-field-full {
        grid-column: 1 / -1;
    }

    #posExpensesModal .pos-expense-form-footer {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    #posExpensesModal .pos-expense-save-btn {
        min-width: 190px;
        justify-content: center;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    #posExpensesModal .pos-expense-footer {
        background: #ffffff;
        flex-shrink: 0;
    }

    .pos-confirm-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .pos-confirm-actions .pos-modal-primary,
    .pos-confirm-actions .pos-modal-secondary {
        width: 100%;
    }

    @media (max-width: 991.98px) {
        #posExpensesModal .pos-expense-modal {
            width: calc(100vw - 18px) !important;
            max-height: 94vh !important;
        }

        #posExpensesModal .pos-expense-filters {
            grid-template-columns: 1fr 1fr;
        }

        #posExpensesModal .pos-expense-filter-actions {
            grid-column: 1 / -1;
        }

        #posExpensesModal .pos-expense-row {
            grid-template-columns: minmax(260px, 1fr) 140px 120px 90px;
        }
    }

    @media (max-width: 767.98px) {
        #posExpensesModal .pos-expense-modal {
            width: calc(100vw - 12px) !important;
            border-radius: 24px;
        }

        #posExpensesModal .pos-expense-head {
            padding: 18px 16px;
        }

        #posExpensesModal .pos-expense-head-left {
            align-items: flex-start;
        }

        #posExpensesModal .pos-expense-head-icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            font-size: 23px;
        }

        #posExpensesModal .pos-expense-head h4 {
            font-size: 20px;
        }

        #posExpensesModal .pos-expense-tabs {
            padding: 10px 12px;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        #posExpensesModal .pos-expense-tab {
            width: 100%;
            padding: 0 8px;
            font-size: 12px;
            height: 42px;
        }

        #posExpensesModal .pos-expense-body {
            padding: 12px;
        }

        #posExpensesModal .pos-expense-summary-card {
            display: grid;
            grid-template-columns: 1fr;
            padding: 14px;
        }

        #posExpensesModal .pos-expense-summary-actions {
            justify-content: stretch;
            display: grid;
            grid-template-columns: 1fr;
        }

        #posExpensesModal .pos-expense-summary-actions .pos-mini-action-btn,
        #posExpensesModal .pos-expense-summary-actions span {
            width: 100%;
            justify-content: center;
        }

        #posExpensesModal .pos-expense-filter-card {
            padding: 12px;
        }

        #posExpensesModal .pos-expense-filters {
            grid-template-columns: 1fr;
        }

        #posExpensesModal .pos-expense-row {
            grid-template-columns: 1fr;
            gap: 8px;
            padding: 14px;
        }

        #posExpensesModal .pos-expense-amount {
            text-align: left;
        }

        #posExpensesModal .pos-expense-delete,
        #posExpensesModal .pos-expense-locked {
            width: 100%;
        }

        #posExpensesModal .pos-expense-form-wrap {
            padding: 15px;
        }

        #posExpensesModal .pos-expense-form-title {
            display: grid;
            grid-template-columns: 1fr;
        }

        #posExpensesModal .pos-expense-form-badge {
            width: fit-content;
        }

        #posExpensesModal .pos-expense-form-grid {
            grid-template-columns: 1fr;
        }

        #posExpensesModal .pos-expense-form-footer {
            display: grid;
            grid-template-columns: 1fr;
        }

        #posExpensesModal .pos-expense-save-btn,
        #posExpensesModal .pos-expense-form-footer .pos-modal-secondary {
            width: 100%;
        }
    }
</style>