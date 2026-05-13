<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\Expense\ExpenseDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:expense.view', only: ['index']),
            new Middleware('permission:expense_category.manage', only: [
                'storeCategory',
                'updateCategory',
                'toggleCategory',
            ]),
            new Middleware('permission:expense.create', only: ['store']),
            new Middleware('permission:expense.update', only: ['update']),
            new Middleware('permission:expense.void', only: ['void']),
        ];
    }

    public function index(Request $request, ExpenseDataService $service): View
    {
        return view('expenses.index', $service->index($request));
    }

    public function storeCategory(Request $request, ExpenseDataService $service): RedirectResponse
    {
        $service->storeCategory($request, Auth::user());

        return back()->with('success', 'Expense category created successfully.');
    }

    public function updateCategory(
        Request $request,
        ExpenseCategory $expenseCategory,
        ExpenseDataService $service
    ): RedirectResponse {
        $service->updateCategory($request, $expenseCategory);

        return back()->with('success', 'Expense category updated successfully.');
    }

    public function toggleCategory(
        ExpenseCategory $expenseCategory,
        ExpenseDataService $service
    ): RedirectResponse {
        $service->toggleCategory($expenseCategory);

        return back()->with('success', 'Expense category status updated successfully.');
    }

    public function store(Request $request, ExpenseDataService $service): RedirectResponse
    {
        $service->store($request, Auth::user());

        return back()->with('success', 'Expense recorded successfully.');
    }

    public function update(
        Request $request,
        Expense $expense,
        ExpenseDataService $service
    ): RedirectResponse {
        $service->update($request, $expense);

        return back()->with('success', 'Expense updated successfully.');
    }

    public function void(
        Request $request,
        Expense $expense,
        ExpenseDataService $service
    ): RedirectResponse {
        $service->void($request, $expense, Auth::user());

        return back()->with('success', 'Expense voided successfully.');
    }
}