<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'branch_id',
        'sale_id',
        'return_no',
        'mobile_reference',
        'device_name',
        'app_version',
        'return_date',
        'subtotal_amount',
        'refund_amount',
        'refund_method',
        'status',
        'return_type',
        'reason',
        'notes',
        'rejection_reason',
        'created_by',
        'approved_by',
        'approved_at',
        'synced_at',
        'offline_created_at',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'subtotal_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'synced_at' => 'datetime',
            'offline_created_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
