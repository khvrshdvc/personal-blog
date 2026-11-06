# Bosqichlar

Har bosqich alohida branch, commit va qo'lda tekshiruv bilan yopiladi. Backend (B) va frontend (F) parallel yuradi, lekin ikkalasi ham `docs/api.md` ga tayanadi.

## 0. Tayyorgarlik
- Monorepo tuzilmasi: `backend/`, `frontend/`, ildizda `docker-compose.yml`, `.gitignore`, `.editorconfig`
- `backend/CLAUDE.md` va `frontend/CLAUDE.md`
- `docs/api.md` ni tasdiqlash

## Backend (B)
- B1. Laravel 11 + Sail (MySQL, Redis), `.env.example`, CORS, Sanctum SPA auth, bitta admin seeder
- B2. Migratsiyalar, modellar, seeder (prototipdagi namuna ma'lumotlar): posts, projects, settings
- B3. Ommaviy API: posts, post, projects, settings, search; Markdown to HTML (tozalash, mundarija, o'qish vaqti)
- B4. Admin API: login/logout/me, dashboard, posts CRUD, avtosaqlash va pending oqimi, publish/apply, cover yuklash, projects CRUD, settings
- B5. Tashqi API: GitHub faollik va Actions, Telegram kanal, keshlash
- B6. Sifat: Pest testlar, rate limit, xavfsizlik tekshiruvi, revalidate webhook

## Frontend (F)
- F1. Next.js + TypeScript + Tailwind, dizayn tokenlari, shriftlar, mavzu almashtirish, `lib/api` (vaqtincha mock)
- F2. Layout: tnav, tmini, mobil navigatsiya, ⌘K paneli, karta komponentlari
- F3. Bosh sahifa (bento), Fikrlar, Dasturlash, Men
- F4. Post sahifasi (HTML render, mundarija, o'qish vaqti), SEO (metadata, OG, sitemap, RSS)
- F5. Admin: login, dashboard, yozuvlar ro'yxati va muharrir (avtosaqlash, pending), loyihalar, sozlamalar, mobil moslashuv
- F6. Playwright testlar, a11y, tezlik

## Integratsiya
- I1. Mock'dan haqiqiy API'ga o'tish
- I2. On-demand revalidate (chop etilganda keshni yangilash)
- I3. End-to-end tekshiruv: yozuv yozish, chop etish, saytda ko'rinishi

## Deploy
- D1. Docker image'lar, nginx, SSL (Let's Encrypt)
- D2. GitHub Actions: build, test, serverga deploy
- D3. Zaxira nusxa (DB), monitoring, 404/500 sahifalar

## Ochiq qarorlar
- Hosting: VPS yoki Hostinger
- Domen nomi
