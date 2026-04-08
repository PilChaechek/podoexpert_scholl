(function () {
    var header = document.querySelector('.site-header');
    if (header) {
        var toggle = function () { header.classList.toggle('is-scrolled', window.scrollY > 0); };
        toggle();
        window.addEventListener('scroll', toggle, { passive: true });
    }
})();

(function () {
    var BVI_KEYS = [
        'fontSize', 'theme', 'images', 'letterSpacing', 'lineHeight',
        'speech', 'fontFamily', 'builtElements', 'panelFixed', 'panelHide',
        'reload', 'lang', 'panelActive'
    ];
    var PAST = 'Thu, 01 Jan 1970 00:00:01 GMT';

    function setBviCookie(key, val, expires) {
        document.cookie = 'bvi_' + key + '=' + val + ';path=/;expires=' + expires;
    }

    function clearBviCookies() {
        BVI_KEYS.forEach(function (k) {
            document.cookie = 'bvi_' + k + '=;path=/;expires=' + PAST;
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-bvi="close"]')) {
            clearBviCookies();
        }
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        var bvi = new isvek.Bvi({ target: '.bvi-no-trigger', lang: 'ru-RU' });

        var btn = document.querySelector('.site-header__btn-bvi');
        if (!btn) return;

        btn.addEventListener('click', function () {
            if (document.body.classList.contains('bvi-active')) return;

            var expires = new Date(Date.now() + 864e5).toUTCString();
            var cfg = [
                'fontSize=16', 'theme=white', 'images=grayscale',
                'letterSpacing=normal', 'lineHeight=normal', 'speech=true',
                'fontFamily=arial', 'builtElements=false', 'panelFixed=true',
                'panelHide=false', 'reload=false', 'lang=ru-RU'
            ];
            cfg.forEach(function (c) {
                setBviCookie(c.split('=')[0], c.split('=')[1], expires);
            });
            setBviCookie('panelActive', 'true', expires);
            bvi._init();
        });
    });
})();
