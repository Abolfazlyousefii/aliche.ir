<?php $__env->startSection('title', 'کمیسیون‌ها | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', 'معرفی کمیسیون‌های اتاق اصناف مرکز استان گلستان، وظایف، اعضا و جلسات مرتبط'); ?>
<?php $__env->startSection('frontend_variant', 'compact'); ?>
<?php $__env->startSection('footer_links_variant', 'short'); ?>

<?php $__env->startSection('content'); ?>
<section class="page-header page-header-alt commissions-hero">
    <div class="site-container">
        <span class="commissions-eyebrow">کمیسیون‌های تخصصی</span>
        <h1>کمیسیون‌ها</h1>
        <p>مسیر دسترسی به اطلاعات کمیسیون‌ها، وظایف، اعضا و جلسات منتشرشده اتاق اصناف.</p>
        <nav class="breadcrumb">
            <a href="<?php echo e(route('home')); ?>">خانه</a>
            <span>کمیسیون‌ها</span>
        </nav>
    </div>
</section>

<section class="site-container commissions-page">
    <div class="commissions-intro-card">
        <div>
            <span class="commissions-eyebrow">نمای کلی</span>
            <h2>کمیسیون‌های اتاق اصناف</h2>
            <p>هر کارت شامل شرح کوتاه و تعداد جلسات منتشرشده است. برای مشاهده جزئیات، روی کارت موردنظر کلیک کنید.</p>
        </div>
        <div class="commissions-count-box">
            <strong><?php echo e($commissions->total()); ?></strong>
            <span>کمیسیون فعال</span>
        </div>
    </div>

    <div class="commissions-list-grid">
        <?php $__empty_1 = true; $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a class="commission-list-card" href="<?php echo e(route('commissions.show', $commission->slug)); ?>">
                <span class="commission-list-number"><?php echo e(str_pad((string) ($commissions->firstItem() + $index), 2, '0', STR_PAD_LEFT)); ?></span>
                <div class="commission-list-content">
                    <h3><?php echo e($commission->title); ?></h3>
                    <p><?php echo e(plain_text($commission->description, 150) ?: 'اطلاعات این کمیسیون به‌زودی تکمیل می‌شود.'); ?></p>
                    <div class="commission-list-meta">
                        <span><?php echo e($commission->sessions_count); ?> جلسه منتشرشده</span>
                        <span>مشاهده جزئیات ←</span>
                    </div>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="commissions-empty-state">
                <strong>کمیسیونی برای نمایش وجود ندارد.</strong>
                <p>پس از انتشار کمیسیون‌ها، اطلاعات آن‌ها در این بخش نمایش داده می‌شود.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="commissions-pagination">
        <?php echo e($commissions->links('frontend.partials.pagination')); ?>

    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/commissions/index.blade.php ENDPATH**/ ?>