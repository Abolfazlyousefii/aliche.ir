<?php if(session('success')): ?>
    <div class="alert alert-success admin-alert" role="alert"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger admin-alert" role="alert"><?php echo e(session('error')); ?></div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger admin-alert" role="alert">
        <strong>لطفاً خطاهای زیر را بررسی کنید:</strong>
        <ul class="mb-0 mt-2">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/partials/alerts.blade.php ENDPATH**/ ?>