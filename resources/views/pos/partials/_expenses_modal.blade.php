<div id="posExpensesModal" class="pos-modal-overlay d-none">
    <div class="pos-modal-card pos-expense-modal">
        <div class="pos-modal-head pos-expense-head">
            <div class="pos-expense-head-left">
                <span class="pos-expense-head-icon">
                    <i class="mdi mdi-cash-minus"></i>
                </span>

                <div>
                    <h4>POS Expenses</h4>
                    <p>Add expenses and review expenses by year or specific day.</p>
                </div>
            </div>

            <button type="button" class="pos-modal-close" data-close-pos-modal>
                <i class="mdi mdi-close"></i>
            </button>
        </div>

        <div class="pos-expense-tabs">
            <button type="button" class="pos-expense-tab active" data-pos-expense-tab="list">
                <i class="mdi mdi-format-list-bulleted"></i>
                Expense List
            </button>

            <button type="button" class="pos-expense-tab" data-pos-expense-tab="add">
                <i class="mdi mdi-plus-circle-outline"></i>
                Add Expense
            </button>
        </div>

        <div class="pos-modal-body pos-expense-body">
            <div id="posExpenseTabList" class="pos-expense-tab-panel active">
                <div class="pos-expense-summary-card">
                    <div>
                        <h5>Expense List</h5>
                        <p>Filter expenses by year or by a specific day.</p>
                    </div>

                    <div class="pos-expense-summary-actions">
                        <span id="posExpenseSummary">Ready</span>

                        <button type="button" id="posRefreshExpensesBtn" class="pos-mini-action-btn">
                            <i class="mdi mdi-refresh"></i>
                            Refresh
                        </button>
                    </div>
                </div>

                <div class="pos-expense-filter-card">
                    <div class="pos-expense-filters">
                        <div>
                            <label>Year</label>
                            <select id="posExpenseYear">
                                @for($year = now()->year + 1; $year >= now()->year - 5; $year--)
                                    <option value="{{ $year }}" {{ $year === now()->year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label>Specific Day</label>
                            <input type="date" id="posExpenseFilterDate" value="{{ now()->toDateString() }}">
                        </div>

                        <div class="pos-expense-filter-actions">
                            <label>&nbsp;</label>
                            <button type="button" id="posClearExpenseDayBtn" class="pos-modal-secondary pos-expense-all-btn">
                                All Year
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pos-expense-list-shell">
                    <div id="posExpensesBody" class="pos-expense-list">
                        <div class="pos-empty">Loading expenses...</div>
                    </div>
                </div>
            </div>

            <div id="posExpenseTabAdd" class="pos-expense-tab-panel">
                <div class="pos-expense-form-wrap">
                    <div class="pos-expense-form-title">
                        <div>
                            <h5>Add Expense</h5>
                            <p>Record small operating expenses directly from POS.</p>
                        </div>

                        <span class="pos-expense-form-badge">
                            <i class="mdi mdi-shield-check-outline"></i>
                            Saved with audit
                        </span>
                    </div>

                    <form id="posExpenseForm">
                        <div class="pos-expense-form-grid">
                            <div class="pos-expense-field">
                                <label>Category</label>
                                <select id="posExpenseCategory" required>
                                    <option value="">Select category</option>
                                    @foreach($expenseCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="pos-expense-field">
                                <label>Date</label>
                                <input type="date" id="posExpenseDate" value="{{ now()->toDateString() }}" required>
                            </div>

                            <div class="pos-expense-field">
                                <label>Title</label>
                                <input type="text"
                                       id="posExpenseTitle"
                                       placeholder="Example: Transport, lunch, electricity"
                                       required>
                            </div>

                            <div class="pos-expense-field">
                                <label>Amount</label>
                                <input type="number"
                                       id="posExpenseAmount"
                                       min="0.01"
                                       step="0.01"
                                       placeholder="0.00"
                                       required>
                            </div>

                            <div class="pos-expense-field">
                                <label>Payment Method</label>
                                <select id="posExpensePayment" required>
                                    <option value="cash">Cash</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="card">Card</option>
                                    <option value="bank">Bank</option>
                                </select>
                            </div>

                            <div class="pos-expense-field">
                                <label>Reference</label>
                                <input type="text" id="posExpenseReference" placeholder="Optional reference">
                            </div>

                            <div class="pos-expense-field pos-expense-field-full">
                                <label>Notes</label>
                                <textarea id="posExpenseNotes" rows="3" placeholder="Optional notes"></textarea>
                            </div>
                        </div>

                        <div class="pos-expense-form-footer">
                            <button type="button" class="pos-modal-secondary" data-pos-expense-tab-open="list">
                                Cancel
                            </button>

                            <button type="submit" id="posSaveExpenseBtn" class="pos-modal-primary pos-expense-save-btn">
                                <i class="mdi mdi-content-save-outline"></i>
                                Save Expense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="pos-modal-footer pos-expense-footer">
            <button type="button" class="pos-modal-secondary" data-close-pos-modal>
                Close
            </button>
        </div>
    </div>
</div>