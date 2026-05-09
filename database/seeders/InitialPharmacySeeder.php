<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Pharmacy;
use App\Models\PharmacySetting;
use Illuminate\Database\Seeder;

class InitialPharmacySeeder extends Seeder
{
    public function run(): void
    {
        $pharmacy = Pharmacy::firstOrCreate(
            ['code' => 'DEFAULT'],
            [
                'name' => 'Default Pharmacy',
                'phone' => null,
                'email' => null,
                'address' => null,
                'status' => 'active',
            ]
        );

        $branch = Branch::firstOrCreate(
            [
                'pharmacy_id' => $pharmacy->id,
                'code' => 'MAIN',
            ],
            [
                'name' => 'Main Branch',
                'phone' => null,
                'address' => null,
                'is_main' => true,
                'is_active' => true,
            ]
        );

        PharmacySetting::firstOrCreate(
            ['pharmacy_id' => $pharmacy->id],
            [
                'currency' => 'TZS',
                'selling_mode' => 'retail_and_wholesale',
                'expiry_warning_days' => 30,
                'block_expired_stock' => true,
                'receipt_footer' => 'Thank you for buying from us.',
                'require_prescription_upload' => false,
                'require_pharmacist_approval' => false,
            ]
        );
    }
}