<?php $__env->startSection('title', 'جزئیات خبر'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $cleanExcerpt = trim(strip_tags(html_entity_decode((string) $post->excerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    $decodedBody = html_entity_decode((string) $post->body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
?>
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">جزئیات خبر</p><h2><?php echo e($post->title); ?></h2></div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-secondary-btn" href="<?php echo e(route('admin.posts.index')); ?>">بازگشت</a>
        <a class="admin-primary-btn" href="<?php echo e(route('admin.posts.edit', $post)); ?>">ویرایش</a>
        <?php if($post->status === 'published'): ?><a class="admin-secondary-btn" href="<?php echo e(route('posts.show', $post->slug)); ?>" target="_blank">مشاهده صفحه در سایت</a><?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-panel-card">
            <?php if($post->featured_image): ?>
                <img class="img-fluid rounded mb-3" src="<?php echo e(Storage::url($post->featured_image)); ?>" alt="<?php echo e($post->title); ?>">
            <?php endif; ?>
            <?php if($cleanExcerpt !== ''): ?>
                <p class="text-muted"><?php echo e($cleanExcerpt); ?></p>
            <?php endif; ?>
            <div class="admin-rich-content"><?php echo $decodedBody ?: '<p class="text-muted">محتوایی برای این خبر ثبت نشده است.</p>'; ?></div>
        </div>
        <?php if($post->galleries->isNotEmpty()): ?>
            <div class="admin-panel-card mt-3">
                <h3 class="h5 mb-3">گالری خبر</h3>
                <div class="row g-3">
                    <?php $__currentLoopData = $post->galleries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gallery): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-4">
                            <img class="img-fluid rounded" src="<?php echo e(Storage::url($gallery->image)); ?>" alt="<?php echo e($gallery->caption ?: $post->title); ?>">
                            <?php if($gallery->caption): ?><p class="small mt-2 mb-0"><?php echo e($gallery->caption); ?></p><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-4">
        <div class="admin-panel-card">
            <dl class="row mb-0">
                <dt class="col-5">وضعیت</dt><dd class="col-7"><span class="admin-status-badge status-<?php echo e($post->status); ?>"><?php echo e($post->status_label); ?></span></dd>
                <dt class="col-5">نوع</dt><dd class="col-7"><?php echo e($post->type_label); ?></dd>
                <dt class="col-5">دسته‌بندی</dt><dd class="col-7"><?php echo e($post->category?->title ?: '—'); ?></dd>
                <dt class="col-5">اتحادیه</dt><dd class="col-7"><?php echo e($post->union?->name ?: 'عمومی'); ?></dd>
                <dt class="col-5">جایگاه صفحه اصلی</dt><dd class="col-7"><?php echo e($post->homepage_position_label); ?></dd>
                <dt class="col-5">بازدید</dt><dd class="col-7"><?php echo e(number_format($post->views_count)); ?></dd>
                <dt class="col-5">نویسنده</dt><dd class="col-7"><?php echo e($post->author?->name ?: '—'); ?></dd>
                <dt class="col-5">تاییدکننده</dt><dd class="col-7"><?php echo e($post->approver?->name ?: '—'); ?></dd>
                <dt class="col-5">انتشار</dt><dd class="col-7"><?php echo e(jalali_datetime($post->published_at) ?: '—'); ?></dd>
            </dl>
        </div>
        <?php if($post->rejected_reason): ?>
            <div class="admin-panel-card mt-3"><strong>دلیل رد:</strong><p class="mb-0 mt-2"><?php echo e($post->rejected_reason); ?></p></div>
        <?php endif; ?>
        <div class="admin-panel-card mt-3">
            <h3 class="h6">اقدام مدیریتی</h3>
            <div class="d-flex gap-2 flex-wrap mb-3">
                <?php if($post->canBePublished() && auth()->user()?->hasPermission('posts.publish')): ?>
                    <form action="<?php echo e(route('admin.posts.publish', $post)); ?>" method="POST" data-single-submit><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><button class="admin-primary-btn" type="submit" data-loading-text="در حال تایید و انتشار...">تایید و انتشار محتوا</button></form>
                <?php endif; ?>
                <?php if($post->canBeUnpublished() && auth()->user()?->hasPermission('posts.publish')): ?>
                    <form action="<?php echo e(route('admin.posts.unpublish', $post)); ?>" method="POST" data-single-submit><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><button class="admin-secondary-btn" type="submit" data-loading-text="در حال بازگرداندن به پیش‌نویس...">بازگرداندن به پیش‌نویس</button></form>
                <?php endif; ?>
                <?php if(! $post->canBeApproved() && ! $post->canBePublished() && ! $post->canBeUnpublished()): ?>
                    <span class="text-muted small">برای وضعیت فعلی این خبر اقدام انتشار دیگری لازم نیست.</span>
                <?php endif; ?>
            </div>
            <?php if($post->canBeRejected() && auth()->user()?->hasPermission('posts.approve')): ?>
                <form action="<?php echo e(route('admin.posts.reject', $post)); ?>" method="POST" data-single-submit>
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <label class="form-label" for="rejected_reason">دلیل رد خبر</label>
                    <textarea class="form-control mb-2" id="rejected_reason" name="rejected_reason" rows="3" required><?php echo e(old('rejected_reason')); ?></textarea>
                    <button class="admin-secondary-btn" type="submit" data-loading-text="در حال ثبت رد...">رد خبر</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/posts/show.blade.php ENDPATH**/ ?>