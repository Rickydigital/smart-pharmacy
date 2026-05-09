<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'stock_transfer_id',
        'source_branch_id',
        'destination_branch_id',
        'source_inventory_id',
        'destination_inventory_id',
        'product_id',
        'batch_no',
        'expiry_date',
        'product_unit_id',
        'quantity',
        'quantity_in_base_units',
        'quantity_base_units',
        'unit_cost_base',
        'total_cost',
        'source_balance_before_base_units',
        'source_balance_after_base_units',
        'destination_balance_before_base_units',
        'destination_balance_after_base_units',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'quantity_base_units' => 'integer',
            'quantity' => 'decimal:2',
            'quantity_in_base_units' => 'integer',
            'unit_cost_base' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'source_balance_before_base_units' => 'integer',
            'source_balance_after_base_units' => 'integer',
            'destination_balance_before_base_units' => 'integer',
            'destination_balance_after_base_units' => 'integer',
        ];
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function sourceBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'source_branch_id');
    }

    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    public function sourceInventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'source_inventory_id');
    }

    public function destinationInventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'destination_inventory_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}