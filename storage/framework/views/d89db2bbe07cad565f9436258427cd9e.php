<?php $__env->startSection('title', $gallery->title.' | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', plain_text($gallery->description, 160)); ?>

<?php $__env->startSection('content'); ?>
<section class="page-header page-header-alt">
  <div class="site-container">
    <h1><?php echo e($gallery->title); ?></h1>
    <nav class="breadcrumb">
      <a href="<?php echo e(route('home')); ?>">خانه</a>
      <a href="<?php echo e(route('galleries.index')); ?>">گالری</a>
      <span><?php echo e($gallery->title); ?></span>
    </nav>
  </div>
</section>

<section class="site-container">
  <div class="gallery-single-layout">

    <div class="gallery-single-main">
      <div class="gallery-desc"><?php echo e(plain_text($gallery->description) ?: 'توضیحی برای این گالری ثبت نشده است.'); ?></div>

      <div class="gallery-thumbs" data-gallery-group="gallery-<?php echo e($gallery->id); ?>">
        <?php $__empty_1 = true; $__currentLoopData = $gallery->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <div class="gallery-thumb" data-gallery-item="<?php echo e($image->image_url); ?>">
            <img src="<?php echo e($image->image_url); ?>" alt="<?php echo e($image->caption ?? $gallery->title); ?>" loading="lazy"/>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <div class="gallery-thumb" data-gallery-item="<?php echo e($gallery->cover_image_url); ?>">
            <img src="<?php echo e($gallery->cover_image_url); ?>" alt="<?php echo e($gallery->title); ?>" loading="lazy"/>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <aside class="gallery-sidebar">
      <div class="gallery-sidebar-card">
        <h4>سایر گالری‌ها</h4>
        <ul class="gallery-sidebar-list">
          <?php $__empty_1 = true; $__currentLoopData = $relatedGalleries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedGallery): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li><a href="<?php echo e(route('galleries.show', $relatedGallery->slug)); ?>"><?php echo e($relatedGallery->title); ?></a></li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li>گالری مرتبطی برای نمایش وجود ندارد.</li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="gallery-sidebar-card">
        <h4>آمار گالری</h4>
        <ul class="gallery-sidebar-list">
          <li>تعداد تصاویر: <?php echo e($gallery->images->count()); ?></li>
          <li>تاریخ انتشار: <?php echo e(jalali_date($gallery->published_at) ?: '—'); ?></li>
          <li>آخرین بروزرسانی: <?php echo e(jalali_date($gallery->updated_at) ?: '—'); ?></li>
        </ul>
      </div>
    </aside>

  </div>
</section>
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

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/galleries/show.blade.php ENDPATH**/ ?>