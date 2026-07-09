document.addEventListener('DOMContentLoaded', function () {
    const copyButton = document.querySelector('.js-copy-report');
    const reportText = document.getElementById('statisticsReportText');
    const comingSoonButtons = document.querySelectorAll('.js-coming-soon');
    const toast = document.getElementById('adminToast');

    /*
     * Toast kecil untuk memberi feedback ke admin.
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

    /*
     * Copy summary statistik.
     * Fitur ini berguna untuk admin yang ingin menyalin laporan singkat.
     */
    if (copyButton && reportText) {
        copyButton.addEventListener('click', function () {
            const text = reportText.value.trim();

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text)
                    .then(function () {
                        showToast('Summary copied successfully.');
                    })
                    .catch(function () {
                        showToast('Failed to copy summary.');
                    });

                return;
            }

            reportText.select();
            document.execCommand('copy');
            showToast('Summary copied successfully.');
        });
    }

    /*
     * Activity Logs belum dibuat pada Babak 6.
     */
    comingSoonButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const feature = button.dataset.feature || 'This feature';
            showToast(`${feature} will be available in the next admin stage.`);
        });
    });
});