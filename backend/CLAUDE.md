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
- Username yo'q yoki noto'g'ri bo'lsa namuna ma'lumot (`real: false`), GitHub xato bersa ham namuna (kesh bo'lmasa). Faollik manbalari ketma-ket: (1) `GITHUB_TOKEN` bo'lsa GraphQL yillik kalendar, (2) tokensiz `github.com/users/{login}/contributions` ochiq profil kalendari (HTML o'qiladi, rasmiy API emas, shuning uchun format o'zgarsa keyingisiga o'tadi), (3) so'nggi ~100 ochiq hodisa (faqat oxirgi 1-2 oy). Token ixtiyoriy, lekin rate limit uchun foydali.
- Loyiha Actions ishlari umumiy ro'yxatdagi (sakkiz repo, jami 12 ta) ishlardan filtrlanadi. Token javobda va logda chiqmaydi (test bilan tekshirilgan).
- Telegram kanal `t.me/s/{username}` sahifasidan o'qiladi; o'qilmasa sozlamalardagi (admin) postlar qaytadi.

## Xavfsizlik va revalidate (B6)
- Chop etilgan postlar, loyihalar va sozlamalar o'zgarganda `FrontendRevalidator` frontend'ga `POST {FRONTEND_INTERNAL_URL}/api/revalidate` yuboradi (`{secret, tags}`), javobdan keyin, bir so'rovga bitta chaqiruv. Faqat model kuzatuvchilari (`app/Observers`) orqali; qoralama va `pending` o'zgarishlari yuborilmaydi. Xato bo'lsa so'rov buzilmaydi, log'ga yoziladi (kalitsiz).
- `FRONTEND_INTERNAL_URL` backend frontend'ga erisha oladigan manzil (Docker'da `http://frontend:3000`); bo'sh bo'lsa `FRONTEND_URL` (brauzer/CORS manzili) ishlatiladi. Sail konteyneridan xostdagi `npm run dev` serveriga yo'l yo'q, shuning uchun mahalliy sinov uchun frontend'ni Docker'da ishga tushiring: `docker compose -p backend --profile frontend up -d frontend` va `.env`ga `FRONTEND_INTERNAL_URL=http://frontend:3000`.
- Ommaviy API IP bo'yicha 120/daqiqa bilan cheklanadi; frontend server-server so'rovlarida `X-Frontend-Secret` yuboradi va cheklanmaydi. Login (5/daqiqa) va admin (300/daqiqa) cheklovlari istisnosiz.
- Barcha API javoblarida `nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: no-referrer`, qat'iy CSP; admin javoblari `no-store`.
