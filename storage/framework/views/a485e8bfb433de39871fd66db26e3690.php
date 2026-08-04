<?php $__env->startSection('title', 'مدیریت صفحات'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">CMS صفحات</p><h2>صفحات سایت</h2></div>
    <a class="admin-primary-btn" href="<?php echo e(route('admin.pages.create')); ?>">ایجاد صفحه جدید</a>
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="<?php echo e(route('admin.pages.index')); ?>" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="<?php echo e($search); ?>" placeholder="عنوان یا اسلاگ...">
        <select class="form-control" name="status" aria-label="فیلتر وضعیت">
            <option value="">همه وضعیت‌ها</option>
            <?php $__currentLoopData = \App\Models\Page::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemStatus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($itemStatus); ?>" <?php if($status === $itemStatus): echo 'selected'; endif; ?>><?php echo e(\App\Models\Page::statusLabels()[$itemStatus] ?? $itemStatus); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button class="admin-primary-btn" type="submit">اعمال</button>
        <?php if($search !== '' || $status !== ''): ?><a class="admin-secondary-btn" href="<?php echo e(route('admin.pages.index')); ?>">حذف فیلتر</a><?php endif; ?>
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead><tr><th>عنوان</th><th>اسلاگ</th><th>وضعیت</th><th>نویسنده</th><th>تاریخ انتشار</th><th>فعال/غیرفعال</th><th>عملیات</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($page->title); ?></strong></td>
                        <td><code><?php echo e($page->slug); ?></code></td>
                        <td><span class="admin-status-badge status-<?php echo e($page->status); ?>"><?php echo e(\App\Models\Page::statusLabels()[$page->status] ?? $page->status); ?></span></td>
                        <td><?php echo e($page->author?->name ?: '—'); ?></td>
                        <td><?php echo e(jalali_datetime($page->published_at) ?: '—'); ?></td>
                        <td><span class="admin-status-badge <?php echo e($page->is_active ? 'is-active' : 'is-inactive'); ?>"><?php echo e($page->is_active ? 'فعال' : 'غیرفعال'); ?></span></td>
                        <td>
                            <div class="admin-actions">
                                <a href="<?php echo e(route('admin.pages.show', $page)); ?>">مشاهده</a>
                                <a href="<?php echo e(route('admin.pages.edit', $page)); ?>">ویرایش</a>
                                <form action="<?php echo e(route('admin.pages.destroy', $page)); ?>" method="POST"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button type="submit">حذف</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">صفحه‌ای یافت نشد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo $__env->make('admin.partials.pagination', ['paginator' => $pages], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/pages/index.blade.php ENDPATH**/ ?>