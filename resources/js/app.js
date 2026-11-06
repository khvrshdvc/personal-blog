import Alpine from 'alpinejs';
import { copyCode, reducedMotion, registerShared } from './shared';

window.Alpine = Alpine;

registerShared(Alpine);

const MONTHS = ['yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun', 'iyul', 'avgust', 'sentabr', 'oktabr', 'noyabr', 'dekabr'];

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
        if (count < 2 || reducedMotion()) return;
        setInterval(() => {
            if (!document.hidden) this.show(this.cur + 1);
        }, 4200);
    },

    show(n) {
        this.prev = this.cur;
        this.cur = (n + count) % count;
    },
}));

Alpine.data('fikrlar', (items, searchUrl) => ({
    items,
    tag: 'all',
    q: '',
    hits: null,
    controller: null,

    get featured() {
        return this.tag === 'all' && !this.q.trim();
    },

    shown(slug, tag) {
        const tagOk = this.tag === 'all' || this.tag === tag;
        const textOk = !this.q.trim() || this.hits === null || this.hits.includes(slug);
        return tagOk && textOk;
    },

    get visibleCount() {
        return this.items.filter((i) => this.shown(i.slug, i.tag)).length;
    },

    async query() {
        if (this.controller) this.controller.abort();
        if (!this.q.trim()) {
            this.hits = null;
            return;
        }
        this.controller = new AbortController();
        try {
            const res = await fetch(searchUrl + '?q=' + encodeURIComponent(this.q), {
                headers: { Accept: 'application/json' },
                signal: this.controller.signal,
            });
            if (res.ok) this.hits = await res.json();
        } catch (e) {}
    },

    clear() {
        this.q = '';
        this.hits = null;
        document.getElementById('bq').focus();
    },
}));

Alpine.data('heatmap', () => ({
    tip: { show: false, text: '', x: 0, y: 0 },

    init() {
        this.$nextTick(() => {
            const wrap = this.$refs.wrap;
            if (wrap) wrap.scrollLeft = wrap.scrollWidth;
        });
    },

    show(e) {
        const cell = e.target.closest('i[data-d]');
        if (!cell) return;
        const count = +cell.dataset.c;
        const [y, m, d] = cell.dataset.d.split('-').map(Number);
        this.tip.text = (count ? count + ' ta hissa' : 'Hissa yoʻq') + ' · ' + d + '-' + MONTHS[m - 1] + ', ' + y;
        const r = cell.getBoundingClientRect();
        this.tip.x = r.left + r.width / 2;
        this.tip.y = r.top;
        this.tip.show = true;
    },

    hide() {
        this.tip.show = false;
    },
}));

Alpine.data('site', (icons, searchUrl) => ({
    icons,
    mini: false,
    lastY: 0,
    miniDir: '',
    toastText: '',
    toastAction: null,
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
        document.addEventListener('click', (e) => this.onClick(e));
    },

    notify(text, action = null) {
        this.toastText = text;
        this.toastAction = action;
        clearTimeout(this.toastTimer);
        this.toastTimer = setTimeout(() => (this.toastText = ''), 3000);
    },

    toggleTheme() {
        Alpine.store('theme').toggle();
    },

    async share() {
        if (navigator.share) {
            try {
                await navigator.share({ title: document.title, url: location.href });
                return;
            } catch (e) {
                if (e && e.name === 'AbortError') return;
            }
        }
        try {
            await navigator.clipboard.writeText(location.href);
        } catch (e) {}
        this.notify('Havola nusxalandi');
    },

    onClick(e) {
        copyCode(e);
    },

    onScroll() {
        const y = window.scrollY;
        const max = document.documentElement.scrollHeight - window.innerHeight;
        const progress = max > 0 ? Math.min(1, Math.max(0, y / max)) : 0;
        const bar = document.getElementById('progress');
        if (bar) bar.style.width = progress * 100 + '%';

        const nav = document.getElementById('tnav');
        const out = nav ? nav.getBoundingClientRect().bottom < -6 : false;
        let show = out;

        if (out && document.body.dataset.route === 'post' && progress < 0.98) {
            if (Math.abs(y - this.lastY) > 10) {
                this.miniDir = y > this.lastY ? 'down' : 'up';
                this.lastY = y;
            }
            show = this.miniDir !== 'down';
        } else {
            this.lastY = y;
        }

        this.mini = show;
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
            return;
        }
        const active = document.activeElement;
        const typing = active && (/INPUT|TEXTAREA|SELECT/.test(active.tagName) || active.isContentEditable);
        if (e.key === '/' && !typing) {
            const input = document.getElementById('bq');
            if (input) {
                e.preventDefault();
                input.focus();
            }
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
