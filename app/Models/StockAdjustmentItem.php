<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'branch_id',
        'stock_adjustment_id',
        'inventory_id',
        'product_id',
        'direction',
        'quantity_base_units',
        'unit_cost_base',
        'total_cost',
        'balance_before_base_units',
        'balance_after_base_units',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity_base_units' => 'integer',
            'unit_cost_base' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'balance_before_base_units' => 'integer',
            'balance_after_base_units' => 'integer',
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

    public function stockAdjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isIn(): bool
    {
        return $this->direction === 'in';
    }

    public function isOut(): bool
    {
        return $this->direction === 'out';
    }
}