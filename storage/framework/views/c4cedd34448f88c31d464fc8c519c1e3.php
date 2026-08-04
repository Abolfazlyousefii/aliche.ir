<?php $__env->startSection('title', 'صندوق ورودی پیام‌ها'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">پیام‌های داخلی</p>
        <h2>صندوق ورودی</h2>
    </div>
    <div class="admin-actions">
        <a class="admin-secondary-btn" href="<?php echo e(route('admin.messages.index')); ?>">همه پیام‌ها</a>
        <a class="admin-secondary-btn" href="<?php echo e(route('admin.messages.sent')); ?>">ارسال‌شده‌ها</a>
        <?php if(request()->user()->hasPermission('messages.send')): ?>
            <a class="admin-primary-btn" href="<?php echo e(route('admin.messages.create')); ?>">ارسال پیام جدید</a>
        <?php endif; ?>
    </div>
</div>

<div class="admin-panel-card">
    <?php echo $__env->make('admin.messages._table', ['messages' => $messages], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/messages/inbox.blade.php ENDPATH**/ ?>