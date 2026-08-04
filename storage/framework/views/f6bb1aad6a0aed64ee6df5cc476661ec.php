<?php $__env->startSection('title', 'ویرایش منو'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">ویرایش منو</p>
        <h2><?php echo e($menu->title); ?></h2>
    </div>
    <a class="admin-secondary-btn" href="<?php echo e(route('admin.menus.show', $menu)); ?>">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="<?php echo e(route('admin.menus.update', $menu)); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    <?php echo $__env->make('admin.menus._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/menus/edit.blade.php ENDPATH**/ ?>