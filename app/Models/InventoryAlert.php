<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'branch_id',
        'inventory_id',
        'product_id',
        'alert_no',
        'alert_type',
        'severity',
        'title',
        'message',
        'available_quantity_base_units',
        'threshold_quantity_base_units',
        'expiry_date',
        'days_to_expiry',
        'status',
        'notified_at',
        'read_at',
        'resolved_at',
        'read_by',
        'resolved_by',
        'channels_sent',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'available_quantity_base_units' => 'integer',
            'threshold_quantity_base_units' => 'integer',
            'expiry_date' => 'date',
            'days_to_expiry' => 'integer',
            'notified_at' => 'datetime',
            'read_at' => 'datetime',
            'resolved_at' => 'datetime',
            'channels_sent' => 'array',
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

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function displayType(): string
    {
        return str($this->alert_type)->replace('_', ' ')->title()->toString();
    }
}