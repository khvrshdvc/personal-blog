import Alpine from 'alpinejs';

window.Alpine = Alpine;

const THEME_KEY = 'ut4_theme';

Alpine.data('clock', () => ({
    time: '',

    init() {
        const fmt = new Intl.DateTimeFormat('en-GB', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Tashkent' });
        this.time = this.$el.querySelector('#ltm').textContent.trim();
        setInterval(() => {
            const v = fmt.format(new Date());
            if (v !== this.time) this.time = v;
        }, 5000);
    },
}));

Alpine.data('rotator', (count) => ({
    cur: 0,
    prev: null,

    init() {
        const reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (count < 2 || reduce) return;
        setInterval(() => {
            if (!document.hidden) this.show(this.cur + 1);
        }, 4200);
    },

    show(n) {
        this.prev = this.cur;
        this.cur = (n + count) % count;
    },
}));

Alpine.data('site', (icons, searchUrl) => ({
    icons,
    mini: false,
    toastText: '',
    toastTimer: null,
    glowEl: null,
    palette: { open: false, q: '', items: [], i: 0, loaded: false, prev: null },
    controller: null,

    init() {
        this.onScroll();
        window.addEventListener('scroll', () => this.onScroll(), { passive: true });
        if (window.matchMedia && matchMedia('(hover: hover) and (pointer: fine)').matches) {
            document.addEventListener('pointermove', (e) => this.onPointer(e), { passive: true });
        }
        document.addEventListener('keydown', (e) => this.onKey(e));
    },

    notify(text) {
        this.toastText = text;
        clearTimeout(this.toastTimer);
        this.toastTimer = setTimeout(() => (this.toastText = ''), 3000);
    },

    toggleTheme() {
        const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = next;
        try {
            localStorage.setItem(THEME_KEY, next);
        } catch (e) {}
    },

    onScroll() {
        const nav = document.getElementById('tnav');
        this.mini = nav ? nav.getBoundingClientRect().bottom < -6 : false;
    },

    onPointer(e) {
        const card = e.target && e.target.closest ? e.target.closest('#site .c') : null;
        if (this.glowEl && this.glowEl !== card) {
            this.glowEl.style.removeProperty('--x');
            this.glowEl.style.removeProperty('--y');
        }
        this.glowEl = card;
        if (card) {
            const r = card.getBoundingClientRect();
            card.style.setProperty('--x', (e.clientX - r.left).toFixed(0) + 'px');
            card.style.setProperty('--y', (e.clientY - r.top).toFixed(0) + 'px');
        }
    },

    onKey(e) {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            this.palette.open ? this.closePalette() : this.openPalette();
        }
    },

    openPalette() {
        if (this.palette.open) return;
        this.palette.prev = document.activeElement;
        this.palette.open = true;
        this.palette.q = '';
        this.palette.i = 0;
        this.search();
        this.$nextTick(() => this.$refs.paletteInput.focus());
    },

    closePalette() {
        if (!this.palette.open) return;
        this.palette.open = false;
        if (this.controller) this.controller.abort();
        const prev = this.palette.prev;
        if (prev && prev.focus) prev.focus();
    },

    async search() {
        if (this.controller) this.controller.abort();
        this.controller = new AbortController();
        try {
            const res = await fetch(searchUrl + '?q=' + encodeURIComponent(this.palette.q), {
                headers: { Accept: 'application/json' },
                signal: this.controller.signal,
            });
            if (!res.ok) return;
            this.palette.items = await res.json();
            this.palette.i = 0;
            this.palette.loaded = true;
        } catch (e) {}
    },

    move(n) {
        const len = this.palette.items.length;
        if (!len) return;
        this.palette.i = (this.palette.i + n + len) % len;
        this.$nextTick(() => {
            const el = document.getElementById('kpo' + this.palette.i);
            if (el) el.scrollIntoView({ block: 'nearest' });
        });
    },

    go() {
        const item = this.palette.items[this.palette.i];
        if (item) window.location.href = item.h;
    },
}));

Alpine.start();
