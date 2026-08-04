<?php $__env->startSection('title', 'تماس با ما | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', 'ارسال پیام ارتباطی به اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('active_menu', 'contact'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="site-container">
        <nav class="breadcrumb-nav">
            <a href="<?php echo e(route('home')); ?>">خانه</a>
            <span class="breadcrumb-sep">/</span>
            <span>تماس با ما</span>
        </nav>
        <h1>تماس با ما</h1>
    </div>
</div>

<main class="archive-page">
    <div class="site-container">
        <div class="archive-header">
            <h1>فرم ارتباط با اتاق اصناف</h1>
            <p>پرسش‌ها، پیشنهادها و پیام‌های خود را از طریق فرم زیر ارسال کنید تا توسط کارشناسان مربوطه بررسی شود.</p>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <form class="complaint-card" action="<?php echo e(route('contact.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="full_name">نام و نام خانوادگی</label>
                            <input class="form-control" id="full_name" name="full_name" value="<?php echo e(old('full_name')); ?>" required maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="mobile">شماره تماس</label>
                            <input class="form-control" id="mobile" name="mobile" value="<?php echo e(old('mobile')); ?>" required maxlength="20" dir="ltr">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">ایمیل <span class="text-muted">(اختیاری)</span></label>
                            <input class="form-control" id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" maxlength="255" dir="ltr">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="subject">موضوع</label>
                            <input class="form-control" id="subject" name="subject" value="<?php echo e(old('subject')); ?>" required maxlength="255">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="message">پیام</label>
                            <textarea class="form-control" id="message" name="message" rows="7" required minlength="10" maxlength="5000"><?php echo e(old('message')); ?></textarea>
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2 flex-wrap">
                        <button class="tab-pill active" type="submit">ارسال پیام</button>
                        <a class="tab-pill" href="<?php echo e(route('home')); ?>">بازگشت به صفحه اصلی</a>
                    </div>
                </form>
            </div>
            <div class="col-lg-4">
                <aside class="complaint-card h-100">
                    <h2 class="h5 mb-3">اطلاعات تماس</h2>
                    <p><strong>تلفن:</strong> <?php echo e($settings->get('site.phone', '۰۱۷۳۲۱۵۲۹۱۲')); ?></p>
                    <p><strong>موبایل:</strong> <?php echo e($settings->get('site.mobile', '—')); ?></p>
                    <p><strong>ایمیل:</strong> <?php echo e($settings->get('site.email', 'info@example.com')); ?></p>
                    <p><strong>آدرس:</strong><br><?php echo e($settings->get('site.address', 'اتاق اصناف مرکز استان گلستان')); ?></p>
                    <?php if($settings->get('site.map_url')): ?>
                        <a class="tab-pill" href="<?php echo e($settings->get('site.map_url')); ?>" target="_blank" rel="noopener">مشاهده روی نقشه</a>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/contact/create.blade.php ENDPATH**/ ?>