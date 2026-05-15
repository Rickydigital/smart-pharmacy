<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MarketBridgeService
{
    public function updateStatus(int $orderId, string $status, ?string $note = null): array
    {
        $baseUrl = rtrim((string) config('services.smart_control.url'), '/');

        if ($baseUrl === '') {
            throw new \RuntimeException('Smart Control URL is not configured.');
        }

        $response = Http::acceptJson()
            ->timeout(20)
            ->patch($baseUrl . "/api/market/orders/{$orderId}/status", [
                'instance_id' => config('services.smart_control.instance_id'),
                'status' => $status,
                'note' => $note,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                $response->json('message') ?? 'Unable to update central market order status.'
            );
        }

        return $response->json() ?? [];
    }
}
