<?php

namespace App\Exports\ProductSetup;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Comment;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductImportTemplateSheet implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(
        private readonly array $productTypes,
        private readonly array $categories,
        private readonly array $units,
    ) {
    }

    public function title(): string
    {
        return 'Products';
    }

    public function array(): array
    {
        return [
            [
                'Product Import Template',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ],
            [
                'Fill one row per product. Codes and barcodes are generated automatically. Base unit is the main/smallest unit. Package units are written like: Strip:10, Box:100',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ],
            [
                'product_type',
                'category',
                'product_name',
                'generic_name',
                'strength',
                'brand',
                'base_unit',
                'package_units',
                'retail_base_price',
                'wholesale_base_price',
            ],
            [
                'Medicine',
                'Medicine | Pain Relief',
                'Paracetamol 500mg Tablet',
                'Paracetamol',
                '500mg',
                '',
                'Tablet',
                'Strip:10, Box:100',
                1000,
                700,
            ],
            [
                'Medical Device',
                'Medical Device | Test Kits',
                'Blood Glucose Test Strip',
                '',
                '',
                '',
                'Strip',
                'Box:20',
                1000,
                700,
            ],
            [
                'Medicine',
                'Medicine | Cough & Cold',
                'Cough Syrup 100ml',
                '',
                '100ml',
                '',
                'Bottle',
                'Box:12',
                3500,
                3000,
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $this->styleSheet($sheet);
                $this->applyDropdowns($sheet);
                $this->applyNumericValidation($sheet);
                $this->addHelpfulComments($sheet);
            },
        ];
    }

    private function styleSheet(Worksheet $sheet): void
    {
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');

        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '0F172A'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'EFF6FF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A2:J2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => '475569'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'F8FAFC'],
            ],
            'alignment' => [
                'wrapText' => true,
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);

        $sheet->getStyle('A3:J3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '2563EB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A3:J200')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ]);

        $sheet->getStyle('A4:J200')->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        $sheet->getStyle('I4:J200')->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->freezePane('A4');
        $sheet->setAutoFilter('A3:J200');

        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(44);
        $sheet->getRowDimension(3)->setRowHeight(24);

        $widths = [
            'A' => 22,
            'B' => 30,
            'C' => 34,
            'D' => 24,
            'E' => 16,
            'F' => 20,
            'G' => 18,
            'H' => 34,
            'I' => 18,
            'J' => 20,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    private function applyDropdowns(Worksheet $sheet): void
    {
        $lastProductTypeRow = max(2, count($this->productTypes) + 1);
        $lastCategoryRow = max(2, count($this->categories) + 1);
        $lastUnitRow = max(2, count($this->units) + 1);

        for ($row = 4; $row <= 200; $row++) {
            $this->setListValidation(
                sheet: $sheet,
                cell: "A{$row}",
                formula: "'_lists'!\$A\$2:\$A\${$lastProductTypeRow}",
                promptTitle: 'Product Type',
                prompt: 'Select or type product type. If new, it will be created automatically.'
            );

            $this->setListValidation(
                sheet: $sheet,
                cell: "B{$row}",
                formula: "'_lists'!\$B\$2:\$B\${$lastCategoryRow}",
                promptTitle: 'Category',
                prompt: 'Choose category in format: Product Type | Category.'
            );

            $this->setListValidation(
                sheet: $sheet,
                cell: "G{$row}",
                formula: "'_lists'!\$C\$2:\$C\${$lastUnitRow}",
                promptTitle: 'Base Unit',
                prompt: 'Select the smallest/main unit used for stock and price calculation.'
            );
        }
    }

    private function applyNumericValidation(Worksheet $sheet): void
    {
        for ($row = 4; $row <= 200; $row++) {
            foreach (["I{$row}", "J{$row}"] as $cell) {
                $validation = $sheet->getCell($cell)->getDataValidation();
                $validation->setType(DataValidation::TYPE_DECIMAL);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(false);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setErrorTitle('Invalid price');
                $validation->setError('Price must be a number greater than or equal to 0.');
                $validation->setPromptTitle('Base price');
                $validation->setPrompt('Enter base unit price only. Other package prices are calculated automatically.');
                $validation->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL);
                $validation->setFormula1('0');
            }
        }
    }

    private function setListValidation(
        Worksheet $sheet,
        string $cell,
        string $formula,
        string $promptTitle,
        string $prompt
    ): void {
        $validation = $sheet->getCell($cell)->getDataValidation();

        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setFormula1($formula);
        $validation->setPromptTitle($promptTitle);
        $validation->setPrompt($prompt);
        $validation->setErrorTitle('Invalid selection');
        $validation->setError('Select from the list or type a valid new value.');
    }

    private function addHelpfulComments(Worksheet $sheet): void
    {
        $comments = [
            'A3' => 'Product type. Example: Medicine, Cosmetic, Medical Device. If not found, system creates it.',
            'B3' => 'Category format should be: Product Type | Category. Example: Medicine | Pain Relief.',
            'C3' => 'Product name is required. Product code and barcode will be generated automatically.',
            'G3' => 'Base unit is the smallest/main unit. Example: Tablet, Strip, Bottle, Piece.',
            'H3' => 'Package units are extra package conversions. Example: Strip:10, Box:100. Do not include base unit here; system adds base unit automatically.',
            'I3' => 'Retail price for base unit only. Example: Tablet retail price = 1000.',
            'J3' => 'Wholesale price for base unit only. Example: Tablet wholesale price = 700.',
        ];

        foreach ($comments as $cell => $text) {
            $richText = new RichText();
            $richText->createText($text);

            $comment = new Comment();
            $comment->setText($richText);
            $comment->setWidth('320pt');
            $comment->setHeight('120pt');

            $sheet->getComment($cell)->setText($richText);
            $sheet->getComment($cell)->setWidth('320pt');
            $sheet->getComment($cell)->setHeight('120pt');
        }
    }
}