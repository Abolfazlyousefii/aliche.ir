راهنمای نصب FTP - مدیریت امن کش

1) ابتدا چون ممکن است Route Cache فعلی فعال باشد، Route کش‌پاک‌کن موجود پروژه را فقط یک‌بار اجرا کن تا کش فعلی خالی شود.
   این Route در نسخه فعلی routes/web.php وجود دارد.
   بعد از انجام این مرحله، Route عمومی قدیمی باید حذف شود.

2) فایل زیر را آپلود کن:
   resources/views/admin/system/cache.blade.php

3) فایل routes/web.php را باز کن.
   داخل گروه موجود:
   Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
   محتوای routes-cache-snippet.txt را اضافه کن.

4) Route عمومی قدیمی مشخص‌شده در REMOVE-OLD-PUBLIC-ROUTE.txt را کامل حذف کن.

5) ذخیره/آپلود کن.

6) از این به بعد:
   https://gorganasnaf.ir/admin/cache

   باید ابتدا لاگین باشی و دسترسی settings.edit داشته باشی.
   super-admin طبق مدل User به‌صورت خودکار همه دسترسی‌ها را دارد.

7) دکمه «پاک‌سازی کش Laravel» یک POST دارای CSRF است و optimize:clear اجرا می‌کند.

نکته:
- دیتابیس پاک نمی‌شود.
- Migration اجرا نمی‌شود.
- فایل‌های آپلودی حذف نمی‌شوند.
- فقط View/Route/Config/Application cache پاک می‌شود.
