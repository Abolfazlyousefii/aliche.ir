<?php $__env->startSection('title', 'آرشیو اطلاعیه‌ها | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', 'آرشیو اطلاعیه‌های منتشرشده اتاق اصناف مرکز استان گلستان'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="site-container">
        <nav class="breadcrumb-nav">
            <a href="<?php echo e(route('home')); ?>">خانه</a>
            <span class="breadcrumb-sep">/</span>
            <span>آرشیو اطلاعیه‌ها</span>
        </nav>
        <h1>آرشیو اطلاعیه‌ها</h1>
    </div>
</div>

<main class="archive-page">
    <div class="site-container">
        <div class="archive-header">
            <h1>همه اطلاعیه‌های فعال</h1>
        </div>

        <form class="archive-filters archive-filter-panel" action="<?php echo e(route('announcements.index')); ?>" method="GET">
            <div class="archive-filter-field archive-filter-search">
                <label for="announcementSearch">جستجو</label>
                <input id="announcementSearch" class="form-control" name="search" value="<?php echo e($search); ?>" placeholder="عنوان یا متن اطلاعیه..." type="search">
            </div>
            <div class="archive-filter-field">
                <label for="announcementCategory">دسته‌بندی</label>
                <select id="announcementCategory" class="form-control" name="category_id" aria-label="فیلتر دسته‌بندی">
                    <option value="">همه دسته‌بندی‌ها</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php if((string) $categoryId === (string) $category->id): echo 'selected'; endif; ?>><?php echo e($category->title); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="archive-filter-field">
                <label for="announcementUnion">اتحادیه</label>
                <select id="announcementUnion" class="form-control" name="union_id" aria-label="فیلتر اتحادیه">
                    <option value="">همه اتحادیه‌ها و اطلاعیه‌های عمومی</option>
                    <?php $__currentLoopData = $unions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $union): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($union->id); ?>" <?php if((string) $unionId === (string) $union->id): echo 'selected'; endif; ?>><?php echo e($union->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="archive-filter-actions">
                <button class="tab-pill active" type="submit">اعمال فیلتر</button>
                <?php if($search !== '' || $categoryId || $unionId): ?>
                    <a class="tab-pill" href="<?php echo e(route('announcements.index')); ?>">حذف فیلتر</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="archive-grid">
            <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="archive-card">
                    <a href="<?php echo e(route('announcements.show', $announcement->slug)); ?>">
                        <img alt="<?php echo e($announcement->title); ?>" class="archive-card-img" src="<?php echo e($announcement->featured_image ? image_url($announcement->featured_image) : asset('assets/img/asnaf-gorgan-default.jpg')); ?>">
                        <div class="archive-card-body">
                            <span class="card-cat"><?php echo e($announcement->category?->title ?: 'اطلاعیه'); ?></span>
                            <h2><?php echo e($announcement->title); ?></h2>
                            <p><?php echo e(plain_text($announcement->excerpt ?: $announcement->body, 150)); ?></p>
                            <span class="card-date"><?php echo e(jalali_date($announcement->published_at)); ?></span>
                            <?php if($announcement->union): ?><span class="card-date"><?php echo e($announcement->union->name); ?></span><?php endif; ?>
                            <?php if($announcement->is_important): ?><span class="card-date">مهم</span><?php endif; ?>
                        </div>
                    </a>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-center">اطلاعیه‌ای با معیارهای انتخاب‌شده یافت نشد.</p>
            <?php endif; ?>
        </div>

        <div class="mt-4">
            <?php echo e($announcements->links('frontend.partials.pagination')); ?>

        </div>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/announcements/index.blade.php ENDPATH**/ ?>