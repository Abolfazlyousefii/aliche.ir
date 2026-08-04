<?php $__env->startSection('title', 'جزئیات اتحادیه'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">جزئیات اتحادیه</p><h2><?php echo e($union->display_title); ?></h2></div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-secondary-btn" href="<?php echo e(route('admin.unions.index')); ?>">بازگشت</a>
        <a class="admin-primary-btn" href="<?php echo e(route('admin.unions.edit', $union)); ?>">ویرایش</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-panel-card">
            <?php if($union->cover_image): ?>
                <img class="img-fluid rounded mb-3" src="<?php echo e(route('media.public', ['path' => $union->cover_image])); ?>" alt="<?php echo e($union->display_title); ?>">
            <?php endif; ?>
            <div class="d-flex align-items-center gap-3 mb-3">
                <?php if($union->logo): ?><img src="<?php echo e(route('media.public', ['path' => $union->logo])); ?>" alt="<?php echo e($union->display_title); ?>" style="width:72px;height:72px;object-fit:contain"><?php endif; ?>
                <div><h3 class="h5 mb-1"><?php echo e($union->display_title); ?></h3><p class="text-muted mb-0"><?php echo e(plain_text($union->short_description)); ?></p></div>
            </div>
            <div class="admin-rich-content"><?php echo $union->description ?: '—'; ?></div>
        </div>

        <div class="admin-panel-card mt-3">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <h3 class="h6 mb-0">بخش‌های قابل ویرایش صفحه اتحادیه</h3>
                <a class="admin-secondary-btn" href="<?php echo e(route('admin.unions.edit', $union)); ?>">ویرایش بخش‌ها</a>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><strong>کمیسیون‌ها</strong><p class="text-muted mb-1"><?php echo e($union->commissions->count()); ?> مورد</p><ul class="mb-0"><?php $__currentLoopData = $union->commissions->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($commission->title); ?> <small>(<?php echo e($commission->tasks->count()); ?> وظیفه)</small></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div>
                <div class="col-md-6"><strong>صورتجلسه‌ها</strong><p class="text-muted mb-1"><?php echo e($union->minutes->count()); ?> مورد</p><ul class="mb-0"><?php $__currentLoopData = $union->minutes->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $minute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($minute->title); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div>
                <div class="col-md-6"><strong>قوانین و دستورالعمل‌ها</strong><p class="text-muted mb-1"><?php echo e($union->rules->count()); ?> مورد</p><ul class="mb-0"><?php $__currentLoopData = $union->rules->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($rule->title); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div>
                <div class="col-md-6"><strong>آموزش‌ها و نرخ‌نامه</strong><p class="text-muted mb-1"><?php echo e($union->educations->count()); ?> آموزش / <?php echo e($union->prices->count()); ?> نرخ</p><ul class="mb-0"><?php $__currentLoopData = $union->educations->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $education): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($education->title); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php $__currentLoopData = $union->prices->take(1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $price): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($price->title); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div>
            </div>
        </div>
        <div class="admin-panel-card mt-3">
            <h3 class="h6">امکانات فعال</h3>
            <div class="d-flex flex-wrap gap-2">
                <?php $__currentLoopData = [
                    'news_enabled' => 'اخبار', 'announcements_enabled' => 'اطلاعیه‌ها', 'gallery_enabled' => 'گالری',
                    'videos_enabled' => 'ویدیوها', 'members_enabled' => 'اعضا', 'services_enabled' => 'خدمات',
                    'complaint_enabled' => 'فرم شکایت', 'congratulations_enabled' => 'پیام تبریک مدیر'
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="admin-status-badge <?php echo e($union->{$field} ? 'is-active' : 'is-inactive'); ?>"><?php echo e($label); ?>: <?php echo e($union->{$field} ? 'فعال' : 'غیرفعال'); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-panel-card">
            <dl class="row mb-0">
                <dt class="col-5">وضعیت</dt><dd class="col-7"><?php echo e($union->is_active ? 'فعال' : 'غیرفعال'); ?></dd>
                <dt class="col-5">نوع اتحادیه</dt><dd class="col-7"><?php echo e($union->union_type_label); ?></dd>
                <dt class="col-5">حالت خبر</dt><dd class="col-7"><?php echo e(\App\Models\GuildUnion::newsModeLabels()[$union->news_mode ?? 'auto'] ?? 'خودکار'); ?></dd>
                <dt class="col-5">مدیر</dt><dd class="col-7"><?php echo e($union->manager_name ?: '—'); ?></dd>
                <dt class="col-5">تلفن</dt><dd class="col-7"><?php echo e($union->phone ?: '—'); ?></dd>
                <dt class="col-5">موبایل</dt><dd class="col-7"><?php echo e($union->mobile ?: '—'); ?></dd>
                <dt class="col-5">ایمیل</dt><dd class="col-7"><?php echo e($union->email ?: '—'); ?></dd>
                <dt class="col-5">وب‌سایت</dt><dd class="col-7"><?php if($union->website): ?><a href="<?php echo e($union->website); ?>" target="_blank">مشاهده</a><?php else: ?> — <?php endif; ?></dd>
                <dt class="col-5">ساعات کاری</dt><dd class="col-7"><?php echo e($union->working_hours ?: '—'); ?></dd>
                <dt class="col-5">ترتیب</dt><dd class="col-7"><?php echo e($union->sort_order); ?></dd>
                <dt class="col-5">اخبار</dt><dd class="col-7"><?php echo e($union->posts_count); ?></dd>
                <dt class="col-5">اطلاعیه‌ها</dt><dd class="col-7"><?php echo e($union->announcements_count); ?></dd>
                <dt class="col-5">کاربران</dt><dd class="col-7"><?php echo e($union->users_count); ?></dd>
            </dl>
        </div>
        <?php if($union->manager_image): ?>
            <div class="admin-panel-card mt-3"><img class="img-fluid rounded" src="<?php echo e(route('media.public', ['path' => $union->manager_image])); ?>" alt="<?php echo e($union->manager_name); ?>"></div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/unions/show.blade.php ENDPATH**/ ?>