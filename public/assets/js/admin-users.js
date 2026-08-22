document.addEventListener('DOMContentLoaded', function () {
    const deleteForms = document.querySelectorAll('.admin-delete-form');
    const comingSoonButtons = document.querySelectorAll('.js-coming-soon');
    const toast = document.getElementById('adminToast');

    // =====================================
    // RESTORE SCROLL POSITION AFTER FORM SUBMIT
    // =====================================
    // Karena create, edit, reset password, dan delete memakai form submit biasa,
    // halaman akan reload. Kode ini menyimpan posisi scroll sebelum submit,
    // lalu mengembalikan posisi scroll setelah halaman selesai reload.
    const allAdminForms = document.querySelectorAll('form');
    const savedScrollPosition = sessionStorage.getItem('adminUsersScrollY');

    if (savedScrollPosition !== null) {
        window.requestAnimationFrame(function () {
            window.scrollTo({
                top: Number(savedScrollPosition),
                left: 0,
                behavior: 'instant'
            });

            sessionStorage.removeItem('adminUsersScrollY');
        });
    }

    allAdminForms.forEach(function (form) {
        form.addEventListener('submit', function () {
            sessionStorage.setItem('adminUsersScrollY', String(window.scrollY));
        });
    });

    /*
     * Konfirmasi delete.
     * Ini mencegah admin tidak sengaja menghapus user.
     */
    deleteForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const message = form.dataset.confirm || 'Are you sure?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    /*
     * Toast kecil untuk fitur admin yang belum masuk babak ini.
     */
    function showToast(message) {
        if (!toast) return;

        toast.textContent = message;
        toast.classList.add('show');

        window.clearTimeout(showToast.timer);
        showToast.timer = window.setTimeout(function () {
            toast.classList.remove('show');
        }, 2200);
    }

    comingSoonButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const feature = button.dataset.feature || 'This feature';
            showToast(`${feature} will be available in the next admin stage.`);
        });
    });


    // =====================================
    // FLOWLIST MK2 - FOCUS USER FROM ADMIN DASHBOARD
    // =====================================
    //
    // URL contoh:
    // /admin/users?search=user@email.com&focus=3
    //
    // Blade memberi attribute data-admin-user-focused="true"
    // pada user target. Script kemudian:
    // - scroll ke user target;
    // - memberi highlight sementara;
    // - tetap menghormati reduced motion.
    const focusedUser =
        document.querySelector(
            '[data-admin-user-focused="true"]'
        );

    if (focusedUser) {
        focusedUser.classList.add(
            'admin-user-focus'
        );

        window.setTimeout(
            function () {
                focusedUser.scrollIntoView({
                    behavior:
                        window.matchMedia(
                            '(prefers-reduced-motion: reduce)'
                        ).matches
                            ? 'auto'
                            : 'smooth',
                    block: 'center'
                });
            },
            120
        );

        window.setTimeout(
            function () {
                focusedUser.classList.add(
                    'admin-user-focus-fade'
                );
            },
            2600
        );
    }

});