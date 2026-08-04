<?php $__env->startSection('title', 'مدیریت تبلیغات'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">تبلیغات</p><h2>مدیریت تبلیغات</h2></div>
    <div class="admin-actions">
        <a class="admin-secondary-btn" href="<?php echo e(route('admin.advertisement_positions.index')); ?>">جایگاه‌ها</a>
        <?php if(request()->user()->hasPermission('advertisements.create')): ?>
            <a class="admin-primary-btn" href="<?php echo e(route('admin.advertisements.create')); ?>">ایجاد تبلیغ جدید</a>
        <?php endif; ?>
    </div>
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="<?php echo e(route('admin.advertisements.index')); ?>" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="<?php echo e($search); ?>" placeholder="عنوان یا لینک...">
        <select class="form-control" name="position_id" aria-label="فیلتر جایگاه">
            <option value="">همه جایگاه‌ها</option>
            <?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($position->id); ?>" <?php if((string) $positionId === (string) $position->id): echo 'selected'; endif; ?>><?php echo e($position->title); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select class="form-control" name="status" aria-label="فیلتر وضعیت">
            <option value="">همه وضعیت‌ها</option>
            <option value="displayable" <?php if($status === 'displayable'): echo 'selected'; endif; ?>>در حال نمایش</option>
            <option value="active" <?php if($status === 'active'): echo 'selected'; endif; ?>>فعال</option>
            <option value="inactive" <?php if($status === 'inactive'): echo 'selected'; endif; ?>>غیرفعال</option>
            <option value="scheduled" <?php if($status === 'scheduled'): echo 'selected'; endif; ?>>زمان‌بندی شده</option>
            <option value="expired" <?php if($status === 'expired'): echo 'selected'; endif; ?>>منقضی شده</option>
        </select>
        <button class="admin-primary-btn" type="submit">اعمال فیلتر</button>
        <a class="admin-secondary-btn" href="<?php echo e(route('admin.advertisements.index')); ?>">حذف فیلتر</a>
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>تصویر</th><th>عنوان</th><th>جایگاه</th><th>وضعیت</th><th>بازه نمایش</th><th>آمار</th><th>ترتیب</th><th>عملیات</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $advertisements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $advertisement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><img src="<?php echo e($advertisement->image_url); ?>" alt="<?php echo e($advertisement->title); ?>" style="width:96px;height:54px;object-fit:cover;border-radius:12px"></td>
                    <td><strong><?php echo e($advertisement->title); ?></strong><br><small dir="ltr"><?php echo e(($advertisement->link ? Str::limit($advertisement->link, 45) : 'بدون لینک')); ?></small></td>
                    <td><?php echo e($advertisement->position?->title ?: '—'); ?><br><small dir="ltr"><?php echo e($advertisement->position?->key); ?></small></td>
                    <td><span class="admin-badge"><?php echo e($advertisement->status_label); ?></span></td>
                    <td><small>شروع: <?php echo e(jalali_datetime($advertisement->starts_at)); ?></small><br><small>پایان: <?php echo e(jalali_datetime($advertisement->expires_at) ?: 'نامحدود'); ?></small></td>
                    <td><small>نمایش: <?php echo e($advertisement->views_count); ?></small><br><small>کلیک: <?php echo e($advertisement->clicks_count); ?></small></td>
                    <td><?php echo e($advertisement->sort_order); ?></td>
                    <td>
                        <div class="admin-actions">
                            <a class="admin-secondary-btn" href="<?php echo e(route('admin.advertisements.show', $advertisement)); ?>">نمایش</a>
                            <?php if(request()->user()->hasPermission('advertisements.edit')): ?><a class="admin-secondary-btn" href="<?php echo e(route('admin.advertisements.edit', $advertisement)); ?>">ویرایش</a><?php endif; ?>
                            <?php if(request()->user()->hasPermission('advertisements.delete')): ?>
                                <form action="<?php echo e(route('admin.advertisements.destroy', $advertisement)); ?>" method="POST"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="admin-danger-btn" type="submit">حذف</button></form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="text-center text-muted">تبلیغی ثبت نشده است.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo $__env->make('admin.partials.pagination', ['paginator' => $advertisements], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/advertisements/index.blade.php ENDPATH**/ ?>