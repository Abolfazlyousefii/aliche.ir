<?php $__env->startSection('title', $post->title.' | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', plain_text($post->short_description ?? $post->description, 160)); ?>

<?php $__env->startSection('content'); ?>
<?php
    $decodedShortDescription = html_entity_decode((string) $post->short_description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $decodedBody = html_entity_decode((string) $post->body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
?>
<div class="page-header">
<div class="site-container">
<nav class="breadcrumb-nav">
<a href="<?php echo e(route('home')); ?>">خانه</a>
<span class="breadcrumb-sep">/</span>
<a href="<?php echo e(route('posts.index')); ?>">اخبار</a>
<span class="breadcrumb-sep">/</span>
<span><?php echo e($post->title); ?></span>
</nav>
<h1><?php echo e($post->title); ?></h1>
</div>
</div>

<main>
<div class="site-container single-post-layout">
<article class="single-post-article">
<img alt="<?php echo e($post->title); ?>" class="post-featured-img" src="<?php echo e($post->featured_image_url); ?>" loading="lazy"/>
<div class="single-post-body">
<div class="post-meta">
<span>تاریخ انتشار: <?php echo e(jalali_date($post->published_at) ?: jalali_date($post->created_at)); ?></span>
<span class="dot"></span>
<span>بازدید: <?php echo e(fa_number($post->views_count)); ?></span>
<?php if($post->type === 'video'): ?>
<span class="dot"></span>
<span>🎥 ویدیویی</span>
<?php elseif($post->galleries_count > 0): ?>
<span class="dot"></span>
<span>🖼 دارای گالری</span>
<?php endif; ?>
</div>
<h1><?php echo e($post->title); ?></h1>
<?php if(trim(strip_tags($decodedShortDescription)) !== ''): ?>
<div class="post-excerpt">
<?php echo $decodedShortDescription; ?>

</div>
<?php endif; ?>
<div class="post-content">
<?php echo $decodedBody ?: '<p>محتوایی برای این نوشته ثبت نشده است.</p>'; ?>

</div>
<?php if($post->galleries->isNotEmpty() || $post->mediaGallery->isNotEmpty()): ?>
<div class="post-gallery" data-gallery-group="post-<?php echo e($post->id); ?>">
<h3>گالری تصاویر</h3>
<div class="post-gallery-grid">
<?php $__currentLoopData = $post->mediaGallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="post-gallery-item" data-gallery-item="<?php echo e($media->url); ?>"><img src="<?php echo e($media->url); ?>" alt="<?php echo e($media->alt_text ?: $post->title); ?>" loading="lazy"/></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__currentLoopData = $post->galleries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="post-gallery-item" data-gallery-item="<?php echo e($image->image_url); ?>"><img src="<?php echo e($image->image_url); ?>" alt="<?php echo e($image->caption ?? $post->title); ?>" loading="lazy"/></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
</div>
<?php endif; ?>
<div class="post-tags">
<?php if($post->union): ?><a class="post-tag" href="<?php echo e(route('posts.index', ['union_id' => $post->union_id])); ?>"><?php echo e($post->union->display_title); ?></a><?php endif; ?>
<?php if($post->category): ?><a class="post-tag" href="<?php echo e(route('posts.index', ['category_id' => $post->category_id])); ?>"><?php echo e($post->category->title); ?></a><?php endif; ?>
<a class="post-tag" href="<?php echo e(route('posts.index', ['search' => $post->type === 'video' ? 'ویدیو' : 'نوشته'])); ?>"><?php echo e($post->type === 'video' ? 'ویدیو' : 'نوشته'); ?></a>
</div>
<div class="post-nav">
<?php if($previousPost): ?>
<a class="post-nav-link post-nav-prev" href="<?php echo e(route('posts.show', $previousPost->slug)); ?>">
<span>→ نوشته قبلی</span>
<strong><?php echo e($previousPost->title); ?></strong>
</a>
<?php else: ?>
<span class="post-nav-link post-nav-prev"><span>→ نوشته قبلی</span><strong>وجود ندارد</strong></span>
<?php endif; ?>
<?php if($nextPost): ?>
<a class="post-nav-link post-nav-next" href="<?php echo e(route('posts.show', $nextPost->slug)); ?>">
<span>نوشته بعدی ←</span>
<strong><?php echo e($nextPost->title); ?></strong>
</a>
<?php else: ?>
<span class="post-nav-link post-nav-next"><span>نوشته بعدی ←</span><strong>وجود ندارد</strong></span>
<?php endif; ?>
</div>
</div>
</article>
<aside class="single-post-sidebar">
<div class="sidebar-card">
<h3>آخرین نوشته‌ها</h3>
<ul class="sidebar-list">
<?php $__empty_1 = true; $__currentLoopData = $relatedPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedPost): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<li><a href="<?php echo e(route('posts.show', $relatedPost->slug)); ?>"><?php echo e($relatedPost->title); ?></a></li>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<li>نوشته مرتبطی برای نمایش وجود ندارد.</li>
<?php endif; ?>
</ul>
</div>
<div class="sidebar-card">
<h3>برچسب‌ها</h3>
<div class="post-tags">
<a class="post-tag" href="<?php echo e(route('posts.index', ['search' => 'اصناف'])); ?>">اصناف</a>
<a class="post-tag" href="<?php echo e(route('posts.index', ['search' => 'گرگان'])); ?>">گرگان</a>
<?php if($post->type === 'video'): ?><a class="post-tag" href="<?php echo e(route('posts.index', ['search' => 'ویدیو'])); ?>">ویدیو</a><?php endif; ?>
</div>
</div>
</aside>
</div>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('after_footer'); ?>
<div class="lightbox">
  <button class="lightbox-close" aria-label="بستن">✕</button>
  <button class="lightbox-nav lightbox-prev" aria-label="قبلی">‹</button>
  <button class="lightbox-nav lightbox-next" aria-label="بعدی">›</button>
  <img class="lightbox-img" src="" alt="تصویر بزرگ"/>
  <div class="lightbox-counter"></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/posts/show.blade.php ENDPATH**/ ?>