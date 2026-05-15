<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Branch;
use App\Services\PublicSearchAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicSearchAnalyticsController extends ApiController
{
    public function index(Request $request, PublicSearchAnalyticsService $service): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'period' => ['nullable', 'string', 'in:today,7days,30days,month'],
        ]);

        return $this->success([
            'branches' => Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'analytics' => $service->summary(
                branchId: $validated['branch_id'] ?? null,
                period: $validated['period'] ?? 'today',
            ),
        ]);
    }
}