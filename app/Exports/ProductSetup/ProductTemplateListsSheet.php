<?php

namespace App\Exports\ProductSetup;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateListsSheet implements FromArray, WithEvents, WithTitle
{
    public function __construct(
        private readonly array $productTypes,
        private readonly array $categories,
        private readonly array $units,
    ) {
    }

    public function title(): string
    {
        return '_lists';
    }

    public function array(): array
    {
        $maxRows = max(
            count($this->productTypes),
            count($this->categories),
            count($this->units),
            1
        );

        $rows = [
            [
                'product_types',
                'categories',
                'units',
            ],
        ];

        for ($i = 0; $i < $maxRows; $i++) {
            $rows[] = [
                $this->productTypes[$i] ?? null,
                $this->categories[$i] ?? null,
                $this->units[$i] ?? null,
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $this->styleSheet($sheet);

                $sheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
            },
        ];
    }

    private function styleSheet(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        foreach (['A' => 30, 'B' => 38, 'C' => 24] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }
}