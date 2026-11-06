import Alpine from 'alpinejs';
import { copyCode, registerShared } from './shared';

window.Alpine = Alpine;

registerShared(Alpine);

const MONTHS = ['yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun', 'iyul', 'avgust', 'sentabr', 'oktabr', 'noyabr', 'dekabr'];
const SHORT = ['yan', 'fev', 'mar', 'apr', 'may', 'iyn', 'iyl', 'avg', 'sen', 'okt', 'noy', 'dek'];

const csrf = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
};

const notify = (text, action = null) => window.dispatchEvent(new CustomEvent('admin-notify', { detail: { text, action } }));

const stash = (text, action = null) => {
    try {
        sessionStorage.setItem('admin_toast', JSON.stringify({ text, action }));
    } catch (e) {}
};

async function api(method, url, body, options = {}) {
    const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf() };
    let payload;
    if (body instanceof FormData) {
        payload = body;
    } else if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
        payload = JSON.stringify(body);
    }
    try {
        const res = await fetch(url, { method, headers, body: payload, credentials: 'same-origin', keepalive: !!options.keepalive });
        let data = null;
        try {
            data = await res.json();
        } catch (e) {}
        if (res.status === 401 || res.status === 419) {
            notify('Sessiya tugagan. Qayta kiring.');
            setTimeout(() => (window.location.href = '/admin/login'), 1500);
        }
        return { ok: res.ok, status: res.status, data };
    } catch (e) {
        return { ok: false, status: 0, data: null };
    }
}

const firstError = (r, fallback) => {
    const errors = r && r.data && r.data.errors;
    if (errors) {
        const first = Object.values(errors)[0];
        if (first && first[0]) return first[0];
    }
    return (r && r.data && r.data.message) || fallback;
};

const esc = (s) => String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const slugify = (s) => String(s || '').toLowerCase().replace(/[ʻʼ‘’'`]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 80);
const words = (t) => (String(t || '').match(/\S+/g) || []).length;
const readMin = (t) => Math.max(1, Math.round(words(t) / 200));
const shortDate = (iso) => {
    const p = String(iso || '').split('-').map(Number);
    return p.length === 3 && p[1] ? p[2] + ' ' + SHORT[p[1] - 1] : '';
};
const longDate = (iso) => {
    const p = String(iso || '').split('-').map(Number);
    return p.length === 3 && p[1] ? p[2] + '-' + MONTHS[p[1] - 1] + ', ' + p[0] : '';
};
const autoExcerpt = (body) => {
    const t = String(body || '')
        .replace(/```[\s\S]*?```/g, ' ')
        .replace(/!\[[^\]]*\]\([^)]*\)/g, ' ')
        .replace(/\[([^\]]+)\]\([^)]*\)/g, '$1')
        .replace(/[#>*`_]/g, '')
        .replace(/\s+/g, ' ')
        .trim();
    return t.length > 160 ? t.slice(0, 157).replace(/\s+\S*$/, '') + '…' : t;
};
const initials = (name) => {
    const w = String(name || '?').split(/[^A-Za-z0-9ʻʼ']+/).filter(Boolean);
    return ((w[0] || '?')[0] + (w[1] ? w[1][0] : (w[0] || '')[1] || '')).toUpperCase();
};

Alpine.store('dialog', {
    open: false,
    title: '',
    message: '',
    ok: 'Oʻchirish',
    cancel: 'Bekor qilish',
    resolver: null,
    prev: null,

    ask(options) {
        this.title = options.title;
        this.message = options.message || '';
        this.ok = options.ok || 'Oʻchirish';
        this.cancel = options.cancel || 'Bekor qilish';
        this.prev = document.activeElement;
        this.open = true;
        setTimeout(() => {
            const cancel = document.querySelector('.alert [data-a="0"]');
            if (cancel) cancel.focus();
        }, 30);
        return new Promise((resolve) => (this.resolver = resolve));
    },

    done(value) {
        if (!this.open) return;
        this.open = false;
        if (this.resolver) this.resolver(value);
        if (this.prev && this.prev.isConnected && this.prev.focus) this.prev.focus();
    },

    trap(e) {
        const buttons = Array.from(document.querySelectorAll('.alert-btns button'));
        const index = buttons.indexOf(document.activeElement);
        buttons[(index + (e.shiftKey ? -1 : 1) + buttons.length) % buttons.length].focus();
    },
});

Alpine.data('admin', (flash, newUrl) => ({
    toastText: '',
    toastAction: null,
    toastTimer: null,

    init() {
        let pending = flash;
        try {
            const stored = sessionStorage.getItem('admin_toast');
            if (stored) {
                pending = JSON.parse(stored);
                sessionStorage.removeItem('admin_toast');
            }
        } catch (e) {}
        if (pending) this.$nextTick(() => this.notify(pending.text, pending.action || null));
        window.addEventListener('admin-notify', (e) => this.notify(e.detail.text, e.detail.action));
        document.addEventListener('click', copyCode);
        document.addEventListener('keydown', (e) => this.onKey(e));
    },

    notify(text, action = null) {
        this.toastText = text;
        this.toastAction = action;
        clearTimeout(this.toastTimer);
        this.toastTimer = setTimeout(() => (this.toastText = ''), 3000);
    },

    onKey(e) {
        const active = document.activeElement;
        const typing = active && (/INPUT|TEXTAREA|SELECT/.test(active.tagName) || active.isContentEditable);
        if ((e.key === 'n' || e.key === 'N') && !typing && !e.metaKey && !e.ctrlKey && !e.altKey && !Alpine.store('dialog').open) {
            e.preventDefault();
            window.location.href = newUrl;
        }
    },
}));

Alpine.data('alist', (items) => ({
    items,
    q: '',
    filter: 'all',
    editing: false,

    vis(id) {
        const item = this.items.find((i) => i.id === id);
        if (!item) return false;
        const q = this.q.trim().toLowerCase();
        return (this.filter === 'all' || this.filter === item.cat) && (!q || item.title.includes(q));
    },

    get empty() {
        return !this.items.some((i) => this.vis(i.id));
    },

    closeReveal() {
        this.$root.querySelectorAll('.ali.reveal').forEach((x) => x.classList.remove('reveal'));
    },

    toggleEdit() {
        this.editing = !this.editing;
        this.closeReveal();
    },

    onClick(e) {
        const li = e.target.closest('.ali');
        if (!li) return;
        if (e.target.closest('[data-arm]')) {
            e.preventDefault();
            const was = li.classList.contains('reveal');
            this.closeReveal();
            if (!was) li.classList.add('reveal');
            return;
        }
        if (e.target.closest('[data-del]')) {
            e.preventDefault();
            this.remove(li);
            return;
        }
        if (this.editing && e.target.closest('.arow')) {
            e.preventDefault();
            this.closeReveal();
        }
    },

    async remove(li) {
        const r = await api('DELETE', li.dataset.url);
        if (!r.ok) {
            notify('Oʻchirib boʻlmadi');
            return;
        }
        const id = Number(li.dataset.id);
        const wasActive = li.dataset.active === '1';
        li.style.height = li.offsetHeight + 'px';
        li.classList.add('gone');
        requestAnimationFrame(() => (li.style.height = '0px'));
        setTimeout(() => {
            li.remove();
            this.items = this.items.filter((i) => i.id !== id);
            this.syncCounts();
            if (wasActive) {
                stash(r.data.message);
                window.location.href = r.data.redirect;
            } else {
                notify(r.data.message);
            }
        }, 260);
    },

    syncCounts() {
        document.querySelectorAll('[data-count="' + this.$root.dataset.section + '"]').forEach((el) => (el.textContent = this.items.length));
    },

    rowHtml(post) {
        const f = post.fields;
        const status = post.status === 'published' ? (post.pending ? 'chg' : 'pub') : 'draft';
        const label = post.status === 'published' ? (post.pending ? 'Oʻzgarish bor' : 'Chop etilgan') : 'Qoralama';
        const one = f.category === 'dev' ? 'Dasturlash' : 'Fikr';
        return (
            '<button class="minus" type="button" data-arm aria-label="Oʻchirish"><svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="currentColor"/><path d="M7.5 12h9" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/></svg></button>' +
            '<a class="arow on" href="' + esc(this.$root.dataset.edit + '/' + post.id) + '" aria-current="true">' +
            '<span class="ar-top"><span class="sdot ' + status + '" aria-hidden="true"></span><b class="rt">' + esc(f.title || 'Sarlavhasiz') + '</b><span class="rd">' + esc(shortDate(f.published_at)) + '</span></span>' +
            '<span class="rs2">' + esc(f.excerpt || autoExcerpt(f.body) || 'Matn yoʻq') + '</span>' +
            '<span class="ar-st">' + one + ' · ' + esc(f.tag || '') + ' · ' + label + '</span></a>' +
            '<button class="delbtn" type="button" data-del>Oʻchirish</button>'
        );
    },

    upsert(post) {
        const list = this.$root.querySelector('#aitems');
        if (!list) return;
        const f = post.fields;
        const entry = { id: post.id, cat: f.category, title: (f.title || '').toLowerCase() };
        let li = list.querySelector('.ali[data-id="' + post.id + '"]');
        const index = this.items.findIndex((i) => i.id === post.id);
        if (index >= 0) this.items[index] = entry;
        else this.items.unshift(entry);
        if (!li) {
            li = document.createElement('li');
            li.className = 'ali';
            li.dataset.id = post.id;
            li.dataset.url = this.$root.dataset.edit + '/' + post.id;
            li.setAttribute('x-show', 'vis(' + post.id + ')');
            list.prepend(li);
            li.innerHTML = this.rowHtml(post);
            Alpine.initTree(li);
            this.syncCounts();
        } else {
            li.innerHTML = this.rowHtml(post);
        }
        li.dataset.active = '1';
        list.querySelectorAll('.arow.on').forEach((a) => {
            if (!li.contains(a)) {
                a.classList.remove('on');
                a.removeAttribute('aria-current');
            }
        });
    },
}));

Alpine.data('editor', (cfg) => {
    const post = cfg.post;
    const f = post ? post.fields : null;
    const category = f ? f.category : cfg.defaultCategory;

    return {
        id: post ? post.id : null,
        status: post ? post.status : 'draft',
        hasPending: post ? post.pending : false,
        url: post ? post.url : null,
        cat: category,
        title: f ? f.title : '',
        slug: f ? f.slug : '',
        tag: f && cfg.tags[category].includes(f.tag) ? f.tag : cfg.tags[category][0],
        excerpt: f ? f.excerpt : '',
        body: f ? f.body : '',
        date: f ? f.published_at || cfg.today : cfg.today,
        cover: f ? f.cover : '',
        coverUrl: f ? f.cover_url : null,
        slugTouched: false,
        slugTaken: post ? post.slug_taken : false,
        mode: 'write',
        sheet: false,
        drag: false,
        saveText: post && post.pending ? 'Saqlangan oʻzgarishlar bor' : '',
        previewHtml: '',
        timer: null,
        previewTimer: null,
        queue: Promise.resolve(),
        tags: cfg.tags,

        init() {
            this.slugTouched = this.status === 'published' ? true : !!(this.slug && this.slug !== slugify(this.title));
            this.$nextTick(() => {
                this.grow(this.$refs.title);
                this.grow(this.$refs.body);
                if (!this.id) this.$refs.title.focus();
            });
            window.addEventListener('beforeunload', () => {
                if (this.timer) this.save(true);
            });
            document.addEventListener('keydown', (e) => this.onKey(e));
        },

        get published() {
            return this.status === 'published';
        },

        get statusLabel() {
            return this.published ? (this.hasPending ? 'Oʻzgarish bor' : 'Chop etilgan') : 'Qoralama';
        },

        get pillClass() {
            return this.published ? (this.hasPending ? 'chg' : 'pub') : '';
        },

        get wordCount() {
            return words(this.body);
        },

        get readTime() {
            return readMin(this.body);
        },

        get tagColor() {
            return cfg.colors[this.tag] || '#8e8e93';
        },

        get dateLong() {
            return longDate(this.date);
        },

        onKey(e) {
            if (Alpine.store('dialog').open) return;
            if (e.key === 'Escape' && this.sheet) {
                e.preventDefault();
                this.sheet = false;
                return;
            }
            if (!(e.ctrlKey || e.metaKey)) return;
            if (e.key.toLowerCase() === 's') {
                e.preventDefault();
                this.save(false);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                this.publish();
            }
        },

        grow(el) {
            if (!el) return;
            const scroller = document.getElementById('edscroll');
            const top = scroller ? scroller.scrollTop : 0;
            const y = window.scrollY;
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
            if (scroller) scroller.scrollTop = top;
            if (window.scrollY !== y) window.scrollTo(window.scrollX, y);
        },

        titleInput() {
            this.grow(this.$refs.title);
            if (!this.slugTouched) this.slug = slugify(this.title);
            this.changed();
        },

        bodyInput() {
            this.grow(this.$refs.body);
            this.changed();
        },

        slugInput() {
            const v = this.slug.toLowerCase().replace(/[^a-z0-9-]/g, '-');
            if (v !== this.slug) this.slug = v;
            this.slugTouched = true;
            this.changed();
        },

        setCat(value) {
            this.cat = value;
            this.tag = cfg.tags[value][0];
            this.$nextTick(() => {
                this.grow(this.$refs.title);
                this.grow(this.$refs.body);
                window.dispatchEvent(new Event('seg-place'));
            });
            this.changed();
        },

        setMode(value) {
            this.mode = value;
            this.$nextTick(() => {
                window.dispatchEvent(new Event('seg-place'));
                if (value === 'preview') {
                    this.loadPreview();
                } else {
                    this.grow(this.$refs.title);
                    this.grow(this.$refs.body);
                }
            });
        },

        async loadPreview() {
            const r = await api('POST', cfg.urls.preview, { body: this.body });
            if (r.ok) this.previewHtml = r.data.html;
        },

        changed() {
            this.saveText = 'Saqlanmoqda…';
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.save(false), 600);
            if (this.mode === 'preview') {
                clearTimeout(this.previewTimer);
                this.previewTimer = setTimeout(() => this.loadPreview(), 400);
            }
        },

        payload() {
            return {
                category: this.cat,
                title: this.title.trim(),
                slug: this.slug.trim().replace(/^-+|-+$/g, ''),
                tag: this.tag,
                excerpt: this.excerpt.trim(),
                body: this.body,
                published_at: this.date || cfg.today,
                cover: this.cover || null,
            };
        },

        applyState(post, reload) {
            this.status = post.status;
            this.hasPending = post.pending;
            this.url = post.url;
            this.slugTaken = post.slug_taken;
            if (!reload) return;
            const f = post.fields;
            this.cat = f.category;
            this.title = f.title;
            this.slug = f.slug;
            this.tag = cfg.tags[f.category].includes(f.tag) ? f.tag : cfg.tags[f.category][0];
            this.excerpt = f.excerpt;
            this.body = f.body;
            this.date = f.published_at || cfg.today;
            this.cover = f.cover;
            this.coverUrl = f.cover_url;
            this.slugTouched = this.status === 'published' ? true : !!(this.slug && this.slug !== slugify(this.title));
            this.$nextTick(() => {
                this.grow(this.$refs.title);
                this.grow(this.$refs.body);
            });
        },

        save(leaving = false) {
            clearTimeout(this.timer);
            this.timer = null;
            const run = async () => {
                if (!this.id && !this.title.trim() && !this.body.trim()) {
                    if (!leaving) this.saveText = '';
                    return null;
                }
                const url = this.id ? cfg.urls.edit + '/' + this.id : cfg.urls.store;
                const r = await api(this.id ? 'PUT' : 'POST', url, this.payload(), { keepalive: leaving });
                if (!r.ok) {
                    if (!leaving) this.saveText = 'Saqlab boʻlmadi';
                    return null;
                }
                const saved = r.data.post;
                if (!saved) {
                    this.saveText = '';
                    return null;
                }
                if (!this.id) {
                    this.id = saved.id;
                    history.replaceState(null, '', cfg.urls.edit + '/' + saved.id);
                }
                this.applyState(saved, false);
                if (!leaving) this.saveText = this.hasPending ? 'Oʻzgarishlar saqlandi' : 'Saqlandi';
                window.dispatchEvent(new CustomEvent('post-saved', { detail: saved }));
                return saved;
            };
            this.queue = this.queue.then(run, run);
            return this.queue;
        },

        focusWrite(ref) {
            if (this.mode !== 'write') this.setMode('write');
            this.$nextTick(() => this.$refs[ref].focus());
        },

        async publish() {
            await this.save(false);
            if (!this.id) {
                notify('Avval sarlavha va matn yozing');
                this.$refs.title.focus();
                return;
            }
            if (this.published && !this.hasPending) {
                notify('Oʻzgarish yoʻq');
                return;
            }
            if (!this.title.trim()) {
                notify('Sarlavha yozilmagan');
                this.focusWrite('title');
                return;
            }
            if (!this.body.trim()) {
                notify('Matn boʻsh');
                this.focusWrite('body');
                return;
            }
            if (this.slugTouched && this.slug && this.slugTaken) {
                notify('Bu havola band — boshqasini tanlang');
                this.sheet = true;
                return;
            }
            const r = await api('POST', cfg.urls.edit + '/' + this.id + '/publish', { strict: this.slugTouched });
            if (!r.ok) {
                if (r.data && r.data.errors && r.data.errors.slug) this.sheet = true;
                notify(firstError(r, 'Chop etib boʻlmadi'));
                return;
            }
            this.applyState(r.data.post, true);
            this.saveText = '';
            notify(r.data.message, r.data.post.url ? { label: 'Koʻrish', href: r.data.post.url } : null);
            window.dispatchEvent(new CustomEvent('post-saved', { detail: r.data.post }));
        },

        async discard() {
            clearTimeout(this.timer);
            const r = await api('POST', cfg.urls.edit + '/' + this.id + '/discard');
            if (!r.ok) {
                notify('Bekor qilib boʻlmadi');
                return;
            }
            this.applyState(r.data.post, true);
            this.saveText = '';
            notify(r.data.message);
            window.dispatchEvent(new CustomEvent('post-saved', { detail: r.data.post }));
        },

        async unpublish() {
            await this.save(false);
            const r = await api('POST', cfg.urls.edit + '/' + this.id + '/unpublish');
            if (!r.ok) {
                notify('Qoralamaga olib boʻlmadi');
                return;
            }
            this.applyState(r.data.post, true);
            this.saveText = '';
            notify(r.data.message);
            window.dispatchEvent(new CustomEvent('post-saved', { detail: r.data.post }));
        },

        async remove() {
            if (!this.id) return;
            const yes = await Alpine.store('dialog').ask({ title: 'Yozuvni oʻchirasizmi?', message: 'Bu amalni bekor qilib boʻlmaydi.', ok: 'Oʻchirish' });
            if (!yes) return;
            clearTimeout(this.timer);
            this.timer = null;
            await this.queue;
            const r = await api('DELETE', cfg.urls.edit + '/' + this.id);
            if (!r.ok) {
                notify('Oʻchirib boʻlmadi');
                return;
            }
            stash(r.data.message);
            window.location.href = r.data.redirect;
        },

        resize(file) {
            return new Promise((resolve, reject) => {
                const url = URL.createObjectURL(file);
                const img = new Image();
                img.onload = () => {
                    const scale = Math.min(1, 1600 / img.width);
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.round(img.width * scale);
                    canvas.height = Math.round(img.height * scale);
                    canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                    URL.revokeObjectURL(url);
                    canvas.toBlob((blob) => (blob ? resolve(blob) : reject()), 'image/jpeg', 0.82);
                };
                img.onerror = () => {
                    URL.revokeObjectURL(url);
                    reject();
                };
                img.src = url;
            });
        },

        async uploadCover(file) {
            if (!file || !/^image\//.test(file.type)) {
                notify('Rasm faylini tanlang');
                return;
            }
            if (file.size > 15 * 1024 * 1024) {
                notify('Rasm 15 MB dan katta');
                return;
            }
            let blob;
            try {
                blob = await this.resize(file);
            } catch (e) {
                notify('Rasmni oʻqib boʻlmadi');
                return;
            }
            const form = new FormData();
            form.append('cover', blob, 'cover.jpg');
            const r = await api('POST', cfg.urls.cover, form);
            if (!r.ok) {
                notify(firstError(r, 'Rasmni yuklab boʻlmadi'));
                return;
            }
            this.cover = r.data.path;
            this.coverUrl = r.data.url;
            this.changed();
            notify('Muqova yuklandi');
        },

        removeCover() {
            this.cover = '';
            this.coverUrl = null;
            this.changed();
        },

        md(kind) {
            const ta = this.$refs.body;
            if (!ta || this.mode !== 'write') return;
            const s = ta.selectionStart;
            const e = ta.selectionEnd;
            const v = ta.value;
            const sel = v.slice(s, e);
            const wrap = (a, b, placeholder) => {
                const t = sel || placeholder;
                ta.setRangeText(a + t + b, s, e, 'end');
                ta.setSelectionRange(s + a.length, s + a.length + t.length);
            };
            const lines = (prefix, placeholder) => {
                const start = v.lastIndexOf('\n', s - 1) + 1;
                const t = v.slice(start, e) || placeholder;
                ta.setRangeText(t.split('\n').map((l) => prefix + l).join('\n'), start, e, 'end');
            };
            const block = (text, from, length) => {
                const before = s > 0 && v[s - 1] !== '\n' ? '\n\n' : '';
                const after = v[e] && v[e] !== '\n' ? '\n\n' : '\n';
                ta.setRangeText(before + text + after, s, e, 'end');
                const start = s + before.length + from;
                ta.setSelectionRange(start, start + length);
            };
            ta.focus();
            if (kind === 'h2') lines('## ', 'Boʻlim sarlavhasi');
            else if (kind === 'bold') wrap('**', '**', 'qalin matn');
            else if (kind === 'italic') wrap('*', '*', 'kursiv');
            else if (kind === 'code') wrap('`', '`', 'kod');
            else if (kind === 'link') wrap('[', '](https://)', 'havola matni');
            else if (kind === 'list') lines('- ', 'Element');
            else if (kind === 'quote') lines('> ', 'Iqtibos');
            else if (kind === 'block') {
                const t = sel || "echo 'Salom';";
                block('```php\n' + t + '\n```', 7, t.length);
            } else if (kind === 'image') block('![rasm tavsifi](https://)', 2, 12);
            ta.dispatchEvent(new Event('input'));
        },

        bodyKey(e) {
            if (!(e.ctrlKey || e.metaKey)) return;
            const k = e.key.toLowerCase();
            if (k === 'b') {
                e.preventDefault();
                this.md('bold');
            } else if (k === 'i') {
                e.preventDefault();
                this.md('italic');
            }
        },
    };
});

Alpine.data('projectForm', (cfg) => ({
    id: cfg.id,
    name: cfg.name,
    repo: cfg.repo,
    description: cfg.description,
    stack: cfg.stack,
    demo: cfg.demo,

    get stackList() {
        return this.stack.split(',').map((s) => s.trim()).filter(Boolean);
    },

    get initials() {
        return initials(this.name.trim() || 'Loyiha nomi');
    },

    async remove() {
        const yes = await Alpine.store('dialog').ask({ title: 'Loyihani oʻchirasizmi?', message: 'Loyiha Dasturlash boʻlimidan ham olib tashlanadi.', ok: 'Oʻchirish' });
        if (!yes) return;
        const r = await api('DELETE', cfg.deleteUrl);
        if (!r.ok) {
            notify('Oʻchirib boʻlmadi');
            return;
        }
        stash(r.data.message);
        window.location.href = r.data.redirect;
    },
}));

Alpine.data('settingsForm', (cfg) => ({
    bio: cfg.bio,
    open: cfg.open,
    github: cfg.github,
    savedGithub: cfg.github,
    now: cfg.now,
    learn: cfg.learn.map((l) => ({ n: l.n, p: l.n ? l.p : '' })),
    tg: cfg.telegram,
    saveText: '',
    saved: false,
    timer: null,

    init() {
        window.addEventListener('beforeunload', () => {
            if (this.timer) this.save(true);
        });
    },

    pct(v) {
        return Math.max(0, Math.min(100, Math.round(+v || 0)));
    },

    bar(i) {
        return this.learn[i].n.trim() ? this.pct(this.learn[i].p) : 0;
    },

    schedule() {
        this.saveText = 'Saqlanmoqda…';
        this.saved = false;
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.save(false), 400);
    },

    async save(leaving = false) {
        clearTimeout(this.timer);
        this.timer = null;
        const payload = {
            bio: this.bio,
            open: this.open,
            github: this.github,
            now: this.now.map((n) => ({ k: n.k, v: n.v })),
            learn: this.learn.map((l) => ({ n: l.n.trim(), p: l.n.trim() ? this.pct(l.p) : 0 })),
            telegram: { u: this.tg.u, n: this.tg.n, posts: this.tg.posts.map((p) => ({ t: p.t, d: p.d || null })) },
        };
        const r = await api('PUT', cfg.url, payload, { keepalive: leaving });
        if (leaving) return;
        if (!r.ok) {
            this.saveText = r.status === 422 ? firstError(r, 'Saqlab boʻlmadi') : 'Saqlab boʻlmadi';
            this.saved = false;
            return;
        }
        this.tg.u = r.data.settings.telegram.u;
        this.saveText = 'Saqlandi';
        this.saved = true;
    },

    saveGithub() {
        const v = this.github.trim().replace(/^@/, '').replace(/^https?:\/\/(www\.)?github\.com\//i, '').replace(/\/.*$/, '');
        this.github = v;
        if (v === this.savedGithub) return;
        this.savedGithub = v;
        this.save(false);
        notify(v ? 'GitHub ulandi' : 'GitHub username olib tashlandi', { label: 'Faollik', href: cfg.activityUrl });
    },

    toggleOpen() {
        this.open = !this.open;
        this.save(false);
    },
}));

Alpine.start();
