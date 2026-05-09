<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductUnit extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'product_id',
        'unit_id',
        'quantity_in_base_units',
        'can_purchase',
        'can_sell_retail',
        'can_sell_wholesale',
        'is_base',
        'is_default_sale_unit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'quantity_in_base_units' => 'integer',
            'can_purchase' => 'boolean',
            'can_sell_retail' => 'boolean',
            'can_sell_wholesale' => 'boolean',
            'is_base' => 'boolean',
            'is_default_sale_unit' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }
}