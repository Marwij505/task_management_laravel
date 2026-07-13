@php
    /*
    |--------------------------------------------------------------------------
    | Flowlist Footer Data
    |--------------------------------------------------------------------------
    | Menentukan:
    | 1. User yang sedang login.
    | 2. Apakah user merupakan admin.
    | 3. Dashboard tujuan sesuai role.
    | 4. Versi aplikasi.
    |--------------------------------------------------------------------------
    */

    $footerUser = auth()->user();

    $footerIsAdmin = $footerUser?->isAdmin() ?? false;

    /*
     * Admin kembali ke Admin Dashboard.
     * User biasa kembali ke User Dashboard.
     */
    $footerHomeRoute = $footerIsAdmin
        ? route('admin.dashboard')
        : route('dashboard');

    /*
     * Versi dibaca dari config/app.php.
     */
    $footerAppVersion = config('app.version', '1.0.0');
@endphp

<footer class="app-footer" aria-label="Flowlist application footer">
    <div class="app-footer__inner">

        {{-- Bagian kiri footer --}}
        <div class="app-footer__left">

            {{-- Logo footer sekaligus link kembali ke dashboard --}}
            <a
                href="{{ $footerHomeRoute }}"
                class="app-footer__brand"
                aria-label="Return to Flowlist dashboard"
            >
                <span class="app-footer__logo" aria-hidden="true">
                    <i class="fa-solid fa-list-check"></i>
                </span>

                <span class="app-footer__brand-copy">
                    <strong>Flowlist</strong>

                    <small>
                        Plan clearly. Finish confidently.
                    </small>
                </span>
            </a>

            {{-- Garis pemisah desktop --}}
            <span
                class="app-footer__divider"
                aria-hidden="true"
            ></span>

            {{-- Copyright otomatis mengikuti tahun --}}
            <p class="app-footer__copyright">
                &copy; {{ now()->year }} Flowlist.
                All rights reserved.
            </p>
        </div>

        {{-- Bagian kanan footer --}}
        <div class="app-footer__right">

            {{-- Status sesi --}}
            <span
                class="app-footer__meta app-footer__session"
                title="Your login session is active"
            >
                <span
                    class="app-footer__status-dot"
                    aria-hidden="true"
                ></span>

                Session Active
            </span>

            {{-- Jenis akun --}}
            <span
                class="app-footer__meta app-footer__account"
                title="Current account role"
            >
                <i
                    class="fa-solid {{ $footerIsAdmin ? 'fa-shield-halved' : 'fa-user' }}"
                    aria-hidden="true"
                ></i>

                {{ $footerIsAdmin ? 'Admin Account' : 'User Account' }}
            </span>

            {{-- Versi aplikasi --}}
            <span
                class="app-footer__meta app-footer__version"
                title="Flowlist application version"
            >
                <i
                    class="fa-solid fa-code-branch"
                    aria-hidden="true"
                ></i>

                Version {{ $footerAppVersion }}
            </span>
        </div>
    </div>
</footer>