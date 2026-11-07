# frontend

Next.js (App Router), TypeScript, Tailwind. Umumiy qoidalar ildizdagi `../CLAUDE.md` da, API shartnomasi `../docs/api.md` da.

## Qoidalar
- Ommaviy sahifalar server komponentlari: SSR/ISR, `generateMetadata`, OG, `sitemap.xml`, `rss.xml` shu yerda quriladi.
- Admin (`/admin`) klient tomonda, login bilan himoyalangan, `noindex`.
- API chaqiruvlari faqat `lib/api` qatlamida, turlar `../docs/api.md` ga mos.
- `/api/revalidate` maxfiy kalit bilan tag bo'yicha keshni yangilaydi.
- Dizayn tokenlari va komponentlar `../design/utkirbek-blog-bento.html` prototipidan olinadi, matnlar o'zgarmaydi.
- Test: Playwright, desktop va mobil.

## Next.js 16 eslatmalari
- `cacheComponents` yoqilgan: `export const dynamic` ishlamaydi. Keshlanadigan ma'lumot `lib/api/index.ts` dagi `"use cache"` + `cacheLife` + `cacheTag` funksiyalaridan olinadi. Teglar `../docs/api.md` revalidate bo'limiga mos: `posts`, `post:{slug}`, `projects`, `settings`.
- So'rov vaqtida hisoblanadigan qism `<Suspense>` ichida bo'ladi (build paytida API kerak emas).
- Kod yozishdan oldin `node_modules/next/dist/docs/` dagi tegishli qo'llanmani o'qing (`AGENTS.md`).

## Ishga tushirish
- `npm run dev`: `http://localhost:3000`, backend `API_URL` (birlamchi `http://localhost`).
- `API_MOCK=1` backend'siz mock ma'lumot bilan ishlaydi.
- Tekshiruv: `npx tsc --noEmit`, `npm run lint`, `npx playwright test` (production build'ni 3100 portda ishga tushiradi).

## Layout (F2)
- Ommaviy sahifalar `app/(site)/` guruhida: `SiteNav` (`tnav` + scroll'da `tmini`), ⌘K paneli (`/api/search` route handler orqali, mock ham ishlaydi), kursorga ergashuvchi nur (`Glow`), footer. Admin keyin alohida guruhda.
- Faol bo'lim URL'dan olinadi. `/post/[slug]` uchun bo'lim (fikr yoki dev) F4'da post kategoriyasidan beriladi.
- Telefon chegarasi nav va chekkalar uchun 760px (prototipdagidek).

## Sahifalar (F3)
- Dizayn CSS'i `app/site.css` da: prototipning ommaviy sayt qoidalari (admin va post sahifa qoidalarisiz) deyarli o'zgarishsiz ko'chirilgan. Sinf nomlari prototipdagi bilan bir xil, shuning uchun JSX shu sinflardan foydalanadi. Tailwind bilan nomi to'qnashgan sinflar (`.ring`) shu faylda bekor qilinadi.
- Faollik, Actions, loyiha GitHub ma'lumoti va Telegram endpointlari (`docs/api.md`) backend B5'da quriladi. Hozircha javob 404 bo'lsa sahifalar "ma'lumot yo'q" holatini ko'rsatadi; `API_MOCK=1` namuna ma'lumot beradi.
- So'rov vaqtiga bog'liq (soat, "oldin") komponentlar `lib/time.ts` dagi `requestTime()` orqali `Suspense` ichida ishlaydi.

## Post sahifasi va SEO (F4)
- `/post/[slug]`: backend'ning `body_html` (allaqachon tozalangan) `lib/post-html.ts` orqali kod bloklari bilan boyitiladi, nusxalash `Prose` komponentida. Mundarija faqat dasturlash postlarida (2 va undan ko'p bo'lim).
- Yo'q post uchun to'g'ri 404 statusi `proxy.ts` da beriladi (oqim boshlangandan keyin `notFound()` statusni o'zgartirmaydi). Noma'lum manzillar `app/(site)/[...rest]` orqali sayt layout'i bilan 404 beradi.
- Sahifa `metadata.alternates` ildizdagini to'liq almashtiradi, shuning uchun `pageAlternates()` (canonical + RSS) ishlatiladi. `openGraph` uchun ham shunday: `siteName` va `locale` har safar yoziladi.
- `/fikrlar` Telegram kanal postlarini ko'rsatadi (`api.getTelegramFeed()` → `GET /telegram/posts`), kartalar t.me havolasiga olib boradi, qidiruv mijoz tomonda. Bazadagi `fikr` yozuvlari bu sahifada chiqmaydi.
- `sitemap.xml`, `rss.xml` route handler'lari, `robots.ts`. `SITE_URL` `.env` da (mutlaq URL'lar uchun).

## Admin (F5)
- `app/(admin)/admin/` guruhi to'liq klient tomonda, `noindex`. Kirish faqat parol bilan: `lib/admin/api.ts` Sanctum cookie + CSRF (`/sanctum/csrf-cookie` → `X-XSRF-TOKEN`) bilan ishlaydi, 419 bo'lsa bir marta qayta urinadi, 401 bo'lsa login'ga qaytaradi. `NEXT_PUBLIC_API_URL` backend manzili (build vaqtida o'rnatiladi).
- Muharrir avtosaqlashi 600ms; so'rovlar ketma-ket (navbat), yangi yozuv birinchi bo'sh bo'lmagan o'zgarishda yaratiladi. Chop etilgan yozuv o'zgarishlari `pending`ga yoziladi ("Yangilash" qo'llaydi, "Bekor qilish" o'chiradi). Ctrl+S saqlaydi, Ctrl+Enter chop etadi/yangilaydi, `N` yangi yozuv.
- `app/admin.css`: prototipning admin qoidalari (iOS tokenlari faqat `#admin` ichida).
- Testlar: `e2e/admin.spec.ts` tarmoqni `e2e/admin-mock.ts` (holatli soxta API) bilan almashtiradi, shuning uchun backend kerak emas. Haqiqiy backend bilan jonli tekshiruv qo'lda o'tkazilgan (vaqtinchalik admin yaratib).

- Loyihalar ro'yxatida "GitHub'dan yuklash" (`use-sync-projects.ts`): `POST /admin/projects/sync`, keyin ro'yxat qayta yuklanadi.

## Xavfsizlik va keshni yangilash (B6)
- `/api/revalidate` (POST): `REVALIDATE_SECRET` (vaqt bo'yicha doimiy solishtirish), faqat `posts`, `projects`, `settings`, `post:{slug}` teglari, 60/daqiqa. `revalidateTag(tag, { expire: 0 })` bilan darhol yangilaydi.
- `/api/search` va `/api/posts` IP bo'yicha 120/daqiqa (`lib/rate-limit.ts`, xotirada, har instansda alohida; `x-forwarded-for` ishonchli proksi orqali keladi deb hisoblanadi).
- `next.config.ts` sarlavhalari: CSP (`script-src` Next'ning inline skriptlari va mavzu skripti uchun `'unsafe-inline'`; nonce bilan qat'iylashtirish har so'rovni dinamik qilib statik qobiqni buzgani uchun ishlatilmagan), `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, productionda HSTS. `connect-src` va `img-src` ga `NEXT_PUBLIC_API_URL` kiradi.
- Server-server so'rovlari `X-Frontend-Secret` yuboradi (backend rate limiti uchun istisno). Kalit hech qachon `NEXT_PUBLIC_` bo'lmasligi kerak.
- Testlarda har bir test o'z mijoz IP'sini oladi (`e2e/client.ts`), aks holda umumiy limitga tushadi. Sahifa gidratsiyasi tugaganini `html[data-ready]` bildiradi.
- Tashqi manbali ma'lumotlar (`getActivity`, `getRuns`, `getRepoInfo`, `getTelegram`) keshi daqiqalar bilan o'lchanadi (`cacheLife("minutes")`): backend ham keshlaydi va webhook mahalliy muhitda yetib bormasligi mumkin, soatlik kesh eski ma'lumotni uzoq ko'rsatib qo'yardi.
