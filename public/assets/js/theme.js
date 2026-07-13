/*
|--------------------------------------------------------------------------
| FLOWLIST GLOBAL THEME MANAGER
|--------------------------------------------------------------------------
| Mengatur tema Light, Dark, dan System.
|
| Sumber tema:
| 1. Tema akun yang dikirim setelah login.
| 2. Cache browser untuk perpindahan halaman.
| 3. Light sebagai fallback.
|
| Tema login selalu diproses lebih dahulu agar tema akun sebelumnya
| tidak digunakan pada akun yang baru login.
|--------------------------------------------------------------------------
*/

(function () {
    'use strict';

    /*
    |--------------------------------------------------------------------------
    | Storage Keys
    |--------------------------------------------------------------------------
    */

    const THEME_STORAGE_KEY = 'flowlist_theme';

    const LOGIN_THEME_STORAGE_KEY =
        'flowlist.login-theme-bootstrap';

    /*
    |--------------------------------------------------------------------------
    | Theme Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Memastikan tema hanya memiliki salah satu nilai:
     * Light, Dark, atau System.
     */
    function normalizeTheme(theme) {
        const normalizedValue = String(theme || '')
            .trim()
            .toLowerCase();

        if (normalizedValue === 'dark') {
            return 'Dark';
        }

        if (normalizedValue === 'system') {
            return 'System';
        }

        return 'Light';
    }

    /**
     * Mengubah setting tema menjadi tema CSS aktual.
     *
     * Light  menjadi light.
     * Dark   menjadi dark.
     * System mengikuti perangkat.
     */
    function resolveTheme(themeSetting) {
        const normalizedTheme = normalizeTheme(themeSetting);

        if (normalizedTheme === 'System') {
            const prefersDark = window.matchMedia
                && window.matchMedia(
                    '(prefers-color-scheme: dark)'
                ).matches;

            return prefersDark ? 'dark' : 'light';
        }

        return normalizedTheme === 'Dark'
            ? 'dark'
            : 'light';
    }

    /*
    |--------------------------------------------------------------------------
    | Safe Storage Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Membaca localStorage secara aman.
     */
    function readThemeCache() {
        try {
            return normalizeTheme(
                localStorage.getItem(THEME_STORAGE_KEY)
            );
        } catch (error) {
            return 'Light';
        }
    }

    /**
     * Menyimpan tema terbaru.
     *
     * Cache ini bukan sumber utama tema akun.
     * Cache hanya menjaga tema tetap sama saat pindah halaman.
     */
    function saveThemeCache(themeSetting) {
        try {
            localStorage.setItem(
                THEME_STORAGE_KEY,
                normalizeTheme(themeSetting)
            );
        } catch (error) {
            console.warn(
                'Unable to save Flowlist theme cache.',
                error
            );
        }
    }

    /**
     * Menghapus payload login theme.
     */
    function clearLoginThemeBootstrap() {
        try {
            sessionStorage.removeItem(
                LOGIN_THEME_STORAGE_KEY
            );
        } catch (error) {
            console.warn(
                'Unable to clear login theme bootstrap.',
                error
            );
        }
    }

    /**
     * Menghapus cache tema ketika logout.
     *
     * Halaman login berikutnya tidak lagi menggunakan
     * tema akun yang baru saja logout.
     */
    function clearThemeCache() {
        try {
            localStorage.removeItem(THEME_STORAGE_KEY);
        } catch (error) {
            console.warn(
                'Unable to clear Flowlist theme cache.',
                error
            );
        }

        clearLoginThemeBootstrap();
    }

    /*
    |--------------------------------------------------------------------------
    | Login Theme Bootstrap
    |--------------------------------------------------------------------------
    */

    /**
     * Membaca tema akun yang baru login.
     *
     * Payload ini dibuat oleh login.js.
     */
    function readLoginThemeBootstrap() {
        try {
            const rawPayload = sessionStorage.getItem(
                LOGIN_THEME_STORAGE_KEY
            );

            if (! rawPayload) {
                return null;
            }

            const payload = JSON.parse(rawPayload);

            const createdAt = Number(
                payload.createdAt || 0
            );

            const payloadAge = Date.now() - createdAt;

            /*
             * Payload login maksimal berlaku selama 30 detik.
             */
            if (
                ! createdAt
                || payloadAge < 0
                || payloadAge > 30000
            ) {
                clearLoginThemeBootstrap();

                return null;
            }

            return normalizeTheme(
                payload.themeSetting
            );
        } catch (error) {
            clearLoginThemeBootstrap();

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Theme
    |--------------------------------------------------------------------------
    */

    /**
     * Menerapkan tema ke elemen HTML.
     *
     * @param {string} themeSetting
     * @param {boolean} persist
     */
    function applyTheme(themeSetting, persist = true) {
        const normalizedTheme =
            normalizeTheme(themeSetting);

        const resolvedTheme =
            resolveTheme(normalizedTheme);

        const rootElement =
            document.documentElement;

        rootElement.setAttribute(
            'data-theme-setting',
            normalizedTheme
        );

        rootElement.setAttribute(
            'data-theme',
            resolvedTheme
        );

        /*
         * Simpan cache agar tema tidak berubah saat
         * user berpindah melalui sidebar.
         */
        if (persist) {
            saveThemeCache(normalizedTheme);
        }

        /*
         * Event ini dapat digunakan oleh preferences.js
         * atau komponen lain apabila perlu merespons tema.
         */
        window.dispatchEvent(
            new CustomEvent('flowlist:theme-changed', {
                detail: {
                    setting: normalizedTheme,
                    resolved: resolvedTheme,
                },
            })
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Initialize Theme
    |--------------------------------------------------------------------------
    */

    /**
     * Urutan tema:
     *
     * 1. Tema akun yang baru login.
     * 2. Cache tema halaman sebelumnya dalam sesi akun.
     * 3. Light.
     */
    function initTheme() {
        const loginTheme =
            readLoginThemeBootstrap();

        /*
         * Tema login harus diprioritaskan.
         *
         * Ini sekaligus menimpa cache akun sebelumnya.
         */
        if (loginTheme !== null) {
            applyTheme(loginTheme, true);

            clearLoginThemeBootstrap();

            return;
        }

        applyTheme(
            readThemeCache(),
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | System Theme Listener
    |--------------------------------------------------------------------------
    */

    const systemThemeQuery = window.matchMedia
        ? window.matchMedia(
            '(prefers-color-scheme: dark)'
        )
        : null;

    /**
     * Saat tema perangkat berubah, Flowlist hanya ikut berubah
     * jika setting akun adalah System.
     */
    function handleSystemThemeChange() {
        const currentSetting =
            document.documentElement.getAttribute(
                'data-theme-setting'
            );

        if (currentSetting === 'System') {
            applyTheme('System', true);
        }
    }

    if (systemThemeQuery) {
        if (
            typeof systemThemeQuery.addEventListener
            === 'function'
        ) {
            systemThemeQuery.addEventListener(
                'change',
                handleSystemThemeChange
            );
        } else if (
            typeof systemThemeQuery.addListener
            === 'function'
        ) {
            /*
             * Fallback browser lama.
             */
            systemThemeQuery.addListener(
                handleSystemThemeChange
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Logout Cache Cleanup
    |--------------------------------------------------------------------------
    */

    /**
     * Hapus cache sebelum form logout dikirim.
     */
    document.addEventListener(
        'submit',
        function (event) {
            const form = event.target;

            if (! (form instanceof HTMLFormElement)) {
                return;
            }

            const formAction = String(
                form.getAttribute('action') || ''
            );

            const isLogoutForm =
                form.id === 'logoutForm'
                || formAction.endsWith('/logout');

            if (isLogoutForm) {
                clearThemeCache();
            }
        },
        true
    );

    /*
    |--------------------------------------------------------------------------
    | Public API
    |--------------------------------------------------------------------------
    */

    window.FlowlistTheme = {
        applyTheme: applyTheme,
        initTheme: initTheme,
        normalizeTheme: normalizeTheme,
        resolveTheme: resolveTheme,
        clearCache: clearThemeCache,
    };

    /*
     * Jalankan hanya satu kali.
     *
     * Tidak ada lagi dua theme manager yang saling menimpa.
     */
    initTheme();
})();