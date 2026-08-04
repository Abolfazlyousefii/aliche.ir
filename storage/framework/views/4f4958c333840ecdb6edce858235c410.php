<?php if($paginator->hasPages()): ?>
    <nav class="pagination-nav" aria-label="صفحه‌بندی">
        <?php if($paginator->onFirstPage()): ?>
            <span class="disabled" aria-disabled="true">قبلی</span>
        <?php else: ?>
            <a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">قبلی</a>
        <?php endif; ?>

        <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(is_string($element)): ?>
                <span class="disabled" aria-disabled="true">…</span>
            <?php endif; ?>

            <?php if(is_array($element)): ?>
                <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $paginator->currentPage()): ?>
                        <span class="current" aria-current="page"><?php echo e(fa_number($page)); ?></span>
                    <?php else: ?>
                        <a href="<?php echo e($url); ?>"><?php echo e(fa_number($page)); ?></a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if($paginator->hasMorePages()): ?>
            <a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">بعدی</a>
        <?php else: ?>
            <span class="disabled" aria-disabled="true">بعدی</span>
        <?php endif; ?>
    </nav>
<?php endif; ?>
<?php /**PATH E:\laragon\www\aliche.ir\resources\views/frontend/partials/pagination.blade.php ENDPATH**/ ?>