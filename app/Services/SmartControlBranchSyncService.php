<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Pharmacy;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmartControlBranchSyncService
{
    public function syncBranch(Branch $branch): array
    {
        if (! config('services.smart_control.enabled')) {
            return $this->skipped('Smart Control disabled.');
        }

        $baseUrl = rtrim((string) config('services.smart_control.url'), '/');

        if ($baseUrl === '') {
            return $this->skipped('Smart Control URL missing.');
        }

        $instanceId = config('services.smart_control.instance_id');

        if (! $instanceId) {
            return $this->skipped('Smart Control instance ID missing.');
        }

        $pharmacy = Pharmacy::query()->first();

        if (! $pharmacy) {
            return $this->skipped('No pharmacy found.');
        }

        if (! $branch->latitude || ! $branch->longitude) {
            return $this->skipped("Branch {$branch->name} skipped: missing latitude/longitude.");
        }

        $branch->loadMissing('street.ward.district.region.country');

        $street = $branch->street;
        $ward = $street?->ward;
        $district = $ward?->district;
        $region = $district?->region;
        $country = $region?->country;

        $fullLocation = collect([
            $country?->name,
            $region?->name,
            $district?->name,
            $ward?->name,
            $street?->name,
        ])->filter()->implode(', ');

        $payload = [
            'instance_id' => $instanceId,

            'pharmacy_id' => $pharmacy->id,
            'branch_id' => $branch->id,

            'pharmacy_name' => $pharmacy->name,
            'branch_name' => $branch->name,

            'logo_url' => $pharmacy->logo_path
                ? asset('storage/'.$pharmacy->logo_path)
                : null,

            'api_url' => config('app.url'),

            'phone' => $branch->phone ?: $pharmacy->phone,
            'whatsapp' => $branch->whatsapp ?: $branch->phone ?: $pharmacy->phone,
            'address' => $branch->address ?: $pharmacy->address,

            'street_id' => $branch->street_id,
            'full_location' => $fullLocation,

            'latitude' => $branch->latitude,
            'longitude' => $branch->longitude,

            'opens_at' => $branch->is_24_hours ? null : $branch->opens_at?->format('H:i'),
            'closes_at' => $branch->is_24_hours ? null : $branch->closes_at?->format('H:i'),
            'is_24_hours' => (bool) $branch->is_24_hours,
            'is_active' => $branch->is_active && $pharmacy->isActive(),
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'X-Project' => config('services.smart_control.project'),
                'X-License-Key' => config('services.smart_control.license_key'),
                'X-Client-Key' => config('services.smart_control.client_key'),
                'X-Client-Secret' => config('services.smart_control.client_secret'),
                'X-Instance-Id' => $instanceId,
            ])->timeout(20)->post($baseUrl.'/api/sync/pharmacy-branches', $payload);

            if (! $response->successful()) {
                Log::warning('Smart Control branch sync failed response', [
                    'branch_id' => $branch->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->failed("Branch {$branch->name} failed: ".$response->body());
            }

            $branch->updateQuietly([
                'last_location_synced_at' => now(),
            ]);

            return $this->synced("Branch {$branch->name} synced successfully.");
        } catch (\Throwable $e) {
            Log::warning('Smart Control branch sync exception', [
                'branch_id' => $branch->id,
                'error' => $e->getMessage(),
            ]);

            return $this->failed("Branch {$branch->name} error: ".$e->getMessage());
        }
    }

    public function syncAllBranches(): array
    {
        $result = [
            'attempted' => 0,
            'synced' => 0,
            'skipped' => 0,
            'failed' => 0,
            'messages' => [],
        ];

        Branch::query()
            ->with('street.ward.district.region.country')
            ->chunkById(50, function ($branches) use (&$result) {
                foreach ($branches as $branch) {
                    $result['attempted']++;

                    $sync = $this->syncBranch($branch);

                    if ($sync['ok']) {
                        $result['synced']++;
                    } elseif ($sync['skipped']) {
                        $result['skipped']++;
                    } else {
                        $result['failed']++;
                    }

                    $result['messages'][] = $sync['message'];
                }
            });

        return $result;
    }

    private function synced(string $message): array
    {
        return [
            'ok' => true,
            'skipped' => false,
            'message' => $message,
        ];
    }

    private function skipped(string $message): array
    {
        return [
            'ok' => false,
            'skipped' => true,
            'message' => $message,
        ];
    }

    private function failed(string $message): array
    {
        return [
            'ok' => false,
            'skipped' => false,
            'message' => $message,
        ];
    }
}