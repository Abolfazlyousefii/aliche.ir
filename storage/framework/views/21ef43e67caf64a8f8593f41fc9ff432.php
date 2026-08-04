<?php $__env->startSection('title', 'جزئیات صفحه'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">جزئیات صفحه</p><h2><?php echo e($page->title); ?></h2></div>
    <div class="admin-actions"><a href="<?php echo e(route('admin.pages.edit', $page)); ?>">ویرایش</a><?php if($page->status === 'published'): ?><a class="admin-primary-btn" href="<?php echo e(route('pages.show', $page)); ?>" target="_blank">مشاهده صفحه در سایت</a><?php endif; ?><a href="<?php echo e(route('admin.pages.index')); ?>">بازگشت</a></div>
</div>

<div class="admin-panel-card">
    <dl class="admin-detail-list">
        <div><dt>اسلاگ</dt><dd><code><?php echo e($page->slug); ?></code></dd></div>
        <div><dt>وضعیت</dt><dd><?php echo e(\App\Models\Page::statusLabels()[$page->status] ?? $page->status); ?></dd></div>
        <div><dt>نویسنده</dt><dd><?php echo e($page->author?->name ?: '—'); ?></dd></div>
        <div><dt>تاییدکننده</dt><dd><?php echo e($page->approver?->name ?: '—'); ?></dd></div>
        <div><dt>تاریخ انتشار</dt><dd><?php echo e(jalali_datetime($page->published_at) ?: '—'); ?></dd></div>
        <div><dt>فعال</dt><dd><?php echo e($page->is_active ? 'بله' : 'خیر'); ?></dd></div>
        <?php if($page->rejected_reason): ?><div><dt>دلیل رد</dt><dd><?php echo e($page->rejected_reason); ?></dd></div><?php endif; ?>
    </dl>

    <?php if($page->status === 'published'): ?>
        <div class="admin-actions mb-4"><a class="admin-primary-btn" href="<?php echo e(route('pages.show', $page)); ?>" target="_blank">مشاهده صفحه در سایت</a></div>
    <?php elseif(auth()->user()?->hasPermission('pages.approve')): ?>
        <div class="admin-actions mb-4">
            <form action="<?php echo e(route('admin.pages.approve', $page)); ?>" method="POST"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><button class="admin-secondary-btn" type="submit">تایید</button></form>
            <form action="<?php echo e(route('admin.pages.publish', $page)); ?>" method="POST"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><button class="admin-primary-btn" type="submit">انتشار</button></form>
        </div>
        <form class="admin-reject-form" action="<?php echo e(route('admin.pages.reject', $page)); ?>" method="POST">
            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
            <textarea class="form-control" name="rejected_reason" rows="2" placeholder="دلیل رد صفحه..."></textarea>
            <button class="admin-secondary-btn" type="submit">رد صفحه</button>
        </form>
    <?php endif; ?>

    <h3 class="admin-section-title mt-4">پیش‌نمایش محتوا</h3>
    <div class="admin-content-preview"><?php echo $page->body ?: '<p class="text-muted">بدون محتوا</p>'; ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/pages/show.blade.php ENDPATH**/ ?>