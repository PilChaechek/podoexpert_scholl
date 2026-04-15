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

const initPhoneInputs = () => {
    document.querySelectorAll('input[data-phone-filter]').forEach((input) => {
        const sanitize = (value) => {
            let result = '';
            for (let i = 0; i < value.length; i++) {
                const ch = value[i];
                if (i === 0 && ch === '+') {
                    result += ch;
                } else if (/\d/.test(ch)) {
                    result += ch;
                }
            }
            return result;
        };

        input.addEventListener('input', () => {
            const pos = input.selectionStart;
            const before = input.value;
            const after = sanitize(before);
            if (before !== after) {
                input.value = after;
                const diff = before.length - after.length;
                input.setSelectionRange(Math.max(0, pos - diff), Math.max(0, pos - diff));
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === '+' && input.value.length > 0) {
                e.preventDefault();
            }
        });

        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            const cursorPos = input.selectionStart;
            const newValue = input.value.slice(0, cursorPos) + pasted + input.value.slice(input.selectionEnd);
            input.value = sanitize(newValue);
            const newPos = sanitize(input.value.slice(0, cursorPos) + pasted).length;
            input.setSelectionRange(newPos, newPos);
        });
    });
};

document.addEventListener('DOMContentLoaded', initPhoneInputs);
