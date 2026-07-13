document.addEventListener('DOMContentLoaded', function () {
    const clearButton = document.querySelector('.js-clear-filters');

    /*
     * Menghapus seluruh filter tanpa hardcode URL aplikasi.
     */
    if (clearButton) {
        clearButton.addEventListener('click', function () {
            const resetUrl = clearButton.dataset.resetUrl;

            if (resetUrl) {
                window.location.href = resetUrl;
            }
        });
    }
});