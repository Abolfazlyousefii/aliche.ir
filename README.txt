فیلتر حرفه‌ای + Ajax صفحه مدیریت اخبار

فایل‌های PHP/Blade:
1) app/Http/Controllers/Admin/PostController.php
2) resources/views/admin/posts/index.blade.php
3) resources/views/admin/posts/partials/results.blade.php

فایل JS جدید:
4) public/assets/admin/js/posts-index-filters.js

برای ساختار فعلی این پروژه که ممکن است public_path() روی public_html باشد، همان JS در بسته در این مسیر هم قرار داده شده:
5) public_html/assets/admin/js/posts-index-filters.js

قابلیت‌ها:
- جستجوی Ajax با debounce
- فیلتر Ajax وضعیت، نوع محتوا، اتحادیه، دسته‌بندی و جایگاه صفحه اصلی
- تاریخ شمسی با DatePicker موجود پروژه
- تبدیل تاریخ شمسی به بازه واقعی published_at در Backend
- شروع روز برای «از تاریخ» و پایان روز برای «تا تاریخ»
- نمایش خطای بازه تاریخ نامعتبر
- پاک کردن کامل فیلترها
- شمارنده فیلترهای فعال
- میانبرهای Ajax برای همه/منتشرشده/پیش‌نویس/در انتظار تایید/تاپ/ویژه/امروز
- Pagination بدون رفرش صفحه
- پشتیبانی Back/Forward مرورگر
- لغو درخواست Ajax قدیمی با AbortController
- fallback کامل GET در صورت غیرفعال بودن JavaScript
- حفظ تایید حذف برای ردیف‌هایی که بعد از Ajax بارگذاری می‌شوند

Migration و Route جدید لازم نیست.

بعد از جایگزینی:
php artisan optimize:clear

Deploy:
- فایل‌های app و resources در روت Laravel قرار می‌گیرند.
- اگر runtime سایت public_html است، فایل JS حتماً باید در:
  public_html/assets/admin/js/posts-index-filters.js
  موجود باشد.
