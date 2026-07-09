<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * This middleware protects admin-only routes.
     *
     * Flow:
     * - Guest users are handled by Laravel auth middleware before this middleware runs.
     * - Logged-in users with role "admin" can continue.
     * - Logged-in users with role "user" will be blocked.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        /*
         * Extra safety check.
         * This condition should rarely happen because the route already uses auth middleware.
         */
        if (! $user) {
            return redirect()->route('login');
        }

        /*
         * Only admin users may access admin routes.
         * The isAdmin() method comes from app/Models/User.php.
         */
        if (! $user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin only.',
                ], 403);
            }

            abort(403, 'Access denied. Admin only.');
        }

        return $next($request);
    }
}