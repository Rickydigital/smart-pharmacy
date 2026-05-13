<?php

namespace App\Http\Controllers\Api;

use App\Services\Dashboard\DashboardDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function index(Request $request, DashboardDataService $dashboard): JsonResponse
    {
        $data = $dashboard->build($request, $request->user());

        return response()->json([
            'ok' => true,
            'message' => 'Dashboard loaded successfully.',
            'data' => $data,
        ]);
    }
}