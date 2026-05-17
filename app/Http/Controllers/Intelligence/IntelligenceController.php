<?php

namespace App\Http\Controllers\Intelligence;

use App\Models\Branch;
use Illuminate\Routing\Controller;
use App\Services\Intelligence\IntelligenceDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class IntelligenceController extends Controller
{
    public function __construct(
        private IntelligenceDashboardService $dashboard
    ) {
        $this->middleware('permission:intelligence.view')->only(['index']);
    }

   public function index(Request $request): View
{
    $pharmacyId = Auth::user()->pharmacy_id;

    $data = $this->dashboard->dashboard(
        pharmacyId: $pharmacyId,
        branchId: $request->integer('branch_id') ?: null,
        month: $request->get('month')
    );

    return view('intelligence.index', [
        'data' => $data,
        'branches' => Branch::query()
            ->where('pharmacy_id', $pharmacyId)
            ->orderBy('name')
            ->get(),
    ]);
}
}