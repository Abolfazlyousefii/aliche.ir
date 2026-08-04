<?php $__env->startSection('title', 'مدیریت اتحادیه‌ها'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">مدیریت اتحادیه‌ها</p><h2>اتحادیه‌های صنفی</h2></div>
    <a class="admin-primary-btn" href="<?php echo e(route('admin.unions.create')); ?>">ایجاد اتحادیه جدید</a>
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="<?php echo e(route('admin.unions.index')); ?>" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="<?php echo e($search); ?>" placeholder="عنوان، مدیر، تلفن یا ایمیل...">
        <select class="form-control" name="status" aria-label="فیلتر وضعیت">
            <option value="">همه وضعیت‌ها</option>
            <option value="active" <?php if($status === 'active'): echo 'selected'; endif; ?>>فعال</option>
            <option value="inactive" <?php if($status === 'inactive'): echo 'selected'; endif; ?>>غیرفعال</option>
        </select>
        <button class="admin-primary-btn" type="submit">اعمال فیلتر</button>
        <?php if($search !== '' || $status !== ''): ?><a class="admin-secondary-btn" href="<?php echo e(route('admin.unions.index')); ?>">حذف فیلتر</a><?php endif; ?>
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead><tr><th>عنوان</th><th>لوگو</th><th>مدیر</th><th>شماره تماس</th><th>وضعیت</th><th>ترتیب نمایش</th><th>عملیات</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $unions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $union): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($union->display_title); ?></strong><br><code><?php echo e($union->slug); ?></code></td>
                        <td>
                            <?php if($union->logo): ?>
                                <img src="<?php echo e(route('media.public', ['path' => $union->logo])); ?>" alt="<?php echo e($union->display_title); ?>" style="width:48px;height:48px;object-fit:contain">
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($union->manager_name ?: '—'); ?></td>
                        <td><?php echo e($union->phone ?: $union->mobile ?: '—'); ?></td>
                        <td><span class="admin-status-badge <?php echo e($union->is_active ? 'is-active' : 'is-inactive'); ?>"><?php echo e($union->is_active ? 'فعال' : 'غیرفعال'); ?></span></td>
                        <td><?php echo e($union->sort_order); ?></td>
                        <td>
                            <div class="admin-actions">
                                <a href="<?php echo e(route('admin.unions.show', $union)); ?>">مشاهده</a>
                                <a href="<?php echo e(route('admin.unions.edit', $union)); ?>">ویرایش</a>
                                <form action="<?php echo e(route('admin.unions.destroy', $union)); ?>" method="POST"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button type="submit">حذف</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">اتحادیه‌ای یافت نشد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo $__env->make('admin.partials.pagination', ['paginator' => $unions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/unions/index.blade.php ENDPATH**/ ?>