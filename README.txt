نسخه جمع‌وجور مدیریت اخبار + جستجوی زنده + Infinite Scroll

فایل‌های جایگزین/جدید:
1) app/Http/Controllers/Admin/PostController.php
2) resources/views/admin/posts/index.blade.php
3) resources/views/admin/posts/partials/results.blade.php
4) resources/views/admin/posts/partials/rows.blade.php
5) public/assets/admin/js/posts-index-filters.js

برای Runtime فعلی پروژه، نسخه JS در این مسیر هم داخل ZIP موجود است:
6) public_html/assets/admin/js/posts-index-filters.js

رفتار جدید:
- فیلترها در حالت پیش‌فرض جمع هستند.
- جستجوی زنده همیشه دیده می‌شود.
- دکمه «فیلترهای بیشتر» فیلترهای وضعیت، نوع، اتحادیه، دسته‌بندی، جایگاه و تاریخ را باز/بسته می‌کند.
- اگر یکی از فیلترهای پیشرفته فعال باشد، بخش پیشرفته هنگام باز شدن صفحه باز می‌ماند.
- جستجو با تاخیر 250 میلی‌ثانیه و Ajax اجرا می‌شود.
- تغییر selectها و تاریخ‌ها Ajax است.
- صفحه‌بندی ظاهری کامل حذف شده است.
- Backend همچنان نتایج را در بسته‌های 30تایی می‌گیرد تا فشار روی سرور کم بماند.
- با نزدیک شدن کاربر به انتهای لیست، 30 خبر بعدی خودکار بارگذاری می‌شود.
- URL فیلترها حفظ می‌شود ولی شماره صفحه هنگام اسکرول وارد URL نمی‌شود.
- Back/Forward مرورگر برای فیلترها کار می‌کند.
- حذف خبر برای ردیف‌های لودشده با Ajax همچنان تایید می‌گیرد.
- Route و Migration جدید لازم نیست.

بعد از انتقال:
php artisan optimize:clear

روی هاست با public_html:
فایل JS باید حتماً در:
public_html/assets/admin/js/posts-index-filters.js
وجود داشته باشد.
