<?php $__env->startSection('title', 'مدیریت منوها'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">مدیریت منوها</p>
        <h2>منوهای سایت</h2>
    </div>
    <a class="admin-primary-btn" href="<?php echo e(route('admin.menus.create')); ?>">ایجاد منو جدید</a>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>محل نمایش</th>
                    <th>تعداد آیتم‌ها</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($menu->title); ?></strong></td>
                        <td><code><?php echo e($menu->location); ?></code></td>
                        <td><?php echo e($menu->items_count); ?></td>
                        <td><span class="admin-status-badge <?php echo e($menu->is_active ? 'is-active' : 'is-inactive'); ?>"><?php echo e($menu->is_active ? 'فعال' : 'غیرفعال'); ?></span></td>
                        <td>
                            <div class="admin-actions">
                                <a href="<?php echo e(route('admin.menus.show', $menu)); ?>">مدیریت آیتم‌ها</a>
                                <a href="<?php echo e(route('admin.menus.edit', $menu)); ?>">ویرایش</a>
                                <form action="<?php echo e(route('admin.menus.destroy', $menu)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">هنوز منویی ثبت نشده است.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php echo $__env->make('admin.partials.pagination', ['paginator' => $menus], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/menus/index.blade.php ENDPATH**/ ?>