<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsRegularUser
{
    /**
     * This middleware protects regular user routes.
     *
     * Admin users should stay in the admin area.
     * This prevents admin accounts from accidentally using the user dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        /*
         * Extra safety check.
         * Laravel auth middleware should already handle guest users.
         */
        if (! $user) {
            return redirect()->route('login');
        }

        /*
         * If an admin opens a regular user page,
         * redirect them back to the admin dashboard.
         */
        if ($user->isAdmin()) {
            if ($request->expectsJson() || $request->is('flowlist-api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin accounts cannot access regular user area.',
                    'redirect' => route('admin.dashboard'),
                ], 403);
            }

            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}