<?php

namespace App\Http\Controllers\Api;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\Expense\ExpenseDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends ApiController
{
    public function index(Request $request, ExpenseDataService $service): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Expenses loaded successfully.',
            'data' => $service->index($request),
        ]);
    }

    public function storeCategory(Request $request, ExpenseDataService $service): JsonResponse
    {
        $category = $service->storeCategory($request, $request->user());

        return response()->json([
            'ok' => true,
            'message' => 'Expense category created successfully.',
            'data' => $category,
        ]);
    }

    public function updateCategory(
        Request $request,
        ExpenseCategory $expenseCategory,
        ExpenseDataService $service
    ): JsonResponse {
        $category = $service->updateCategory($request, $expenseCategory);

        return response()->json([
            'ok' => true,
            'message' => 'Expense category updated successfully.',
            'data' => $category,
        ]);
    }

    public function toggleCategory(
        ExpenseCategory $expenseCategory,
        ExpenseDataService $service
    ): JsonResponse {
        $category = $service->toggleCategory($expenseCategory);

        return response()->json([
            'ok' => true,
            'message' => 'Expense category status updated successfully.',
            'data' => $category,
        ]);
    }

    public function store(Request $request, ExpenseDataService $service): JsonResponse
    {
        $expense = $service->store($request, $request->user());

        return response()->json([
            'ok' => true,
            'message' => 'Expense recorded successfully.',
            'data' => $expense->load(['branch', 'category', 'creator', 'voider']),
        ]);
    }

    public function update(
        Request $request,
        Expense $expense,
        ExpenseDataService $service
    ): JsonResponse {
        $expense = $service->update($request, $expense);

        return response()->json([
            'ok' => true,
            'message' => 'Expense updated successfully.',
            'data' => $expense->load(['branch', 'category', 'creator', 'voider']),
        ]);
    }

    public function void(
        Request $request,
        Expense $expense,
        ExpenseDataService $service
    ): JsonResponse {
        $expense = $service->void($request, $expense, $request->user());

        return response()->json([
            'ok' => true,
            'message' => 'Expense voided successfully.',
            'data' => $expense->load(['branch', 'category', 'creator', 'voider']),
        ]);
    }
}