<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:audit.view')->only([
            'index',
        ]);
    }

    public function index(Request $request): View
    {
        $logs = Activity::query()
            ->with(['causer', 'subject'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('log_name', 'like', "%{$search}%")
                        ->orWhere('event', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%")
                        ->orWhere('causer_type', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('log_name'), function ($query) use ($request) {
                $query->where('log_name', $request->log_name);
            })
            ->when($request->filled('event'), function ($query) use ($request) {
                $query->where('event', $request->event);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $logs->getCollection()->transform(function ($log) {
            $properties = $log->properties ? $log->properties->toArray() : [];

            $log->old_values = $properties['old'] ?? [];
            $log->new_values = $properties['attributes'] ?? [];
            $log->extra_values = collect($properties)
                ->except(['old', 'attributes'])
                ->toArray();

            return $log;
        });

        $logNames = Activity::query()
            ->select('log_name')
            ->whereNotNull('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name');

        $events = Activity::query()
            ->select('event')
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        return view('activity-logs.index', compact(
            'logs',
            'logNames',
            'events'
        ));
    }
}