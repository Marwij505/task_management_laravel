<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', Rule::in(['0', '1'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $login = trim((string) $request->input('login'));
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $remember = $request->boolean('remember');
        $guard = Auth::guard();

        if ($remember && method_exists($guard, 'setRememberDuration')) {
            // Remember me aktif selama 30 hari.
            $guard->setRememberDuration(60 * 24 * 30);
        }

        if (! $guard->attempt([
            $field => $login,
            'password' => (string) $request->input('password'),
        ], $remember)) {
            return response()->json([
                'success' => false,
                'message' => 'The email / username or password you entered is wrong. Please try again.',
            ], 401);
        }

        $request->session()->regenerate();

        /*
        * Session marker buatan kita.
        * remember = true  -> login berlaku 30 hari
        * remember = false -> login hanya ikut SESSION_LIFETIME
        */
        $request->session()->put('login_remembered', $remember);
        $request->session()->put(
            'login_expires_at',
            $remember
                ? now()->addDays(30)->timestamp
                : now()->addMinutes((int) config('session.lifetime', 120))->timestamp
        );

        if (! $remember) {
            Auth::user()?->forceFill([
                'remember_token' => null,
            ])->save();

            if (method_exists($guard, 'getRecallerName')) {
                Cookie::queue(Cookie::forget($guard->getRecallerName()));
            }
        }

        $username = Auth::user()?->username ?: Auth::user()?->name ?: 'User';

        return response()->json([
            'success' => true,
            'message' => 'Enjoy This Website, '.$username.'!',
            'redirect' => route('dashboard'),
        ]);
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:4', 'max:255'],
            'confirm_password' => ['required', 'same:password'],
            'terms' => ['accepted'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $username = trim((string) $request->input('username'));

        User::create([
            'name' => $username,
            'username' => $username,
            'full_name' => $username,
            'email' => strtolower(trim((string) $request->input('email'))),
            'password' => Hash::make((string) $request->input('password')),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your account has been created successfully.',
            'redirect' => route('login'),
        ], 201);
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Alur ini dipertahankan dari aplikasi manual untuk demonstrasi lokal.
     * Untuk production, gunakan password broker + token melalui email.
     */
    public function resetPasswordDirect(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:150', 'exists:users,email'],
            'password' => ['required', 'string', 'min:4', 'max:255'],
            'confirm_password' => ['required', 'same:password'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('email', strtolower(trim((string) $request->input('email'))))->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Email address was not found.',
            ], 404);
        }

        $user->forceFill([
            'password' => Hash::make((string) $request->input('password')),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully. Please login with your new password.',
            'redirect' => route('login'),
        ]);
    }

    public function logout(Request $request): RedirectResponse
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

        $response = redirect()->route('login');

        if ($recallerName) {
            $response->headers->clearCookie($recallerName);
        }

        return $response;
    }
}
