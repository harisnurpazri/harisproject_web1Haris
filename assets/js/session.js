// Frontend session helper.
// - Attempts to discover `api/session_status.php` by trying several relative paths.
// - Exposes `window.APP_SESSION` with {logged_in, user}
// - Adds helper `ensureLoggedIn()` to redirect when needed.

(function(){
    if (window.APP_SESSION) return; // already loaded

    const candidates = [
        'api/session_status.php',
        '../api/session_status.php',
        '../../api/session_status.php',
        '../../../api/session_status.php'
    ];

    async function probe(paths) {
        for (const p of paths) {
            try {
                const res = await fetch(p, { credentials: 'same-origin', cache: 'no-store' });
                if (!res.ok) continue;
                const data = await res.json();
                // basic validation
                if (typeof data.logged_in !== 'undefined') {
                    window.APP_SESSION = data;
                    return data;
                }
            } catch (e) {
                // ignore and try next
            }
        }
        // fallback: try to detect PHPSESSID cookie
        const hasPhpSess = document.cookie.split(';').some(c => c.trim().startsWith('PHPSESSID='));
        window.APP_SESSION = { logged_in: hasPhpSess || false, user: null };
        return window.APP_SESSION;
    }

    // Start probing but don't block page render
    probe(candidates).then(() => {
        document.dispatchEvent(new CustomEvent('app:session-ready', { detail: window.APP_SESSION }));
    });

    // Helper to ensure logged in; accepts a loginUrl (defaults to /auth/login.php)
    window.ensureLoggedIn = function(options) {
        options = options || {};
        const loginUrl = options.loginUrl || '/auth/login.php';
        // If session already known
        if (window.APP_SESSION && window.APP_SESSION.logged_in) return Promise.resolve(true);
        return new Promise((resolve) => {
            // wait for session probe
            document.addEventListener('app:session-ready', function handler(e){
                document.removeEventListener('app:session-ready', handler);
                if (window.APP_SESSION && window.APP_SESSION.logged_in) {
                    resolve(true);
                } else {
                    // redirect to login page
                    window.location.href = options.loginUrl || loginUrl;
                    resolve(false);
                }
            });
        });
    };

})();
