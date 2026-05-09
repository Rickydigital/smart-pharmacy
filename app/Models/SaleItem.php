<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'branch_id',
        'sale_id',
        'product_id',
        'product_unit_id',
        'quantity',
        'quantity_in_base_units',
        'total_base_units',
        'unit_price',
        'line_discount',
        'line_tax',
        'line_total',
        'cost_per_base_unit',
        'total_cost',
        'profit_amount',
        'inventory_allocations',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'line_discount' => 'decimal:2',
            'line_tax' => 'decimal:2',
            'line_total' => 'decimal:2',
            'cost_per_base_unit' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'profit_amount' => 'decimal:2',
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

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function calculateTotalBaseUnits(): int
    {
        return (int) $this->quantity * (int) $this->quantity_in_base_units;
    }
}