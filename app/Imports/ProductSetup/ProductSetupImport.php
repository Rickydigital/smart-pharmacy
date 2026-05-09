<?php

namespace App\Imports\ProductSetup;

use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\ProductType;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductSetupImport implements ToCollection
{
    private int $createdTypes = 0;
    private int $updatedTypes = 0;

    private int $createdCategories = 0;
    private int $updatedCategories = 0;

    private int $createdUnits = 0;
    private int $updatedUnits = 0;

    private int $createdProducts = 0;
    private int $updatedProducts = 0;

    private int $createdProductUnits = 0;
    private int $updatedProductUnits = 0;

    private int $createdPrices = 0;
    private int $updatedPrices = 0;

    private array $errors = [];

    public function __construct(
        private readonly Pharmacy $pharmacy
    ) {
    }

    public function collection(Collection $rows): void
    {
        $dataRows = $this->normalRows($rows);

        if ($dataRows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'The import file does not contain product rows.',
            ]);
        }

        DB::transaction(function () use ($dataRows) {
            foreach ($dataRows as $rowNumber => $row) {
                try {
                    $this->importRow($row, (int) $rowNumber);
                } catch (\Throwable $exception) {
                    $this->errors[] = 'Row ' . $rowNumber . ': ' . $exception->getMessage();
                }
            }

            if (! empty($this->errors)) {
                throw ValidationException::withMessages([
                    'file' => $this->errors,
                ]);
            }
        });
    }

    public function summary(): array
    {
        return [
            'types_created' => $this->createdTypes,
            'types_updated' => $this->updatedTypes,

            'categories_created' => $this->createdCategories,
            'categories_updated' => $this->updatedCategories,

            'units_created' => $this->createdUnits,
            'units_updated' => $this->updatedUnits,

            'products_created' => $this->createdProducts,
            'products_updated' => $this->updatedProducts,

            'product_units_created' => $this->createdProductUnits,
            'product_units_updated' => $this->updatedProductUnits,

            'prices_created' => $this->createdPrices,
            'prices_updated' => $this->updatedPrices,
        ];
    }

    private function normalRows(Collection $rows): Collection
    {
        $headingRowIndex = null;
        $headings = [];

        foreach ($rows as $index => $row) {
            $normalized = collect($row)
                ->map(fn ($value) => $this->key((string) $value))
                ->toArray();

            if (
                in_array('product_type', $normalized, true)
                && in_array('product_name', $normalized, true)
                && in_array('base_unit', $normalized, true)
            ) {
                $headingRowIndex = $index;
                $headings = $normalized;
                break;
            }
        }

        if ($headingRowIndex === null) {
            throw ValidationException::withMessages([
                'file' => 'The import file is missing required headings. Please use the downloaded sample template.',
            ]);
        }

        $required = [
            'product_type',
            'category',
            'product_name',
            'base_unit',
            'retail_base_price',
            'wholesale_base_price',
        ];

        foreach ($required as $requiredHeading) {
            if (! in_array($requiredHeading, $headings, true)) {
                throw ValidationException::withMessages([
                    'file' => "Missing required column: {$requiredHeading}.",
                ]);
            }
        }

        return $rows
            ->slice($headingRowIndex + 1)
            ->values()
            ->mapWithKeys(function ($row, $index) use ($headingRowIndex, $headings) {
                $rowNumber = $headingRowIndex + $index + 2;

                $mapped = [];

                foreach ($headings as $columnIndex => $heading) {
                    if ($heading === '') {
                        continue;
                    }

                    $mapped[$heading] = $this->clean($row[$columnIndex] ?? null);
                }

                return [$rowNumber => $mapped];
            })
            ->filter(function (array $row) {
                return $this->clean($row['product_name'] ?? null) !== '';
            });
    }

    private function importRow(array $row, int $rowNumber): void
    {
        $productTypeName = $this->required($row, 'product_type', $rowNumber);
        $categoryName = $this->required($row, 'category', $rowNumber);
        $productName = $this->required($row, 'product_name', $rowNumber);
        $baseUnitName = $this->required($row, 'base_unit', $rowNumber);

        $retailBasePrice = $this->money($row['retail_base_price'] ?? null, 'retail_base_price', $rowNumber);
        $wholesaleBasePrice = $this->money($row['wholesale_base_price'] ?? null, 'wholesale_base_price', $rowNumber);

        [$categoryTypeName, $cleanCategoryName] = $this->parseCategory($categoryName, $productTypeName);

        $productType = $this->createOrUpdateType($categoryTypeName);
        $category = $this->createOrUpdateCategory($productType, $cleanCategoryName);
        $baseUnit = $this->createOrUpdateUnit($baseUnitName);

        $product = $this->createOrUpdateProduct(
            productType: $productType,
            category: $category,
            baseUnit: $baseUnit,
            row: $row,
            productName: $productName
        );

        $baseProductUnit = $this->createOrUpdateProductUnit(
            product: $product,
            unit: $baseUnit,
            quantityInBaseUnits: 1,
            isBase: true,
            isDefaultSaleUnit: true
        );

        $packageUnits = $this->parsePackageUnits($row['package_units'] ?? '');

        foreach ($packageUnits as $packageUnitName => $quantity) {
            if ($this->sameName($packageUnitName, $baseUnit->name)) {
                continue;
            }

            $unit = $this->createOrUpdateUnit($packageUnitName);

            $this->createOrUpdateProductUnit(
                product: $product,
                unit: $unit,
                quantityInBaseUnits: $quantity,
                isBase: false,
                isDefaultSaleUnit: false
            );
        }

        ProductPrice::query()
            ->where('pharmacy_id', $this->pharmacy->id)
            ->where('product_id', $product->id)
            ->where('product_unit_id', '!=', $baseProductUnit->id)
            ->delete();

        $this->createOrUpdateBasePrice($product, $baseProductUnit, 'retail', $retailBasePrice);
        $this->createOrUpdateBasePrice($product, $baseProductUnit, 'wholesale', $wholesaleBasePrice);
    }

    private function createOrUpdateType(string $name): ProductType
    {
        /** @var ProductType|null $existing */
        $existing = ProductType::query()
            ->where('pharmacy_id', $this->pharmacy->id)
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->first();

        if ($existing instanceof ProductType) {
            $existing->update([
                'name' => $name,
                'code' => $existing->code ?: $this->generateCode(ProductType::class, $name),
                'is_active' => true,
            ]);

            $this->updatedTypes++;

            return $existing;
        }

        $this->createdTypes++;

        /** @var ProductType $productType */
        $productType = ProductType::query()->create([
            'pharmacy_id' => $this->pharmacy->id,
            'name' => $name,
            'code' => $this->generateCode(ProductType::class, $name),
            'description' => null,
            'is_active' => true,
        ]);

        return $productType;
    }

    private function createOrUpdateCategory(ProductType $productType, string $name): ProductCategory
    {
        /** @var ProductCategory|null $existing */
        $existing = ProductCategory::query()
            ->where('pharmacy_id', $this->pharmacy->id)
            ->where('product_type_id', $productType->id)
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->first();

        if ($existing instanceof ProductCategory) {
            $existing->update([
                'product_type_id' => $productType->id,
                'name' => $name,
                'code' => $existing->code ?: $this->generateCode(ProductCategory::class, $name),
                'is_active' => true,
            ]);

            $this->updatedCategories++;

            return $existing;
        }

        $this->createdCategories++;

        /** @var ProductCategory $category */
        $category = ProductCategory::query()->create([
            'pharmacy_id' => $this->pharmacy->id,
            'product_type_id' => $productType->id,
            'name' => $name,
            'code' => $this->generateCode(ProductCategory::class, $name),
            'description' => null,
            'is_active' => true,
        ]);

        return $category;
    }

    private function createOrUpdateUnit(string $name): Unit
    {
        /** @var Unit|null $existing */
        $existing = Unit::query()
            ->where('pharmacy_id', $this->pharmacy->id)
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->first();

        if ($existing instanceof Unit) {
            $existing->update([
                'name' => $name,
                'code' => $existing->code ?: $this->generateCode(Unit::class, $name),
                'is_active' => true,
            ]);

            $this->updatedUnits++;

            return $existing;
        }

        $this->createdUnits++;

        /** @var Unit $unit */
        $unit = Unit::query()->create([
            'pharmacy_id' => $this->pharmacy->id,
            'name' => $name,
            'code' => $this->generateCode(Unit::class, $name),
            'description' => null,
            'is_active' => true,
        ]);

        return $unit;
    }

    private function createOrUpdateProduct(
        ProductType $productType,
        ProductCategory $category,
        Unit $baseUnit,
        array $row,
        string $productName
    ): Product {
        $genericName = $this->nullable($row['generic_name'] ?? null);
        $strength = $this->nullable($row['strength'] ?? null);
        $brand = $this->nullable($row['brand'] ?? null);

        /** @var Product|null $existing */
        $existing = Product::query()
            ->where('pharmacy_id', $this->pharmacy->id)
            ->where('product_category_id', $category->id)
            ->whereRaw('LOWER(name) = ?', [Str::lower($productName)])
            ->when($strength, function ($query) use ($strength) {
                $query->whereRaw('LOWER(strength) = ?', [Str::lower($strength)]);
            })
            ->first();

        if ($existing instanceof Product) {
            $existing->update([
                'product_type_id' => $productType->id,
                'product_category_id' => $category->id,
                'base_unit_id' => $baseUnit->id,
                'name' => $productName,
                'code' => $existing->code ?: $this->generateCode(Product::class, $productName),
                'barcode' => $existing->barcode ?: $this->generateBarcode(),
                'generic_name' => $genericName,
                'strength' => $strength,
                'brand' => $brand,
                'requires_expiry' => $existing->requires_expiry ?? true,
                'requires_prescription' => $existing->requires_prescription ?? false,
                'is_active' => true,
                'description' => $existing->description,
            ]);

            $this->updatedProducts++;

            return $existing;
        }

        $this->createdProducts++;

        /** @var Product $product */
        $product = Product::query()->create([
            'pharmacy_id' => $this->pharmacy->id,
            'product_type_id' => $productType->id,
            'product_category_id' => $category->id,
            'base_unit_id' => $baseUnit->id,
            'name' => $productName,
            'code' => $this->generateCode(Product::class, $productName),
            'barcode' => $this->generateBarcode(),
            'generic_name' => $genericName,
            'strength' => $strength,
            'brand' => $brand,
            'requires_expiry' => true,
            'requires_prescription' => false,
            'is_active' => true,
            'description' => null,
        ]);

        return $product;
    }

    private function createOrUpdateProductUnit(
        Product $product,
        Unit $unit,
        int $quantityInBaseUnits,
        bool $isBase,
        bool $isDefaultSaleUnit
    ): ProductUnit {
        if ($isBase) {
            ProductUnit::query()
                ->where('product_id', $product->id)
                ->update([
                    'is_base' => false,
                    'is_default_sale_unit' => false,
                ]);
        }

        if ($isDefaultSaleUnit) {
            ProductUnit::query()
                ->where('product_id', $product->id)
                ->update([
                    'is_default_sale_unit' => false,
                ]);
        }

        /** @var ProductUnit|null $existing */
        $existing = ProductUnit::query()
            ->where('pharmacy_id', $this->pharmacy->id)
            ->where('product_id', $product->id)
            ->where('unit_id', $unit->id)
            ->first();

        if ($existing instanceof ProductUnit) {
            $existing->update([
                'quantity_in_base_units' => $quantityInBaseUnits,
                'can_purchase' => true,
                'can_sell_retail' => true,
                'can_sell_wholesale' => true,
                'is_base' => $isBase,
                'is_default_sale_unit' => $isDefaultSaleUnit,
                'is_active' => true,
            ]);

            $this->updatedProductUnits++;

            return $existing;
        }

        $this->createdProductUnits++;

        /** @var ProductUnit $productUnit */
        $productUnit = ProductUnit::query()->create([
            'pharmacy_id' => $this->pharmacy->id,
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'quantity_in_base_units' => $quantityInBaseUnits,
            'can_purchase' => true,
            'can_sell_retail' => true,
            'can_sell_wholesale' => true,
            'is_base' => $isBase,
            'is_default_sale_unit' => $isDefaultSaleUnit,
            'is_active' => true,
        ]);

        return $productUnit;
    }

    private function createOrUpdateBasePrice(
        Product $product,
        ProductUnit $baseProductUnit,
        string $priceType,
        float $price
    ): ProductPrice {
        /** @var ProductPrice|null $existing */
        $existing = ProductPrice::query()
            ->where('pharmacy_id', $this->pharmacy->id)
            ->where('product_unit_id', $baseProductUnit->id)
            ->where('price_type', $priceType)
            ->first();

        if ($existing instanceof ProductPrice) {
            $existing->update([
                'product_id' => $product->id,
                'price' => $price,
                'currency' => $existing->currency ?: 'TZS',
                'is_active' => true,
            ]);

            $this->updatedPrices++;

            return $existing;
        }

        $this->createdPrices++;

        /** @var ProductPrice $productPrice */
        $productPrice = ProductPrice::query()->create([
            'pharmacy_id' => $this->pharmacy->id,
            'product_id' => $product->id,
            'product_unit_id' => $baseProductUnit->id,
            'price_type' => $priceType,
            'price' => $price,
            'currency' => 'TZS',
            'is_active' => true,
        ]);

        return $productPrice;
    }

    private function parseCategory(string $categoryValue, string $fallbackProductType): array
    {
        if (str_contains($categoryValue, '|')) {
            [$typeName, $categoryName] = array_map('trim', explode('|', $categoryValue, 2));

            return [
                $typeName !== '' ? $typeName : $fallbackProductType,
                $categoryName,
            ];
        }

        return [
            $fallbackProductType,
            $categoryValue,
        ];
    }

    private function parsePackageUnits(?string $value): array
    {
        $value = trim((string) $value);

        if ($value === '') {
            return [];
        }

        $items = preg_split('/[,;\n]+/', $value);
        $units = [];

        foreach ($items as $item) {
            $item = trim((string) $item);

            if ($item === '') {
                continue;
            }

            if (str_contains($item, ':')) {
                [$unitName, $quantity] = array_map('trim', explode(':', $item, 2));
            } elseif (str_contains($item, '=')) {
                [$unitName, $quantity] = array_map('trim', explode('=', $item, 2));
            } else {
                $parts = preg_split('/\s+/', $item) ?: [];
                $quantity = array_pop($parts);
                $unitName = trim(implode(' ', $parts));
            }

            if ($unitName === '') {
                throw new \RuntimeException("Invalid package unit '{$item}'. Use format like Strip:10, Box:100.");
            }

            if (! is_numeric($quantity) || (int) $quantity < 1) {
                throw new \RuntimeException("Invalid quantity for package unit '{$unitName}'. Quantity must be at least 1.");
            }

            $units[$unitName] = (int) $quantity;
        }

        return $units;
    }

    private function generateCode(string $modelClass, string $name): string
    {
        $base = strtoupper(Str::slug($name, '_'));
        $base = preg_replace('/[^A-Z0-9_]/', '', $base) ?: 'ITEM';
        $base = substr($base, 0, 45);

        $code = $base;
        $counter = 1;

        while ($modelClass::query()
            ->where('pharmacy_id', $this->pharmacy->id)
            ->where('code', $code)
            ->exists()) {
            $code = $base . '_' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $code;
    }

    private function generateBarcode(): string
    {
        do {
            $barcode = 'PRD'
                . str_pad((string) $this->pharmacy->id, 3, '0', STR_PAD_LEFT)
                . now()->format('ymd')
                . random_int(100000, 999999);
        } while (Product::query()
            ->where('pharmacy_id', $this->pharmacy->id)
            ->where('barcode', $barcode)
            ->exists());

        return $barcode;
    }

    private function required(array $row, string $field, int $rowNumber): string
    {
        $value = $this->clean($row[$field] ?? null);

        if ($value === '') {
            throw new \RuntimeException("{$field} is required.");
        }

        return $value;
    }

    private function money(mixed $value, string $field, int $rowNumber): float
    {
        $value = $this->clean($value);

        if ($value === '') {
            throw new \RuntimeException("{$field} is required.");
        }

        $value = str_replace(',', '', $value);

        if (! is_numeric($value) || (float) $value < 0) {
            throw new \RuntimeException("{$field} must be a valid number greater than or equal to 0.");
        }

        return (float) $value;
    }

    private function nullable(mixed $value): ?string
    {
        $value = $this->clean($value);

        return $value === '' ? null : $value;
    }

    private function clean(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    private function key(string $value): string
    {
        return Str::of($value)
            ->trim()
            ->lower()
            ->replace([' ', '-', '.'], '_')
            ->replaceMatches('/[^a-z0-9_]/', '')
            ->toString();
    }

    private function sameName(string $first, string $second): bool
    {
        return Str::lower(trim($first)) === Str::lower(trim($second));
    }
}