<?php

namespace Database\Seeders;

use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\ProductType;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class DefaultProductSetupSeeder extends Seeder
{
    public function run(): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $this->seedTypes($pharmacy);
        $this->seedCategories($pharmacy);
        $this->seedUnits($pharmacy);
        $this->seedProducts($pharmacy);
    }

    private function seedTypes(Pharmacy $pharmacy): void
    {
        $types = [
            ['name' => 'Medicine', 'code' => 'MEDICINE', 'description' => 'Human medicines and pharmaceutical products.'],
            ['name' => 'Cosmetic', 'code' => 'COSMETIC', 'description' => 'Beauty, skincare and cosmetic products.'],
            ['name' => 'Medical Device', 'code' => 'MEDICAL_DEVICE', 'description' => 'Devices, test kits, diagnostic items and health equipment.'],
            ['name' => 'Personal Care', 'code' => 'PERSONAL_CARE', 'description' => 'Soaps, lotions, hygiene and personal care items.'],
            ['name' => 'General Item', 'code' => 'GENERAL_ITEM', 'description' => 'Other sellable pharmacy items.'],
        ];

        foreach ($types as $type) {
            ProductType::query()->updateOrCreate(
                ['pharmacy_id' => $pharmacy->id, 'code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedCategories(Pharmacy $pharmacy): void
    {
        $typeMap = ProductType::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->pluck('id', 'code');

        $categories = [
            ['type' => 'MEDICINE', 'name' => 'Pain Relief', 'code' => 'PAIN_RELIEF'],
            ['type' => 'MEDICINE', 'name' => 'Antibiotic', 'code' => 'ANTIBIOTIC'],
            ['type' => 'MEDICINE', 'name' => 'Antifungal', 'code' => 'ANTIFUNGAL'],
            ['type' => 'MEDICINE', 'name' => 'Antimalarial', 'code' => 'ANTIMALARIAL'],
            ['type' => 'MEDICINE', 'name' => 'Vitamins & Supplements', 'code' => 'VITAMINS'],
            ['type' => 'MEDICINE', 'name' => 'Cough & Cold', 'code' => 'COUGH_COLD'],
            ['type' => 'MEDICINE', 'name' => 'Digestive Health', 'code' => 'DIGESTIVE_HEALTH'],

            ['type' => 'COSMETIC', 'name' => 'Skin Care', 'code' => 'SKIN_CARE'],
            ['type' => 'COSMETIC', 'name' => 'Perfumes', 'code' => 'PERFUMES'],
            ['type' => 'COSMETIC', 'name' => 'Hair Care', 'code' => 'HAIR_CARE'],

            ['type' => 'MEDICAL_DEVICE', 'name' => 'Test Kits', 'code' => 'TEST_KITS'],
            ['type' => 'MEDICAL_DEVICE', 'name' => 'Medical Supplies', 'code' => 'MEDICAL_SUPPLIES'],
            ['type' => 'MEDICAL_DEVICE', 'name' => 'Health Equipment', 'code' => 'HEALTH_EQUIPMENT'],

            ['type' => 'PERSONAL_CARE', 'name' => 'Soap & Hygiene', 'code' => 'SOAP_HYGIENE'],
            ['type' => 'PERSONAL_CARE', 'name' => 'Baby Care', 'code' => 'BABY_CARE'],
            ['type' => 'PERSONAL_CARE', 'name' => 'Oral Care', 'code' => 'ORAL_CARE'],

            ['type' => 'GENERAL_ITEM', 'name' => 'General Accessories', 'code' => 'GENERAL_ACCESSORIES'],
        ];

        foreach ($categories as $category) {
            ProductCategory::query()->updateOrCreate(
                ['pharmacy_id' => $pharmacy->id, 'code' => $category['code']],
                [
                    'product_type_id' => $typeMap[$category['type']] ?? null,
                    'name' => $category['name'],
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedUnits(Pharmacy $pharmacy): void
    {
        $units = [
            ['name' => 'Pill', 'code' => 'PILL', 'description' => 'Single pill unit.'],
            ['name' => 'Tablet', 'code' => 'TABLET', 'description' => 'Single tablet unit.'],
            ['name' => 'Capsule', 'code' => 'CAPSULE', 'description' => 'Single capsule unit.'],
            ['name' => 'Strip', 'code' => 'STRIP', 'description' => 'Medicine strip.'],
            ['name' => 'Box', 'code' => 'BOX', 'description' => 'Outer box or carton.'],
            ['name' => 'Bottle', 'code' => 'BOTTLE', 'description' => 'Bottle item.'],
            ['name' => 'Tube', 'code' => 'TUBE', 'description' => 'Tube item.'],
            ['name' => 'Sachet', 'code' => 'SACHET', 'description' => 'Single sachet.'],
            ['name' => 'Piece', 'code' => 'PIECE', 'description' => 'Single piece item.'],
            ['name' => 'Pack', 'code' => 'PACK', 'description' => 'Pack containing multiple items.'],
            ['name' => 'Pair', 'code' => 'PAIR', 'description' => 'Pair of items.'],
            ['name' => 'Roll', 'code' => 'ROLL', 'description' => 'Roll item.'],
        ];

        foreach ($units as $unit) {
            Unit::query()->updateOrCreate(
                ['pharmacy_id' => $pharmacy->id, 'code' => $unit['code']],
                [
                    'name' => $unit['name'],
                    'description' => $unit['description'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedProducts(Pharmacy $pharmacy): void
    {
        $categoryMap = ProductCategory::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->pluck('id', 'code');

        $unitMap = Unit::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->pluck('id', 'code');

        foreach ($this->defaultProducts() as $categoryCode => $items) {
            $category = ProductCategory::query()->find($categoryMap[$categoryCode] ?? null);

            if (! $category) {
                continue;
            }

            foreach ($items as $item) {
                $baseUnitId = $unitMap[$item['base_unit']] ?? null;

                if (! $baseUnitId) {
                    continue;
                }

                $product = Product::query()->updateOrCreate(
                    [
                        'pharmacy_id' => $pharmacy->id,
                        'code' => $item['code'],
                    ],
                    [
                        'product_type_id' => $category->product_type_id,
                        'product_category_id' => $category->id,
                        'base_unit_id' => $baseUnitId,
                        'name' => $item['name'],
                        'generic_name' => $item['generic_name'] ?? null,
                        'strength' => $item['strength'] ?? null,
                        'brand' => $item['brand'] ?? null,
                        'barcode' => null,
                        'requires_expiry' => $item['requires_expiry'] ?? true,
                        'requires_prescription' => $item['requires_prescription'] ?? false,
                        'is_active' => true,
                        'description' => $item['description'] ?? null,
                    ]
                );

                $this->syncProductUnits(
                    pharmacyId: $pharmacy->id,
                    product: $product,
                    unitMap: $unitMap,
                    scheme: $item['scheme']
                );
            }
        }
    }

private function syncProductUnits(int $pharmacyId, Product $product, $unitMap, string $scheme): void
{
    ProductUnit::query()
        ->where('product_id', $product->id)
        ->update([
            'is_base' => false,
            'is_default_sale_unit' => false,
        ]);

    foreach ($this->unitScheme($scheme) as $row) {
        if (! isset($unitMap[$row['unit']])) {
            continue;
        }

        ProductUnit::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'unit_id' => $unitMap[$row['unit']],
            ],
            [
                'pharmacy_id' => $pharmacyId,
                'quantity_in_base_units' => $row['quantity'],

                // All units can be used later for purchase/retail/wholesale.
                // Price is NOT stored for all units. It is calculated from base price.
                'can_purchase' => true,
                'can_sell_retail' => true,
                'can_sell_wholesale' => true,

                'is_base' => $row['base'],
                'is_default_sale_unit' => $row['default'],
                'is_active' => true,
            ]
        );
    }

    $baseProductUnit = ProductUnit::query()
        ->where('product_id', $product->id)
        ->where('is_base', true)
        ->first();

    if (! $baseProductUnit) {
        $baseProductUnit = ProductUnit::query()
            ->where('product_id', $product->id)
            ->orderBy('quantity_in_base_units')
            ->first();
    }

    if (! $baseProductUnit) {
        return;
    }

    /*
     * IMPORTANT:
     * Only base unit prices are stored.
     *
     * Example:
     * Base unit: Strip
     * Retail base price: 1,000
     * Wholesale base price: 700
     *
     * Box = 20 Strips
     * Box retail price = 1,000 x 20 = 20,000
     * Box wholesale price = 700 x 20 = 14,000
     *
     * So we delete old non-base prices created by the previous seeder logic.
     */
    ProductPrice::query()
        ->where('pharmacy_id', $pharmacyId)
        ->where('product_id', $product->id)
        ->where('product_unit_id', '!=', $baseProductUnit->id)
        ->delete();

    $this->seedDefaultPrices($pharmacyId, $product, $baseProductUnit);
}

private function seedDefaultPrices(int $pharmacyId, Product $product, ProductUnit $baseProductUnit): void
{
    /*
     * Store price for BASE UNIT ONLY.
     *
     * Retail base price    = 1,000
     * Wholesale base price = 700
     *
     * Other unit prices are calculated in UI/POS using:
     * base_price x quantity_in_base_units
     */

    ProductPrice::query()->firstOrCreate(
        [
            'product_unit_id' => $baseProductUnit->id,
            'price_type' => 'retail',
        ],
        [
            'pharmacy_id' => $pharmacyId,
            'product_id' => $product->id,
            'price' => 1000,
            'currency' => 'TZS',
            'is_active' => true,
        ]
    );

    ProductPrice::query()->firstOrCreate(
        [
            'product_unit_id' => $baseProductUnit->id,
            'price_type' => 'wholesale',
        ],
        [
            'pharmacy_id' => $pharmacyId,
            'product_id' => $product->id,
            'price' => 700,
            'currency' => 'TZS',
            'is_active' => true,
        ]
    );
}
    private function unitScheme(string $scheme): array
    {
        return match ($scheme) {
            'tablet_strip_box' => [
                ['unit' => 'TABLET', 'quantity' => 1, 'base' => true, 'default' => true],
                ['unit' => 'STRIP', 'quantity' => 10, 'base' => false, 'default' => false],
                ['unit' => 'BOX', 'quantity' => 100, 'base' => false, 'default' => false],
            ],

            'pill_strip_box' => [
                ['unit' => 'PILL', 'quantity' => 1, 'base' => true, 'default' => true],
                ['unit' => 'STRIP', 'quantity' => 10, 'base' => false, 'default' => false],
                ['unit' => 'BOX', 'quantity' => 100, 'base' => false, 'default' => false],
            ],

            'capsule_strip_box' => [
                ['unit' => 'CAPSULE', 'quantity' => 1, 'base' => true, 'default' => true],
                ['unit' => 'STRIP', 'quantity' => 10, 'base' => false, 'default' => false],
                ['unit' => 'BOX', 'quantity' => 100, 'base' => false, 'default' => false],
            ],

            'strip_box' => [
                ['unit' => 'STRIP', 'quantity' => 1, 'base' => true, 'default' => true],
                ['unit' => 'BOX', 'quantity' => 20, 'base' => false, 'default' => false],
            ],

            'bottle_box' => [
                ['unit' => 'BOTTLE', 'quantity' => 1, 'base' => true, 'default' => true],
                ['unit' => 'BOX', 'quantity' => 12, 'base' => false, 'default' => false],
            ],

            'tube_box' => [
                ['unit' => 'TUBE', 'quantity' => 1, 'base' => true, 'default' => true],
                ['unit' => 'BOX', 'quantity' => 12, 'base' => false, 'default' => false],
            ],

            'sachet_box' => [
                ['unit' => 'SACHET', 'quantity' => 1, 'base' => true, 'default' => true],
                ['unit' => 'BOX', 'quantity' => 100, 'base' => false, 'default' => false],
            ],

            'piece_pack_box' => [
                ['unit' => 'PIECE', 'quantity' => 1, 'base' => true, 'default' => true],
                ['unit' => 'PACK', 'quantity' => 6, 'base' => false, 'default' => false],
                ['unit' => 'BOX', 'quantity' => 72, 'base' => false, 'default' => false],
            ],

            'piece_box' => [
                ['unit' => 'PIECE', 'quantity' => 1, 'base' => true, 'default' => true],
                ['unit' => 'BOX', 'quantity' => 24, 'base' => false, 'default' => false],
            ],

            'pair_box' => [
                ['unit' => 'PAIR', 'quantity' => 1, 'base' => true, 'default' => true],
                ['unit' => 'BOX', 'quantity' => 50, 'base' => false, 'default' => false],
            ],

            'pack_box' => [
                ['unit' => 'PACK', 'quantity' => 1, 'base' => true, 'default' => true],
                ['unit' => 'BOX', 'quantity' => 12, 'base' => false, 'default' => false],
            ],

            'roll_box' => [
                ['unit' => 'ROLL', 'quantity' => 1, 'base' => true, 'default' => true],
                ['unit' => 'BOX', 'quantity' => 24, 'base' => false, 'default' => false],
            ],

            default => [
                ['unit' => 'PIECE', 'quantity' => 1, 'base' => true, 'default' => true],
            ],
        };
    }

    private function defaultProducts(): array
    {
        return [
            'PAIN_RELIEF' => [
                $this->product('Paracetamol 500mg Tablet', 'PARA500TAB', 'TABLET', 'tablet_strip_box', 'Paracetamol', '500mg'),
                $this->product('Ibuprofen 400mg Tablet', 'IBU400TAB', 'TABLET', 'tablet_strip_box', 'Ibuprofen', '400mg'),
                $this->product('Diclofenac 50mg Tablet', 'DICLO50TAB', 'TABLET', 'tablet_strip_box', 'Diclofenac', '50mg', true, true),
                $this->product('Aspirin 75mg Tablet', 'ASP75TAB', 'TABLET', 'tablet_strip_box', 'Aspirin', '75mg'),
                $this->product('Naproxen 250mg Tablet', 'NAP250TAB', 'TABLET', 'tablet_strip_box', 'Naproxen', '250mg', true, true),
                $this->product('Tramadol 50mg Capsule', 'TRAM50CAP', 'CAPSULE', 'capsule_strip_box', 'Tramadol', '50mg', true, true),
                $this->product('Celecoxib 200mg Capsule', 'CEL200CAP', 'CAPSULE', 'capsule_strip_box', 'Celecoxib', '200mg', true, true),
                $this->product('Paracetamol Syrup 120mg/5ml', 'PARASYR120', 'BOTTLE', 'bottle_box', 'Paracetamol', '120mg/5ml'),
                $this->product('Diclofenac Gel 30g', 'DICLOGEL30', 'TUBE', 'tube_box', 'Diclofenac', '30g'),
                $this->product('Mefenamic Acid 500mg Tablet', 'MEF500TAB', 'TABLET', 'tablet_strip_box', 'Mefenamic Acid', '500mg', true, true),
            ],

            'ANTIBIOTIC' => [
                $this->product('Amoxicillin 500mg Capsule', 'AMOX500CAP', 'CAPSULE', 'capsule_strip_box', 'Amoxicillin', '500mg', true, true),
                $this->product('Ampicillin 500mg Capsule', 'AMP500CAP', 'CAPSULE', 'capsule_strip_box', 'Ampicillin', '500mg', true, true),
                $this->product('Azithromycin 500mg Tablet', 'AZI500TAB', 'TABLET', 'tablet_strip_box', 'Azithromycin', '500mg', true, true),
                $this->product('Ciprofloxacin 500mg Tablet', 'CIP500TAB', 'TABLET', 'tablet_strip_box', 'Ciprofloxacin', '500mg', true, true),
                $this->product('Doxycycline 100mg Capsule', 'DOXY100CAP', 'CAPSULE', 'capsule_strip_box', 'Doxycycline', '100mg', true, true),
                $this->product('Cephalexin 500mg Capsule', 'CEPH500CAP', 'CAPSULE', 'capsule_strip_box', 'Cephalexin', '500mg', true, true),
                $this->product('Metronidazole 400mg Tablet', 'METRO400TAB', 'TABLET', 'tablet_strip_box', 'Metronidazole', '400mg', true, true),
                $this->product('Co-Amoxiclav 625mg Tablet', 'COAM625TAB', 'TABLET', 'tablet_strip_box', 'Amoxicillin/Clavulanate', '625mg', true, true),
                $this->product('Erythromycin 250mg Tablet', 'ERY250TAB', 'TABLET', 'tablet_strip_box', 'Erythromycin', '250mg', true, true),
                $this->product('Cefixime 200mg Tablet', 'CEFIX200TAB', 'TABLET', 'tablet_strip_box', 'Cefixime', '200mg', true, true),
            ],

            'ANTIFUNGAL' => [
                $this->product('Fluconazole 150mg Capsule', 'FLU150CAP', 'CAPSULE', 'capsule_strip_box', 'Fluconazole', '150mg', true, true),
                $this->product('Ketoconazole 200mg Tablet', 'KETO200TAB', 'TABLET', 'tablet_strip_box', 'Ketoconazole', '200mg', true, true),
                $this->product('Clotrimazole Cream 20g', 'CLOTCREAM20', 'TUBE', 'tube_box', 'Clotrimazole', '20g'),
                $this->product('Miconazole Cream 20g', 'MICOCREAM20', 'TUBE', 'tube_box', 'Miconazole', '20g'),
                $this->product('Terbinafine 250mg Tablet', 'TERB250TAB', 'TABLET', 'tablet_strip_box', 'Terbinafine', '250mg', true, true),
                $this->product('Nystatin Oral Drops', 'NYSDROP', 'BOTTLE', 'bottle_box', 'Nystatin'),
                $this->product('Griseofulvin 500mg Tablet', 'GRIS500TAB', 'TABLET', 'tablet_strip_box', 'Griseofulvin', '500mg', true, true),
                $this->product('Econazole Cream 20g', 'ECONCREAM20', 'TUBE', 'tube_box', 'Econazole', '20g'),
                $this->product('Clotrimazole Pessary', 'CLOTPESS', 'PIECE', 'piece_box', 'Clotrimazole'),
                $this->product('Antifungal Dusting Powder', 'ANTIFUNGPOW', 'BOTTLE', 'bottle_box'),
            ],

            'ANTIMALARIAL' => [
                $this->product('Artemether Lumefantrine 20/120', 'ALU20120', 'TABLET', 'tablet_strip_box', 'Artemether/Lumefantrine', '20/120mg', true, true),
                $this->product('Artemether Lumefantrine DS', 'ALUDS', 'TABLET', 'tablet_strip_box', 'Artemether/Lumefantrine', '80/480mg', true, true),
                $this->product('Quinine 300mg Tablet', 'QUI300TAB', 'TABLET', 'tablet_strip_box', 'Quinine', '300mg', true, true),
                $this->product('Dihydroartemisinin Piperaquine', 'DHAPPQ', 'TABLET', 'tablet_strip_box', 'DHA/Piperaquine', null, true, true),
                $this->product('Sulfadoxine Pyrimethamine Tablet', 'SPTAB', 'TABLET', 'tablet_strip_box', 'SP', null, true, true),
                $this->product('Primaquine 15mg Tablet', 'PRI15TAB', 'TABLET', 'tablet_strip_box', 'Primaquine', '15mg', true, true),
                $this->product('Mefloquine 250mg Tablet', 'MEFLO250TAB', 'TABLET', 'tablet_strip_box', 'Mefloquine', '250mg', true, true),
                $this->product('Chloroquine 250mg Tablet', 'CHLOR250TAB', 'TABLET', 'tablet_strip_box', 'Chloroquine', '250mg', true, true),
                $this->product('Artesunate Suppository', 'ARTSUPP', 'PIECE', 'piece_box', 'Artesunate', null, true, true),
                $this->product('Malaria Rapid Test Kit', 'MALRDTMED', 'PIECE', 'piece_box'),
            ],

            'VITAMINS' => [
                $this->product('Vitamin C 1000mg Tablet', 'VITC1000TAB', 'TABLET', 'tablet_strip_box', 'Vitamin C', '1000mg'),
                $this->product('Vitamin B Complex Tablet', 'VITBCOMPTAB', 'TABLET', 'tablet_strip_box', 'B Complex'),
                $this->product('Multivitamin Tablet', 'MULTIVITTAB', 'TABLET', 'tablet_strip_box', 'Multivitamin'),
                $this->product('Folic Acid 5mg Tablet', 'FOLIC5TAB', 'TABLET', 'tablet_strip_box', 'Folic Acid', '5mg'),
                $this->product('Ferrous Sulphate Tablet', 'FERROUSTAB', 'TABLET', 'tablet_strip_box', 'Ferrous Sulphate'),
                $this->product('Calcium 500mg Tablet', 'CAL500TAB', 'TABLET', 'tablet_strip_box', 'Calcium', '500mg'),
                $this->product('Zinc 20mg Tablet', 'ZINC20TAB', 'TABLET', 'tablet_strip_box', 'Zinc', '20mg'),
                $this->product('Vitamin D3 Softgel', 'VITD3SOFT', 'CAPSULE', 'capsule_strip_box', 'Vitamin D3'),
                $this->product('Omega 3 Capsule', 'OMEGA3CAP', 'CAPSULE', 'capsule_strip_box', 'Omega 3'),
                $this->product('Pregnancy Supplement Tablet', 'PREGSUPTAB', 'TABLET', 'tablet_strip_box'),
            ],

            'COUGH_COLD' => [
                $this->product('Cough Syrup 100ml', 'COUGHSYR100', 'BOTTLE', 'bottle_box', null, '100ml'),
                $this->product('Dry Cough Syrup 100ml', 'DRYCOUGHSYR', 'BOTTLE', 'bottle_box', null, '100ml'),
                $this->product('Chest Rub 25g', 'CHESTRUB25', 'TUBE', 'tube_box', null, '25g'),
                $this->product('Menthol Lozenges', 'MENTHLOZ', 'PIECE', 'piece_box'),
                $this->product('Cetirizine 10mg Tablet', 'CET10TAB', 'TABLET', 'tablet_strip_box', 'Cetirizine', '10mg'),
                $this->product('Loratadine 10mg Tablet', 'LORA10TAB', 'TABLET', 'tablet_strip_box', 'Loratadine', '10mg'),
                $this->product('Salbutamol Syrup 100ml', 'SALBSYR100', 'BOTTLE', 'bottle_box', 'Salbutamol', '100ml', true, true),
                $this->product('Nasal Drops 10ml', 'NASALDROP10', 'BOTTLE', 'bottle_box', null, '10ml'),
                $this->product('Flu Combination Tablet', 'FLUCOMBTAB', 'TABLET', 'tablet_strip_box'),
                $this->product('Expectorant Syrup 100ml', 'EXPECTSYR100', 'BOTTLE', 'bottle_box', null, '100ml'),
            ],

            'DIGESTIVE_HEALTH' => [
                $this->product('Omeprazole 20mg Capsule', 'OME20CAP', 'CAPSULE', 'capsule_strip_box', 'Omeprazole', '20mg'),
                $this->product('Loperamide 2mg Capsule', 'LOP2CAP', 'CAPSULE', 'capsule_strip_box', 'Loperamide', '2mg'),
                $this->product('ORS Sachet', 'ORSSACHET', 'SACHET', 'sachet_box', 'Oral Rehydration Salts'),
                $this->product('Antacid Suspension 100ml', 'ANTACIDSUS100', 'BOTTLE', 'bottle_box', 'Antacid', '100ml'),
                $this->product('Domperidone 10mg Tablet', 'DOMP10TAB', 'TABLET', 'tablet_strip_box', 'Domperidone', '10mg', true, true),
                $this->product('Metoclopramide 10mg Tablet', 'METOC10TAB', 'TABLET', 'tablet_strip_box', 'Metoclopramide', '10mg', true, true),
                $this->product('Bisacodyl 5mg Tablet', 'BISA5TAB', 'TABLET', 'tablet_strip_box', 'Bisacodyl', '5mg'),
                $this->product('Activated Charcoal Tablet', 'CHARCOALTAB', 'TABLET', 'tablet_strip_box', 'Activated Charcoal'),
                $this->product('Probiotic Capsule', 'PROBIOCAP', 'CAPSULE', 'capsule_strip_box', 'Probiotic'),
                $this->product('Zinc ORS Combo Sachet', 'ZINCORSSACH', 'SACHET', 'sachet_box'),
            ],

            'SKIN_CARE' => [
                $this->product('Body Lotion 250ml', 'BODYLOTION250', 'BOTTLE', 'bottle_box', null, '250ml', false),
                $this->product('Moisturizing Cream 100g', 'MOISTCREAM100', 'TUBE', 'tube_box', null, '100g', false),
                $this->product('Sunscreen SPF50 100ml', 'SUNSPF50100', 'BOTTLE', 'bottle_box', null, '100ml'),
                $this->product('Face Wash 150ml', 'FACEWASH150', 'BOTTLE', 'bottle_box', null, '150ml'),
                $this->product('Acne Gel 30g', 'ACNEGEL30', 'TUBE', 'tube_box', null, '30g'),
                $this->product('Petroleum Jelly 100g', 'PETJELLY100', 'BOTTLE', 'bottle_box', null, '100g', false),
                $this->product('Hand Cream 50ml', 'HANDCREAM50', 'TUBE', 'tube_box', null, '50ml', false),
                $this->product('Aloe Vera Gel 100ml', 'ALOEGEL100', 'BOTTLE', 'bottle_box', null, '100ml'),
                $this->product('Skin Toner 200ml', 'SKINTONER200', 'BOTTLE', 'bottle_box', null, '200ml'),
                $this->product('Lip Balm Piece', 'LIPBALMPIECE', 'PIECE', 'piece_box', null, null, false),
            ],

            'PERFUMES' => [
                $this->product('Perfume 30ml', 'PERFUME30', 'BOTTLE', 'bottle_box', null, '30ml', false),
                $this->product('Perfume 50ml', 'PERFUME50', 'BOTTLE', 'bottle_box', null, '50ml', false),
                $this->product('Perfume 100ml', 'PERFUME100', 'BOTTLE', 'bottle_box', null, '100ml', false),
                $this->product('Body Spray 150ml', 'BODYSPRAY150', 'BOTTLE', 'bottle_box', null, '150ml', false),
                $this->product('Roll On 50ml', 'ROLLON50', 'BOTTLE', 'bottle_box', null, '50ml', false),
                $this->product('Deodorant Stick', 'DEOSTICK', 'PIECE', 'piece_box', null, null, false),
                $this->product('Body Mist 250ml', 'BODYMIST250', 'BOTTLE', 'bottle_box', null, '250ml', false),
                $this->product('Cologne 100ml', 'COLOGNE100', 'BOTTLE', 'bottle_box', null, '100ml', false),
                $this->product('Aftershave 100ml', 'AFTERSHAVE100', 'BOTTLE', 'bottle_box', null, '100ml', false),
                $this->product('Mini Perfume Pack', 'MINIPERFPACK', 'PACK', 'pack_box', null, null, false),
            ],

            'HAIR_CARE' => [
                $this->product('Hair Shampoo 250ml', 'SHAMPOO250', 'BOTTLE', 'bottle_box', null, '250ml'),
                $this->product('Hair Conditioner 250ml', 'COND250', 'BOTTLE', 'bottle_box', null, '250ml'),
                $this->product('Hair Oil 100ml', 'HAIROIL100', 'BOTTLE', 'bottle_box', null, '100ml', false),
                $this->product('Hair Cream 100g', 'HAIRCREAM100', 'TUBE', 'tube_box', null, '100g', false),
                $this->product('Hair Gel 150g', 'HAIRGEL150', 'BOTTLE', 'bottle_box', null, '150g', false),
                $this->product('Anti-Dandruff Shampoo', 'ANTIDANDSHAMP', 'BOTTLE', 'bottle_box'),
                $this->product('Hair Relaxer Kit', 'RELAXERKIT', 'PACK', 'pack_box'),
                $this->product('Hair Treatment 250ml', 'HAIRTREAT250', 'BOTTLE', 'bottle_box', null, '250ml'),
                $this->product('Hair Spray 200ml', 'HAIRSPRAY200', 'BOTTLE', 'bottle_box', null, '200ml', false),
                $this->product('Hair Dye Pack', 'HAIRDYEPACK', 'PACK', 'pack_box'),
            ],

            'TEST_KITS' => [
                $this->product('Pregnancy Test Kit', 'PREGTESTKIT', 'PIECE', 'piece_box'),
                $this->product('Malaria Rapid Test Kit', 'MALRDTKIT', 'PIECE', 'piece_box'),
                $this->product('HIV Rapid Test Kit', 'HIVRDTKIT', 'PIECE', 'piece_box'),
                $this->product('Blood Glucose Test Strip', 'GLUCSTRIP', 'STRIP', 'strip_box'),
                $this->product('Urine Test Strip', 'URINETESTSTRIP', 'STRIP', 'strip_box'),
                $this->product('COVID Rapid Test Kit', 'COVIDRDTKIT', 'PIECE', 'piece_box'),
                $this->product('Typhoid Test Kit', 'TYPHOIDKIT', 'PIECE', 'piece_box'),
                $this->product('Hepatitis B Test Kit', 'HEPBKIT', 'PIECE', 'piece_box'),
                $this->product('Ovulation Test Kit', 'OVULATIONKIT', 'PIECE', 'piece_box'),
                $this->product('Cholesterol Test Strip', 'CHOLSTRIP', 'STRIP', 'strip_box'),
            ],

            'MEDICAL_SUPPLIES' => [
                $this->product('Syringe 5ml', 'SYRINGE5ML', 'PIECE', 'piece_box', null, '5ml'),
                $this->product('Syringe 10ml', 'SYRINGE10ML', 'PIECE', 'piece_box', null, '10ml'),
                $this->product('Disposable Gloves Pair', 'GLOVESPAIR', 'PAIR', 'pair_box'),
                $this->product('Face Mask Piece', 'FACEMASK', 'PIECE', 'piece_box'),
                $this->product('Cotton Wool Roll', 'COTTONROLL', 'ROLL', 'roll_box', null, null, false),
                $this->product('Bandage Roll', 'BANDAGEROLL', 'ROLL', 'roll_box', null, null, false),
                $this->product('Gauze Swab Pack', 'GAUZEPACK', 'PACK', 'pack_box'),
                $this->product('IV Cannula Piece', 'IVCANNULA', 'PIECE', 'piece_box'),
                $this->product('Alcohol Swab Piece', 'ALCSWAB', 'PIECE', 'piece_box'),
                $this->product('Urine Container Piece', 'URINECONT', 'PIECE', 'piece_box', null, null, false),
            ],

            'HEALTH_EQUIPMENT' => [
                $this->product('Digital Thermometer', 'DIGITHERMO', 'PIECE', 'piece_box', null, null, false),
                $this->product('Blood Pressure Machine', 'BPMACHINE', 'PIECE', 'piece_box', null, null, false),
                $this->product('Glucometer Device', 'GLUCOMETER', 'PIECE', 'piece_box', null, null, false),
                $this->product('Pulse Oximeter', 'OXIMETER', 'PIECE', 'piece_box', null, null, false),
                $this->product('Weighing Scale', 'WEIGHSCALE', 'PIECE', 'piece_box', null, null, false),
                $this->product('Nebulizer Machine', 'NEBULIZER', 'PIECE', 'piece_box', null, null, false),
                $this->product('Stethoscope', 'STETHOSCOPE', 'PIECE', 'piece_box', null, null, false),
                $this->product('Hot Water Bottle', 'HOTWATERBOT', 'PIECE', 'piece_box', null, null, false),
                $this->product('Walking Stick', 'WALKSTICK', 'PIECE', 'piece_box', null, null, false),
                $this->product('First Aid Kit', 'FIRSTAIDKIT', 'PACK', 'pack_box'),
            ],

            'SOAP_HYGIENE' => [
                $this->product('Antibacterial Soap 90g', 'ANTIBSOAP90', 'PIECE', 'piece_pack_box', null, '90g', false),
                $this->product('Dettol Soap 90g', 'DETTSOAP90', 'PIECE', 'piece_pack_box', null, '90g', false),
                $this->product('Liquid Hand Wash 250ml', 'HANDWASH250', 'BOTTLE', 'bottle_box', null, '250ml'),
                $this->product('Hand Sanitizer 100ml', 'SANITIZER100', 'BOTTLE', 'bottle_box', null, '100ml'),
                $this->product('Hand Sanitizer 500ml', 'SANITIZER500', 'BOTTLE', 'bottle_box', null, '500ml'),
                $this->product('Toilet Tissue Pack', 'TISSUEPACK', 'PACK', 'pack_box', null, null, false),
                $this->product('Wet Wipes Pack', 'WETWIPESPACK', 'PACK', 'pack_box'),
                $this->product('Cotton Buds Pack', 'COTTONBUDS', 'PACK', 'pack_box', null, null, false),
                $this->product('Antiseptic Solution 500ml', 'ANTISEPTIC500', 'BOTTLE', 'bottle_box', null, '500ml'),
                $this->product('Disinfectant 1L', 'DISINFECT1L', 'BOTTLE', 'bottle_box', null, '1L'),
            ],

            'BABY_CARE' => [
                $this->product('Baby Diapers Small Pack', 'DIAPERSPACKS', 'PACK', 'pack_box', null, null, false),
                $this->product('Baby Diapers Medium Pack', 'DIAPERPACKM', 'PACK', 'pack_box', null, null, false),
                $this->product('Baby Wipes Pack', 'BABYWIPES', 'PACK', 'pack_box'),
                $this->product('Baby Lotion 250ml', 'BABYLOTION250', 'BOTTLE', 'bottle_box', null, '250ml'),
                $this->product('Baby Oil 100ml', 'BABYOIL100', 'BOTTLE', 'bottle_box', null, '100ml', false),
                $this->product('Baby Powder 100g', 'BABYPOWDER100', 'BOTTLE', 'bottle_box', null, '100g', false),
                $this->product('Baby Soap 75g', 'BABYSOAP75', 'PIECE', 'piece_pack_box', null, '75g', false),
                $this->product('Baby Shampoo 200ml', 'BABYSHAMP200', 'BOTTLE', 'bottle_box', null, '200ml'),
                $this->product('Feeding Bottle', 'FEEDBOTTLE', 'PIECE', 'piece_box', null, null, false),
                $this->product('Baby Cream 100g', 'BABYCREAM100', 'TUBE', 'tube_box', null, '100g'),
            ],

            'ORAL_CARE' => [
                $this->product('Toothpaste 100ml', 'TOOTHPASTE100', 'TUBE', 'tube_box', null, '100ml'),
                $this->product('Toothpaste 50ml', 'TOOTHPASTE50', 'TUBE', 'tube_box', null, '50ml'),
                $this->product('Toothbrush Adult', 'TOOTHBRUSHAD', 'PIECE', 'piece_box', null, null, false),
                $this->product('Toothbrush Kids', 'TOOTHBRUSHKD', 'PIECE', 'piece_box', null, null, false),
                $this->product('Mouthwash 250ml', 'MOUTHWASH250', 'BOTTLE', 'bottle_box', null, '250ml'),
                $this->product('Dental Floss', 'DENTFLOSS', 'PIECE', 'piece_box', null, null, false),
                $this->product('Denture Cleaner Tablet', 'DENTCLEANERTAB', 'TABLET', 'tablet_strip_box'),
                $this->product('Oral Rehydration Sachet', 'ORALREHYDSACH', 'SACHET', 'sachet_box'),
                $this->product('Gum Gel 10g', 'GUMGEL10', 'TUBE', 'tube_box', null, '10g'),
                $this->product('Tongue Cleaner', 'TONGUECLEAN', 'PIECE', 'piece_box', null, null, false),
            ],

            'GENERAL_ACCESSORIES' => [
                $this->product('Pharmacy Carry Bag', 'CARRYBAG', 'PIECE', 'piece_box', null, null, false),
                $this->product('Measuring Cup', 'MEASCUP', 'PIECE', 'piece_box', null, null, false),
                $this->product('Medicine Spoon', 'MEDSPOON', 'PIECE', 'piece_box', null, null, false),
                $this->product('Pill Cutter', 'PILLCUTTER', 'PIECE', 'piece_box', null, null, false),
                $this->product('Pill Organizer', 'PILLORG', 'PIECE', 'piece_box', null, null, false),
                $this->product('Elastic Support Band', 'ELASTSUPPORT', 'PIECE', 'piece_box', null, null, false),
                $this->product('Reusable Ice Pack', 'ICEPACK', 'PIECE', 'piece_box', null, null, false),
                $this->product('Hot Cold Gel Pack', 'GELPACK', 'PIECE', 'piece_box', null, null, false),
                $this->product('Medical Record Book', 'MEDRECBOOK', 'PIECE', 'piece_box', null, null, false),
                $this->product('Small Storage Container', 'STORAGECONT', 'PIECE', 'piece_box', null, null, false),
            ],
        ];
    }

    private function product(
        string $name,
        string $code,
        string $baseUnit,
        string $scheme,
        ?string $genericName = null,
        ?string $strength = null,
        bool $requiresExpiry = true,
        bool $requiresPrescription = false,
        ?string $brand = null,
        ?string $description = null
    ): array {
        return [
            'name' => $name,
            'code' => $code,
            'base_unit' => $baseUnit,
            'scheme' => $scheme,
            'generic_name' => $genericName,
            'strength' => $strength,
            'brand' => $brand,
            'requires_expiry' => $requiresExpiry,
            'requires_prescription' => $requiresPrescription,
            'description' => $description,
        ];
    }
}