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
