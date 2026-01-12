/* Frontend session client (modules/frontend/session_client.js)
   Provides same API as assets/js/session.js but lives in modules/frontend
*/
(function(){
    if (window.APP_SESSION) return;

    const candidates = [
        '/meubeul_harisproject/api/session_status.php',
        'api/session_status.php',
        '../api/session_status.php',
        '../../api/session_status.php'
    ];

    async function probe(paths){
        for(const p of paths){
            try{
                const res = await fetch(p, { credentials: 'same-origin', cache: 'no-store' });
                if (!res.ok) continue;
                const data = await res.json();
                if (typeof data.logged_in !== 'undefined'){
                    window.APP_SESSION = data;
                    return data;
                }
            }catch(e){ }
        }
        const hasPhpSess = document.cookie.split(';').some(c=>c.trim().startsWith('PHPSESSID='));
        window.APP_SESSION = { logged_in: hasPhpSess || false, user: null };
        return window.APP_SESSION;
    }

    probe(candidates).then(()=>{
        document.dispatchEvent(new CustomEvent('app:session-ready',{ detail: window.APP_SESSION }));
    });

    window.ensureLoggedIn = function(options){
        options = options || {};
        const loginUrl = options.loginUrl || '/meubeul_harisproject/auth/login.php';
        if (window.APP_SESSION && window.APP_SESSION.logged_in) return Promise.resolve(true);
        return new Promise((resolve)=>{
            document.addEventListener('app:session-ready', function handler(){
                document.removeEventListener('app:session-ready', handler);
                if (window.APP_SESSION && window.APP_SESSION.logged_in) resolve(true);
                else { window.location.href = loginUrl; resolve(false); }
            });
        });
    };

})();
