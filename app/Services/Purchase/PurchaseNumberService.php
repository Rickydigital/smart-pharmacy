<?php

namespace App\Services\Purchase;

use App\Models\Purchase;

class PurchaseNumberService
{
    public function generate(): string
    {
        $prefix = 'PUR-' . now()->format('Ymd');

        $lastPurchase = Purchase::query()
            ->where('purchase_no', 'like', $prefix . '-%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastPurchase) {
            $parts = explode('-', $lastPurchase->purchase_no);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        do {
            $purchaseNo = $prefix . '-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (Purchase::query()->where('purchase_no', $purchaseNo)->exists());

        return $purchaseNo;
    }
}