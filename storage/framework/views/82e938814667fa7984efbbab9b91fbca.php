<?php
    /** @var \Illuminate\Contracts\Pagination\Paginator|\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator */
?>

<?php if($paginator->hasPages()): ?>
    <nav class="admin-pagination-wrapper" aria-label="صفحه‌بندی مدیریت" data-admin-pagination>
        <?php if(method_exists($paginator, 'firstItem')): ?>
            <div class="admin-pagination-summary">
                نمایش <?php echo e(fa_number($paginator->firstItem())); ?> تا <?php echo e(fa_number($paginator->lastItem())); ?> از <?php echo e(fa_number($paginator->total())); ?> مورد
            </div>
        <?php endif; ?>

        <div class="admin-pagination-links">
            <?php echo e($paginator->onEachSide(1)->links()); ?>

        </div>

        <div class="admin-pagination-fallback" aria-label="لینک‌های صفحه قبل و بعد">
            <?php if($paginator->previousPageUrl()): ?>
                <a class="admin-secondary-btn" href="<?php echo e($paginator->previousPageUrl()); ?>" data-url="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">صفحه قبل</a>
            <?php else: ?>
                <span class="admin-secondary-btn is-disabled" aria-disabled="true">صفحه قبل</span>
            <?php endif; ?>

            <?php if($paginator->nextPageUrl()): ?>
                <a class="admin-secondary-btn" href="<?php echo e($paginator->nextPageUrl()); ?>" data-url="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">صفحه بعد</a>
            <?php else: ?>
                <span class="admin-secondary-btn is-disabled" aria-disabled="true">صفحه بعد</span>
            <?php endif; ?>
        </div>
    </nav>
<?php elseif(method_exists($paginator, 'total')): ?>
    <div class="admin-pagination-wrapper admin-pagination-wrapper-empty">
        <div class="admin-pagination-summary">نمایش <?php echo e(fa_number($paginator->total())); ?> مورد</div>
    </div>
<?php endif; ?>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/partials/pagination.blade.php ENDPATH**/ ?>