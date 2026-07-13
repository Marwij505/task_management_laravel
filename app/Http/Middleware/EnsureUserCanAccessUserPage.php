<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessUserPage
{
    /**
     * Mengizinkan regular user dan admin
     * mengakses halaman antarmuka user.
     *
     * Regular user tetap menggunakan halaman user seperti biasa.
     * Admin dapat membuka halaman user tanpa mengubah role akun.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        /*
         * Tolak akses jika user belum login.
         */
        if (! $user) {
            return redirect()->route('login');
        }

        /*
         * Hanya role user dan admin yang boleh
         * membuka halaman antarmuka user.
         */
        if (! in_array($user->role, [
            User::ROLE_USER,
            User::ROLE_ADMIN,
        ], true)) {
            abort(403, 'You do not have permission to access the user page.');
        }

        return $next($request);
    }
}