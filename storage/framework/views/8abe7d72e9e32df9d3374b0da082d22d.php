<?php $__env->startSection('title', 'ویرایش اتحادیه'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">مدیریت اتحادیه‌ها</p><h2>ویرایش اتحادیه: <?php echo e($union->display_title); ?></h2></div>
    <a class="admin-secondary-btn" href="<?php echo e(route('admin.unions.show', $union)); ?>">مشاهده اتحادیه</a>
</div>

<form action="<?php echo e(route('admin.unions.update', $union)); ?>" method="POST" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    <?php echo $__env->make('admin.unions._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/unions/edit.blade.php ENDPATH**/ ?>