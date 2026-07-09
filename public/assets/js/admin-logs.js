document.addEventListener('DOMContentLoaded', function () {
    const clearButton = document.querySelector('.js-clear-filters');

    /*
     * Tombol Clear Filters mengembalikan halaman ke daftar log tanpa filter.
     */
    if (clearButton) {
        clearButton.addEventListener('click', function () {
            window.location.href = '/admin/activity-logs';
        });
    }
});