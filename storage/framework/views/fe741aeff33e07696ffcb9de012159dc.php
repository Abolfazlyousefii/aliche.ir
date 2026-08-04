<?php $__env->startSection('title', 'آرشیو ویدیوها | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', 'آرشیو ویدیوهای آموزشی، خبری و گزارش‌های تصویری اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('footer_links_variant', 'short'); ?>

<?php $__env->startSection('content'); ?>
<section class="page-header page-header-alt">
  <div class="site-container">
    <h1>آرشیو ویدیوها</h1>
    <nav class="breadcrumb">
      <a href="<?php echo e(route('home')); ?>">خانه</a>
      <span>ویدیوها</span>
    </nav>
  </div>
</section>

<section class="site-container">
  <div class="section-heading section-heading-centered">
    <h2>ویدیوهای منتشرشده</h2>
    <p>گزارش‌های تصویری، آموزش‌ها و محتوای چندرسانه‌ای اتاق اصناف</p>
  </div>

  <form class="archive-filters mb-4" action="<?php echo e(route('videos.index')); ?>" method="GET">
    <input class="form-control" name="search" value="<?php echo e($search); ?>" placeholder="جستجو در ویدیوها..." type="search">
    <button class="tab-pill active" type="submit">جستجو</button>
    <?php if($search !== ''): ?><a class="tab-pill" href="<?php echo e(route('videos.index')); ?>">حذف جستجو</a><?php endif; ?>
  </form>

  <div class="media-grid">
    <?php $__empty_1 = true; $__currentLoopData = $videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <article class="media-card <?php echo e($loop->first ? 'media-card-lg' : ''); ?>">
        <a href="<?php echo e(route('videos.show', $video->slug)); ?>">
          <div class="tourism-img-wrap">
            <img alt="<?php echo e($video->title); ?>" src="<?php echo e(image_url($video->cover_image)); ?>" loading="lazy"/>
            <div class="tourism-badge"><?php echo e($video->type_label); ?></div>
          </div>
          <div class="tourism-card-body">
            <h3><?php echo e($video->title); ?></h3>
            <p><?php echo e(plain_text($video->description, 110) ?: 'ویدیوی منتشرشده اتاق اصناف مرکز استان گلستان'); ?></p>
          </div>
        </a>
      </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <p class="text-muted text-center">ویدیوی منتشرشده‌ای یافت نشد.</p>
    <?php endif; ?>
  </div>

  <?php echo e($videos->links('frontend.partials.pagination')); ?>

</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/videos/index.blade.php ENDPATH**/ ?>