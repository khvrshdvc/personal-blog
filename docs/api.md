# API shartnomasi

Asosiy manzil: `/api`. Format: JSON. Sanalar ISO 8601. Xatolar: `{ "message": "...", "errors": { "field": ["..."] } }` (422), 401, 403, 404. Barcha javoblar JSON. Yagona resurs va ro'yxatlar o'ramasiz qaytadi (`{data}` faqat `/posts` ro'yxatida).

## Turlar

Post (ro'yxat):
`id, category ("fikr"|"dev"), tag, title, slug, excerpt, cover, published_at, read_minutes`

Post (to'liq): ro'yxat maydonlari + `body_html, toc [{id, text, level}]`

Post (admin): `id, category, tag, title, slug, status ("draft"|"published"), excerpt, cover, body, published_at, updated_at, pending (null | {category, title, tag, slug, excerpt, cover, body, published_at}), words`. Chop etilgan postda asosiy maydonlar saytdagi holat, `pending` esa kutilayotgan tahrir. `words` joriy (pending bo'lsa pending) matn bo'yicha. Etiketlar kategoriyaga bog'liq: `fikr`: Hayot, Oʻsish, Ustozlik, Til; `dev`: Laravel, PHP, SQL, Docker. Boshqa etiket birinchisiga almashtiriladi.

Post (qisqa, dashboard): `id, title, excerpt, tag, status ("draft"|"published"|"pending"), updated_at`. `pending`: chop etilgan va kutilayotgan tahriri bor.

Project: `id, name, repo, description, stack [string], demo_url`

Settings (ommaviy): `github, open, bio, now [{k, v}], learn [{n, p}], telegram {u, n, posts [{t, d}]}`

## Ommaviy

| Metod | Yo'l | Izoh |
|---|---|---|
| GET | `/posts` | Chop etilganlar, yangisi birinchi. Query: `category` (`fikr`\|`dev`), `tag`, `q`, `page`, `per_page` (birlamchi 12, eng ko'pi 50). Javob: `{data: [Post], meta: {current_page, last_page, per_page, total}}` |
| GET | `/posts/{slug}` | To'liq post. `prev` (bir xil kategoriyadagi oldingi, eskiroq) va `next` (keyingi, yangiroq): ro'yxat shaklidagi post yoki `null`. Chop etilmagan bo'lsa 404 |
| GET | `/tags` | `[{tag, count}]`, soni bo'yicha kamayish tartibida. Query: `category` |
| GET | `/search?q=` | ⌘K uchun, eng ko'pi 8 ta: `[{type: "post"\|"project", category: "fikr"\|"dev"\|null, title, subtitle, path}]` (`category` faqat postda). `q` bo'sh bo'lsa so'nggi 5 ta post. `path` frontend yo'li |
| GET | `/projects` | `[Project]` |
| GET | `/settings` | Ommaviy sozlamalar (yuqoridagi Settings shakli) |
| GET | `/activity` | GitHub faollik xaritasi `{days: [{d, c}], total}` (keshlangan) |
| GET | `/projects/{id}/runs` | GitHub Actions natijalari (keshlangan) |
| GET | `/telegram` | Kanal so'nggi postlari (keshlangan) |

## Autentifikatsiya (Sanctum SPA, cookie)

| Metod | Yo'l | Izoh |
|---|---|---|
| GET | `/sanctum/csrf-cookie` | CSRF cookie olish |
| POST | `/admin/login` | `{password}`. 204, xato bo'lsa 422. Rate limit |
| POST | `/admin/logout` | 204 |
| GET | `/admin/me` | `{name}` yoki 401 |

## Admin (auth talab qilinadi)

| Metod | Yo'l | Izoh |
|---|---|---|
| GET | `/admin/dashboard` | Javob: `{counts: {published, thoughts, dev, drafts, pending, words, projects}, last_edited: Post(qisqa)\|null, resume: [Post(qisqa)] (eng ko'pi 4, qoralama va kutilayotgan, last_edited'siz), activity: {days: [{d, c}] (oxirgi 182 kun, d: sana), active_days, this_month, days_since_last}, tags: [{tag, count}], checks: [{key: "github"\|"telegram"\|"bio"\|"learn"\|"open", ok, value}], projects: [{id, name, repo}] (4 ta)}` |
| GET | `/admin/posts` | Barchasi. Query: `category`, `q` |
| POST | `/admin/posts` | Yangi yozuv (qoralama), 201. `{category, title?, body?}` |
| GET | `/admin/posts/{id}` | Admin ko'rinishi |
| PATCH | `/admin/posts/{id}` | Avtosaqlash, qisman maydonlar. Qoralamada to'g'ridan-to'g'ri (havola band bo'lsa raqam qo'shiladi), chop etilganda `pending` ga yoziladi (asosiy bilan bir xil bo'lsa `pending: null`). Javob: Post (admin) |
| POST | `/admin/posts/{id}/publish` | Qoralamani chop etish. 422: sarlavha yoki matn bo'sh, havola band; allaqachon chop etilgan bo'lsa 409. Javob: Post (admin) |
| POST | `/admin/posts/{id}/apply` | `pending` ni asosiy maydonlarga qo'llash ("Yangilash"). `pending` yo'q bo'lsa 409 |
| DELETE | `/admin/posts/{id}/pending` | Kutilayotgan o'zgarishni bekor qilish. Javob: Post (admin) |
| POST | `/admin/posts/{id}/unpublish` | Qoralamaga qaytarish (`pending` bo'lsa unga ham o'tadi) |
| DELETE | `/admin/posts/{id}` | O'chirish, 204 |
| POST | `/admin/preview` | `{body}` → `{html}`: muharrir ko'rinishi uchun Markdown |
| POST | `/admin/uploads` | Muqova rasmi (multipart, maydon `file`, JPG/PNG/WebP, 5 MB gacha). Javob: `{url, path}`. `cover` maydoniga `path` (`covers/...`) yoki `https://` manzil yoziladi |
| GET | `/admin/projects` | Ro'yxat |
| POST | `/admin/projects` | `{name, repo?, description?, stack? [string] (8 tagacha), demo_url?}`, 201. Javob: Project |
| PATCH | `/admin/projects/{id}` | Tahrirlash (qisman). Javob: Project |
| DELETE | `/admin/projects/{id}` | O'chirish, 204 |
| GET | `/admin/settings` | Barcha sozlamalar (Settings shakli) |
| PUT | `/admin/settings` | To'liq sozlamalar: `now` aynan 4 ta, `learn` aynan 3 ta (`p` 0–100), `telegram.posts` aynan 2 ta. Javob: Settings |

## Revalidate

Backend chop etish, yangilash, o'chirish va sozlama o'zgarganda frontend'ga `POST {FRONTEND_URL}/api/revalidate` yuboradi: `{secret, tags: ["posts", "post:{slug}", "projects", "settings"]}`. Maxfiy kalit ikkala `.env` da bir xil.

## Frontend yo'llari (API'ga bog'liq emas, Next.js o'zi)

`/rss.xml` va `/sitemap.xml` frontend ichida `/posts` ma'lumotidan quriladi.
