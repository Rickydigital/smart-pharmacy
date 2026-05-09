@extends('components.main-layout')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')
@section('page-subtitle', 'Audit trail showing system actions and data changes')

@section('content')
<style>
    .audit-page {
        max-width: 100%;
    }

    .audit-header-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .audit-header-card .card-body {
        padding: 20px 22px;
    }

    .audit-filter-card,
    .audit-table-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .audit-filter-card .card-header,
    .audit-table-card .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        padding: 14px 18px;
        font-weight: 800;
        color: #334155;
    }

    .audit-filter-card .card-body {
        padding: 18px;
    }

    .audit-filter-card label {
        font-size: 12px;
        font-weight: 800;
        color: #64748b;
        margin-bottom: 6px;
    }

    .audit-filter-card .form-control,
    .audit-filter-card .custom-select {
        min-height: 40px;
        border-radius: 11px;
        border-color: #dbe3ef;
        font-size: 13px;
        font-weight: 700;
    }

    .audit-filter-card .btn {
        min-height: 40px;
        border-radius: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .audit-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .audit-table {
        min-width: 1180px;
        margin-bottom: 0;
    }

    .audit-table thead th {
        background: #2563eb;
        color: #ffffff;
        border: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: 13px 14px;
        white-space: nowrap;
    }

    .audit-table tbody td {
        vertical-align: middle;
        padding: 13px 14px;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        border-top: 1px solid #eef2f7;
    }

    .audit-table tbody tr:hover td {
        background: #f8fbff;
    }

    .audit-date {
        white-space: nowrap;
    }

    .audit-date strong {
        display: block;
        color: #0f172a;
    }

    .audit-date small {
        color: #64748b;
        font-weight: 700;
    }

    .audit-causer {
        min-width: 160px;
    }

    .audit-causer strong {
        display: block;
        color: #0f172a;
        line-height: 1.2;
    }

    .audit-target {
        min-width: 130px;
    }

    .audit-action {
        min-width: 230px;
    }

    .audit-description {
        margin-top: 6px;
        color: #334155;
        max-width: 280px;
        white-space: normal;
        line-height: 1.35;
    }

    .audit-change-box {
        min-width: 320px;
        max-width: 460px;
        font-size: 12px;
        line-height: 1.35;
    }

    .audit-change-item {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 7px 9px;
        margin-bottom: 6px;
    }

    .audit-change-key {
        display: block;
        color: #0f172a;
        font-weight: 900;
        margin-bottom: 3px;
    }

    .audit-value-old,
    .audit-value-new {
        display: inline-block;
        max-width: 170px;
        vertical-align: top;
        word-break: break-word;
    }

    .audit-value-old {
        color: #dc2626;
    }

    .audit-value-new {
        color: #15803d;
    }

    .audit-empty {
        padding: 42px 20px;
        text-align: center;
        color: #64748b;
        font-weight: 700;
    }

    .audit-empty i {
        display: block;
        font-size: 36px;
        color: #94a3b8;
        margin-bottom: 8px;
    }

    .audit-pagination {
        padding: 15px 18px;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .audit-pagination .pagination {
        margin-bottom: 0;
    }

    .audit-modal .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
    }

    .audit-modal .modal-header {
        background: #0f172a;
        color: #ffffff;
        border-bottom: 0;
        padding: 17px 20px;
    }

    .audit-modal .modal-title {
        font-weight: 900;
    }

    .audit-modal .modal-body {
        padding: 20px;
    }

    .audit-info-label {
        font-size: 12px;
        font-weight: 900;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 4px;
    }

    .audit-info-value {
        font-weight: 800;
        color: #0f172a;
        word-break: break-word;
    }

    .audit-json-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        height: 100%;
    }

    .audit-json-card .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 14px;
        font-weight: 900;
        color: #334155;
    }

    .audit-json-card .card-body {
        padding: 14px;
    }

    .audit-json-card pre {
        max-height: 360px;
        overflow: auto;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        padding: 12px;
        font-size: 12px;
        white-space: pre-wrap;
        word-break: break-word;
    }

    @media (max-width: 767.98px) {
        .audit-header-card .card-body {
            padding: 18px 16px;
        }

        .audit-header-card h4 {
            font-size: 1.1rem;
        }

        .audit-filter-card .card-body {
            padding: 14px;
        }

        .audit-filter-card .col-md-3,
        .audit-filter-card .col-md-2,
        .audit-filter-card .col-md-1 {
            margin-bottom: 10px;
        }

        .audit-table {
            min-width: 1050px;
        }

        .audit-table thead th {
            font-size: 10px;
            padding: 10px;
        }

        .audit-table tbody td {
            font-size: 12px;
            padding: 10px;
        }

        .audit-action {
            min-width: 200px;
        }

        .audit-change-box {
            min-width: 290px;
        }

        .audit-pagination {
            align-items: flex-start;
        }

        .audit-pagination nav {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .audit-modal .modal-dialog {
            margin: .75rem;
        }
    }
</style>

<div class="container-fluid audit-page">

    <div class="card audit-header-card mb-4">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <div>
                <h4 class="mb-1 font-weight-bold text-dark">
                    <i class="mdi mdi-history text-primary mr-1"></i>
                    Activity Logs
                </h4>
                <p class="mb-0 text-muted">
                    Track system actions, user activity, and important data changes.
                </p>
            </div>

            <div class="mt-3 mt-md-0">
                <span class="badge badge-light p-2">
                    <i class="mdi mdi-shield-check-outline mr-1"></i>
                    Audit Protected
                </span>
            </div>
        </div>
    </div>

    <div class="card audit-filter-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>
                <i class="mdi mdi-filter-outline mr-1"></i>
                Filter Logs
            </span>

            @if(request()->filled('search') || request()->filled('log_name') || request()->filled('event') || request()->filled('date_from') || request()->filled('date_to'))
                <a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-light">
                    Clear Filters
                </a>
            @endif
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('activity-logs.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label>Search</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control"
                               placeholder="Description, log, event">
                    </div>

                    <div class="col-md-2">
                        <label>Log Name</label>
                        <select name="log_name" class="custom-select">
                            <option value="">All</option>
                            @foreach($logNames as $logName)
                                <option value="{{ $logName }}" {{ request('log_name') == $logName ? 'selected' : '' }}>
                                    {{ ucfirst($logName) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Event</label>
                        <select name="event" class="custom-select">
                            <option value="">All</option>
                            @foreach($events as $event)
                                <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                    {{ ucfirst($event) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Date From</label>
                        <input type="date"
                               name="date_from"
                               value="{{ request('date_from') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label>Date To</label>
                        <input type="date"
                               name="date_to"
                               value="{{ request('date_to') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card audit-table-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>
                <i class="mdi mdi-format-list-bulleted mr-1"></i>
                System Activity Logs
            </span>

            <small class="text-muted">
                Showing latest activities
            </small>
        </div>

        <div class="audit-table-wrap">
            <table class="table table-hover audit-table">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Date</th>
                        <th>Who</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>Changes</th>
                        <th width="110">Details</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($logs as $log)
                        @php
                            $eventBadge = match($log->event) {
                                'created' => 'badge-success',
                                'updated' => 'badge-warning',
                                'deleted' => 'badge-danger',
                                'login' => 'badge-info',
                                'logout' => 'badge-dark',
                                'view' => 'badge-primary',
                                default => 'badge-secondary',
                            };

                            $oldValues = $log->old_values ?? [];
                            $newValues = $log->new_values ?? [];
                            $changedKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));

                            $causerName = 'System';

                            if ($log->causer) {
                                if (method_exists($log->causer, 'displayName')) {
                                    $causerName = $log->causer->displayName();
                                } else {
                                    $causerName = $log->causer->full_name
                                        ?? $log->causer->name
                                        ?? ('ID: ' . $log->causer->id);
                                }
                            }
                        @endphp

                        <tr>
                            <td>{{ ($logs->firstItem() ?? 0) + $loop->index }}</td>

                            <td class="audit-date">
                                <strong>{{ optional($log->created_at)->format('d M Y') }}</strong>
                                <small>{{ optional($log->created_at)->format('H:i:s') }}</small>
                            </td>

                            <td class="audit-causer">
                                <strong>{{ $causerName }}</strong>
                                <small class="text-muted">
                                    {{ $log->causer_type ? class_basename($log->causer_type) : 'System' }}
                                </small>
                            </td>

                            <td class="audit-action">
                                <div class="mb-1">
                                    <span class="badge {{ $eventBadge }}">
                                        {{ $log->event ?: '-' }}
                                    </span>

                                    <span class="badge badge-light">
                                        {{ $log->log_name ?: '-' }}
                                    </span>
                                </div>

                                <div class="audit-description">
                                    {{ $log->description ?: '-' }}
                                </div>
                            </td>

                            <td class="audit-target">
                                <div class="font-weight-bold">
                                    {{ $log->subject_type ? class_basename($log->subject_type) : '-' }}
                                </div>
                                <small class="text-muted">
                                    {{ $log->subject_id ? 'ID: ' . $log->subject_id : '-' }}
                                </small>
                            </td>

                            <td>
                                <div class="audit-change-box">
                                    @if(count($changedKeys))
                                        @foreach(array_slice($changedKeys, 0, 3) as $key)
                                            <div class="audit-change-item">
                                                <span class="audit-change-key">{{ $key }}</span>

                                                <span class="audit-value-old">
                                                    {{ is_array($oldValues[$key] ?? null) ? json_encode($oldValues[$key]) : ($oldValues[$key] ?? '-') }}
                                                </span>

                                                <i class="mdi mdi-arrow-right mx-1 text-muted"></i>

                                                <span class="audit-value-new">
                                                    {{ is_array($newValues[$key] ?? null) ? json_encode($newValues[$key]) : ($newValues[$key] ?? '-') }}
                                                </span>
                                            </div>
                                        @endforeach

                                        @if(count($changedKeys) > 3)
                                            <small class="text-muted font-weight-bold">
                                                + {{ count($changedKeys) - 3 }} more changes
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">No field changes</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-toggle="modal"
                                        data-target="#logDetailModal{{ $log->id }}">
                                    View
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade audit-modal" id="logDetailModal{{ $log->id }}" tabindex="-1" role="dialog" aria-labelledby="logDetailModalLabel{{ $log->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="logDetailModalLabel{{ $log->id }}">
                                            <i class="mdi mdi-file-document-outline mr-1"></i>
                                            Log Details #{{ $log->id }}
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                            <span>&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row mb-4">
                                            <div class="col-md-3 mb-3">
                                                <div class="audit-info-label">Date</div>
                                                <div class="audit-info-value">
                                                    {{ optional($log->created_at)->format('d M Y H:i:s') }}
                                                </div>
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <div class="audit-info-label">Log Name</div>
                                                <div class="audit-info-value">{{ $log->log_name ?: '-' }}</div>
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <div class="audit-info-label">Event</div>
                                                <div class="audit-info-value">{{ $log->event ?: '-' }}</div>
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <div class="audit-info-label">Description</div>
                                                <div class="audit-info-value">{{ $log->description ?: '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-6 mb-3">
                                                <div class="audit-info-label">Causer</div>
                                                <div class="audit-info-value">{{ $causerName }}</div>
                                                <small class="text-muted">
                                                    {{ $log->causer_type ? class_basename($log->causer_type) : '-' }}
                                                </small>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="audit-info-label">Subject</div>
                                                <div class="audit-info-value">
                                                    {{ $log->subject_type ? class_basename($log->subject_type) : '-' }}
                                                </div>
                                                <small class="text-muted">ID: {{ $log->subject_id ?: '-' }}</small>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="audit-json-card">
                                                    <div class="card-header">
                                                        Old Values
                                                    </div>
                                                    <div class="card-body">
                                                        @if(count($oldValues))
                                                            <pre class="mb-0">{{ json_encode($oldValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        @else
                                                            <span class="text-muted">No old values</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="audit-json-card">
                                                    <div class="card-header">
                                                        New Values
                                                    </div>
                                                    <div class="card-body">
                                                        @if(count($newValues))
                                                            <pre class="mb-0">{{ json_encode($newValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        @else
                                                            <span class="text-muted">No new values</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if(!empty($log->extra_values) && count($log->extra_values))
                                            <div class="audit-json-card mt-2">
                                                <div class="card-header">
                                                    Extra Properties
                                                </div>
                                                <div class="card-body">
                                                    <pre class="mb-0">{{ json_encode($log->extra_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-dismiss="modal">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="audit-empty">
                                    <i class="mdi mdi-history"></i>
                                    No activity logs found.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="audit-pagination">
                <small>
                    Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries
                </small>

                {{ $logs->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection