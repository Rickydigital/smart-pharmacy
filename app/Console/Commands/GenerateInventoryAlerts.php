<?php

namespace App\Console\Commands;

use App\Models\Pharmacy;
use App\Services\InventoryAlertService;
use Illuminate\Console\Command;

class GenerateInventoryAlerts extends Command
{
    protected $signature = 'inventory:generate-alerts {--pharmacy_id=}';

    protected $description = 'Generate inventory alerts for low stock, out of stock, expiring soon and expired stock';

    public function handle(InventoryAlertService $alertService): int
    {
        $query = Pharmacy::query();

        if ($this->option('pharmacy_id')) {
            $query->whereKey($this->option('pharmacy_id'));
        }

        $pharmacies = $query->get();

        if ($pharmacies->isEmpty()) {
            $this->warn('No pharmacies found.');
            return self::SUCCESS;
        }

        foreach ($pharmacies as $pharmacy) {
            $this->info('Generating alerts for: ' . $pharmacy->name);

            $result = $alertService->generateForPharmacy($pharmacy);

            $this->line('Created alerts: ' . $result['created']);
        }

        return self::SUCCESS;
    }
}