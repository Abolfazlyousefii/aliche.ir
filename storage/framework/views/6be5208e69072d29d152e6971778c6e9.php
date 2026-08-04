<?php $__env->startSection('title', 'آرشیو نوشته‌ها | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', 'آرشیو کامل اخبار، اطلاعیه‌ها و نوشته‌های اتاق اصناف مرکز استان گلستان'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
<div class="site-container">
<nav class="breadcrumb-nav">
<a href="<?php echo e(route('home')); ?>">خانه</a>
<span class="breadcrumb-sep">/</span>
<span>آرشیو نوشته‌ها</span>
</nav>
<h1>آرشیو نوشته‌ها</h1>
</div>
</div>

<main class="archive-page">
<div class="site-container">
<div class="archive-header">
<h1>همه نوشته‌ها</h1>
</div>

<?php
    $baseFilterQuery = array_filter([
        'search' => $search ?: null,
        'union_id' => $unionId ?: null,
        'date' => $date ?: null,
    ], fn ($value) => filled($value));
?>
<section class="archive-category-section" aria-labelledby="archive-category-title">
<div class="archive-category-head">
<div>
<span class="archive-category-kicker">مرتب‌سازی اخبار</span>
<h2 id="archive-category-title">دسته‌بندی‌های اخبار</h2>
</div>
<a class="archive-category-reset <?php echo e($categoryId ? '' : 'active'); ?>" href="<?php echo e(route('posts.index', $baseFilterQuery)); ?>">همه اخبار</a>
</div>
<div class="archive-category-grid">
<?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<?php
    $categoryQuery = array_merge($baseFilterQuery, ['category_id' => $category->id]);
?>
<a class="archive-category-chip <?php echo e((string) $categoryId === (string) $category->id ? 'active' : ''); ?>" href="<?php echo e(route('posts.index', $categoryQuery)); ?>">
<span><?php echo e($category->icon ?: 'خبر'); ?></span>
<strong><?php echo e($category->title); ?></strong>
<small><?php echo e(number_format($category->published_posts_count)); ?> نوشته</small>
</a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<div class="archive-category-empty">هنوز دسته‌بندی فعالی برای اخبار ثبت نشده است.</div>
<?php endif; ?>
</div>
</section>

<div class="archive-layout"><div class="archive-main"><div class="archive-grid">
<?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<article class="archive-card">
<a href="<?php echo e(route('posts.show', $post->slug)); ?>">
<img alt="<?php echo e($post->title); ?>" class="archive-card-img" src="<?php echo e($post->featured_image_url); ?>" loading="lazy"/>
<div class="archive-card-body">
<?php if($post->type === 'video'): ?><span class="card-cat">🎥 ویدیو</span><?php elseif($post->galleries_count > 0): ?><span class="card-cat">🖼 گالری</span><?php endif; ?>
<h2><?php echo e($post->title); ?></h2>
<p><?php echo e(plain_text($post->excerpt ?: $post->short_description ?: $post->body, 120)); ?></p>
<span class="card-date"><?php echo e(jalali_date($post->published_at) ?: jalali_date($post->created_at)); ?></span>
</div>
</a>
</article>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<p class="text-muted">هیچ پست فعالی برای نمایش وجود ندارد.</p>
<?php endif; ?>
</div>
<?php echo e($posts->links('frontend.partials.pagination')); ?>

</div>
<aside class="archive-sidebar">
<div class="sidebar-card">
<h3>جستجو در نوشته‌ها</h3>
<form action="<?php echo e(route('posts.index')); ?>" method="GET">
<input class="search-input" name="search" type="search" value="<?php echo e($search); ?>" placeholder="جستجوی خبر یا نوشته...">
<?php if($categoryId): ?><input type="hidden" name="category_id" value="<?php echo e($categoryId); ?>"><?php endif; ?>
<?php if($unionId): ?><input type="hidden" name="union_id" value="<?php echo e($unionId); ?>"><?php endif; ?>
<?php if($date): ?><input type="hidden" name="date" value="<?php echo e($date); ?>"><?php endif; ?>
<button class="tab-pill active" type="submit">جستجو</button>
</form>
</div>
<div class="sidebar-card">
<h3>اتحادیه‌ها</h3>
<ul class="sidebar-list">
<?php $__empty_1 = true; $__currentLoopData = $unions->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $union): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<li><a href="<?php echo e(route('posts.index', ['union_id' => $union->id])); ?>"><?php echo e($union->display_title); ?></a></li>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<li>اتحادیه‌ای برای فیلتر وجود ندارد.</li>
<?php endif; ?>
</ul>
</div>
</aside>
</div>
</div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/posts/index.blade.php ENDPATH**/ ?>