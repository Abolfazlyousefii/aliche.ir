<?php $__env->startSection('title', 'گالری تصاویر و ویدیوها | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', 'گالری تصاویر و گزارش‌های تصویری اتاق اصناف مرکز استان گلستان'); ?>

<?php $__env->startSection('content'); ?>
<section class="page-header page-header-alt">
  <div class="site-container">
    <h1>گالری تصاویر و ویدیوها</h1>
    <nav class="breadcrumb">
      <a href="<?php echo e(route('home')); ?>">خانه</a>
      <span>گالری</span>
    </nav>
  </div>

  <?php echo e($galleries->links('frontend.partials.pagination')); ?>

</section>

<section class="site-container">
  <div class="section-heading section-heading-centered">
    <h2>دسته‌بندی گالری‌ها</h2>
    <p>مجموعه تصاویر و ویدیوهای رویدادها، جلسات و فعالیت‌های اتاق اصناف</p>
  </div>
  <div class="gallery-albums-grid">
    <?php $__empty_1 = true; $__currentLoopData = $galleries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gallery): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <a href="<?php echo e(route('galleries.show', $gallery->slug)); ?>" class="gallery-album-card">
        <img class="gallery-album-img" src="<?php echo e($gallery->cover_image_url); ?>" alt="<?php echo e($gallery->title); ?>" loading="lazy"/>
        <div class="gallery-album-body">
          <h3><?php echo e($gallery->title); ?></h3>
          <p><?php echo e(plain_text($gallery->description, 120) ?: 'توضیحی برای این گالری ثبت نشده است.'); ?></p>
          <div class="gallery-album-meta">
            <span><?php echo e($gallery->images_count); ?> تصویر</span>
            <span><?php echo e(jalali_date($gallery->published_at) ?: jalali_date($gallery->created_at)); ?></span>
          </div>
        </div>
      </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <a href="<?php echo e(route('galleries.index')); ?>" class="gallery-album-card">
        <img class="gallery-album-img" src="<?php echo e(asset('assets/img/asnaf-gorgan-default.jpg')); ?>" alt="موردی موجود نیست" loading="lazy"/>
        <div class="gallery-album-body">
          <h3>موردی موجود نیست</h3>
          <p>در حال حاضر گالری تصویری برای نمایش منتشر نشده است.</p>
          <div class="gallery-album-meta"><span>۰ تصویر</span><span>—</span></div>
        </div>
      </a>
    <?php endif; ?>
  </div>

  <?php echo e($galleries->links('frontend.partials.pagination')); ?>

</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/galleries/index.blade.php ENDPATH**/ ?>