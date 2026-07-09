document.addEventListener('DOMContentLoaded', function () {
    const deleteForms = document.querySelectorAll('.admin-delete-form');
    const completeForms = document.querySelectorAll('.admin-complete-form');
    const comingSoonButtons = document.querySelectorAll('.js-coming-soon');
    const toast = document.getElementById('adminToast');

    /*
     * Simpan posisi scroll sebelum form submit.
     * Setelah halaman reload, posisi scroll dikembalikan.
     */
    const allForms = document.querySelectorAll('form');
    const savedScrollPosition = sessionStorage.getItem('adminTasksScrollY');

    if (savedScrollPosition !== null) {
        window.requestAnimationFrame(function () {
            window.scrollTo({
                top: Number(savedScrollPosition),
                left: 0,
                behavior: 'instant'
            });

            sessionStorage.removeItem('adminTasksScrollY');
        });
    }

    allForms.forEach(function (form) {
        form.addEventListener('submit', function () {
            sessionStorage.setItem('adminTasksScrollY', String(window.scrollY));
        });
    });

    /*
     * Konfirmasi delete agar admin tidak menghapus task tanpa sengaja.
     */
    deleteForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const message = form.dataset.confirm || 'Delete this task?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    /*
     * Konfirmasi mark completed.
     */
    completeForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm('Mark this task as completed?')) {
                event.preventDefault();
            }
        });
    });

    /*
     * Toast untuk modul admin yang belum dibuat.
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
});