<?php $__env->startSection('title', 'پیگیری شکایت | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', 'پیگیری وضعیت شکایت ثبت‌شده با شماره موبایل و کد رهگیری'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="site-container">
        <nav class="breadcrumb-nav">
            <a href="<?php echo e(route('home')); ?>">خانه</a>
            <span class="breadcrumb-sep">/</span>
            <span>پیگیری شکایت</span>
        </nav>
        <h1>پیگیری شکایت</h1>
    </div>
</div>

<main class="archive-page">
    <div class="site-container">
        <div class="archive-header">
            <h1>مشاهده وضعیت شکایت</h1>
            <p>کد رهگیری دریافت‌شده پس از ثبت شکایت و همان شماره موبایل ثبت‌شده را وارد کنید.</p>
        </div>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="complaint-card" action="<?php echo e(route('complaints.lookup')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="tracking_code">کد رهگیری</label>
                    <input class="form-control" id="tracking_code" name="tracking_code" value="<?php echo e(old('tracking_code')); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="mobile">شماره موبایل</label>
                    <input class="form-control" id="mobile" name="mobile" value="<?php echo e(old('mobile')); ?>" required>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <button class="tab-pill active" type="submit">پیگیری شکایت</button>
                <a class="tab-pill" href="<?php echo e(route('complaints.create')); ?>">ثبت شکایت جدید</a>
            </div>
        </form>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/complaints/track.blade.php ENDPATH**/ ?>