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
