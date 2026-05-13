<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'source_branch_id',
        'destination_branch_id',
        'transfer_no',
        'mobile_reference',
        'device_name',
        'app_version',
        'transfer_date',
        'status',
        'total_items',
        'total_quantity_base_units',
        'total_cost',
        'reason',
        'notes',
        'rejection_reason',
        'created_by',
        'approved_by',
        'dispatched_by',
        'received_by',
        'approved_at',
        'dispatched_at',
        'received_at',
        'synced_at',
        'offline_created_at',
    ];

    protected function casts(): array
    {
        return [
            'transfer_date' => 'date',
            'total_items' => 'integer',
            'total_quantity_base_units' => 'integer',
            'total_cost' => 'decimal:2',
            'approved_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'received_at' => 'datetime',
            'synced_at' => 'datetime',
            'offline_created_at' => 'datetime',
        ];
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function sourceBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'source_branch_id');
    }

    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isDispatched(): bool
    {
        return $this->status === 'dispatched';
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
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
