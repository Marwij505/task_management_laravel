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