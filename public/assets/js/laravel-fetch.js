(function () {
    'use strict';

    const originalFetch = window.fetch.bind(window);

    window.fetch = function (input, init) {
        const options = Object.assign({}, init || {});
        const requestUrl = typeof input === 'string' ? input : input.url;
        const url = new URL(requestUrl, window.location.origin);

        if (url.origin === window.location.origin) {
            const headers = new Headers(options.headers || {});
            const method = String(options.method || 'GET').toUpperCase();

            headers.set('Accept', 'application/json');

            if (!['GET', 'HEAD', 'OPTIONS'].includes(method)) {
                const token = document.querySelector('meta[name="csrf-token"]')?.content;

                if (token && !headers.has('X-CSRF-TOKEN')) {
                    headers.set('X-CSRF-TOKEN', token);
                }
            }

            options.headers = headers;
            options.credentials = options.credentials || 'same-origin';
        }

        return originalFetch(input, options);
    };
})();
