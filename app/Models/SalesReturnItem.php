<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'branch_id',
        'sales_return_id',
        'sale_id',
        'sale_item_id',
        'product_id',
        'product_unit_id',
        'quantity',
        'quantity_in_base_units',
        'total_base_units',
        'unit_price',
        'refund_amount',
        'cost_per_base_unit',
        'total_cost',
        'profit_reversed',
        'condition',
        'restore_to_inventory',
        'inventory_allocations',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'cost_per_base_unit' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'profit_reversed' => 'decimal:2',
            'restore_to_inventory' => 'boolean',
            'inventory_allocations' => 'array',
        ];
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function isSellable(): bool
    {
        return $this->condition === 'sellable';
    }

    public function shouldRestoreInventory(): bool
    {
        return $this->restore_to_inventory && $this->isSellable();
    }
}