<?php $__env->startSection('title', 'اتحادیه‌های صنفی | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', 'فهرست اتحادیه‌های صنفی فعال استان گلستان بر اساس نوع و دسته‌بندی'); ?>

<?php
    $assetImage = fn (?string $path) => image_url($path, 'assets/img/asnaf-gorgan-default.jpg');
    $typeLabels = ($unionTypes ?? collect())->pluck('title', 'slug')->all() ?: \App\Models\GuildUnion::typeLabels();
    $hasTypeTabs = ($typeTabs ?? collect())->filter(fn ($tab) => collect($tab['items'] ?? [])->isNotEmpty())->isNotEmpty();
?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="site-container">
        <nav class="breadcrumb-nav"><a href="<?php echo e(route('home')); ?>">خانه</a><span class="breadcrumb-sep">/</span><span>اتحادیه‌ها</span></nav>
        <h1>اتحادیه‌های صنفی</h1>
    </div>
</div>

<main class="archive-page">
    <div class="site-container">
        <div class="archive-header"><h1>فهرست اتحادیه‌های فعال</h1></div>

        <form class="archive-filters archive-filter-panel" action="<?php echo e(route('guilds.index')); ?>" method="GET">
            <div class="archive-filter-field archive-filter-search">
                <label for="guildSearch">جستجو</label>
                <input id="guildSearch" class="form-control" name="search" value="<?php echo e($search); ?>" placeholder="نام اتحادیه یا مدیر..." type="search">
            </div>
            <div class="archive-filter-field">
                <label for="guildType">نوع اتحادیه</label>
                <select id="guildType" class="form-control" name="type" aria-label="فیلتر نوع اتحادیه">
                    <option value="">همه نوع‌ها</option>
                    <?php $__currentLoopData = $typeLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>" <?php if($type === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="archive-filter-field">
                <label for="guildCategory">دسته‌بندی</label>
                <select id="guildCategory" class="form-control" name="category_id" aria-label="فیلتر دسته‌بندی اتحادیه">
                    <option value="">همه دسته‌بندی‌ها</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php if((string) $categoryId === (string) $category->id): echo 'selected'; endif; ?>><?php echo e($category->title); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="archive-filter-actions">
                <button class="tab-pill active" type="submit">اعمال فیلتر</button>
                <?php if($search !== '' || $type !== '' || $categoryId !== ''): ?><a class="tab-pill" href="<?php echo e(route('guilds.index')); ?>">حذف فیلتر</a><?php endif; ?>
            </div>
        </form>


        <?php if($search === '' && $type === '' && $categoryId === '' && $hasTypeTabs): ?>
            <div class="media-tabs" data-tab-group="guild-types">
                <?php $__currentLoopData = $typeTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button class="media-tab <?php if($loop->first): ?> active <?php endif; ?>" data-tab-target="guild-type-<?php echo e($key); ?>" type="button"><?php echo e(trim(($tab['icon'] ?? '').' '.$tab['label'])); ?></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="tab-panels" data-tab-panels="guild-types">
                <?php $__currentLoopData = $typeTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="tab-panel <?php if($loop->first): ?> active <?php endif; ?>" data-tab-panel="guild-type-<?php echo e($key); ?>">
                        <div class="archive-grid">
                            <?php $__empty_1 = true; $__currentLoopData = $tab['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $union): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php echo $__env->make('frontend.guilds.partials.card', ['union' => $union, 'assetImage' => $assetImage], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="text-center">در این نوع اتحادیه فعالی ثبت نشده است.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="archive-grid">
                <?php $__empty_1 = true; $__currentLoopData = $unions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $union): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php echo $__env->make('frontend.guilds.partials.card', ['union' => $union, 'assetImage' => $assetImage], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-center">اتحادیه فعالی با معیارهای انتخاب‌شده یافت نشد.</p>
                <?php endif; ?>
            </div>
            <?php echo e($unions->links('frontend.partials.pagination')); ?>

        <?php endif; ?>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/guilds/index.blade.php ENDPATH**/ ?>