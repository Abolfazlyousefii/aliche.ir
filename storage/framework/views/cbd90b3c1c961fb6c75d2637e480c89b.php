<?php $__env->startSection('title', 'ثبت شکایت از اتحادیه | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', 'ثبت شکایت از اتحادیه‌های صنفی فعال و دریافت کد رهگیری'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="site-container">
        <nav class="breadcrumb-nav">
            <a href="<?php echo e(route('home')); ?>">خانه</a>
            <span class="breadcrumb-sep">/</span>
            <span>ثبت شکایت</span>
        </nav>
        <h1>ثبت شکایت از اتحادیه</h1>
    </div>
</div>

<main class="archive-page">
    <div class="site-container">
        <div class="archive-header">
            <h1>فرم ثبت شکایت</h1>
            <p>پس از ثبت شکایت، کد رهگیری یکتا نمایش داده می‌شود. برای پیگیری، شماره موبایل و کد رهگیری را نگهداری کنید.</p>
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

        <?php if($unions->isEmpty()): ?>
            <div class="alert alert-warning">در حال حاضر ثبت شکایت برای هیچ اتحادیه‌ای فعال نیست.</div>
        <?php else: ?>
            <form class="complaint-card" action="<?php echo e(route('complaints.store')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="union_id">اتحادیه مربوطه</label>
                        <select class="form-control" id="union_id" name="union_id" required>
                            <option value="">انتخاب اتحادیه</option>
                            <?php $__currentLoopData = $unions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $union): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($union->id); ?>" <?php if((string) old('union_id', $selectedUnionId) === (string) $union->id): echo 'selected'; endif; ?>><?php echo e($union->display_title); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="full_name">نام و نام خانوادگی</label>
                        <input class="form-control" id="full_name" name="full_name" value="<?php echo e(old('full_name')); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="national_code">کد ملی</label>
                        <input class="form-control" id="national_code" name="national_code" value="<?php echo e(old('national_code')); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="mobile">شماره موبایل</label>
                        <input class="form-control" id="mobile" name="mobile" value="<?php echo e(old('mobile')); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="subject">موضوع شکایت</label>
                        <input class="form-control" id="subject" name="subject" value="<?php echo e(old('subject')); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="body">شرح شکایت</label>
                        <textarea class="form-control" id="body" name="body" rows="7" required><?php echo e(old('body')); ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="attachment">فایل پیوست</label>
                        <input class="form-control" id="attachment" name="attachment" type="file">
                        <small class="text-muted">بارگذاری پیوست اختیاری است. حداکثر حجم فایل ۱۰ مگابایت است.</small>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <button class="tab-pill active" type="submit">ثبت شکایت و دریافت کد رهگیری</button>
                    <a class="tab-pill" href="<?php echo e(route('complaints.track')); ?>">پیگیری شکایت ثبت‌شده</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/complaints/create.blade.php ENDPATH**/ ?>