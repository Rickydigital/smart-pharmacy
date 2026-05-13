<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopbarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $isAdminOrOwner = $user?->hasAnyRole(['Owner', 'Admin']) ?? false;

        $alertCount = 0;
        $alerts = collect();

        if ($user?->can('inventory_alert.view')) {
            $baseQuery = InventoryAlert::query()
                ->with(['branch', 'product', 'inventory'])
                ->where('pharmacy_id', $user->pharmacy_id)
                ->when(! $isAdminOrOwner, function ($query) use ($user) {
                    $query->where('branch_id', $user->branch_id);
                })
                ->whereIn('status', ['open', 'read']);

            $alertCount = (clone $baseQuery)->count();

            $alerts = (clone $baseQuery)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($alert) => [
                    'id' => $alert->id,
                    'title' => $alert->title,
                    'message' => $alert->message,
                    'alert_type' => $alert->alert_type,
                    'severity' => $alert->severity,
                    'status' => $alert->status,
                    'branch_name' => $alert->branch?->name ?: ($alert->meta['branch_name'] ?? '-'),
                    'product_name' => $alert->product?->name ?: ($alert->meta['product_name'] ?? '-'),
                    'created_at' => $alert->created_at?->diffForHumans(),
                ]);
        }

        $messageCount = $user?->unreadNotifications()->count() ?? 0;

        $messages = $user
            ? $user->unreadNotifications()
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($notification) {
                    $data = $notification->data ?? [];

                    return [
                        'id' => $notification->id,
                        'title' => $data['title'] ?? 'System message',
                        'message' => $data['message'] ?? '',
                        'type' => $data['type'] ?? 'system',
                        'severity' => $data['severity'] ?? 'info',
                        'url' => $data['url'] ?? null,
                        'created_at' => $notification->created_at?->diffForHumans(),
                    ];
                })
            : collect();

        return response()->json([
            'ok' => true,
            'data' => [
                'alert_count' => $alertCount,
                'alerts' => $alerts,
                'message_count' => $messageCount,
                'messages' => $messages,
            ],
        ]);
    }
}