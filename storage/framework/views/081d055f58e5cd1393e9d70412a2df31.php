<?php $__env->startSection('title', 'جزئیات تبلیغ'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">تبلیغات</p><h2><?php echo e($advertisement->title); ?></h2></div>
    <div class="admin-actions">
        <a class="admin-secondary-btn" href="<?php echo e(route('admin.advertisements.index')); ?>">بازگشت</a>
        <?php if(request()->user()->hasPermission('advertisements.edit')): ?><a class="admin-primary-btn" href="<?php echo e(route('admin.advertisements.edit', $advertisement)); ?>">ویرایش</a><?php endif; ?>
    </div>
</div>

<div class="admin-panel-card">
    <div class="row g-4">
        <div class="col-md-5"><img src="<?php echo e($advertisement->image_url); ?>" alt="<?php echo e($advertisement->title); ?>" style="width:100%;max-height:320px;object-fit:contain;border-radius:18px;background:#f7f7f7"></div>
        <div class="col-md-7">
            <div class="row g-3">
                <div class="col-md-6"><strong>جایگاه:</strong><p><?php echo e($advertisement->position?->title ?: '—'); ?></p></div>
                <div class="col-md-6"><strong>کلید جایگاه:</strong><p dir="ltr"><?php echo e($advertisement->position?->key ?: '—'); ?></p></div>
                <div class="col-md-6"><strong>وضعیت:</strong><p><?php echo e($advertisement->status_label); ?></p></div>
                <div class="col-md-6"><strong>فعال:</strong><p><?php echo e($advertisement->is_active ? 'بله' : 'خیر'); ?></p></div>
                <div class="col-md-6"><strong>شروع:</strong><p><?php echo e(jalali_datetime($advertisement->starts_at)); ?></p></div>
                <div class="col-md-6"><strong>پایان:</strong><p><?php echo e(jalali_datetime($advertisement->expires_at) ?: 'نامحدود'); ?></p></div>
                <div class="col-md-6"><strong>نمایش:</strong><p><?php echo e($advertisement->views_count); ?></p></div>
                <div class="col-md-6"><strong>کلیک:</strong><p><?php echo e($advertisement->clicks_count); ?></p></div>
                <div class="col-md-6"><strong>ترتیب:</strong><p><?php echo e($advertisement->sort_order); ?></p></div>
                <div class="col-md-6"><strong>Target:</strong><p dir="ltr"><?php echo e($advertisement->target); ?> (<?php echo e($advertisement->target_label); ?>)</p></div>
                <div class="col-12"><strong>لینک:</strong><p dir="ltr"><?php if($advertisement->link): ?><a href="<?php echo e($advertisement->link); ?>" target="_blank"><?php echo e($advertisement->link); ?></a><?php else: ?> — <?php endif; ?></p></div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/advertisements/show.blade.php ENDPATH**/ ?>