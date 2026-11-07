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
