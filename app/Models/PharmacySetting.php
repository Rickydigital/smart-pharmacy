<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'currency',
        'selling_mode',
        'expiry_warning_days',
        'block_expired_stock',
        'require_prescription_upload',
        'require_pharmacist_approval',
        'receipt_footer',
    ];

    protected function casts(): array
    {
        return [
            'expiry_warning_days' => 'integer',
            'block_expired_stock' => 'boolean',
            'require_prescription_upload' => 'boolean',
            'require_pharmacist_approval' => 'boolean',
        ];
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function isRetailOnly(): bool
    {
        return $this->selling_mode === 'retail_only';
    }

    public function isWholesaleOnly(): bool
    {
        return $this->selling_mode === 'wholesale_only';
    }

    public function allowsRetailAndWholesale(): bool
    {
        return $this->selling_mode === 'retail_and_wholesale';
    }

    public function blocksExpiredStock(): bool
    {
        return $this->block_expired_stock === true;
    }

    public function requiresPrescriptionUpload(): bool
    {
        return $this->require_prescription_upload === true;
    }

    public function requiresPharmacistApproval(): bool
    {
        return $this->require_pharmacist_approval === true;
    }
}