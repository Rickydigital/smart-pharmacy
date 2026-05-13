<?php

namespace App\Services;

use App\Models\Country;
use App\Models\District;
use App\Models\Region;
use App\Models\Street;
use App\Models\Ward;
use Illuminate\Support\Facades\DB;

class AdministrativeHierarchyImportService
{
    public function importFromCsv(string $filePath): array
    {
        if (! file_exists($filePath)) {
            throw new \InvalidArgumentException("CSV file not found at path: {$filePath}");
        }

        $handle = fopen($filePath, 'r');

        if (! $handle) {
            throw new \RuntimeException("Unable to open CSV file: {$filePath}");
        }

        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);
            throw new \RuntimeException('CSV file is empty or header could not be read.');
        }

        $header = array_map(function ($value) {
            $value = (string) $value;
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);

            return trim($value);
        }, $header);

        $requiredColumns = [
            'region_code',
            'region_name',
            'district_code',
            'district_name',
            'ward_code',
            'ward_name',
            'village_mtaa_code',
            'village_mtaa_name',
        ];

        foreach ($requiredColumns as $column) {
            if (! in_array($column, $header, true)) {
                fclose($handle);
                throw new \RuntimeException("Missing required CSV column: {$column}");
            }
        }

        $country = Country::query()->updateOrCreate(
            ['code' => 'TZ'],
            [
                'name' => 'Tanzania',
                'is_active' => true,
            ]
        );

        $created = [
            'regions' => 0,
            'districts' => 0,
            'wards' => 0,
            'streets' => 0,
            'rows_processed' => 0,
        ];

        DB::transaction(function () use ($handle, $header, $country, &$created) {
            while (($row = fgetcsv($handle)) !== false) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $data = $this->mapRow($header, $row);

                $regionName = $this->cleanValue($data['region_name'] ?? null);
                $districtName = $this->cleanValue($data['district_name'] ?? null);
                $wardName = $this->cleanValue($data['ward_name'] ?? null);
                $streetName = $this->cleanValue($data['village_mtaa_name'] ?? null);

                $regionCode = $this->cleanCode($data['region_code'] ?? null);
                $districtCode = $this->cleanCode($data['district_code'] ?? null);
                $wardCode = $this->cleanCode($data['ward_code'] ?? null);
                $streetCode = $this->cleanCode($data['village_mtaa_code'] ?? null);

                if (! $regionName || ! $districtName || ! $wardName || ! $streetName) {
                    continue;
                }

                $region = Region::query()->firstOrCreate(
                    [
                        'country_id' => $country->id,
                        'name' => $regionName,
                    ],
                    [
                        'code' => $regionCode,
                        'is_active' => true,
                    ]
                );

                if ($region->wasRecentlyCreated) {
                    $created['regions']++;
                }

                if (! $region->code && $regionCode) {
                    $region->update(['code' => $regionCode]);
                }

                $district = District::query()->firstOrCreate(
                    [
                        'region_id' => $region->id,
                        'name' => $districtName,
                    ],
                    [
                        'code' => $districtCode,
                        'is_active' => true,
                    ]
                );

                if ($district->wasRecentlyCreated) {
                    $created['districts']++;
                }

                if (! $district->code && $districtCode) {
                    $district->update(['code' => $districtCode]);
                }

                $ward = Ward::query()->firstOrCreate(
                    [
                        'district_id' => $district->id,
                        'name' => $wardName,
                    ],
                    [
                        'code' => $wardCode,
                        'is_active' => true,
                    ]
                );

                if ($ward->wasRecentlyCreated) {
                    $created['wards']++;
                }

                if (! $ward->code && $wardCode) {
                    $ward->update(['code' => $wardCode]);
                }

                $street = Street::query()->firstOrCreate(
                    [
                        'ward_id' => $ward->id,
                        'name' => $streetName,
                    ],
                    [
                        'code' => $streetCode,
                        'is_active' => true,
                    ]
                );

                if ($street->wasRecentlyCreated) {
                    $created['streets']++;
                }

                if (! $street->code && $streetCode) {
                    $street->update(['code' => $streetCode]);
                }

                $created['rows_processed']++;
            }
        });

        fclose($handle);

        return $created;
    }

    protected function mapRow(array $header, array $row): array
    {
        $mapped = [];

        foreach ($header as $index => $column) {
            $mapped[$column] = $row[$index] ?? null;
        }

        return $mapped;
    }

    protected function cleanValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '' || strtolower($value) === 'nan') {
            return null;
        }

        $value = $this->normalizeEncoding($value);
        $value = $this->transliterateToSafeString($value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    protected function cleanCode(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '' || strtolower($value) === 'nan') {
            return null;
        }

        $value = $this->normalizeEncoding($value);
        $value = trim($value);

        if (str_contains($value, '.')) {
            $value = preg_replace('/\.0+$/', '', $value);
        }

        return $value !== '' ? $value : null;
    }

    protected function normalizeEncoding(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        $detected = mb_detect_encoding(
            $value,
            ['UTF-8', 'Windows-1252', 'ISO-8859-1', 'ISO-8859-15', 'ASCII'],
            true
        );

        if ($detected && $detected !== 'UTF-8') {
            $converted = @mb_convert_encoding($value, 'UTF-8', $detected);

            if ($converted !== false) {
                $value = $converted;
            }
        }

        $cleaned = @iconv('UTF-8', 'UTF-8//IGNORE', $value);

        if ($cleaned !== false) {
            $value = $cleaned;
        }

        return $value;
    }

    protected function transliterateToSafeString(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        if ($ascii !== false && $ascii !== '') {
            $value = $ascii;
        }

        $value = preg_replace('/[^\x20-\x7E]/', '', $value);

        return trim($value);
    }

    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}