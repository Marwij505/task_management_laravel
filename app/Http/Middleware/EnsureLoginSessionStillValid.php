<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class EnsureLoginSessionStillValid
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $expiresAt = (int) $request->session()->get('login_expires_at', 0);

        if ($expiresAt === 0 || now()->timestamp > $expiresAt) {
            return $this->forceLogout($request);
        }

        return $next($request);
    }

    private function forceLogout(Request $request): Response
    {
        $guard = Auth::guard();

        $recallerName = method_exists($guard, 'getRecallerName')
            ? $guard->getRecallerName()
            : null;

        if ($request->user()) {
            $request->user()->forceFill([
                'remember_token' => null,
            ])->save();
        }

        Auth::logout();

        $request->session()->forget([
            'login_remembered',
            'login_expires_at',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($recallerName) {
            Cookie::queue(Cookie::forget($recallerName));
        }

        if ($request->expectsJson() || $request->is('flowlist-api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please login again.',
            ], 401);
        }

        return redirect()->route('login');
    }
}