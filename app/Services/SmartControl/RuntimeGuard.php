<?php

namespace App\Services\SmartControl;

use App\Models\SmartControlState;
use Illuminate\Support\Facades\Auth;

class RuntimeGuard
{
    public function currentState(): ?SmartControlState
    {
        return SmartControlState::query()
            ->where('project', config('smartcontrol.project'))
            ->where('license_key', config('smartcontrol.license_key'))
            ->where('instance_id', config('smartcontrol.instance_id'))
            ->latest()
            ->first();
    }

    public function allowed(): bool
    {
        if (! config('smartcontrol.enabled')) {
            return true;
        }

        $state = $this->currentState();

        if (! $state) {
            return false;
        }

        return $state->isCurrentlyAllowed();
    }

    public function shouldCheckNow(): bool
    {
        if (! config('smartcontrol.enabled')) {
            return false;
        }

        $state = $this->currentState();

        if (! $state) {
            return true;
        }

        if (! $state->next_check_after) {
            return true;
        }

        return $state->next_check_after->isPast();
    }

    public function message(): string
    {
        return $this->currentState()?->message
            ?: 'This system is not activated. Please contact support.';
    }

    public function forceLogoutIfBlocked(): void
    {
        if (! config('smartcontrol.enabled')) {
            return;
        }

        $state = $this->currentState();

        if (! $state || $state->shouldForceLogout()) {
            Auth::guard('web')->logout();

            request()->session()?->invalidate();
            request()->session()?->regenerateToken();
        }
    }

    public function featureEnabled(string $feature): bool
    {
        if (! config('smartcontrol.enabled')) {
            return true;
        }

        $features = $this->currentState()?->features;

        if (! is_array($features)) {
            return false;
        }

        return (bool) ($features[$feature] ?? false);
    }
}