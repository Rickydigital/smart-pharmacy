<script>
    POS.routes.expenses = @json(route('pos.expenses.index'));
    POS.routes.storeExpense = @json(route('pos.expenses.store'));

    Object.assign(els, {
        confirmModal: document.getElementById('posConfirmModal'),
        confirmTitle: document.getElementById('posConfirmTitle'),
        confirmText: document.getElementById('posConfirmText'),
        confirmCancelBtn: document.getElementById('posConfirmCancelBtn'),
        confirmYesBtn: document.getElementById('posConfirmYesBtn'),

        expensesBtn: document.getElementById('posExpensesBtn'),
        expensesModal: document.getElementById('posExpensesModal'),

        expenseTabs: document.querySelectorAll('[data-pos-expense-tab]'),
        expenseTabOpeners: document.querySelectorAll('[data-pos-expense-tab-open]'),
        expenseTabList: document.getElementById('posExpenseTabList'),
        expenseTabAdd: document.getElementById('posExpenseTabAdd'),

        expensesBody: document.getElementById('posExpensesBody'),
        expenseSummary: document.getElementById('posExpenseSummary'),
        expenseForm: document.getElementById('posExpenseForm'),
        expenseCategory: document.getElementById('posExpenseCategory'),
        expenseDate: document.getElementById('posExpenseDate'),
        expenseTitle: document.getElementById('posExpenseTitle'),
        expenseAmount: document.getElementById('posExpenseAmount'),
        expensePayment: document.getElementById('posExpensePayment'),
        expenseReference: document.getElementById('posExpenseReference'),
        expenseNotes: document.getElementById('posExpenseNotes'),
        expenseYear: document.getElementById('posExpenseYear'),
        expenseFilterDate: document.getElementById('posExpenseFilterDate'),
        refreshExpensesBtn: document.getElementById('posRefreshExpensesBtn'),
        clearExpenseDayBtn: document.getElementById('posClearExpenseDayBtn'),
        saveExpenseBtn: document.getElementById('posSaveExpenseBtn'),
    });

    els.expensesBtn?.addEventListener('click', () => {
        els.expensesModal.classList.remove('d-none');
        openPosExpenseTab('list');
        loadPosExpenses();
    });

    els.expenseTabs?.forEach(button => {
        button.addEventListener('click', () => {
            openPosExpenseTab(button.dataset.posExpenseTab);
        });
    });

    els.expenseTabOpeners?.forEach(button => {
        button.addEventListener('click', () => {
            openPosExpenseTab(button.dataset.posExpenseTabOpen);
        });
    });

    els.refreshExpensesBtn?.addEventListener('click', () => {
        loadPosExpenses();
    });

    els.expenseYear?.addEventListener('change', () => {
        loadPosExpenses();
    });

    els.expenseFilterDate?.addEventListener('change', () => {
        loadPosExpenses();
    });

    els.clearExpenseDayBtn?.addEventListener('click', () => {
        els.expenseFilterDate.value = '';
        loadPosExpenses();
    });

    els.expenseForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!POS.branchId) {
            showPosMessage('warning', 'No Branch', 'Please select a branch before recording expense.');
            return;
        }

        setExpenseSaving(true);

        try {
            const response = await fetch(POS.routes.storeExpense, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': POS.csrf,
                },
                body: JSON.stringify({
                    branch_id: POS.branchId,
                    expense_category_id: els.expenseCategory.value,
                    expense_date: els.expenseDate.value,
                    title: els.expenseTitle.value,
                    amount: Number(els.expenseAmount.value || 0),
                    payment_method: els.expensePayment.value,
                    reference_no: els.expenseReference.value,
                    notes: els.expenseNotes.value,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                showPosMessage('error', 'Expense Failed', data.message || 'Expense could not be saved.');
                return;
            }

            els.expenseTitle.value = '';
            els.expenseAmount.value = '';
            els.expenseReference.value = '';
            els.expenseNotes.value = '';

            if (els.expenseDate.value) {
                els.expenseFilterDate.value = els.expenseDate.value;
                els.expenseYear.value = new Date(els.expenseDate.value).getFullYear();
            }

            openPosExpenseTab('list');
            loadPosExpenses();

            showPosMessage('success', 'Expense Saved', `${data.expense_no} recorded successfully.`);
        } catch (error) {
            showPosMessage('error', 'Expense Failed', 'Expense could not be saved. Please try again.');
        } finally {
            setExpenseSaving(false);
        }
    });

    function openPosExpenseTab(tab) {
        const selectedTab = tab === 'add' ? 'add' : 'list';

        els.expenseTabs?.forEach(button => {
            button.classList.toggle('active', button.dataset.posExpenseTab === selectedTab);
        });

        els.expenseTabList?.classList.toggle('active', selectedTab === 'list');
        els.expenseTabAdd?.classList.toggle('active', selectedTab === 'add');
    }

    async function loadPosExpenses() {
        if (!POS.branchId) {
            els.expensesBody.innerHTML = '<div class="pos-empty">No branch selected.</div>';
            return;
        }

        els.expensesBody.innerHTML = '<div class="pos-empty">Loading expenses...</div>';

        const url = new URL(POS.routes.expenses);
        url.searchParams.set('branch_id', POS.branchId);
        url.searchParams.set('year', els.expenseYear.value || new Date().getFullYear());

        if (els.expenseFilterDate.value) {
            url.searchParams.set('expense_date', els.expenseFilterDate.value);
        }

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!data.ok) {
                els.expensesBody.innerHTML = '<div class="pos-empty">Unable to load expenses.</div>';
                return;
            }

            els.expenseSummary.textContent = `${data.summary.count} records • ${money(data.summary.paid_total)} paid`;

            if (!data.expenses.length) {
                els.expensesBody.innerHTML = '<div class="pos-empty">No expenses found for selected period.</div>';
                return;
            }

            els.expensesBody.innerHTML = data.expenses.map(expense => `
                <div class="pos-expense-row">
                    <div>
                        <div class="pos-expense-main">${escapeHtml(expense.title)}</div>
                        <div class="pos-expense-sub">
                            ${escapeHtml(expense.expense_no)} • ${escapeHtml(expense.category)} • ${escapeHtml(expense.expense_date)}
                        </div>
                    </div>

                    <div>
                        <div class="pos-expense-main">${escapeHtml(expense.payment_method)}</div>
                        <div class="pos-expense-sub">${escapeHtml(expense.created_by)}</div>
                    </div>

                    <div class="pos-expense-amount">${money(expense.amount)}</div>

                    ${expense.can_delete && expense.delete_url ? `
                        <button type="button"
                                class="pos-expense-delete"
                                onclick="deletePosExpense('${expense.delete_url}')">
                            Delete
                        </button>
                    ` : `
                        <span class="pos-expense-locked">
                            ${els.expenseFilterDate.value ? 'Locked' : 'Day only'}
                        </span>
                    `}
                </div>
            `).join('');
        } catch (error) {
            els.expensesBody.innerHTML = '<div class="pos-empty">Unable to load expenses.</div>';
        }
    }

    async function deletePosExpense(deleteUrl) {
        showPosConfirm(
            'Delete Expense',
            'Are you sure you want to delete this expense record?',
            async () => {
                try {
                    const response = await fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': POS.csrf,
                        },
                    });

                    const data = await response.json();

                    if (!response.ok || !data.ok) {
                        showPosMessage('error', 'Delete Failed', data.message || 'Expense could not be deleted.');
                        return;
                    }

                    showPosMessage('success', 'Expense Deleted', data.message || 'Expense deleted successfully.');
                    loadPosExpenses();
                } catch (error) {
                    showPosMessage('error', 'Delete Failed', 'Expense could not be deleted. Please try again.');
                }
            }
        );
    }

    function setExpenseSaving(isSaving) {
        if (!els.saveExpenseBtn) {
            return;
        }

        if (isSaving) {
            els.saveExpenseBtn.disabled = true;
            els.saveExpenseBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Saving...';
            return;
        }

        els.saveExpenseBtn.disabled = false;
        els.saveExpenseBtn.innerHTML = '<i class="mdi mdi-content-save-outline"></i> Save Expense';
    }

    function showPosConfirm(title, message, onConfirm) {
        els.confirmTitle.textContent = title;
        els.confirmText.textContent = message;
        els.confirmModal.classList.remove('d-none');

        const yesButton = els.confirmYesBtn.cloneNode(true);
        els.confirmYesBtn.parentNode.replaceChild(yesButton, els.confirmYesBtn);
        els.confirmYesBtn = yesButton;

        els.confirmYesBtn.addEventListener('click', async () => {
            els.confirmModal.classList.add('d-none');
            await onConfirm();
        });

        els.confirmCancelBtn.onclick = () => {
            els.confirmModal.classList.add('d-none');
        };
    }

    window.deletePosExpense = deletePosExpense;
</script>