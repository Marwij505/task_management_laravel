// ===============================
// FLOWLIST GLOBAL THEME MANAGER
// Light / Dark / System
// ===============================

(function () {
    const STORAGE_KEY = 'flowlist_theme';

    function normalizeTheme(theme) {
        if (theme === 'Dark') return 'Dark';
        if (theme === 'System') return 'System';
        return 'Light';
    }

    function getResolvedTheme(theme) {
        const normalizedTheme = normalizeTheme(theme);

        if (normalizedTheme === 'System') {
            const prefersDark = window.matchMedia &&
                window.matchMedia('(prefers-color-scheme: dark)').matches;

            return prefersDark ? 'dark' : 'light';
        }

        return normalizedTheme === 'Dark' ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        const normalizedTheme = normalizeTheme(theme);
        const resolvedTheme = getResolvedTheme(normalizedTheme);

        document.documentElement.setAttribute('data-theme-setting', normalizedTheme);
        document.documentElement.setAttribute('data-theme', resolvedTheme);

        localStorage.setItem(STORAGE_KEY, normalizedTheme);
    }

    function initTheme() {
        const savedTheme = localStorage.getItem(STORAGE_KEY) || 'Light';
        applyTheme(savedTheme);
    }

    // Saat user pilih System, theme ikut berubah kalau OS berubah
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
            const currentSetting = localStorage.getItem(STORAGE_KEY) || 'Light';

            if (currentSetting === 'System') {
                applyTheme('System');
            }
        });
    }

    window.FlowlistTheme = {
        applyTheme,
        initTheme
    };

    initTheme();
})();