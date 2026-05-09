<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryAlert;
use App\Models\Pharmacy;
use App\Models\User;
use App\Notifications\InventoryAlertNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InventoryAlertService
{
    public function generateForPharmacy(Pharmacy $pharmacy): array
    {
        $created = collect();

        $created = $created
            ->merge($this->generateLowStockAlerts($pharmacy))
            ->merge($this->generateOutOfStockAlerts($pharmacy))
            ->merge($this->generateExpiringSoonAlerts($pharmacy))
            ->merge($this->generateExpiredAlerts($pharmacy));

        $this->sendNotifications($pharmacy, $created);

        return [
            'created' => $created->count(),
            'alerts' => $created,
        ];
    }

    private function generateLowStockAlerts(Pharmacy $pharmacy): Collection
    {
        $threshold = (int) config('pharmacy_alerts.low_stock_threshold', 10);

        return Inventory::query()
            ->with(['product.baseUnit', 'branch'])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->where('status', 'available')
            ->where('available_quantity_base_units', '>', 0)
            ->where('available_quantity_base_units', '<=', $threshold)
            ->get()
            ->map(function (Inventory $inventory) use ($pharmacy, $threshold) {
                return $this->createOrKeepOpenAlert(
                    pharmacy: $pharmacy,
                    inventory: $inventory,
                    alertType: 'low_stock',
                    severity: 'high',
                    title: 'Low stock: ' . ($inventory->product?->name ?: 'Unknown product'),
                    message: ($inventory->product?->name ?: 'Product') . ' is running low at '
                        . ($inventory->branch?->name ?: 'branch') . '. Available: '
                        . number_format((int) $inventory->available_quantity_base_units) . ' '
                        . ($inventory->product?->baseUnit?->name ?: 'base units') . '.',
                    threshold: $threshold,
                    daysToExpiry: null
                );
            })
            ->filter();
    }

    private function generateOutOfStockAlerts(Pharmacy $pharmacy): Collection
    {
        return Inventory::query()
            ->with(['product.baseUnit', 'branch'])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('available_quantity_base_units', '<=', 0)
            ->whereIn('status', ['available', 'depleted'])
            ->get()
            ->map(function (Inventory $inventory) use ($pharmacy) {
                return $this->createOrKeepOpenAlert(
                    pharmacy: $pharmacy,
                    inventory: $inventory,
                    alertType: 'out_of_stock',
                    severity: 'critical',
                    title: 'Out of stock: ' . ($inventory->product?->name ?: 'Unknown product'),
                    message: ($inventory->product?->name ?: 'Product') . ' is out of stock at '
                        . ($inventory->branch?->name ?: 'branch') . '.',
                    threshold: 0,
                    daysToExpiry: null
                );
            })
            ->filter();
    }

    private function generateExpiringSoonAlerts(Pharmacy $pharmacy): Collection
    {
        $days = (int) config('pharmacy_alerts.expiry_warning_days', 30);

        return Inventory::query()
            ->with(['product.baseUnit', 'branch'])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->where('available_quantity_base_units', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->whereDate('expiry_date', '<=', now()->addDays($days)->toDateString())
            ->get()
            ->map(function (Inventory $inventory) use ($pharmacy) {
                $daysToExpiry = now()->startOfDay()->diffInDays($inventory->expiry_date, false);

                return $this->createOrKeepOpenAlert(
                    pharmacy: $pharmacy,
                    inventory: $inventory,
                    alertType: 'expiring_soon',
                    severity: $daysToExpiry <= 7 ? 'critical' : 'high',
                    title: 'Expiring soon: ' . ($inventory->product?->name ?: 'Unknown product'),
                    message: ($inventory->product?->name ?: 'Product') . ' batch '
                        . ($inventory->batch_no ?: '-') . ' at '
                        . ($inventory->branch?->name ?: 'branch')
                        . ' expires in ' . $daysToExpiry . ' day(s).',
                    threshold: null,
                    daysToExpiry: $daysToExpiry
                );
            })
            ->filter();
    }

    private function generateExpiredAlerts(Pharmacy $pharmacy): Collection
    {
        return Inventory::query()
            ->with(['product.baseUnit', 'branch'])
            ->where('pharmacy_id', $pharmacy->id)
            ->where('available_quantity_base_units', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now()->toDateString())
            ->get()
            ->map(function (Inventory $inventory) use ($pharmacy) {
                $daysToExpiry = now()->startOfDay()->diffInDays($inventory->expiry_date, false);

                return $this->createOrKeepOpenAlert(
                    pharmacy: $pharmacy,
                    inventory: $inventory,
                    alertType: 'expired',
                    severity: 'critical',
                    title: 'Expired stock: ' . ($inventory->product?->name ?: 'Unknown product'),
                    message: ($inventory->product?->name ?: 'Product') . ' batch '
                        . ($inventory->batch_no ?: '-') . ' at '
                        . ($inventory->branch?->name ?: 'branch')
                        . ' is expired and should be written off.',
                    threshold: null,
                    daysToExpiry: $daysToExpiry
                );
            })
            ->filter();
    }

    private function createOrKeepOpenAlert(
        Pharmacy $pharmacy,
        Inventory $inventory,
        string $alertType,
        string $severity,
        string $title,
        string $message,
        ?int $threshold,
        ?int $daysToExpiry
    ): ?InventoryAlert {
        $existing = InventoryAlert::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('inventory_id', $inventory->id)
            ->where('alert_type', $alertType)
            ->whereIn('status', ['open', 'read'])
            ->first();

        if ($existing) {
            return null;
        }

        return InventoryAlert::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'branch_id' => $inventory->branch_id,
            'inventory_id' => $inventory->id,
            'product_id' => $inventory->product_id,
            'alert_no' => $this->generateAlertNumber(),
            'alert_type' => $alertType,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'available_quantity_base_units' => (int) $inventory->available_quantity_base_units,
            'threshold_quantity_base_units' => $threshold,
            'expiry_date' => $inventory->expiry_date,
            'days_to_expiry' => $daysToExpiry,
            'status' => 'open',
            'meta' => [
                'batch_no' => $inventory->batch_no,
                'base_unit' => $inventory->product?->baseUnit?->name,
                'branch_name' => $inventory->branch?->name,
                'product_name' => $inventory->product?->name,
            ],
        ]);
    }

    private function sendNotifications(Pharmacy $pharmacy, Collection $alerts): void
    {
        if ($alerts->isEmpty()) {
            return;
        }

        $users = User::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('status', 'active')
            ->role(['Owner', 'Admin'])
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($alerts as $alert) {
            Notification::send($users, new InventoryAlertNotification($alert));

            $channels = ['database'];

            /*
            // SMS READY - uncomment when SMS provider is ready.
            foreach ($users as $user) {
                if ($user->phone) {
                    app(\App\Services\SmsService::class)->send(
                        $user->phone,
                        $alert->title . ': ' . $alert->message
                    );

                    $channels[] = 'sms';
                }
            }
            */

            $alert->update([
                'notified_at' => now(),
                'channels_sent' => array_values(array_unique($channels)),
            ]);
        }
    }

    private function generateAlertNumber(): string
    {
        return 'ALT-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(5));
    }
}