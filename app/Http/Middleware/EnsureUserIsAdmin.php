<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        /*
         * Route admin hanya boleh dibuka oleh user yang sudah login
         * dan memiliki role admin.
         */
        if (! $request->user() || ! $request->user()->isAdmin()) {
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