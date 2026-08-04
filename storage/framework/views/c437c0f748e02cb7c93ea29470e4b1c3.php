<?php $__env->startSection('title', ($page->meta_title ?: $page->title).' | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', $page->meta_description ?: plain_text($page->excerpt ?: $page->body, 160)); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
<div class="site-container">
<nav class="breadcrumb-nav">
<a href="<?php echo e(route('home')); ?>">خانه</a>
<span class="breadcrumb-sep">/</span>
<span><?php echo e($page->title ?? 'صفحه'); ?></span>
</nav>
<h1><?php echo e($page->title ?? 'صفحه'); ?></h1>
</div>
</div>

<main class="blank-page">
<div class="site-container blank-page-content">
<?php if($page->featured_image): ?>
<img class="post-featured-img" src="<?php echo e(image_url($page->featured_image)); ?>" alt="<?php echo e($page->title); ?>" loading="lazy">
<?php endif; ?>
<?php if($page->excerpt): ?>
<div class="post-excerpt"><?php echo $page->excerpt; ?></div>
<?php endif; ?>
<?php echo $page->body ?: '<p>محتوایی برای این صفحه ثبت نشده است.</p>'; ?>

</div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/pages/show.blade.php ENDPATH**/ ?>