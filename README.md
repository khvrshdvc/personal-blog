# personal-blog

Shaxsiy blog va portfolio sayti (Laravel). Prototip `design/` papkasida.

## Prototipni ko'rish
`design/utkirbek-blog-bento.html` faylini brauzerda oching. Admin: `#/admin`, parol `admin` (faqat prototip).

## Holat
Laravel'ga o'tkazish bosqichlari `docs/plan.md` da.

## Ishga tushirish (Sail)
```
cp .env.example .env
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer install --ignore-platform-reqs
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
```
`.env` da `ADMIN_EMAIL` va `ADMIN_PASSWORD` ni to'ldiring, so'ng `migrate --seed` admin foydalanuvchini yaratadi. Kirish: `/admin/login`.
