<?php

namespace App\Services\SmartControl;

use App\Models\SmartControlState;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SmartControlClient
{
    public function statusCheck(): SmartControlState
    {
        return $this->send('/api/v1/status-check');
    }

    public function heartbeat(): SmartControlState
    {
        return $this->send('/api/v1/heartbeat', [
            'app_version' => config('app.version', '1.0.0'),
            'server_time' => now()->toDateTimeString(),
        ]);
    }

    protected function send(string $endpoint, array $extraPayload = []): SmartControlState
    {
        $payload = array_merge([
            'project' => config('smartcontrol.project'),
            'license_key' => config('smartcontrol.license_key'),
            'instance_id' => config('smartcontrol.instance_id'),
            'domain' => config('app.url'),
        ], $extraPayload);

        $headers = $this->headers($payload);

        $url = rtrim((string) config('smartcontrol.url'), '/') . $endpoint;

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->withHeaders($headers)
                ->post($url, $payload);

            $data = $response->json() ?: [];

            return $this->storeResponse($data, $response->successful());
        } catch (\Throwable $exception) {
            return $this->storeFailure($exception->getMessage());
        }
    }

    protected function headers(array $payload): array
    {
        $timestamp = now()->timestamp;
        $nonce = (string) Str::uuid();

        return [
            'X-SmartControl-Client-Key' => config('smartcontrol.client_key'),
            'X-SmartControl-Timestamp' => $timestamp,
            'X-SmartControl-Nonce' => $nonce,
            'X-SmartControl-Signature' => $this->signature($payload),
        ];
    }

    protected function signature(array $payload): string
    {
        ksort($payload);

        return hash_hmac(
            'sha256',
            json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            (string) config('smartcontrol.client_secret')
        );
    }

    protected function storeResponse(array $data, bool $success): SmartControlState
{
    $state = SmartControlState::query()->firstOrNew([
        'project' => config('smartcontrol.project'),
        'license_key' => config('smartcontrol.license_key'),
        'instance_id' => config('smartcontrol.instance_id'),
    ]);

    $allowed = (bool) ($data['allowed'] ?? false);
    $forceLogout = (bool) ($data['force_logout'] ?? false);

    $state->fill([
        'allowed' => $allowed,
        'force_logout' => $forceLogout,
        'tenant_status' => $data['tenant_status'] ?? null,
        'license_status' => $data['license_status'] ?? null,
        'subscription_status' => $data['subscription_status'] ?? null,
        'message' => $data['message'] ?? null,
        'features' => $data['features'] ?? null,
        'payload' => $data,
        'last_checked_at' => now(),

        // IMPORTANT: use local app time, not central server time
        'valid_until' => $allowed && ! $forceLogout
            ? now()->addMinutes(config('smartcontrol.grace_minutes', 1440))
            : now(),

        'next_check_after' => now()->addMinutes(config('smartcontrol.check_interval_minutes', 10)),

        'last_success_at' => $success ? now() : $state->last_success_at,
        'last_failed_at' => $success ? $state->last_failed_at : now(),
    ]);

    $state->save();

    return $state;
}

    protected function storeFailure(string $message): SmartControlState
    {
        $state = SmartControlState::query()->firstOrNew([
            'project' => config('smartcontrol.project'),
            'license_key' => config('smartcontrol.license_key'),
            'instance_id' => config('smartcontrol.instance_id'),
        ]);

        $lastSuccess = $state->last_success_at;

        $failOpenAllowed = $state->allowed
            && $lastSuccess
            && $lastSuccess->greaterThanOrEqualTo(now()->subMinutes(config('smartcontrol.fail_open_minutes')));

        $state->fill([
            'allowed' => $failOpenAllowed,
            'force_logout' => ! $failOpenAllowed,
            'message' => $failOpenAllowed
                ? 'Central control temporarily unavailable. Last valid license is still trusted.'
                : 'Central control unavailable and local trust period has expired.',
            'last_checked_at' => now(),
            'last_failed_at' => now(),
            'valid_until' => $failOpenAllowed ? now()->addMinutes(10) : now(),
            'next_check_after' => now()->addMinutes(config('smartcontrol.check_interval_minutes')),
            'payload' => [
                'error' => $message,
            ],
        ]);

        $state->save();

        return $state;
    }
}