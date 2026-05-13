<?php

namespace App\Http\Controllers\Api;

use App\Models\DailyClosing;
use App\Services\DailyClosing\DailyClosingDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyClosingController extends ApiController
{
    public function index(Request $request, DailyClosingDataService $service): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Daily closings loaded successfully.',
            'data' => $service->index($request, $request->user()),
        ]);
    }

    public function calculate(Request $request, DailyClosingDataService $service): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Daily closing totals calculated successfully.',
            'totals' => $service->calculate($request),
        ]);
    }

    public function store(Request $request, DailyClosingDataService $service): JsonResponse
    {
        $closing = $service->store($request, $request->user());

        return response()->json([
            'ok' => true,
            'message' => $closing->status === 'submitted'
                ? 'Branch daily closing submitted successfully.'
                : 'Branch daily closing saved as draft.',
            'data' => $closing->load(['branch', 'creator', 'verifier']),
        ]);
    }

    public function submit(DailyClosing $dailyClosing, Request $request, DailyClosingDataService $service): JsonResponse
    {
        $closing = $service->submit($dailyClosing, $request->user());

        return response()->json([
            'ok' => true,
            'message' => 'Daily closing submitted successfully.',
            'data' => $closing->load(['branch', 'creator', 'verifier']),
        ]);
    }

    public function verify(DailyClosing $dailyClosing, Request $request, DailyClosingDataService $service): JsonResponse
    {
        $closing = $service->verify($dailyClosing, $request->user());

        return response()->json([
            'ok' => true,
            'message' => 'Daily closing verified successfully.',
            'data' => $closing->load(['branch', 'creator', 'verifier']),
        ]);
    }

    public function reject(Request $request, DailyClosing $dailyClosing, DailyClosingDataService $service): JsonResponse
    {
        $closing = $service->reject($request, $dailyClosing, $request->user());

        return response()->json([
            'ok' => true,
            'message' => 'Daily closing rejected successfully.',
            'data' => $closing->load(['branch', 'creator', 'verifier']),
        ]);
    }

    public function recalculate(DailyClosing $dailyClosing, Request $request, DailyClosingDataService $service): JsonResponse
    {
        $closing = $service->recalculate($dailyClosing, $request->user());

        return response()->json([
            'ok' => true,
            'message' => 'Daily closing recalculated and submitted for verification.',
            'data' => $closing->load(['branch', 'creator', 'verifier']),
        ]);
    }
}