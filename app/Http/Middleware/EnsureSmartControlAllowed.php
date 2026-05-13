<?php

namespace App\Http\Middleware;

use App\Services\SmartControl\RuntimeGuard;
use App\Services\SmartControl\SmartControlClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSmartControlAllowed
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('smartcontrol.enabled')) {
            return $next($request);
        }

        $guard = app(RuntimeGuard::class);

        if ($guard->shouldCheckNow()) {
            app(SmartControlClient::class)->statusCheck();
        }

        if (! $guard->allowed()) {
            if ($request->expectsJson()) {
                $request->user()?->currentAccessToken()?->delete();

                Auth::guard('web')->logout();

                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                return response()->json([
                    'success' => false,
                    'message' => $guard->message(),
                ], 423);
            }

            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'login' => $guard->message(),
                ]);
        }

        return $next($request);
    }
}
