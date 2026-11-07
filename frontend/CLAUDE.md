# frontend

Next.js (App Router), TypeScript, Tailwind. Umumiy qoidalar ildizdagi `../CLAUDE.md` da, API shartnomasi `../docs/api.md` da.

## Qoidalar
- Ommaviy sahifalar server komponentlari: SSR/ISR, `generateMetadata`, OG, `sitemap.xml`, `rss.xml` shu yerda quriladi.
- Admin (`/admin`) klient tomonda, login bilan himoyalangan, `noindex`.
- API chaqiruvlari faqat `lib/api` qatlamida, turlar `../docs/api.md` ga mos.
- `/api/revalidate` maxfiy kalit bilan tag bo'yicha keshni yangilaydi.
- Dizayn tokenlari va komponentlar `../design/utkirbek-blog-bento.html` prototipidan olinadi, matnlar o'zgarmaydi.
- Test: Playwright, desktop va mobil.
