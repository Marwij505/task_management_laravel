// ===============================
// FLOWLIST GLOBAL PREFERENCES
// Date format helper global
// ===============================

(function () {
    const DATE_FORMAT_KEY = 'flowlist_date_format';

    function normalizeDateFormat(format) {
        if (format === 'DD/MM/YYYY') return 'DD/MM/YYYY';
        if (format === 'YYYY-MM-DD') return 'YYYY-MM-DD';
        return 'MM/DD/YYYY';
    }

    function setDateFormat(format) {
        const safeFormat = normalizeDateFormat(format);
        localStorage.setItem(DATE_FORMAT_KEY, safeFormat);
    }

    function getDateFormat() {
        return normalizeDateFormat(localStorage.getItem(DATE_FORMAT_KEY) || 'MM/DD/YYYY');
    }

    function parseDate(dateString) {
        if (!dateString) return null;

        // Supaya tanggal database YYYY-MM-DD tidak mundur 1 hari karena timezone
        const onlyDate = String(dateString).split(' ')[0];

        if (/^\d{4}-\d{2}-\d{2}$/.test(onlyDate)) {
            const parts = onlyDate.split('-');
            return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
        }

        const date = new Date(dateString);
        return isNaN(date) ? null : date;
    }

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function formatDate(dateString, fallback = '-') {
        if (!dateString) return fallback;

        const date = parseDate(dateString);
        if (!date) return dateString;

        const day = pad(date.getDate());
        const month = pad(date.getMonth() + 1);
        const year = date.getFullYear();

        const format = getDateFormat();

        if (format === 'DD/MM/YYYY') {
            return `${day}/${month}/${year}`;
        }

        if (format === 'YYYY-MM-DD') {
            return `${year}-${month}-${day}`;
        }

        return `${month}/${day}/${year}`;
    }

    window.FlowlistPreferences = {
        setDateFormat,
        getDateFormat,
        formatDate
    };
})();

/*
|--------------------------------------------------------------------------
| FLOWLIST UI ENHANCEMENTS
|--------------------------------------------------------------------------
| Digunakan oleh seluruh halaman user dan admin.
|
| Fungsi utama:
| 1. Menyimpan posisi scroll sebelum form dikirim.
| 2. Mengembalikan posisi scroll setelah halaman dimuat ulang.
| 3. Mencegah scroll mendadak ke atas akibat perubahan DOM.
| 4. Menambahkan reveal animation.
| 5. Menambahkan loading state.
| 6. Menambahkan ripple effect.
|--------------------------------------------------------------------------
*/

(function () {
    'use strict';

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    const CONFIG = {
        /*
         * Scroll hanya dikembalikan jika penyimpanan masih baru.
         * Nilai menggunakan milidetik.
         */
        scrollRestoreLifetime: 15000,

        /*
         * Marker dihapus jika tidak terjadi reload dalam waktu ini.
         * Berguna untuk form AJAX yang tidak melakukan reload halaman.
         */
        pendingMarkerLifetime: 8000,

        /*
         * Batas minimal scroll agar dianggap sebagai posisi penting.
         */
        minimumScrollToRestore: 80,

        /*
         * Durasi loading state pada form AJAX.
         */
        formBusyTimeout: 5000,

        /*
         * Waktu tunggu sebelum anti-jump observer diaktifkan.
         * Ini mencegah observer mengganggu proses render awal.
         */
        antiJumpStartDelay: 1200,
    };

    /*
    |--------------------------------------------------------------------------
    | Internal State
    |--------------------------------------------------------------------------
    */

    const state = {
        /*
         * Posisi scroll terakhir yang stabil.
         */
        lastStableScrollY: window.scrollY,

        /*
         * Mencegah anti-jump ketika user memang ingin ke bagian atas.
         */
        allowScrollTopUntil: 0,

        /*
         * Menandai scroll yang dibuat oleh script.
         */
        programmaticScrollUntil: 0,

        /*
         * Mencegah restore dilakukan lebih dari satu kali.
         */
        scrollHasBeenRestored: false,

        /*
         * Menandai apakah observer anti-jump sudah aktif.
         */
        antiJumpEnabled: false,
    };

    /*
    |--------------------------------------------------------------------------
    | Storage Helpers
    |--------------------------------------------------------------------------
    */

    const SCROLL_STORAGE_KEY = 'flowlist.ui.pending-scroll';

    /**
     * Membuat token sederhana untuk membedakan setiap aksi.
     */
    function createToken() {
        return [
            Date.now(),
            Math.random().toString(36).slice(2),
        ].join('-');
    }

    /**
     * Membaca data scroll dari sessionStorage.
     */
    function readStoredScroll() {
        try {
            const rawValue = sessionStorage.getItem(SCROLL_STORAGE_KEY);

            if (! rawValue) {
                return null;
            }

            return JSON.parse(rawValue);
        } catch (error) {
            sessionStorage.removeItem(SCROLL_STORAGE_KEY);

            return null;
        }
    }

    /**
     * Menghapus data scroll.
     */
    function clearStoredScroll() {
        try {
            sessionStorage.removeItem(SCROLL_STORAGE_KEY);
        } catch (error) {
            /*
             * Tidak perlu menghentikan aplikasi jika storage diblokir.
             */
        }
    }

    /**
     * Menyimpan posisi scroll sebelum aksi yang berpotensi reload.
     */
    function storeCurrentScroll() {
        const token = createToken();

        const payload = {
            token: token,
            path: window.location.pathname,
            x: window.scrollX,
            y: window.scrollY,
            createdAt: Date.now(),
        };

        try {
            sessionStorage.setItem(
                SCROLL_STORAGE_KEY,
                JSON.stringify(payload)
            );
        } catch (error) {
            return;
        }

        /*
         * Jika halaman tidak reload, hapus marker secara otomatis.
         * Ini mencegah posisi lama dipakai pada navigasi berikutnya.
         */
        window.setTimeout(function () {
            const currentPayload = readStoredScroll();

            if (
                currentPayload
                && currentPayload.token === token
            ) {
                clearStoredScroll();
            }
        }, CONFIG.pendingMarkerLifetime);
    }

    /*
    |--------------------------------------------------------------------------
    | Scroll Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Melakukan scroll langsung tanpa smooth animation.
     *
     * Digunakan ketika mengembalikan posisi setelah reload.
     */
    function jumpToPosition(x, y) {
        const rootElement = document.documentElement;
        const previousBehavior = rootElement.style.scrollBehavior;

        state.programmaticScrollUntil = performance.now() + 250;

        rootElement.style.scrollBehavior = 'auto';

        window.scrollTo({
            left: x,
            top: y,
            behavior: 'auto',
        });

        window.requestAnimationFrame(function () {
            rootElement.style.scrollBehavior = previousBehavior;
        });
    }

    /**
     * Mengembalikan posisi scroll setelah halaman dimuat ulang.
     */
    function restoreStoredScroll() {
        if (state.scrollHasBeenRestored) {
            return;
        }

        const storedScroll = readStoredScroll();

        if (! storedScroll) {
            return;
        }

        const storedAge = Date.now() - Number(storedScroll.createdAt || 0);
        const samePath = storedScroll.path === window.location.pathname;
        const stillValid = storedAge <= CONFIG.scrollRestoreLifetime;
        const validPosition = Number(storedScroll.y || 0)
            >= CONFIG.minimumScrollToRestore;

        /*
         * Restore hanya dilakukan pada path yang sama.
         * Contoh:
         * /admin/users setelah edit user.
         * /admin/tasks setelah update task.
         */
        if (! samePath || ! stillValid || ! validPosition) {
            clearStoredScroll();

            return;
        }

        state.scrollHasBeenRestored = true;
        state.lastStableScrollY = Number(storedScroll.y || 0);

        /*
         * Dua requestAnimationFrame menunggu layout awal selesai.
         */
        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                jumpToPosition(
                    Number(storedScroll.x || 0),
                    Number(storedScroll.y || 0)
                );
            });
        });

        /*
         * Restore kedua digunakan jika gambar, font, atau card
         * masih mengubah tinggi halaman setelah render awal.
         */
        window.setTimeout(function () {
            jumpToPosition(
                Number(storedScroll.x || 0),
                Number(storedScroll.y || 0)
            );

            clearStoredScroll();
        }, 180);
    }

    /**
     * Smooth scroll untuk link anchor.
     */
    function smoothScrollToTarget(target) {
        if (! target) {
            return;
        }

        state.allowScrollTopUntil = performance.now() + 1200;

        target.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Form State
    |--------------------------------------------------------------------------
    */

    /**
     * Menambahkan loading state pada form.
     */
    function markFormAsBusy(form, submitter) {
        if (! form) {
            return;
        }

        form.classList.add('ui-busy');
        form.setAttribute('aria-busy', 'true');

        if (submitter) {
            submitter.classList.add('ui-submit-active');
        }

        /*
         * Reset otomatis dibutuhkan untuk form AJAX.
         * Form biasa akan reload sebelum timeout selesai.
         */
        window.setTimeout(function () {
            form.classList.remove('ui-busy');
            form.removeAttribute('aria-busy');

            if (submitter) {
                submitter.classList.remove('ui-submit-active');
            }
        }, CONFIG.formBusyTimeout);
    }

    /**
     * Menghapus seluruh loading state.
     *
     * Fungsi ini juga menangani browser back-forward cache.
     */
    function resetBusyForms() {
        document.querySelectorAll('form.ui-busy').forEach(function (form) {
            form.classList.remove('ui-busy');
            form.removeAttribute('aria-busy');
        });

        document.querySelectorAll('.ui-submit-active').forEach(function (button) {
            button.classList.remove('ui-submit-active');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reveal Animation
    |--------------------------------------------------------------------------
    */

    const revealSelector = [
        '.stat-card',
        '.recent-tasks-card',
        '.deadlines-card',
        '.task-item',
        '.admin-user-row',
        '.admin-task-row',
        '.admin-log-row',
        '.admin-action-card',
        '.admin-profile-card',
        '.admin-progress-box',
        '.admin-activity-box',
    ].join(',');

    let revealObserver = null;

    /**
     * Menampilkan elemen saat masuk viewport.
     */
    function revealElement(element) {
        element.classList.add('is-visible');

        if (revealObserver) {
            revealObserver.unobserve(element);
        }
    }

    /**
     * Menyiapkan IntersectionObserver.
     */
    function createRevealObserver() {
        if (! ('IntersectionObserver' in window)) {
            return null;
        }

        return new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    revealElement(entry.target);
                }
            });
        }, {
            threshold: 0.08,
            rootMargin: '0px 0px -20px 0px',
        });
    }

    /**
     * Menambahkan reveal animation pada elemen.
     */
    function prepareRevealElements(root) {
        const source = root || document;

        let elements = [];

        /*
         * Root dapat berupa document atau elemen hasil MutationObserver.
         */
        if (
            source instanceof Element
            && source.matches(revealSelector)
        ) {
            elements.push(source);
        }

        if (typeof source.querySelectorAll === 'function') {
            elements = elements.concat(
                Array.from(source.querySelectorAll(revealSelector))
            );
        }

        elements.forEach(function (element, index) {
            if (
                element.dataset.noReveal === 'true'
                || element.classList.contains('ui-reveal')
            ) {
                return;
            }

            element.classList.add('ui-reveal');

            /*
             * Maksimal delay dibuat kecil agar halaman tidak terasa lambat.
             */
            const delay = Math.min(index, 8) * 45;

            element.style.setProperty(
                '--ui-reveal-delay',
                delay + 'ms'
            );

            if (revealObserver) {
                revealObserver.observe(element);
            } else {
                revealElement(element);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Ripple Effect
    |--------------------------------------------------------------------------
    */

    const rippleSelector = [
        'button',
        '.new-task-btn',
        '.admin-primary-btn',
        '.admin-secondary-btn',
        '.admin-danger-btn',
        '.admin-link-btn',
        '.admin-small-btn',
        '.menu-item',
        '.logout-btn',
    ].join(',');

    /**
     * Membuat efek ripple berdasarkan posisi pointer.
     */
    function createRipple(event) {
        const element = event.target.closest(rippleSelector);

        if (
            ! element
            || element.disabled
            || element.getAttribute('aria-disabled') === 'true'
        ) {
            return;
        }

        const rectangle = element.getBoundingClientRect();
        const ripple = document.createElement('span');

        ripple.className = 'ui-ripple';

        ripple.style.left = (
            event.clientX - rectangle.left
        ) + 'px';

        ripple.style.top = (
            event.clientY - rectangle.top
        ) + 'px';

        element.appendChild(ripple);

        window.setTimeout(function () {
            ripple.remove();
        }, 700);
    }

    /*
    |--------------------------------------------------------------------------
    | Anchor Handling
    |--------------------------------------------------------------------------
    */

    /**
     * Menangani anchor agar href="#" tidak membuat halaman meloncat.
     */
    function handleAnchorClick(event) {
        const anchor = event.target.closest('a[href]');

        if (! anchor) {
            return;
        }

        const rawHref = String(
            anchor.getAttribute('href') || ''
        ).trim();

        /*
         * Anchor kosong sering menyebabkan browser meloncat ke atas.
         */
        if (
            rawHref === ''
            || rawHref === '#'
            || rawHref.toLowerCase() === 'javascript:void(0)'
            || rawHref.toLowerCase() === 'javascript:void(0);'
        ) {
            event.preventDefault();

            return;
        }

        /*
         * Tangani anchor menuju bagian tertentu pada halaman.
         */
        if (rawHref.startsWith('#')) {
            const targetId = rawHref.slice(1);

            if (! targetId) {
                event.preventDefault();

                return;
            }

            const target = document.getElementById(targetId);

            if (target) {
                event.preventDefault();

                smoothScrollToTarget(target);

                /*
                 * Ubah URL tanpa memicu scroll bawaan browser.
                 */
                window.history.replaceState(
                    null,
                    '',
                    rawHref
                );
            }

            return;
        }

        /*
         * Simpan posisi jika link mengarah ke URL halaman yang sama.
         */
        try {
            const destination = new URL(anchor.href, window.location.href);

            const sameDocument = (
                destination.origin === window.location.origin
                && destination.pathname === window.location.pathname
                && destination.search === window.location.search
            );

            if (
                sameDocument
                && ! destination.hash
                && anchor.target !== '_blank'
            ) {
                storeCurrentScroll();
            }
        } catch (error) {
            /*
             * Abaikan href yang bukan URL normal.
             */
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Anti Sudden Scroll-to-Top
    |--------------------------------------------------------------------------
    */

    /**
     * Mencatat bahwa user memang berniat melakukan scroll manual.
     */
    function registerUserScrollIntent() {
        state.allowScrollTopUntil = performance.now() + 900;
    }

    /**
     * Mengembalikan scroll jika DOM mutation membuat halaman
     * tiba-tiba pindah ke posisi paling atas.
     */
    function protectAgainstSuddenScrollTop() {
        if (! state.antiJumpEnabled) {
            return;
        }

        const previousPosition = state.lastStableScrollY;

        window.requestAnimationFrame(function () {
            const isAtTop = window.scrollY <= 5;
            const hadMeaningfulPosition = previousPosition
                >= CONFIG.minimumScrollToRestore;

            const topIsAllowed = performance.now()
                <= state.allowScrollTopUntil;

            const isProgrammatic = performance.now()
                <= state.programmaticScrollUntil;

            if (
                isAtTop
                && hadMeaningfulPosition
                && ! topIsAllowed
                && ! isProgrammatic
                && document.visibilityState === 'visible'
            ) {
                jumpToPosition(
                    window.scrollX,
                    previousPosition
                );
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Dynamic Element Observer
    |--------------------------------------------------------------------------
    */

    /**
     * Observer menangani elemen baru yang dibuat oleh AJAX.
     */
    function createDynamicContentObserver() {
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node instanceof Element) {
                        prepareRevealElements(node);
                    }
                });
            });

            /*
             * Setelah DOM berubah, periksa apakah scroll meloncat.
             */
            protectAgainstSuddenScrollTop();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });

        return observer;
    }

    /*
    |--------------------------------------------------------------------------
    | Event Listeners
    |--------------------------------------------------------------------------
    */

    document.addEventListener('DOMContentLoaded', function () {
        /*
         * Class ini mengaktifkan animasi pada CSS.
         */
        document.body.classList.add('ui-motion-ready');

        revealObserver = createRevealObserver();

        prepareRevealElements(document);

        createDynamicContentObserver();

        /*
         * Anti-jump baru aktif setelah render awal selesai.
         */
        window.setTimeout(function () {
            state.antiJumpEnabled = true;
        }, CONFIG.antiJumpStartDelay);

        restoreStoredScroll();
        resetBusyForms();
    });

    /*
     * Restore juga dijalankan pada browser back-forward cache.
     */
    window.addEventListener('pageshow', function () {
        resetBusyForms();
        restoreStoredScroll();
    });

    /*
     * Simpan scroll terakhir yang stabil.
     */
    window.addEventListener('scroll', function () {
        if (
            performance.now()
            <= state.programmaticScrollUntil
        ) {
            return;
        }

        if (window.scrollY > 20) {
            state.lastStableScrollY = window.scrollY;
        }

        /*
         * Jika user sengaja berada di atas, hapus posisi lama.
         */
        if (
            window.scrollY <= 5
            && performance.now() <= state.allowScrollTopUntil
        ) {
            state.lastStableScrollY = 0;
        }
    }, {
        passive: true,
    });

    /*
     * Event ini mendeteksi scroll manual.
     */
    window.addEventListener('wheel', registerUserScrollIntent, {
        passive: true,
    });

    window.addEventListener('touchstart', registerUserScrollIntent, {
        passive: true,
    });

    window.addEventListener('keydown', function (event) {
        const scrollKeys = [
            'Home',
            'End',
            'PageUp',
            'PageDown',
            'ArrowUp',
            'ArrowDown',
            ' ',
        ];

        if (scrollKeys.includes(event.key)) {
            registerUserScrollIntent();
        }
    });

    /*
     * Tangani seluruh klik melalui event delegation.
     */
    document.addEventListener('click', function (event) {
        handleAnchorClick(event);
    });

    /*
     * Ripple menggunakan pointerdown agar terasa responsif.
     */
    document.addEventListener('pointerdown', function (event) {
        createRipple(event);
    });

    /*
     * Simpan scroll sebelum form dikirim.
     */
    document.addEventListener('submit', function (event) {
        const form = event.target;

        if (! (form instanceof HTMLFormElement)) {
            return;
        }

        /*
         * Gunakan data-no-preserve-scroll="true"
         * jika suatu form memang harus kembali ke atas.
         */
        if (form.dataset.noPreserveScroll !== 'true') {
            storeCurrentScroll();
        }

        markFormAsBusy(
            form,
            event.submitter || null
        );
    }, true);

    /*
     * Tandai browser saat mulai meninggalkan halaman.
     */
    window.addEventListener('beforeunload', function () {
        document.body.classList.add('ui-page-leaving');
    });
})();