<?php

namespace App\Http\Controllers\Api;

use App\Services\Reports\ReportDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends ApiController
{
    public function index(Request $request, ReportDataService $service): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Report center loaded successfully.',
            'data' => $service->index($request),
        ]);
    }

    public function sales(Request $request, ReportDataService $service): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Sales report loaded successfully.',
            'data' => $service->sales($request),
        ]);
    }

    public function stock(Request $request, ReportDataService $service): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Stock report loaded successfully.',
            'data' => $service->stock($request),
        ]);
    }

    public function purchases(Request $request, ReportDataService $service): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Purchase report loaded successfully.',
            'data' => $service->purchases($request),
        ]);
    }

    public function profit(Request $request, ReportDataService $service): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Profit report loaded successfully.',
            'data' => $service->profit($request),
        ]);
    }

    public function expenses(Request $request, ReportDataService $service): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Expense report loaded successfully.',
            'data' => $service->expenses($request),
        ]);
    }

    public function prescriptions(Request $request, ReportDataService $service): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Prescription report loaded successfully.',
            'data' => $service->prescriptions($request),
        ]);
    }

    public function export(Request $request, string $report, ReportDataService $service): mixed
    {
        return $service->export($request, $report);
    }
}