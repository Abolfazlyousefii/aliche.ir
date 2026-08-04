<?php $__env->startSection('title', $service->title.' | خدمات الکترونیک'); ?>
<?php $__env->startSection('meta_description', plain_text($service->short_description ?: $service->body, 160)); ?>
<?php $__env->startSection('frontend_variant', 'compact'); ?>
<?php $__env->startSection('footer_links_variant', 'short'); ?>

<?php $__env->startSection('content'); ?>
<section class="page-header page-header-alt portal-hero portal-detail-hero service-hero">
  <div class="site-container">
    <span class="portal-eyebrow">جزئیات خدمت</span>
    <h1><?php echo e($service->title); ?></h1>
    <p><?php echo e(plain_text($service->short_description ?: $service->body, 180) ?: 'اطلاعات این خدمت در حال تکمیل است.'); ?></p>
    <nav class="breadcrumb">
      <a href="<?php echo e(route('home')); ?>">خانه</a>
      <a href="<?php echo e(route('electronic-services.index')); ?>">خدمات الکترونیک</a>
      <span><?php echo e($service->title); ?></span>
    </nav>
  </div>
</section>

<section class="site-container portal-detail-page">
  <div class="portal-detail-layout">
    <article class="portal-detail-main">
      <?php if($service->image): ?>
        <figure class="portal-detail-cover">
          <img src="<?php echo e(Storage::url($service->image)); ?>" alt="<?php echo e($service->title); ?>" loading="lazy">
        </figure>
      <?php endif; ?>

      <div class="portal-detail-card">
        <div class="portal-detail-heading">
          <div class="portal-detail-icon service-detail-icon"><?php echo e($service->icon ?: '⚡'); ?></div>
          <div>
            <span class="portal-card-category"><?php echo e($service->category?->title ?: 'خدمات الکترونیک'); ?></span>
            <h2><?php echo e($service->title); ?></h2>
          </div>
        </div>
        <?php if($service->short_description): ?>
          <p class="portal-lead"><?php echo e(plain_text($service->short_description)); ?></p>
        <?php endif; ?>
        <div class="portal-rich-text"><?php echo rich_text($service->body, '<p>توضیحات این خدمت هنوز تکمیل نشده است.</p>'); ?></div>
        <?php if($service->link_type !== 'none' && $service->link): ?>
          <a class="portal-primary-action portal-main-link" href="<?php echo e($service->link); ?>" target="<?php echo e($service->target); ?>" <?php if($service->target === '_blank'): ?> rel="noopener" <?php endif; ?>>ورود به خدمت</a>
        <?php endif; ?>
      </div>
    </article>

    <aside class="portal-detail-sidebar">
      <div class="portal-sidebar-card">
        <h3>اطلاعات سریع</h3>
        <div class="portal-stat-row"><span>دسته‌بندی</span><strong><?php echo e($service->category?->title ?: 'خدمات الکترونیک'); ?></strong></div>
        <div class="portal-stat-row"><span>نوع دسترسی</span><strong><?php echo e($service->link_type !== 'none' && $service->link ? 'دارای لینک ورود' : 'راهنمای اطلاعاتی'); ?></strong></div>
        <a class="portal-secondary-action portal-full-action" href="<?php echo e(route('electronic-services.index')); ?>">بازگشت به خدمات</a>
      </div>

      <div class="portal-sidebar-card">
        <h3>خدمات مرتبط</h3>
        <div class="portal-related-list">
          <?php $__empty_1 = true; $__currentLoopData = $relatedServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('electronic-services.show', $related->slug)); ?>" class="portal-related-item">
              <span><?php echo e($related->icon ?: '⚡'); ?></span>
              <div><strong><?php echo e($related->title); ?></strong><small><?php echo e($related->category?->title ?: 'خدمات الکترونیک'); ?></small></div>
            </a>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-muted mb-0">خدمت مرتبطی برای نمایش وجود ندارد.</p>
          <?php endif; ?>
        </div>
      </div>
    </aside>
  </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/electronic_services/show.blade.php ENDPATH**/ ?>