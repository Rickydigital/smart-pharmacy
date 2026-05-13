<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        /** @var User $user */
        $user = Auth::user();

        if ($request->boolean('revoke_existing_tokens')) {
            $user->tokens()->delete();
        }

        $tokenName = $request->string('device_name')->toString() ?: 'mobile-app';
        $token = $user->createToken($tokenName);

        return $this->success([
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
            'user' => $this->userPayload($user),
        ], 'Login successful.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success($this->userPayload($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->tokens()
        ->where('id', $request->user()?->currentAccessToken()?->id)
        ->delete();

        return $this->success([], 'Logged out successfully.');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()?->tokens()->delete();

        return $this->success([], 'Logged out from all devices successfully.');
    }

    public function forgotPassword(Request $request): JsonResponse
{
    $validated = $request->validate([
        'email' => ['required', 'email'],
    ]);

    $email = strtolower(trim($validated['email']));
    $key = 'password-reset-otp:' . $email . ':' . $request->ip();

    if (RateLimiter::tooManyAttempts($key, 3)) {
        throw ValidationException::withMessages([
            'email' => 'Too many reset attempts. Please try again later.',
        ]);
    }

    RateLimiter::hit($key, 300);

    $user = User::query()
        ->where('email', $email)
        ->first();

    if (! $user) {
        throw ValidationException::withMessages([
            'email' => 'No account found with this email address.',
        ]);
    }

    PasswordResetOtp::query()
        ->where('email', $email)
        ->delete();

    $otp = (string) random_int(100000, 999999);

    PasswordResetOtp::query()->create([
        'email' => $email,
        'otp_hash' => Hash::make($otp),
        'expires_at' => now()->addMinutes(10),
        'attempts' => 0,
    ]);

    $user->notify(new PasswordResetOtpNotification($otp));

    return $this->success([], 'Password reset code sent to your email.');
}

    public function verifyPasswordOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = strtolower(trim($validated['email']));

        $otpRecord = PasswordResetOtp::query()
            ->where('email', $email)
            ->latest('id')
            ->first();

        if (! $otpRecord || $otpRecord->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid or expired reset code.',
            ]);
        }

        if ((int) $otpRecord->attempts >= 5) {
            PasswordResetOtp::query()
            ->whereKey($otpRecord->id)
            ->delete();

            throw ValidationException::withMessages([
                'otp' => 'Too many wrong attempts. Request a new code.',
            ]);
        }

        if (! Hash::check($validated['otp'], $otpRecord->otp_hash)) {
            $otpRecord->attempts = ((int) $otpRecord->attempts) + 1;
            $otpRecord->save();

            throw ValidationException::withMessages([
                'otp' => 'Invalid reset code.',
            ]);
        }

        $otpRecord->verified_at = now();
        $otpRecord->save();

        return $this->success([], 'Reset code verified successfully.');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = strtolower(trim($validated['email']));

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'User not found.',
            ]);
        }

        $otpRecord = PasswordResetOtp::query()
            ->where('email', $email)
            ->latest('id')
            ->first();

        if (! $otpRecord || $otpRecord->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid or expired reset code.',
            ]);
        }

        if ((int) $otpRecord->attempts >= 5) {
            PasswordResetOtp::query()
                ->whereKey($otpRecord->id)
                ->delete();

            throw ValidationException::withMessages([
                'otp' => 'Too many wrong attempts. Request a new code.',
            ]);
        }

        if (! Hash::check($validated['otp'], $otpRecord->otp_hash)) {
            $otpRecord->attempts = ((int) $otpRecord->attempts) + 1;
            $otpRecord->save();

            throw ValidationException::withMessages([
                'otp' => 'Invalid reset code.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        $user->tokens()->delete();

        PasswordResetOtp::query()
            ->whereKey($otpRecord->id)
            ->delete();

        event(new PasswordReset($user));

        return $this->success([], 'Password reset successfully. Please login again.');
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return $this->success([], 'Password updated successfully.');
    }

    private function userPayload(User $user): array
    {
        $user->loadMissing(['pharmacy', 'branch', 'roles.permissions']);

        return [
            'id' => $user->id,
            'pharmacy_id' => $user->pharmacy_id,
            'branch_id' => $user->branch_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'last_login_at' => $user->last_login_at,
            'pharmacy' => $user->pharmacy,
            'branch' => $user->branch,
            'roles' => $user->roles->pluck('name')->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
        ];
    }
}