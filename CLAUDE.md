# personal-blog (blog-tulqinov)

Utkirbek'ning shaxsiy blog va portfolio sayti. Monorepo: `backend/` (Laravel API) va `frontend/` (Next.js). Prototip: `design/utkirbek-blog-bento.html` (bitta fayl, sayt + admin, localStorage'da ishlaydi). Vazifa: shuni ikki qismga ajratib qurish. Dizayn va matnlar prototipdan o'zgarmaydi.

## Tuzilma
- `backend/`: Laravel 12, PHP 8.3, faqat JSON API (Blade yo'q), MySQL, Redis, Sail
- `frontend/`: Next.js (App Router), TypeScript, Tailwind, SSR/ISR
- `design/`: prototip (o'zgartirilmaydi, faqat andoza)
- `docs/`: `plan.md` (bosqichlar), `api.md` (API shartnomasi)
- Har qism o'zining `CLAUDE.md` faylini oladi (`backend/CLAUDE.md`, `frontend/CLAUDE.md`), bu fayl umumiy qoidalar.

## Umumiy qoidalar
- Foydalanuvchi bilan muloqot va UI matnlari o'zbek tilida (lotin).
- Kodga izoh yozilmaydi. Fayllar toza va ishga tayyor bo'ladi.
- Har bosqich alohida branch va commit. Bosqichni tugatmay keyingisiga o'tilmaydi.
- Avval reja, keyin kod. Katta o'zgarishdan oldin `docs/plan.md` va `docs/api.md` bilan solishtiring.
- `docs/api.md` yagona manba: endpoint yoki javob shakli o'zgarsa, avval shu fayl yangilanadi, keyin backend va frontend.
- Sirlar (`.env`, tokenlar) hech qachon commit qilinmaydi. Har qismda `.env.example` bo'ladi.

## Backend qoidalari
- Controller yupqa: mantiq Action/Service klasslarida. Validatsiya FormRequest'da. Javoblar API Resource orqali.
- Admin faqat bitta foydalanuvchi (ega). Sanctum SPA cookie auth, CSRF, login uchun rate limit.
- Markdown serverda HTML'ga aylanadi va tozalanadi (XSS). Javobda `body_html`, `toc`, `read_minutes` bo'ladi.
- Tashqi API (GitHub, Telegram) keshlanadi, token faqat `.env`'da.
- Test: Pest. Har endpoint uchun kamida muvaffaqiyat, validatsiya va ruxsat testi.

## Frontend qoidalari
- Ommaviy sahifalar server komponentlari: SSR/ISR, `generateMetadata`, OG, `sitemap.xml`, `rss.xml`.
- Admin (`/admin`) klient tomonda, login bilan himoyalangan, `noindex`.
- API chaqiruvlari bitta `lib/api` qatlamida, turlar `docs/api.md` ga mos.
- Chop etilganda backend frontend'ga revalidate so'rovi yuboradi (on-demand ISR).
- Test: Playwright (asosiy sahifalar va admin oqimi, desktop va mobil).

## Dizayn (Bento Pro)
- Qorong'i mavzu birlamchi, yorug' mavzu ham bor. Faqat qora/oq/kulrang. Rang faqat holat uchun (yashil/to'q sariq/qizil).
- Shriftlar: Inter (UI), serif (post sarlavhalari va fikr matni), mono (kod).
- Kartalar `.c`, sarlavhasi `.ck`, kursorga ergashuvchi nur (glow). Navbar: `tnav` + scroll'da `tmini`. ⌘K komanda paneli.
- Aniq token qiymatlari prototipdagi `body{--bg,--card,--line,--t1,--t2,--t3,--chip,--inv...}` bo'limida.
- Responsiv: ≤900px telefon (pastda navigatsiya), 901–1180px ixcham sidebar, >1180px to'liq.

## Ma'lumotlar modeli
- posts: id, category (fikr|dev), tag, title, slug (unique), status (draft|published), published_at, cover, excerpt, body (markdown), pending (json, kutilayotgan tahrir), timestamps
- projects: id, name, repo (owner/repo), description, stack (json), demo_url
- settings: key/value (github, open, bio, now[4]{k,v}, learn[3]{n,p}, telegram{u,n,posts[2]{t,d}})

## Sahifalar (frontend)
Sayt: `/`, `/fikrlar`, `/dasturlash` (+ `/yozuvlar`, `/loyihalar`, `/faollik`), `/men`, `/post/[slug]`, `/rss.xml`, `/sitemap.xml`
Admin: `/admin/login`, `/admin` (dashboard), `/admin/posts`, `/admin/posts/[id]`, `/admin/projects`, `/admin/settings`

## Admin xususiyatlari
Avtosaqlash (600ms), qoralama/chop etilgan/kutilayotgan o'zgarish ("Yangilash"), kategoriya, muqova, mavzu sozlamasi, GitHub va Telegram sozlamalari, `N` yangi yozuv, `Ctrl+S`, `Ctrl+Enter`.

## Deploy
Bitta VPS, Docker: nginx, Laravel (php-fpm), Next.js (node), MySQL, Redis. `domen.uz` frontend, `api.domen.uz` backend (Sanctum cookie uchun bir xil asosiy domen). GitHub Actions orqali avtomatik deploy.
