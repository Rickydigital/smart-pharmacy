<?php

namespace App\Services\Sale;

use App\Models\Sale;

class SaleNumberService
{
    public function generate(): string
    {
        $prefix = 'SAL-' . now()->format('Ymd');

        $lastSale = Sale::query()
            ->where('sale_no', 'like', $prefix . '-%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastSale) {
            $parts = explode('-', $lastSale->sale_no);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        do {
            $saleNo = $prefix . '-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (Sale::query()->where('sale_no', $saleNo)->exists());

        return $saleNo;
    }
}