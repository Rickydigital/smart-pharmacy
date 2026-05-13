<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Street extends Model
{
    protected $fillable = [
        'ward_id',
        'name',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function getFullNameAttribute(): string
    {
        $this->loadMissing('ward.district.region.country');

        return collect([
            $this->ward?->district?->region?->country?->name,
            $this->ward?->district?->region?->name,
            $this->ward?->district?->name,
            $this->ward?->name,
            $this->name,
        ])->filter()->implode(', ');
    }
}