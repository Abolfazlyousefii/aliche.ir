<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود به پنل مدیریت | اتاق اصناف مرکز استان گلستان</title>
    <link href="https://cdn.jsdelivr.net" rel="preconnect">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
    <link href="<?php echo e(asset('assets/admin/css/admin.css')); ?>?v=<?php echo e(filemtime(public_path('assets/admin/css/admin.css'))); ?>" rel="stylesheet">
</head>
<body class="admin-auth-body">
    <main class="admin-auth-shell">
        <section class="admin-auth-card" aria-labelledby="adminLoginTitle">
            <div class="admin-auth-brand">
                <div class="admin-brand-mark">ا</div>
                <div>
                    <p class="admin-eyebrow">سامانه مدیریت محتوا</p>
                    <h1 id="adminLoginTitle">ورود به پنل مدیریت</h1>
                </div>
            </div>

            <?php echo $__env->make('admin.partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <form class="admin-form" action="<?php echo e(route('login')); ?>" method="POST" novalidate>
                <?php echo csrf_field(); ?>
                <div>
                    <label class="form-label" for="mobile">شماره تماس مدیر</label>
                    <input class="form-control <?php $__errorArgs = ['mobile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" dir="ltr" id="mobile" name="mobile" type="tel" value="<?php echo e(old('mobile')); ?>" autocomplete="tel" inputmode="tel" placeholder="09110000000" required autofocus>
                    <?php $__errorArgs = ['mobile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label class="form-label" for="password">رمز عبور</label>
                    <input class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="password" name="password" type="password" autocomplete="current-password" placeholder="رمز عبور حساب مدیریت" required>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <div class="form-text">اطلاعات ورود را از مدیر سامانه دریافت کنید.</div>
                </div>

                <label class="form-check admin-auth-check" for="remember">
                    <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                    <span class="form-check-label">مرا به خاطر بسپار</span>
                </label>

                <button class="admin-primary-btn w-100" type="submit">ورود به پنل</button>
                <a class="admin-secondary-btn w-100" href="<?php echo e(route('home')); ?>">بازگشت به سایت</a>
            </form>
        </section>
    </main>
</body>
</html>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/auth/login.blade.php ENDPATH**/ ?>