<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'name',
        'code',
        'phone',
        'whatsapp',
        'address',
        'street_id',
        'latitude',
        'longitude',
        'opens_at',
        'closes_at',
        'is_24_hours',
        'is_main',
        'is_active',
        'last_location_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'is_active' => 'boolean',
            'is_24_hours' => 'boolean',
            'opens_at' => 'datetime:H:i',
            'closes_at' => 'datetime:H:i',
            'last_location_synced_at' => 'datetime',
        ];
    }

    public function street(): BelongsTo
    {
        return $this->belongsTo(Street::class);
    }

    public function getFullLocationAttribute(): string
    {
        $this->loadMissing('street.ward.district.region.country');

        return collect([
            $this->street?->ward?->district?->region?->country?->name,
            $this->street?->ward?->district?->region?->name,
            $this->street?->ward?->district?->name,
            $this->street?->ward?->name,
            $this->street?->name,
        ])->filter()->implode(', ');
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
    public function activeUsers(): HasMany
    {
        return $this->hasMany(User::class)->where('status', 'active');
    }

    public function isMain(): bool
    {
        return $this->is_main === true;
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function outgoingStockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'source_branch_id');
    }

    public function incomingStockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'destination_branch_id');
    }

    public function dailyClosings(): HasMany
    {
        return $this->hasMany(DailyClosing::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
