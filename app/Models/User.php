<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use LogsActivity;

    protected $fillable = [
        'pharmacy_id',
        'branch_id',
        'first_name',
        'last_name',
        'username',
        'email',
        'phone',
        'password',
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function dailyClosings(): HasMany
    {
        return $this->hasMany(DailyClosing::class, 'cashier_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function displayName(): string
    {
        return $this->full_name ?: ($this->username ?: $this->email);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function belongsToActivePharmacy(): bool
    {
        return $this->pharmacy?->isActive() === true;
    }

    public function belongsToActiveBranch(): bool
    {
        if (! $this->branch_id) {
            return true;
        }

        return $this->branch?->isActive() === true;
    }

    public function canLogin(): bool
    {
        return $this->isActive()
            && $this->belongsToActivePharmacy()
            && $this->belongsToActiveBranch();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('users')
            ->logOnly([
                'pharmacy_id',
                'branch_id',
                'first_name',
                'last_name',
                'username',
                'email',
                'phone',
                'status',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "User account {$eventName}";
    }
}