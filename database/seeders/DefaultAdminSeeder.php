<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultAdminSeeder extends Seeder
{
    public function run(): void
    {
        $pharmacy = Pharmacy::firstWhere('code', 'DEFAULT');

        if (! $pharmacy) {
            $this->command?->error('Default pharmacy was not found. Run InitialPharmacySeeder first.');
            return;
        }

        $branch = Branch::query()
            ->where('pharmacy_id', '=', $pharmacy->id)
            ->where('code', '=', 'MAIN')
            ->first();

        if (! $branch) {
            $this->command?->error('Main branch was not found. Run InitialPharmacySeeder first.');
            return;
        }

        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'pharmacy_id' => $pharmacy->id,
                'branch_id' => $branch->id,
                'first_name' => 'System',
                'last_name' => 'Admin',
                'email' => 'admin@pharmacy.test',
                'phone' => null,
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        $admin->syncRoles(['Admin']);

        $this->command?->info('Default admin user created/updated successfully.');
    }
}