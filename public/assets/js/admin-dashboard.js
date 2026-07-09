document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.js-coming-soon');
    const toast = document.getElementById('adminToast');

    /*
     * Stage 3B only redesigns the admin dashboard.
     * These buttons will be connected to real admin pages in the next stages.
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

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            const feature = button.dataset.feature || 'This feature';
            showToast(`${feature} will be available in the next admin stage.`);
        });
    });
});