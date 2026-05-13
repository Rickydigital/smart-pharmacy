<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __construct(private ActivityLogService $activityLogs)
    {
        $this->middleware('permission:audit.view')->only(['index']);
    }

    public function index(Request $request): View
    {
        $data = $this->activityLogs->list(
            $request->only([
                'search',
                'log_name',
                'event',
                'date_from',
                'date_to',
                'per_page',
            ])
        );

        return view('activity-logs.index', [
            'logs' => $data['logs'],
            'logNames' => $data['logNames'],
            'events' => $data['events'],
        ]);
    }
}