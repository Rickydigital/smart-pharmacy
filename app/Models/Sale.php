<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'branch_id',
        'sale_no',
        'mobile_reference',
        'device_name',
        'app_version',
        'customer_name',
        'customer_phone',
        'sale_type',
        'subtotal_amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'balance_amount',
        'payment_method',
        'payment_status',
        'status',
        'notes',
        'created_by',
        'sold_at',
        'synced_at',
        'offline_created_at',
        'returned_amount',
        'returned_base_units',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'sold_at' => 'datetime',
            'synced_at' => 'datetime',
            'offline_created_at' => 'datetime',
            'returned_amount' => 'decimal:2',
            'returned_base_units' => 'integer',
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

    public function movements(): MorphMany
    {
        return $this->morphMany(InventoryMovement::class, 'source');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function isPartiallyReturned(): bool
    {
        return $this->status === 'partially_returned';
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isRetail(): bool
    {
        return $this->sale_type === 'retail';
    }

    public function isWholesale(): bool
    {
        return $this->sale_type === 'wholesale';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function displayCustomer(): string
    {
        return $this->customer_name ?: 'Walk-in Customer';
    }
}
