فارسی‌سازی خطاها + رفع هشدار ناامن بودن فرم

مشکل هشدار انگلیسی Chrome:
"This form is not secure. Autofill has been turned off."
این پیام از خود مرورگر است و قابل ترجمه توسط Laravel نیست.
علت محتمل در این پروژه: سایت پشت Arvan/CDN با HTTPS باز می‌شود ولی Laravel به علت Trusted Proxy نبودن، بعضی URL/form actionها را HTTP تولید می‌کند.

فایل‌های رفع HTTPS:
- bootstrap/app.php
- app/Providers/AppServiceProvider.php

روی Production حتماً در .env:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://gorganasnaf.ir

بعد:
php artisan optimize:clear

فارسی‌سازی فوری بدون نیاز به Composer روی هاست:
- lang/fa/auth.php
- lang/fa/pagination.php
- lang/fa/passwords.php
- lang/fa/validation.php
- resources/views/errors/*

این فایل‌ها باعث می‌شوند Validationهای Laravel مثل validation.max.file دیگر خام/انگلیسی نمایش داده نشوند و صفحات 403/404/419/422/429/500/503 هم فارسی باشند.

پکیج پیشنهادی برای نگهداری ترجمه‌ها:
laravel-lang/common

روی لوکال یا cPanel دارای Terminal:
composer require laravel-lang/common:^6.7
php artisan lang:add fa
php artisan lang:update
php artisan optimize:clear

سپس composer.json و composer.lock و فایل‌های lang تولیدشده را commit کنید.

نکته مهم:
پیام‌های خود مرورگر Chrome/Firefox، افزونه‌های مرورگر و DevTools بخشی از Laravel نیستند و زبان آن‌ها با زبان خود مرورگر تعیین می‌شود. هدف این تغییر این است که تمام پیام‌های قابل‌کنترل توسط نرم‌افزار Laravel فارسی باشند و هشدار امنیتی مرورگر نیز با اصلاح HTTPS حذف شود.
