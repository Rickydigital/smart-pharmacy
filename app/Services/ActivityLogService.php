<?php

namespace App\Services;

use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    public function list(array $filters = []): array
    {
        $logs = Activity::query()
            ->with(['causer', 'subject'])
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $search = trim((string) $filters['search']);

                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('log_name', 'like', "%{$search}%")
                        ->orWhere('event', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%")
                        ->orWhere('causer_type', 'like', "%{$search}%");
                });
            })
            ->when(!empty($filters['log_name']), function ($query) use ($filters) {
                $query->where('log_name', $filters['log_name']);
            })
            ->when(!empty($filters['event']), function ($query) use ($filters) {
                $query->where('event', $filters['event']);
            })
            ->when(!empty($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            })
            ->when(!empty($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();

        $logs->getCollection()->transform(function ($log) {
            return $this->transformLog($log);
        });

        return [
            'logs' => $logs,
            'logNames' => $this->logNames(),
            'events' => $this->events(),
        ];
    }

    private function transformLog(Activity $log): Activity
    {
        $properties = $log->properties ? $log->properties->toArray() : [];

        $log->old_values = $properties['old'] ?? [];
        $log->new_values = $properties['attributes'] ?? [];
        $log->extra_values = collect($properties)
            ->except(['old', 'attributes'])
            ->toArray();

        $log->causer_name = $this->causerName($log);
        $log->subject_name = $log->subject_type
            ? class_basename($log->subject_type)
            : null;

        return $log;
    }

    private function causerName(Activity $log): string
    {
        if (! $log->causer) {
            return 'System';
        }

        if (method_exists($log->causer, 'displayName')) {
            return $log->causer->displayName();
        }

        return $log->causer->full_name
            ?? $log->causer->name
            ?? $log->causer->username
            ?? ('ID: ' . $log->causer->id);
    }

    private function logNames()
    {
        return Activity::query()
            ->select('log_name')
            ->whereNotNull('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name');
    }

    private function events()
    {
        return Activity::query()
            ->select('event')
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');
    }
}