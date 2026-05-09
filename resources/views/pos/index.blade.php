@extends('components.pos-layout')

@section('title', 'Point of Sale')

@section('content')
<div class="pos-screen">
    <section class="pos-left-panel">
        <div class="pos-search-card">
            <div class="pos-search-box">
                <i class="mdi mdi-magnify"></i>
                <input type="text"
                       id="posSearchInput"
                       placeholder="Search medicine by name, category, type, code or scan barcode"
                       autocomplete="off">

                <button type="button" id="posBarcodeBtn" class="pos-barcode-btn">
                    <i class="mdi mdi-barcode-scan"></i>
                </button>
            </div>
        </div>

        <div class="pos-results-card">
            <div class="pos-section-head">
                <div>
                    <h5>Search Results</h5>
                    <span id="posResultCount">Ready</span>
                </div>

                <div class="pos-sale-type">
                    <button type="button" class="active" data-sale-type="retail">Retail</button>
                    <button type="button" data-sale-type="wholesale">Wholesale</button>
                </div>
            </div>

            <div class="pos-table-wrap">
                <table class="pos-products-table">
                    <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Strength</th>
                        <th>Default Unit</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody id="posProductsBody">
                    <tr>
                        <td colspan="6">
                            <div class="pos-empty">
                                Start typing product name, code, category, type, generic name, brand, or scan barcode.
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <aside class="pos-cart-panel">
        <div class="pos-cart-card">
            <div class="pos-cart-head">
                <h5>Current Sale <span id="cartCount">(0)</span></h5>

                <div class="pos-cart-head-actions">
                    <button type="button" id="posExpensesBtn" class="pos-mini-action-btn">
                        <i class="mdi mdi-cash-minus"></i>
                        Expenses
                    </button>
                    <button type="button" id="todaySalesBtn" class="pos-mini-action-btn">
                        <i class="mdi mdi-receipt-text-clock-outline"></i>
                        Today
                    </button>

                    <button type="button" id="clearCartBtn" class="pos-clear-btn" title="Clear cart">
                        <i class="mdi mdi-trash-can-outline"></i>
                    </button>
                </div>
            </div>

            <div id="cartItems" class="pos-cart-items">
                <div class="pos-cart-empty">
                    <i class="mdi mdi-cart-outline"></i>
                    <strong>No items added</strong>
                    <span>Select a product from the left side.</span>
                </div>
            </div>

            <div class="pos-cart-summary">
                <div class="pos-summary-line">
                    <span>Subtotal</span>
                    <strong id="subtotalText">0.00</strong>
                </div>

                <div class="pos-summary-line">
                    <span>Discount</span>
                    <input type="number" min="0" step="0.01" id="discountInput" value="0">
                </div>

                <div class="pos-summary-total">
                    <span>Total</span>
                    <strong id="totalText">0.00</strong>
                </div>

                <div class="pos-payment-grid">
                    <div>
                        <label>Customer Name</label>
                        <input type="text" id="customerNameInput" placeholder="Walk-in Customer">
                    </div>

                    <div>
                        <label>Customer Phone</label>
                        <input type="text" id="customerPhoneInput" placeholder="Optional">
                    </div>

                    <div>
                        <label>Payment Method</label>
                        <select id="paymentMethodInput">
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="card">Card</option>
                            <option value="bank">Bank</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                </div>

                <div class="pos-summary-line mt-2">
                    <span>Amount To Pay</span>
                    <strong id="amountToPayText">0.00</strong>
                </div>

                <button type="button" id="completeSaleBtn" class="pos-complete-btn">
                    <span>
                        <i class="mdi mdi-credit-card-check-outline"></i>
                        Complete Sale
                    </span>
                    <strong id="completeTotalText">0.00</strong>
                </button>
            </div>
        </div>
    </aside>
</div>

<div id="posMessageModal" class="pos-message-overlay d-none">
    <div class="pos-message-card">
        <div class="pos-message-icon" id="posMessageIcon">
            <i class="mdi mdi-check-circle-outline"></i>
        </div>

        <h4 id="posMessageTitle">Success</h4>
        <p id="posMessageText">Message here</p>

        <div class="pos-message-actions">
            <button type="button" id="posMessageOkBtn" class="pos-message-btn">
                OK
            </button>
        </div>
    </div>
</div>

<div id="posConfirmModal" class="pos-message-overlay d-none">
    <div class="pos-message-card">
        <div class="pos-message-icon warning">
            <i class="mdi mdi-alert-outline"></i>
        </div>

        <h4 id="posConfirmTitle">Confirm Action</h4>
        <p id="posConfirmText">Are you sure?</p>

        <div class="pos-message-actions pos-confirm-actions">
            <button type="button" id="posConfirmCancelBtn" class="pos-modal-secondary">
                Cancel
            </button>

            <button type="button" id="posConfirmYesBtn" class="pos-modal-primary">
                Yes, Continue
            </button>
        </div>
    </div>
</div>



<div id="todaySalesModal" class="pos-modal-overlay d-none">
    <div class="pos-modal-card pos-modal-wide">
        <div class="pos-modal-head">
            <div>
                <h4>Today Sales</h4>
                <p>View and reprint receipts from today without leaving POS.</p>
            </div>

            <button type="button" class="pos-modal-close" data-close-pos-modal>
                <i class="mdi mdi-close"></i>
            </button>
        </div>

        <div class="pos-modal-body">
            <div id="todaySalesBody" class="today-sales-list">
                <div class="pos-empty">Loading today sales...</div>
            </div>
        </div>
    </div>
</div>
@include('pos.partials._expenses_modal')
<div id="receiptModal" class="pos-modal-overlay d-none">
    <div class="pos-modal-card pos-receipt-modal">
        <div class="pos-modal-head">
            <div>
                <h4 id="receiptModalTitle">Receipt</h4>
                <p>Preview and print receipt.</p>
            </div>

            <button type="button" class="pos-modal-close" data-close-pos-modal>
                <i class="mdi mdi-close"></i>
            </button>
        </div>

        <div class="pos-modal-body">
            <div id="receiptPreview" class="receipt-preview-box">
                <div class="pos-empty">Receipt preview will appear here.</div>
            </div>
        </div>

        <div class="pos-modal-footer">
            <button type="button" class="pos-modal-secondary" data-close-pos-modal>
                Close
            </button>

            <button type="button" id="printReceiptBtn" class="pos-modal-primary">
                <i class="mdi mdi-printer"></i>
                Print Receipt
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>

    .pos-screen {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 420px;
        gap: 22px;
        align-items: start;
    }

    .pos-left-panel,
    .pos-cart-panel {
        min-width: 0;
    }

    .pos-search-card,
    .pos-results-card,
    .pos-cart-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 22px;
        box-shadow: var(--pos-shadow);
    }

    .pos-search-card {
        padding: 20px;
        margin-bottom: 18px;
    }

    .pos-search-box {
        height: 58px;
        border: 2px solid #155dfc;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 14px 0 18px;
        background: #ffffff;
        box-shadow: 0 0 0 5px rgba(37, 99, 235, .05);
    }

    .pos-search-box i {
        color: #64748b;
        font-size: 24px;
    }

    .pos-search-box input {
        border: 0;
        outline: 0;
        flex: 1;
        height: 100%;
        color: #0f172a;
        font-size: 15px;
        font-weight: 700;
    }

    .pos-search-box input::placeholder {
        color: #94a3b8;
    }

    .pos-barcode-btn {
        width: 42px;
        height: 42px;
        border-radius: 13px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #155dfc;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .pos-barcode-btn i {
        color: #155dfc;
        font-size: 22px;
    }

    .pos-results-card {
        overflow: hidden;
    }

    .pos-section-head {
        padding: 20px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .pos-section-head h5,
    .pos-cart-head h5 {
        margin: 0;
        color: #0f172a;
        font-size: 18px;
        font-weight: 950;
        letter-spacing: -.025em;
    }

    .pos-section-head span {
        display: inline-flex;
        align-items: center;
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        padding: 5px 10px;
        margin-top: 7px;
    }

    .pos-sale-type {
        display: inline-flex;
        padding: 4px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 14px;
    }

    .pos-sale-type button {
        border: 0;
        background: transparent;
        border-radius: 11px;
        height: 34px;
        padding: 0 13px;
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
    }

    .pos-sale-type button.active {
        background: #155dfc;
        color: #ffffff;
        box-shadow: 0 10px 18px rgba(37, 99, 235, .2);
    }

    .pos-table-wrap {
        overflow-x: auto;
    }

    .pos-products-table {
        width: 100%;
        min-width: 800px;
        border-collapse: collapse;
    }

    .pos-products-table th {
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 14px 18px;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .pos-products-table td {
        padding: 16px 18px;
        border-bottom: 1px solid #edf2f7;
        vertical-align: middle;
        color: #334155;
        font-size: 14px;
        font-weight: 750;
    }

    .pos-product-info {
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 0;
    }

    .pos-product-thumb {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        background: linear-gradient(135deg, #f8fafc, #eff6ff);
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #155dfc;
        font-size: 25px;
        flex: 0 0 auto;
    }

    .pos-product-name {
        color: #0f172a;
        font-size: 15px;
        font-weight: 950;
        line-height: 1.15;
    }

    .pos-product-meta {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-top: 4px;
    }

    .pos-stock-ok {
        color: #16a34a;
        font-weight: 950;
    }

    .pos-stock-bad {
        color: #ef4444;
        font-weight: 950;
    }

    .pos-add-btn {
        width: 40px;
        height: 40px;
        border-radius: 13px;
        border: 1px solid #dbeafe;
        background: #ffffff;
        color: #155dfc;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
        font-weight: 900;
    }

    .pos-add-btn:hover {
        background: #155dfc;
        color: #ffffff;
    }

    .pos-add-btn:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .pos-empty,
    .pos-cart-empty {
        padding: 42px 20px;
        text-align: center;
        color: #64748b;
        font-weight: 800;
    }

    .pos-cart-card {
        position: sticky;
        top: 108px;
        overflow: hidden;
    }

    .pos-cart-head {
        height: 58px;
        padding: 0 18px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pos-cart-head-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .pos-mini-action-btn {
        height: 34px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #155dfc;
        border-radius: 12px;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .pos-clear-btn {
        border: 0;
        background: transparent;
        color: #64748b;
        font-size: 21px;
    }

    .pos-cart-items {
        max-height: 390px;
        overflow-y: auto;
    }

    .pos-cart-empty i {
        display: block;
        font-size: 36px;
        color: #94a3b8;
        margin-bottom: 6px;
    }

    .pos-cart-empty strong,
    .pos-cart-empty span {
        display: block;
    }

    .pos-cart-item {
        padding: 16px 18px;
        border-bottom: 1px solid #e2e8f0;
    }

    .pos-cart-top {
        display: flex;
        justify-content: space-between;
        gap: 12px;
    }

    .pos-cart-name {
        color: #0f172a;
        font-size: 14px;
        font-weight: 950;
    }

    .pos-cart-meta {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-top: 3px;
    }

    .pos-cart-unit-select {
        margin-top: 8px;
        width: 100%;
        height: 36px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        color: #0f172a;
        font-size: 12px;
        font-weight: 850;
        padding: 0 10px;
        outline: 0;
    }

    .pos-cart-unit-select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .08);
    }

    .pos-remove-item {
        width: 30px;
        height: 30px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        color: #64748b;
    }

    .pos-cart-bottom {
        margin-top: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .pos-qty-control {
        display: inline-flex;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }

    .pos-qty-control button {
        width: 36px;
        height: 34px;
        border: 0;
        background: #ffffff;
        color: #0f172a;
        font-weight: 900;
    }

    .pos-qty-control span {
        width: 42px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        color: #0f172a;
        border-left: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
    }

    .pos-line-total {
        color: #0f172a;
        font-size: 15px;
        font-weight: 950;
    }

    .pos-cart-summary {
        padding: 18px;
        border-top: 1px solid #e2e8f0;
        background: #ffffff;
    }

    .pos-summary-line {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
        color: #334155;
        font-size: 14px;
        font-weight: 800;
    }

    .pos-summary-line input {
        width: 110px;
        height: 38px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0 10px;
        text-align: right;
        font-weight: 800;
    }

    .pos-summary-total {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 0;
        margin: 6px 0 12px;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
    }

    .pos-summary-total span {
        font-size: 17px;
        color: #0f172a;
        font-weight: 950;
    }

    .pos-summary-total strong {
        font-size: 22px;
        color: #155dfc;
        font-weight: 950;
    }

    .pos-payment-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .pos-payment-grid label {
        display: block;
        color: #475569;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 6px;
    }

    .pos-payment-grid input,
    .pos-payment-grid select {
        width: 100%;
        height: 42px;
        border: 1px solid #e2e8f0;
        border-radius: 13px;
        padding: 0 12px;
        color: #0f172a;
        font-size: 13px;
        font-weight: 800;
        outline: 0;
        background: #ffffff;
    }

    .pos-complete-btn {
        margin-top: 12px;
        width: 100%;
        height: 54px;
        border: 0;
        border-radius: 15px;
        background: linear-gradient(135deg, #155dfc, #0f3fbf);
        color: #ffffff;
        box-shadow: 0 16px 28px rgba(37, 99, 235, .28);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 18px;
        font-weight: 950;
    }

    .pos-complete-btn:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    .pos-message-overlay,
    .pos-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 5000;
        background: rgba(15, 23, 42, .55);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 18px;
        backdrop-filter: blur(5px);
    }

    .pos-modal-overlay {
        z-index: 5100;
    }

    .pos-message-overlay.d-none,
    .pos-modal-overlay.d-none {
        display: none !important;
    }

    .pos-message-card {
        width: min(420px, 100%);
        background: #ffffff;
        border-radius: 24px;
        padding: 28px;
        box-shadow: 0 30px 90px rgba(15, 23, 42, .28);
        text-align: center;
        border: 1px solid #e2e8f0;
        animation: posPop .18s ease-out;
    }

    @keyframes posPop {
        from {
            transform: translateY(10px) scale(.96);
            opacity: 0;
        }

        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    .pos-message-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 14px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #dcfce7;
        color: #15803d;
        font-size: 34px;
    }

    .pos-message-icon.error {
        background: #fee2e2;
        color: #b91c1c;
    }

    .pos-message-icon.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .pos-message-card h4 {
        color: #0f172a;
        font-weight: 950;
        margin-bottom: 8px;
    }

    .pos-message-card p {
        color: #64748b;
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 22px;
    }

    .pos-message-btn {
        width: 100%;
        height: 46px;
        border: 0;
        border-radius: 14px;
        background: linear-gradient(135deg, #155dfc, #0f3fbf);
        color: #ffffff;
        font-weight: 950;
    }

    .pos-modal-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 30px 90px rgba(15, 23, 42, .28);
        width: min(520px, 100%);
        max-height: 92vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        animation: posPop .18s ease-out;
    }

    .pos-modal-wide {
        width: min(920px, 100%);
    }

    .pos-receipt-modal {
        width: min(460px, 100%);
    }

    .pos-modal-head {
        padding: 18px 20px;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #eff6ff, #ffffff);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .pos-modal-head h4 {
        margin: 0;
        color: #0f172a;
        font-weight: 950;
        letter-spacing: -.02em;
    }

    .pos-modal-head p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .pos-modal-close {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 12px;
        background: #ffffff;
        color: #64748b;
        font-size: 20px;
    }

    .pos-modal-body {
        padding: 18px;
        overflow-y: auto;
    }

    .pos-modal-footer {
        padding: 14px 18px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .pos-modal-primary,
    .pos-modal-secondary {
        height: 42px;
        border-radius: 13px;
        padding: 0 16px;
        font-weight: 950;
        border: 0;
    }

    .pos-modal-primary {
        background: #155dfc;
        color: #ffffff;
    }

    .pos-modal-secondary {
        background: #f1f5f9;
        color: #334155;
    }

    .today-sale-row {
        display: grid;
        grid-template-columns: 1.4fr 1fr 1fr 1fr auto;
        gap: 12px;
        align-items: center;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        margin-bottom: 10px;
        background: #ffffff;
    }

    .today-sale-main {
        color: #0f172a;
        font-weight: 950;
    }

    .today-sale-sub {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-top: 3px;
    }

    .today-sale-amount {
        color: #155dfc;
        font-size: 15px;
        font-weight: 950;
        text-align: right;
    }

    .today-sale-print {
        height: 36px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #155dfc;
        border-radius: 12px;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 950;
    }

    .receipt-preview-box {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        padding: 14px;
        overflow-x: auto;
    }

    @media (max-width: 1199.98px) {
        .pos-screen {
            grid-template-columns: 1fr;
        }

        .pos-cart-card {
            position: relative;
            top: auto;
        }
    }

   @media (max-width: 767.98px) {
    .content-wrapper,
    .container-fluid {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .pos-screen {
        display: flex;
        flex-direction: column;
        gap: 14px;
        padding-bottom: 20px;
    }

    .pos-search-card {
        position: sticky;
        top: 0;
        z-index: 30;
        padding: 12px;
        margin-bottom: 12px;
        border-radius: 20px;
        background: #ffffff;
    }

    .pos-search-box {
        height: 54px;
        border-radius: 18px;
        padding: 0 12px 0 14px;
    }

    .pos-search-box input {
        font-size: 13px;
    }

    .pos-section-head {
        padding: 14px;
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }

    .pos-sale-type {
        width: 100%;
    }

    .pos-sale-type button {
        flex: 1;
        height: 40px;
    }

    .pos-table-wrap {
        overflow: visible;
    }

    .pos-products-table,
    .pos-products-table thead,
    .pos-products-table tbody,
    .pos-products-table tr,
    .pos-products-table th,
    .pos-products-table td {
        display: block;
        width: 100%;
    }

    .pos-products-table {
        min-width: 100%;
        padding: 10px;
    }

    .pos-products-table thead {
        display: none;
    }

    .pos-products-table tr {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 14px;
        margin-bottom: 12px;
    }

    .pos-products-table td {
        border: 0;
        padding: 0;
    }

    .pos-products-table td:nth-child(2),
    .pos-products-table td:nth-child(3),
    .pos-products-table td:nth-child(4),
    .pos-products-table td:nth-child(5) {
        margin-top: 8px;
        font-size: 12px;
        color: #64748b;
        font-weight: 800;
    }

    .pos-add-btn {
        width: 100%;
        height: 46px;
        margin-top: 12px;
        border-radius: 14px;
        background: #155dfc;
        color: #ffffff;
        border: 0;
    }

    .pos-cart-card {
        position: relative !important;
        top: auto !important;
        border-radius: 22px;
        overflow: visible;
    }

    .pos-cart-head {
        height: auto;
        min-height: 58px;
        padding: 12px 14px;
        align-items: flex-start;
        gap: 10px;
        flex-direction: column;
    }

    .pos-cart-head-actions {
        width: 100%;
        display: grid;
        grid-template-columns: 1fr 1fr 42px;
        gap: 8px;
    }

    .pos-mini-action-btn {
        width: 100%;
        height: 38px;
        justify-content: center;
    }

    .pos-clear-btn {
        width: 42px;
        height: 38px;
        border-radius: 12px;
        background: #f8fafc;
    }

    .pos-cart-items {
        max-height: none !important;
        overflow: visible !important;
    }

    .pos-cart-summary {
        padding: 14px;
    }

    .pos-payment-grid input,
    .pos-payment-grid select {
        height: 44px;
        border-radius: 14px;
    }

    .pos-complete-btn {
        height: 56px;
        border-radius: 18px;
    }

    .pos-modal-overlay,
    .pos-message-overlay {
        align-items: center;
        padding: 14px;
        overflow-y: auto;
    }

    .pos-modal-card,
    .pos-modal-wide,
    .pos-receipt-modal,
    .pos-message-card {
        width: 100%;
        max-height: calc(100vh - 28px);
        border-radius: 22px;
        margin: auto 0;
    }

    .pos-modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 180px);
    }

    .pos-modal-footer {
        display: grid;
        grid-template-columns: 1fr;
    }

    .pos-modal-primary,
    .pos-modal-secondary {
        width: 100%;
        height: 48px;
    }

    .today-sale-row {
        grid-template-columns: 1fr;
    }

    .today-sale-amount {
        text-align: left;
    }
}

    
</style>
@include('pos.partials._expenses_styles')
@endpush

@push('scripts')
<script>
    const POS = {
        routes: {
            search: @json(route('pos.products.search')),
            units: @json(url('/pos/products')),
            checkout: @json(route('pos.checkout')),
            todaySales: @json(route('pos.today-sales')),
        },
        csrf: @json(csrf_token()),
        saleType: 'retail',
        branchId: String(document.getElementById('posBranchSelect')?.value || ''),
        cart: [],
        searchTimer: null,
        lastReceiptUrl: null,
        lastSaleNo: null,
    };

    const money = (value) => Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    const els = {
        messageModal: document.getElementById('posMessageModal'),
        messageIcon: document.getElementById('posMessageIcon'),
        messageTitle: document.getElementById('posMessageTitle'),
        messageText: document.getElementById('posMessageText'),
        messageOkBtn: document.getElementById('posMessageOkBtn'),

        todaySalesBtn: document.getElementById('todaySalesBtn'),
        todaySalesModal: document.getElementById('todaySalesModal'),
        todaySalesBody: document.getElementById('todaySalesBody'),

        receiptModal: document.getElementById('receiptModal'),
        receiptPreview: document.getElementById('receiptPreview'),
        receiptModalTitle: document.getElementById('receiptModalTitle'),
        printReceiptBtn: document.getElementById('printReceiptBtn'),

        clock: document.getElementById('posClock'),
        branch: document.getElementById('posBranchSelect'),
        search: document.getElementById('posSearchInput'),
        productsBody: document.getElementById('posProductsBody'),
        resultCount: document.getElementById('posResultCount'),
        cartItems: document.getElementById('cartItems'),
        cartCount: document.getElementById('cartCount'),
        subtotalText: document.getElementById('subtotalText'),
        discountInput: document.getElementById('discountInput'),
        totalText: document.getElementById('totalText'),
        amountToPayText: document.getElementById('amountToPayText'),
        completeTotalText: document.getElementById('completeTotalText'),
        completeSaleBtn: document.getElementById('completeSaleBtn'),
        clearCartBtn: document.getElementById('clearCartBtn'),
        customerNameInput: document.getElementById('customerNameInput'),
        customerPhoneInput: document.getElementById('customerPhoneInput'),
        paymentMethodInput: document.getElementById('paymentMethodInput'),
    };

    setInterval(() => {
        const now = new Date();

        if (els.clock) {
            els.clock.textContent = now.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }, 1000);

    document.querySelectorAll('.pos-sale-type button').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.pos-sale-type button').forEach(btn => btn.classList.remove('active'));

            button.classList.add('active');

            POS.saleType = button.dataset.saleType;
            POS.cart = [];

            renderCart();
            searchProducts();
        });
    });

    els.branch?.addEventListener('change', () => {
        POS.branchId = String(els.branch.value || '');
        POS.cart = [];

        renderCart();
        searchProducts();
    });

    els.search?.addEventListener('input', () => {
        clearTimeout(POS.searchTimer);
        POS.searchTimer = setTimeout(searchProducts, 250);
    });

    els.discountInput?.addEventListener('input', renderCart);

    els.clearCartBtn?.addEventListener('click', () => {
        POS.cart = [];
        renderCart();
    });

    els.messageOkBtn?.addEventListener('click', () => {
        els.messageModal.classList.add('d-none');

        if (POS.lastReceiptUrl) {
            openReceipt(POS.lastReceiptUrl, POS.lastSaleNo || 'Receipt');
            POS.lastReceiptUrl = null;
            POS.lastSaleNo = null;
        }
    });

    els.todaySalesBtn?.addEventListener('click', () => {
        openTodaySalesModal();
    });

    document.querySelectorAll('[data-close-pos-modal]').forEach(button => {
        button.addEventListener('click', () => {
            button.closest('.pos-modal-overlay')?.classList.add('d-none');
        });
    });

    els.printReceiptBtn?.addEventListener('click', () => {
        printReceipt();
    });

    async function searchProducts() {
        const q = els.search.value || '';

        if (!POS.branchId) {
            renderProducts([]);
            return;
        }

        const url = new URL(POS.routes.search);
        url.searchParams.set('branch_id', POS.branchId);
        url.searchParams.set('sale_type', POS.saleType);
        url.searchParams.set('q', q);

        els.resultCount.textContent = 'Searching...';

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            renderProducts(data.products || []);
        } catch (error) {
            els.resultCount.textContent = 'Failed';

            els.productsBody.innerHTML = `
                <tr>
                    <td colspan="6">
                        <div class="pos-empty">Unable to load products.</div>
                    </td>
                </tr>
            `;
        }
    }

    function renderProducts(products) {
        els.resultCount.textContent = `${products.length} results`;

        if (!products.length) {
            els.productsBody.innerHTML = `
                <tr>
                    <td colspan="6">
                        <div class="pos-empty">No products found in this branch inventory.</div>
                    </td>
                </tr>
            `;
            return;
        }

        els.productsBody.innerHTML = products.map(product => {
            const unit = product.default_unit;
            const stockClass = product.has_stock ? 'pos-stock-ok' : 'pos-stock-bad';
            const stockText = product.available_base_units ?? 0;

            return `
                <tr>
                    <td>
                        <div class="pos-product-info">
                            <span class="pos-product-thumb">
                                <i class="mdi mdi-pill"></i>
                            </span>

                            <div>
                                <div class="pos-product-name">${escapeHtml(product.name)}</div>
                                <div class="pos-product-meta">
                                    ${escapeHtml(product.category || 'Product')}
                                    ${product.type ? ' • ' + escapeHtml(product.type) : ''}
                                    ${product.generic_name ? ' • ' + escapeHtml(product.generic_name) : ''}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td>${escapeHtml(product.strength || '-')}</td>
                    <td>${escapeHtml(unit?.unit_name || product.base_unit || '-')}</td>
                    <td><span class="${stockClass}">${stockText}</span></td>
                    <td><strong>${money(unit?.price || 0)}</strong></td>

                    <td class="text-right">
                        <button class="pos-add-btn"
                                type="button"
                                ${product.has_stock && unit ? '' : 'disabled'}
                                onclick="addProduct(${product.id})">
                            +
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function addProduct(productId) {
        const url = new URL(`${POS.routes.units}/${productId}/units`);
        url.searchParams.set('branch_id', POS.branchId);
        url.searchParams.set('sale_type', POS.saleType);

        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!data.ok || !data.units.length) {
            showPosMessage('warning', 'No Selling Unit', 'No selling unit configured for this product.');
            return;
        }

        const preferredUnit = data.units.find(unit => unit.is_default_sale_unit) || data.units[0];

        const existing = POS.cart.find(item => Number(item.product_id) === Number(data.product.id));

        if (existing) {
            if (existing.quantity + 1 > existing.available_sale_units) {
                showPosMessage('warning', 'Not Enough Inventory', 'Not enough available inventory.');
                return;
            }

            existing.quantity += 1;
        } else {
            POS.cart.push(makeCartItem(data.product, data.units, preferredUnit));
        }

        renderCart();
    }

    window.addProduct = addProduct;

    function makeCartItem(product, units, selectedUnit) {
        return {
            key: `${product.id}-${Date.now()}`,
            product_id: product.id,
            product_name: product.name,
            product_code: product.code,
            product_unit_id: selectedUnit.product_unit_id,
            unit_name: selectedUnit.unit_name,
            quantity_in_base_units: Number(selectedUnit.quantity_in_base_units || 1),
            available_sale_units: Number(selectedUnit.available_sale_units || 0),
            quantity: 1,
            unit_price: Number(selectedUnit.price || 0),
            line_discount: 0,
            units: units.map(unit => ({
                product_unit_id: unit.product_unit_id,
                unit_name: unit.unit_name,
                quantity_in_base_units: Number(unit.quantity_in_base_units || 1),
                price: Number(unit.price || 0),
                available_sale_units: Number(unit.available_sale_units || 0),
                is_default_sale_unit: Boolean(unit.is_default_sale_unit),
            })),
        };
    }

    function renderCart() {
        els.cartCount.textContent = `(${POS.cart.length})`;

        if (!POS.cart.length) {
            els.cartItems.innerHTML = `
                <div class="pos-cart-empty">
                    <i class="mdi mdi-cart-outline"></i>
                    <strong>No items added</strong>
                    <span>Select a product from the left side.</span>
                </div>
            `;
        } else {
            els.cartItems.innerHTML = POS.cart.map((item, index) => {
                const lineTotal = (item.unit_price * item.quantity) - Number(item.line_discount || 0);
                const unitOptions = item.units.map(unit => `
                    <option value="${unit.product_unit_id}" ${Number(unit.product_unit_id) === Number(item.product_unit_id) ? 'selected' : ''}>
                        ${escapeHtml(unit.unit_name)} - ${money(unit.price)} (${unit.available_sale_units} available)
                    </option>
                `).join('');

                return `
                    <div class="pos-cart-item">
                        <div class="pos-cart-top">
                            <div style="flex:1;min-width:0;">
                                <div class="pos-cart-name">${escapeHtml(item.product_name)}</div>
                                <div class="pos-cart-meta">
                                    ${money(item.unit_price)} • Max ${item.available_sale_units} ${escapeHtml(item.unit_name)}
                                </div>

                                <select class="pos-cart-unit-select" onchange="changeCartUnit(${index}, this.value)">
                                    ${unitOptions}
                                </select>
                            </div>

                            <button type="button" class="pos-remove-item" onclick="removeCartItem(${index})">×</button>
                        </div>

                        <div class="pos-cart-bottom">
                            <div class="pos-qty-control">
                                <button type="button" onclick="changeQty(${index}, -1)">−</button>
                                <span>${item.quantity}</span>
                                <button type="button" onclick="changeQty(${index}, 1)">+</button>
                            </div>

                            <div class="pos-line-total">${money(lineTotal)}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        const subtotal = calculateSubtotal();
        const discount = Number(els.discountInput.value || 0);
        const total = calculateTotal();

        els.subtotalText.textContent = money(subtotal);
        els.totalText.textContent = money(total);
        els.amountToPayText.textContent = money(total);
        els.completeTotalText.textContent = money(total);

        els.completeSaleBtn.disabled = POS.cart.length < 1 || total <= 0;
    }

    function changeCartUnit(index, productUnitId) {
        const item = POS.cart[index];

        if (!item) {
            return;
        }

        const selectedUnit = item.units.find(unit => Number(unit.product_unit_id) === Number(productUnitId));

        if (!selectedUnit) {
            return;
        }

        item.product_unit_id = selectedUnit.product_unit_id;
        item.unit_name = selectedUnit.unit_name;
        item.quantity_in_base_units = selectedUnit.quantity_in_base_units;
        item.available_sale_units = selectedUnit.available_sale_units;
        item.unit_price = selectedUnit.price;

        if (item.quantity > item.available_sale_units) {
            item.quantity = Math.max(1, item.available_sale_units);
        }

        renderCart();
    }

    function changeQty(index, delta) {
        const item = POS.cart[index];

        if (!item) {
            return;
        }

        const next = item.quantity + delta;

        if (next < 1) {
            return;
        }

        if (next > item.available_sale_units) {
            showPosMessage('warning', 'Not Enough Inventory', 'Not enough available inventory for selected unit.');
            return;
        }

        item.quantity = next;
        renderCart();
    }

    function removeCartItem(index) {
        POS.cart.splice(index, 1);
        renderCart();
    }

    window.changeCartUnit = changeCartUnit;
    window.changeQty = changeQty;
    window.removeCartItem = removeCartItem;

    els.completeSaleBtn?.addEventListener('click', async () => {
        const discount = Number(els.discountInput.value || 0);
        const total = calculateTotal();

        if (!POS.cart.length || total <= 0) {
            showPosMessage('warning', 'Empty Sale', 'Please add at least one product before completing the sale.');
            return;
        }

        setCheckoutButtonLoading(true);

        try {
            const response = await fetch(POS.routes.checkout, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': POS.csrf,
                },
                body: JSON.stringify({
                    branch_id: POS.branchId,
                    sale_type: POS.saleType,
                    customer_name: els.customerNameInput.value,
                    customer_phone: els.customerPhoneInput.value,
                    payment_method: els.paymentMethodInput.value,
                    paid_amount: total,
                    discount_amount: discount,
                    tax_amount: 0,
                    notes: null,
                    items: POS.cart.map(item => ({
                        product_id: item.product_id,
                        product_unit_id: item.product_unit_id,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                        line_discount: item.line_discount || 0,
                    })),
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                showPosMessage('error', 'Sale Failed', data.message || 'Sale failed. Please try again.');
                return;
            }

            POS.cart = [];
            els.discountInput.value = 0;
            els.customerNameInput.value = '';
            els.customerPhoneInput.value = '';

            POS.lastReceiptUrl = data.sale.receipt_url;
            POS.lastSaleNo = data.sale.sale_no;

            renderCart();
            searchProducts();

            showPosMessage(
                'success',
                'Sale Completed',
                `Sale completed successfully. Receipt: ${data.sale.sale_no}`
            );
        } catch (error) {
            showPosMessage('error', 'Sale Failed', 'Sale failed. Please check connection and try again.');
        } finally {
            setCheckoutButtonLoading(false);
            renderCart();
        }
    });

    async function openTodaySalesModal() {
        els.todaySalesModal.classList.remove('d-none');
        els.todaySalesBody.innerHTML = '<div class="pos-empty">Loading today sales...</div>';

        const url = new URL(POS.routes.todaySales);
        url.searchParams.set('branch_id', POS.branchId);

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!data.ok || !data.sales.length) {
                els.todaySalesBody.innerHTML = '<div class="pos-empty">No sales found for today.</div>';
                return;
            }

            els.todaySalesBody.innerHTML = data.sales.map(sale => `
                <div class="today-sale-row">
                    <div>
                        <div class="today-sale-main">${escapeHtml(sale.sale_no)}</div>
                        <div class="today-sale-sub">${escapeHtml(sale.sold_at)} • ${escapeHtml(sale.cashier)}</div>
                    </div>

                    <div>
                        <div class="today-sale-main">${escapeHtml(sale.customer)}</div>
                        <div class="today-sale-sub">${sale.items_count} item(s)</div>
                    </div>

                    <div>
                        <div class="today-sale-main">${escapeHtml(sale.payment_method)}</div>
                        <div class="today-sale-sub">${escapeHtml(sale.payment_status)}</div>
                    </div>

                    <div class="today-sale-amount">${money(sale.total_amount)}</div>

                    <div>
                        <button type="button" class="today-sale-print" onclick="openReceipt('${sale.receipt_url}', '${escapeAttr(sale.sale_no)}')">
                            Print
                        </button>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            els.todaySalesBody.innerHTML = '<div class="pos-empty">Unable to load today sales.</div>';
        }
    }

    async function openReceipt(receiptUrl, saleNo = 'Receipt') {
        els.receiptModalTitle.textContent = saleNo;
        els.receiptPreview.innerHTML = '<div class="pos-empty">Loading receipt...</div>';
        els.receiptModal.classList.remove('d-none');

        try {
            const response = await fetch(receiptUrl, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!data.ok) {
                els.receiptPreview.innerHTML = '<div class="pos-empty">Unable to load receipt.</div>';
                return;
            }

            els.receiptModalTitle.textContent = data.sale_no;
            els.receiptPreview.innerHTML = data.html;
        } catch (error) {
            els.receiptPreview.innerHTML = '<div class="pos-empty">Unable to load receipt.</div>';
        }
    }

    function printReceipt() {
        const receiptHtml = els.receiptPreview.innerHTML;

        if (!receiptHtml || receiptHtml.includes('Receipt preview will appear here')) {
            return;
        }

        const printWindow = window.open('', '_blank', 'width=420,height=650');

        printWindow.document.open();
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Receipt</title>
            </head>
            <body>
                ${receiptHtml}
                <script>
                    window.onload = function () {
                        window.focus();
                        window.print();
                        setTimeout(function () {
                            window.close();
                        }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);
        printWindow.document.close();
    }

    window.openReceipt = openReceipt;

    function setCheckoutButtonLoading(isLoading) {
        if (!els.completeSaleBtn) {
            return;
        }

        if (isLoading) {
            els.completeSaleBtn.disabled = true;
            els.completeSaleBtn.innerHTML = '<span><i class="mdi mdi-loading mdi-spin"></i> Processing...</span>';
            return;
        }

        const total = calculateTotal();

        els.completeSaleBtn.innerHTML = `
            <span>
                <i class="mdi mdi-credit-card-check-outline"></i>
                Complete Sale
            </span>
            <strong id="completeTotalText">${money(total)}</strong>
        `;

        els.completeTotalText = document.getElementById('completeTotalText');
        els.completeSaleBtn.disabled = POS.cart.length < 1 || total <= 0;
    }

    function calculateSubtotal() {
        return POS.cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
    }

    function calculateTotal() {
        const subtotal = calculateSubtotal();
        const discount = Number(els.discountInput.value || 0);

        return Math.max(0, subtotal - discount);
    }

    function showPosMessage(type, title, message) {
        els.messageIcon.className = 'pos-message-icon';

        if (type === 'error') {
            els.messageIcon.classList.add('error');
            els.messageIcon.innerHTML = '<i class="mdi mdi-alert-circle-outline"></i>';
        } else if (type === 'warning') {
            els.messageIcon.classList.add('warning');
            els.messageIcon.innerHTML = '<i class="mdi mdi-alert-outline"></i>';
        } else {
            els.messageIcon.innerHTML = '<i class="mdi mdi-check-circle-outline"></i>';
        }

        els.messageTitle.textContent = title;
        els.messageText.textContent = message;
        els.messageModal.classList.remove('d-none');
    }

    function escapeHtml(value) {
        return String(value || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function escapeAttr(value) {
        return String(value || '')
            .replaceAll('\\', '\\\\')
            .replaceAll("'", "\\'")
            .replaceAll('"', '&quot;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;');
    }

    renderCart();
    searchProducts();
</script>
@include('pos.partials._expenses_scripts')
@endpush
@endsection