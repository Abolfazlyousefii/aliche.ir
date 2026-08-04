<?php $__env->startSection('title', 'مدیریت اخبار'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">CMS اخبار</p><h2>مدیریت اخبار</h2></div>
    <a class="admin-primary-btn" href="<?php echo e(route('admin.posts.create')); ?>">ایجاد خبر جدید</a>
</div>

<div class="admin-panel-card mb-3">
    <div class="mb-2"><?php $__currentLoopData = \App\Models\Post::statusLabels(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="admin-secondary-btn" href="<?php echo e(route('admin.posts.index',['status'=>$key])); ?>"><?php echo e($label); ?> (<?php echo e($statusCounts[$key] ?? 0); ?>)</a> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <a class="admin-secondary-btn" href="<?php echo e(route('admin.posts.index',['status'=>'published'])); ?>">روی وبسایت</a> <a class="admin-secondary-btn" href="<?php echo e(route('admin.posts.index',['status'=>'draft'])); ?>">پیش‌نویس‌ها</a> <a class="admin-secondary-btn" href="<?php echo e(route('admin.posts.index',['homepage_position'=>'top'])); ?>">خبرهای تاپ</a> <a class="admin-secondary-btn" href="<?php echo e(route('admin.posts.index',['homepage_position'=>'featured'])); ?>">خبرهای ویژه</a> <a class="admin-secondary-btn" href="<?php echo e(route('admin.posts.index',['today'=>1])); ?>">اخبار امروز: <?php echo e($todayPublishedCount ?? 0); ?></a></div><form class="admin-search-form" action="<?php echo e(route('admin.posts.index')); ?>" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="<?php echo e($search); ?>" placeholder="عنوان، اسلاگ یا خلاصه...">
        <select class="form-control" name="status" aria-label="فیلتر وضعیت">
            <option value="">همه وضعیت‌ها</option>
            <?php $__currentLoopData = \App\Models\Post::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemStatus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($itemStatus); ?>" <?php if($status === $itemStatus): echo 'selected'; endif; ?>><?php echo e(\App\Models\Post::statusLabels()[$itemStatus] ?? $itemStatus); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select class="form-control" name="type" aria-label="فیلتر نوع">
            <option value="">همه نوع‌ها</option>
            <?php $__currentLoopData = \App\Models\Post::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($itemType); ?>" <?php if($type === $itemType): echo 'selected'; endif; ?>><?php echo e(\App\Models\Post::typeLabels()[$itemType] ?? $itemType); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <input class="form-control" name="from" type="date" value="<?php echo e(request('from')); ?>"><input class="form-control" name="to" type="date" value="<?php echo e(request('to')); ?>"><button class="admin-primary-btn" type="submit">اعمال</button>
        <?php if($search !== '' || $status !== '' || $type !== ''): ?><a class="admin-secondary-btn" href="<?php echo e(route('admin.posts.index')); ?>">حذف فیلتر</a><?php endif; ?>
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead><tr><th>عنوان</th><th>دسته‌بندی</th><th>اتحادیه</th><th>نوع</th><th>جایگاه صفحه اصلی</th><th>وضعیت</th><th>بازدید</th><th>انتشار</th><th>عملیات</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($post->title); ?></strong><br><code><?php echo e($post->slug); ?></code></td>
                        <td><?php echo e($post->category?->title ?: '—'); ?></td>
                        <td><?php echo e($post->union?->name ?: 'عمومی'); ?></td>
                        <td><?php echo e($post->type_label); ?></td>
                        <td><?php echo e($post->homepage_position_label); ?></td>
                        <td><span class="admin-status-badge status-<?php echo e($post->status); ?>"><?php echo e($post->status_label); ?></span></td>
                        <td><?php echo e(number_format($post->views_count)); ?></td>
                        <td><?php echo e(jalali_datetime($post->published_at) ?: '—'); ?></td>
                        <td>
                            <div class="admin-actions">
                                <a href="<?php echo e(route('admin.posts.show', $post)); ?>">مشاهده</a>
                                <a href="<?php echo e(route('admin.posts.edit', $post)); ?>">ویرایش</a>
                                <form action="<?php echo e(route('admin.posts.destroy', $post)); ?>" method="POST"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button type="submit">حذف</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">خبری یافت نشد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo $__env->make('admin.partials.pagination', ['paginator' => $posts], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/posts/index.blade.php ENDPATH**/ ?>