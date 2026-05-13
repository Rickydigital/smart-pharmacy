<?php

namespace App\Http\Controllers\Api;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ActivityLogController extends ApiController
{
    public function __construct(private ActivityLogService $activityLogs)
    {
    }

    public function index(Request $request): mixed
    {
        return $this->success(
            $this->activityLogs->list(
                $request->only([
                    'search',
                    'log_name',
                    'event',
                    'date_from',
                    'date_to',
                    'page',
                    'per_page',
                ])
            )
        );
    }
}