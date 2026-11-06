# personal-blog (blog-tulqinov)

Utkirbek'ning shaxsiy blog va portfolio sayti. Prototip: `design/utkirbek-blog-bento.html` (bitta fayl, sayt + admin, localStorage'da ishlaydi). Vazifa: shuni Laravel'ga o'tkazish. Dizayn va matnlar prototipdan o'zgarmaydi.

## Stek
- Laravel 11, PHP 8.3, Blade, Tailwind (yoki prototipdagi CSS tokenlari), Alpine.js
- MySQL, Redis (kesh), Docker/Sail
- Test: Pest yoki PHPUnit, Playwright (ko'rinish tekshiruvi)

## Qoidalar
- Foydalanuvchi bilan muloqot va UI matnlari o'zbek tilida (lotin).
- Kodga izoh yozilmaydi. Fayllar toza va ishga tayyor bo'ladi.
- Har bosqich alohida branch va commit. Bosqichni tugatmay keyingisiga o'tilmaydi.
- Avval reja, keyin kod. Katta o'zgarishdan oldin `docs/plan.md` bilan solishtiring.
- Sirlar (`.env`, tokenlar) hech qachon commit qilinmaydi.
- Controller yupqa: mantiq Action/Service klasslarida. Validatsiya FormRequest'da.
- Admin faqat bitta foydalanuvchi uchun (egasi). Parol hash bilan, `auth` middleware, CSRF, rate limit.
- Markdown render'da XSS'dan himoya (sanitize).

## Dizayn (Bento Pro)
- Qorong'i mavzu birlamchi, yorug' mavzu ham bor. Faqat qora/oq/kulrang. Rang faqat holat uchun (yashil/to'q sariq/qizil).
- Shriftlar: Inter (UI), serif (post sarlavhalari va fikr matni), mono (kod).
- Kartalar `.c`, sarlavhasi `.ck`, kursorga ergashuvchi nur (glow). Navbar: `tnav` + scroll'da `tmini`. ⌘K komanda paneli.
- Aniq token qiymatlari prototipdagi `body{--bg,--card,--line,--t1,--t2,--t3,--chip,--inv...}` bo'limida.
- Responsiv: ≤900px telefon (pastda navigatsiya), 901–1180px ixcham sidebar, >1180px to'liq.

## Ma'lumotlar modeli (prototipdan)
- posts: id, category (fikr|dev), tag, title, slug (unique), status (draft|published), published_at, cover, excerpt, body (markdown), pending (json, kutilayotgan tahrir), timestamps
- projects: id, name, repo (owner/repo), description, stack (json), demo_url
- settings: key/value (github, open, bio, now[4]{k,v}, learn[3]{n,p}, telegram{u,n,posts[2]{t,d}})

## Marshrutlar
Sayt: `/`, `/fikrlar`, `/dasturlash` (+ `/yozuvlar`, `/loyihalar`, `/faollik`), `/men`, `/post/{slug}`, `/rss.xml`, `/sitemap.xml`
Admin: `/admin/login`, `/admin` (dashboard), `/admin/posts`, `/admin/posts/{id}`, `/admin/projects`, `/admin/settings`

## Admin xususiyatlari
Avtosaqlash (600ms), qoralama/chop etilgan/kutilayotgan o'zgarish ("Yangilash"), kategoriya, muqova, mavzu sozlamasi, GitHub va Telegram sozlamalari, `N` yangi yozuv, `Ctrl+S`, `Ctrl+Enter`.

## Tashqi API
- GitHub: faollik xaritasi va Actions (keshlangan, token `.env`'da)
- Telegram kanal: so'nggi postlar (keshlangan)
