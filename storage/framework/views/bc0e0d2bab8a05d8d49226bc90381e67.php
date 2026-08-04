<?php $__env->startSection('title', 'آیتم‌های منو'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow"><?php echo e($menu->location); ?></p>
        <h2><?php echo e($menu->title); ?></h2>
    </div>
    <div class="admin-actions">
        <a class="admin-primary-btn" href="<?php echo e(route('admin.menus.items.create', $menu)); ?>">افزودن آیتم</a>
        <a href="<?php echo e(route('admin.menus.edit', $menu)); ?>">ویرایش منو</a>
        <a href="<?php echo e(route('admin.menus.index')); ?>">بازگشت</a>
    </div>
</div>

<div class="admin-panel-card">
    <div class="admin-panel-header">
        <h3>ساختار منو</h3>
        <span>برای جابه‌جایی drag & drop کنید</span>
    </div>

    <div class="admin-menu-sort" data-menu-sort data-sort-url="<?php echo e(route('admin.menus.items.sort', $menu)); ?>" data-csrf="<?php echo e(csrf_token()); ?>">
        <?php if($items->isNotEmpty()): ?>
            <ol class="admin-menu-tree" data-menu-list>
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('admin.menus.items._tree-item', ['item' => $item, 'menu' => $menu], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ol>
            <div class="admin-form-actions">
                <button class="admin-primary-btn" type="button" data-menu-save-sort>ذخیره ترتیب</button>
                <span class="text-muted" data-menu-sort-message></span>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">هنوز آیتمی برای این منو ثبت نشده است.</p>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/menus/show.blade.php ENDPATH**/ ?>