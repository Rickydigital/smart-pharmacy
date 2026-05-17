<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductIntelligenceSnapshot extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'branch_id',
        'product_id',
        'period_type',
        'period_start',
        'period_end',
        'sales_count',
        'sales_base_units',
        'revenue',
        'gross_profit',
        'profit_margin',
        'public_searches',
        'missed_searches',
        'current_stock_base_units',
        'avg_daily_sales_base_units',
        'stock_cover_days',
        'near_expiry_base_units',
        'expired_base_units',
        'sales_score',
        'search_score',
        'profit_score',
        'stock_risk_score',
        'expiry_risk_score',
        'priority_score',
        'recommendation_type',
        'recommendation_text',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'revenue' => 'decimal:2',
            'gross_profit' => 'decimal:2',
            'profit_margin' => 'decimal:2',
            'avg_daily_sales_base_units' => 'decimal:2',
            'stock_cover_days' => 'decimal:2',
            'meta' => 'array',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}