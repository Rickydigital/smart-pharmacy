<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'branch_id',
        'cashier_id',
        'closing_date',

        'cash_sales_amount',
        'mobile_money_sales_amount',
        'card_sales_amount',
        'bank_sales_amount',
        'credit_sales_amount',

        'total_sales_amount',
        'total_discount_amount',

        'cash_expenses_amount',
        'other_expenses_amount',
        'total_expenses_amount',

        'expected_cash_amount',
        'counted_cash_amount',
        'difference_amount',

        'closing_result',
        'status',

        'notes',
        'rejection_reason',

        'submitted_at',
        'verified_by',
        'verified_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'closing_date' => 'date',

            'cash_sales_amount' => 'decimal:2',
            'mobile_money_sales_amount' => 'decimal:2',
            'card_sales_amount' => 'decimal:2',
            'bank_sales_amount' => 'decimal:2',
            'credit_sales_amount' => 'decimal:2',

            'total_sales_amount' => 'decimal:2',
            'total_discount_amount' => 'decimal:2',

            'cash_expenses_amount' => 'decimal:2',
            'other_expenses_amount' => 'decimal:2',
            'total_expenses_amount' => 'decimal:2',

            'expected_cash_amount' => 'decimal:2',
            'counted_cash_amount' => 'decimal:2',
            'difference_amount' => 'decimal:2',

            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function needsRecalculation(): bool
    {
        return $this->status === 'needs_recalculation';
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isBalanced(): bool
    {
        return $this->closing_result === 'balanced';
    }

    public function isShort(): bool
    {
        return $this->closing_result === 'short';
    }

    public function isOver(): bool
    {
        return $this->closing_result === 'over';
    }
}