<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Pharmacy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $pharmacy = Pharmacy::query()->first();

        if (! $pharmacy) {
            return;
        }

        $categories = [
            [
                'name' => 'Rent',
                'description' => 'Shop, office, or branch rent payments.',
            ],
            [
                'name' => 'Salaries & Wages',
                'description' => 'Staff salaries, overtime, and wages.',
            ],
            [
                'name' => 'Electricity',
                'description' => 'Electricity and power bills.',
            ],
            [
                'name' => 'Water',
                'description' => 'Water bills and related utility payments.',
            ],
            [
                'name' => 'Internet & Communication',
                'description' => 'Internet bundles, phone bills, and communication costs.',
            ],
            [
                'name' => 'Transport',
                'description' => 'Transport, delivery, fuel, and logistics expenses.',
            ],
            [
                'name' => 'Packaging',
                'description' => 'Bags, labels, envelopes, and packaging materials.',
            ],
            [
                'name' => 'Maintenance',
                'description' => 'Repairs, equipment maintenance, and service costs.',
            ],
            [
                'name' => 'Cleaning & Sanitation',
                'description' => 'Cleaning materials, sanitation, and hygiene costs.',
            ],
            [
                'name' => 'Bank Charges',
                'description' => 'Bank fees, mobile money charges, and transaction costs.',
            ],
            [
                'name' => 'Marketing',
                'description' => 'Promotion, branding, adverts, and customer awareness costs.',
            ],
            [
                'name' => 'Licenses & Permits',
                'description' => 'Business license, pharmacy permits, and regulatory fees.',
            ],
            [
                'name' => 'Office Supplies',
                'description' => 'Stationery, printing, paper, and office consumables.',
            ],
            [
                'name' => 'Security',
                'description' => 'Security services, guards, and related costs.',
            ],
            [
                'name' => 'Other Expenses',
                'description' => 'General expenses not listed in other categories.',
            ],
        ];

        foreach ($categories as $category) {
            $code = Str::upper(Str::slug($category['name'], '_'));

            ExpenseCategory::query()->updateOrCreate(
                [
                    'pharmacy_id' => $pharmacy->id,
                    'code' => $code,
                ],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}