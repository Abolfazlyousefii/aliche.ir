<?php $__env->startSection('title', 'ایجاد اتحادیه'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">مدیریت اتحادیه‌ها</p><h2>ایجاد اتحادیه جدید</h2></div>
    <a class="admin-secondary-btn" href="<?php echo e(route('admin.unions.index')); ?>">بازگشت به اتحادیه‌ها</a>
</div>

<form action="<?php echo e(route('admin.unions.store')); ?>" method="POST" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <?php echo $__env->make('admin.unions._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/unions/create.blade.php ENDPATH**/ ?>