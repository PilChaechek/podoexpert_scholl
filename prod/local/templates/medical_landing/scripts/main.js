const smoothScrollTo = (target, offset = 0) => {
    const el = typeof target === 'string' ? document.querySelector(target) : target;
    if (!el) return;

    const top = el.getBoundingClientRect().top + window.scrollY - offset;
    window.scrollTo({ top, behavior: 'smooth' });
};

const initSmoothLinks = () => {
    document.querySelectorAll('a.smooth-link, button.smooth-link').forEach((link) => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href') || link.dataset.href;
            if (!href || !href.startsWith('#')) return;

            e.preventDefault();
            smoothScrollTo(href);
        });
    });
};

document.addEventListener('DOMContentLoaded', initSmoothLinks);
