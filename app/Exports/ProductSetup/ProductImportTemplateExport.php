<?php

namespace App\Exports\ProductSetup;

use App\Models\Pharmacy;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductImportTemplateExport implements WithMultipleSheets
{
    public function __construct(
        private readonly ?Pharmacy $pharmacy = null
    ) {
    }

    public function sheets(): array
    {
        $pharmacy = $this->pharmacy ?: Pharmacy::query()->firstOrFail();

        $productTypes = ProductType::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        $categories = ProductCategory::query()
            ->with('productType')
            ->where('pharmacy_id', $pharmacy->id)
            ->orderBy('name')
            ->get()
            ->map(function (ProductCategory $category) {
                return trim(($category->productType?->name ?: 'Unknown') . ' | ' . $category->name);
            })
            ->values()
            ->all();

        $units = Unit::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        if (empty($productTypes)) {
            $productTypes = [
                'Medicine',
                'Cosmetic',
                'Medical Device',
                'Personal Care',
                'General Item',
            ];
        }

        if (empty($categories)) {
            $categories = [
                'Medicine | Pain Relief',
                'Medicine | Antibiotic',
                'Medicine | Cough & Cold',
                'Medical Device | Test Kits',
                'Medical Device | Medical Supplies',
                'Cosmetic | Skin Care',
                'Personal Care | Soap & Hygiene',
            ];
        }

        if (empty($units)) {
            $units = [
                'Tablet',
                'Capsule',
                'Strip',
                'Box',
                'Bottle',
                'Tube',
                'Sachet',
                'Piece',
                'Pack',
                'Pair',
                'Roll',
            ];
        }

        return [
            new ProductImportTemplateSheet(
                productTypes: $productTypes,
                categories: $categories,
                units: $units,
            ),

            new ProductTemplateListsSheet(
                productTypes: $productTypes,
                categories: $categories,
                units: $units,
            ),
        ];
    }
}