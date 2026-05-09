<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmartControlState extends Model
{
    protected $fillable = [
        'project',
        'license_key',
        'instance_id',
        'allowed',
        'force_logout',
        'tenant_status',
        'license_status',
        'subscription_status',
        'message',
        'features',
        'payload',
        'last_checked_at',
        'valid_until',
        'next_check_after',
        'last_success_at',
        'last_failed_at',
    ];

    protected function casts(): array
    {
        return [
            'allowed' => 'boolean',
            'force_logout' => 'boolean',
            'features' => 'array',
            'payload' => 'array',
            'last_checked_at' => 'datetime',
            'valid_until' => 'datetime',
            'next_check_after' => 'datetime',
            'last_success_at' => 'datetime',
            'last_failed_at' => 'datetime',
        ];
    }

    public function isCurrentlyAllowed(): bool
    {
        if (! $this->allowed) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        return true;
    }

    public function shouldForceLogout(): bool
    {
        return $this->force_logout || ! $this->isCurrentlyAllowed();
    }
}