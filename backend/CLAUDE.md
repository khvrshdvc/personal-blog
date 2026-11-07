# backend

Laravel 12, PHP 8.3. Faqat JSON API, Blade yo'q. MySQL, Redis, Sail. Umumiy qoidalar ildizdagi `../CLAUDE.md` da, API shartnomasi `../docs/api.md` da.

## Qoidalar
- Controller yupqa: mantiq `app/Actions` va `app/Services` da. Validatsiya FormRequest'da. Javoblar API Resource orqali.
- Admin faqat bitta foydalanuvchi: `ADMIN_EMAIL` va `ADMIN_PASSWORD` (`.env`) seeder orqali yoziladi. Kirish faqat parol bilan, Sanctum SPA cookie, CSRF, login uchun rate limit.
- Markdown serverda HTML'ga aylanadi va tozalanadi. Javobda `body_html`, `toc`, `read_minutes`.
- GitHub va Telegram javoblari keshlanadi, tokenlar faqat `.env` da.
- Chop etish, yangilash, o'chirish va sozlama o'zgarganda frontend'ga revalidate so'rovi yuboriladi.
- Test: Pest. Har endpoint uchun muvaffaqiyat, validatsiya va ruxsat testi.
- Har yangi endpoint avval `../docs/api.md` ga yoziladi.

## Mahalliy sozlash
- Muqova rasmlari `public` diskka yoziladi. Brauzerda ochilishi uchun bir marta `sail artisan storage:link` kerak (production deploy'da ham).
- Admin SPA (frontend) cookie bilan ishlaydi: `.env` da `FRONTEND_URL` va `SANCTUM_STATEFUL_DOMAINS` frontend manziliga mos bo'lishi shart (mahalliy: `localhost:3000`).

## Tashqi API (B5)
- `GET /api/activity|runs|telegram` va `/api/projects/{id}/runs|github` (`docs/api.md`). Manba bitta: `ActivityProvider` (GitHub: foydalanuvchi, repolar, Actions, yillik kalendar). Natija `ResilientCache` bilan keshlanadi: yangi (`services.github.fresh`), xato bo'lsa eskisi (`stale`), xatodan keyin 1 daqiqa pauza.
- Username yo'q yoki noto'g'ri bo'lsa namuna ma'lumot (`real: false`), GitHub xato bersa ham namuna (kesh bo'lmasa). `GITHUB_TOKEN` ixtiyoriy: bilan bo'lsa GraphQL yillik kalendar, bo'lmasa so'nggi ochiq hodisalar (taxminan 90 kun).
- Loyiha Actions ishlari umumiy ro'yxatdagi (sakkiz repo, jami 12 ta) ishlardan filtrlanadi. Token javobda va logda chiqmaydi (test bilan tekshirilgan).
- Telegram kanal `t.me/s/{username}` sahifasidan o'qiladi; o'qilmasa sozlamalardagi (admin) postlar qaytadi.
