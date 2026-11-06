const THEME_KEY = 'ut4_theme';

export const reducedMotion = () => window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;

export function copyCode(e) {
    const button = e.target.closest('.copy-code');
    if (!button) return;
    const pre = button.closest('.code-wrap').querySelector('pre');
    const label = button.querySelector('span');
    const done = () => {
        label.textContent = 'Nusxalandi';
        setTimeout(() => (label.textContent = 'Nusxalash'), 1400);
    };
    navigator.clipboard.writeText(pre.innerText).then(done, done);
}

export function registerShared(Alpine) {
    Alpine.store('theme', {
        mode: 'dark',
        query: null,

        init() {
            try {
                this.mode = localStorage.getItem(THEME_KEY) || 'dark';
            } catch (e) {}
            this.query = window.matchMedia ? matchMedia('(prefers-color-scheme: dark)') : null;
            if (this.query && this.query.addEventListener) {
                this.query.addEventListener('change', () => {
                    if (this.mode === 'auto') this.apply();
                });
            }
        },

        apply() {
            const dark = this.mode === 'dark' || (this.mode === 'auto' && this.query && this.query.matches);
            document.documentElement.dataset.theme = dark ? 'dark' : 'light';
        },

        set(mode) {
            this.mode = mode;
            try {
                localStorage.setItem(THEME_KEY, mode);
            } catch (e) {}
            this.apply();
        },

        toggle() {
            this.set(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
        },
    });

    Alpine.data('seg', (initial = null) => ({
        cur: initial,

        init() {
            this.$nextTick(() => this.place());
            window.addEventListener('resize', () => this.place());
        window.addEventListener('seg-place', () => this.place());
            if (document.fonts && document.fonts.ready) document.fonts.ready.then(() => this.place());
        },

        place() {
            const el = this.$el;
            const active = el.querySelector('[aria-pressed="true"], [aria-current="page"]');
            const knob = el.querySelector('.knob');
            if (!active || !knob) return;
            knob.style.width = active.offsetWidth + 'px';
            knob.style.transform = 'translateX(' + active.offsetLeft + 'px)';
            if (!el.classList.contains('ready')) {
                requestAnimationFrame(() => requestAnimationFrame(() => el.classList.add('ready')));
            }
        },

        pick(value) {
            this.cur = value;
            this.$nextTick(() => this.place());
        },
    }));
}
