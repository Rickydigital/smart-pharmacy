<?php

namespace App\Console\Commands;

use App\Services\AdministrativeHierarchyImportService;
use Illuminate\Console\Command;

class ImportTanzaniaHierarchy extends Command
{
    protected $signature = 'hierarchy:import-tz {file : Absolute or relative path to the CSV file}';

    protected $description = 'Import Tanzania administrative hierarchy from CSV into countries, regions, districts, wards, and streets tables';

    public function handle(AdministrativeHierarchyImportService $importService): int
    {
        $file = $this->argument('file');

        $resolvedPath = base_path($file);

        if (file_exists($file)) {
            $resolvedPath = $file;
        }

        try {
            $this->info('Starting Tanzania hierarchy import...');

            $result = $importService->importFromCsv($resolvedPath);

            $this->newLine();
            $this->info('Import completed successfully.');
            $this->line('Rows processed: '.$result['rows_processed']);
            $this->line('Regions created: '.$result['regions']);
            $this->line('Districts created: '.$result['districts']);
            $this->line('Wards created: '.$result['wards']);
            $this->line('Streets created: '.$result['streets']);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Import failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}

// php artisan hierarchy:import-tz "storage/app/administrative_hierachy.csv"