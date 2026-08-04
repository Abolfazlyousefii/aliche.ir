<?php $__env->startSection('title', 'ایجاد خبر'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">مدیریت اخبار</p><h2>ایجاد خبر جدید</h2></div>
    <a class="admin-secondary-btn" href="<?php echo e(route('admin.posts.index')); ?>">بازگشت به اخبار</a>
</div>

<form action="<?php echo e(route('admin.posts.store')); ?>" method="POST" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <?php echo $__env->make('admin.posts._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/posts/create.blade.php ENDPATH**/ ?>