<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use Illuminate\Routing\Controller;
use App\Services\Intelligence\IntelligenceDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IntelligenceController extends Controller
{
    public function __construct(
        private IntelligenceDashboardService $dashboard
    ) {
        $this->middleware('permission:intelligence.view')->only(['index']);
    }

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $data = $this->dashboard->dashboard(
            pharmacyId: $user->pharmacy_id,
            branchId: $request->integer('branch_id') ?: null,
            month: $request->get('month')
        );

        $branches = Branch::query()
            ->where('pharmacy_id', $user->pharmacy_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'is_main']);

        return response()->json([
            'success' => true,
            'message' => 'Intelligence dashboard loaded successfully.',
            'data' => [
                'filters' => [
                    'selected_branch_id' => $request->integer('branch_id') ?: null,
                    'selected_month' => $request->get('month', now()->format('Y-m')),
                    'branches' => $branches,
                ],
                'dashboard' => $data,
            ],
        ]);
    }
}
