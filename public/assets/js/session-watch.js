(function () {
    const routes = window.FlowlistRoutes || {};
    const expiresAt = Number(routes.sessionExpiresAt || 0);

    if (!routes.login || !expiresAt) return;

    const redirectToLogin = function () {
        window.location.href = routes.login;
    };

    const delay = (expiresAt * 1000) - Date.now();

    if (delay <= 0) {
        redirectToLogin();
        return;
    }

    setTimeout(redirectToLogin, delay + 1000);

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden && Date.now() >= expiresAt * 1000) {
            redirectToLogin();
        }
    });
})();