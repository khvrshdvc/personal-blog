# API shartnomasi

Asosiy manzil: `/api`. Format: JSON. Sanalar ISO 8601. Xatolar: `{ "message": "...", "errors": { "field": ["..."] } }` (422), 401, 403, 404.

## Turlar

Post (ro'yxat):
`id, category ("fikr"|"dev"), tag, title, slug, excerpt, cover, published_at, read_minutes`

Post (to'liq): ro'yxat maydonlari + `body_html, toc [{id, text, level}]`

Post (admin): `id, category, tag, title, slug, status ("draft"|"published"), excerpt, cover, body, published_at, updated_at, pending (null | {title, tag, excerpt, cover, body, slug}), words`

Project: `id, name, repo, description, stack [string], demo_url`

Settings (ommaviy): `github, open, bio, now [{k, v}], learn [{n, p}], telegram {u, n, posts [{t, d}]}`

## Ommaviy

| Metod | Yo'l | Izoh |
|---|---|---|
| GET | `/posts` | Chop etilganlar. Query: `category`, `tag`, `q`, `page`, `per_page`. Javob: `{data, meta}` |
| GET | `/posts/{slug}` | To'liq post, `prev` va `next` bilan |
| GET | `/tags` | `[{tag, count}]` |
| GET | `/search?q=` | ⌘K uchun: postlar va loyihalar, eng ko'pi 8 ta |
| GET | `/projects` | Loyihalar ro'yxati |
| GET | `/settings` | Ommaviy sozlamalar |
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
| GET | `/admin/dashboard` | Statistika, oxirgi tahrir, davom ettirish, faollik, mavzular, sayt holati |
| GET | `/admin/posts` | Barchasi. Query: `category`, `q` |
| POST | `/admin/posts` | Yangi yozuv (qoralama). `{category, title?, body?}` |
| GET | `/admin/posts/{id}` | Admin ko'rinishi |
| PATCH | `/admin/posts/{id}` | Avtosaqlash. Qoralamada to'g'ridan-to'g'ri, chop etilganda `pending` ga yoziladi |
| POST | `/admin/posts/{id}/publish` | Qoralamani chop etish |
| POST | `/admin/posts/{id}/apply` | `pending` ni asosiy maydonlarga qo'llash ("Yangilash") |
| DELETE | `/admin/posts/{id}/pending` | Kutilayotgan o'zgarishni bekor qilish |
| POST | `/admin/posts/{id}/unpublish` | Qoralamaga qaytarish |
| DELETE | `/admin/posts/{id}` | O'chirish |
| POST | `/admin/uploads` | Muqova rasmi (multipart). Javob: `{url}` |
| GET | `/admin/projects` | Ro'yxat |
| POST | `/admin/projects` | `{name, repo, description, stack, demo_url}` |
| PATCH | `/admin/projects/{id}` | Tahrirlash |
| DELETE | `/admin/projects/{id}` | O'chirish |
| GET | `/admin/settings` | Barcha sozlamalar |
| PUT | `/admin/settings` | To'liq sozlamalar |

## Revalidate

Backend chop etish, yangilash, o'chirish va sozlama o'zgarganda frontend'ga `POST {FRONTEND_URL}/api/revalidate` yuboradi: `{secret, tags: ["posts", "post:{slug}", "projects", "settings"]}`. Maxfiy kalit ikkala `.env` da bir xil.

## Frontend yo'llari (API'ga bog'liq emas, Next.js o'zi)

`/rss.xml` va `/sitemap.xml` frontend ichida `/posts` ma'lumotidan quriladi.
