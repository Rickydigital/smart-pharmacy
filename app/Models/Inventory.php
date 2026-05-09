<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'branch_id',
        'product_id',
        'purchase_id',
        'purchase_item_id',
        'batch_no',
        'expiry_date',
        'received_quantity_base_units',
        'available_quantity_base_units',
        'unit_cost_base',
        'total_cost',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'unit_cost_base' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'is_active' => 'boolean',
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

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available'
            && $this->available_quantity_base_units > 0
            && $this->is_active === true;
    }

    public function isDepleted(): bool
    {
        return $this->status === 'depleted'
            || $this->available_quantity_base_units <= 0;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null
            && $this->expiry_date->isPast();
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function markDepleted(): void
    {
        $this->update([
            'status' => 'depleted',
            'available_quantity_base_units' => 0,
        ]);
    }

    public function stockAdjustmentItems(): HasMany
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function outgoingTransferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'source_inventory_id');
    }

    public function incomingTransferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'destination_inventory_id');
    }
    public function refreshStatus(): void
    {
        if ($this->available_quantity_base_units <= 0) {
            $this->update(['status' => 'depleted']);
            return;
        }

        if ($this->isExpired()) {
                $this->update(['status' => 'expired']);
                return;
            }

            if ($this->status !== 'blocked') {
                $this->update(['status' => 'available']);
            }
        }
    public function isLowStock(int $threshold = 10): bool
    {
        return $this->available_quantity_base_units <= $threshold;
    }
}