<?php $__env->startSection('title', $system->title.' | سامانه‌ها'); ?>
<?php $__env->startSection('meta_description', plain_text($system->short_description ?: $system->description, 160)); ?>
<?php $__env->startSection('frontend_variant', 'compact'); ?>
<?php $__env->startSection('footer_links_variant', 'short'); ?>

<?php $__env->startSection('content'); ?>
<section class="page-header page-header-alt portal-hero portal-detail-hero">
  <div class="site-container">
    <span class="portal-eyebrow">جزئیات سامانه</span>
    <h1><?php echo e($system->title); ?></h1>
    <p><?php echo e(plain_text($system->short_description ?: $system->description, 180) ?: 'اطلاعات این سامانه در حال تکمیل است.'); ?></p>
    <nav class="breadcrumb">
      <a href="<?php echo e(route('home')); ?>">خانه</a>
      <a href="<?php echo e(route('systems.index')); ?>">سامانه‌ها</a>
      <span><?php echo e($system->title); ?></span>
    </nav>
  </div>
</section>

<section class="site-container portal-detail-page">
  <div class="portal-detail-layout">
    <article class="portal-detail-main">
      <?php if($system->image): ?>
        <figure class="portal-detail-cover">
          <img src="<?php echo e($system->image_url); ?>" alt="<?php echo e($system->title); ?>" loading="lazy">
        </figure>
      <?php endif; ?>

      <div class="portal-detail-card">
        <div class="portal-detail-heading">
          <div class="portal-detail-icon"><?php echo e($system->icon ?: '💻'); ?></div>
          <div>
            <span class="portal-card-category"><?php echo e($system->category?->title ?: 'سامانه'); ?></span>
            <h2><?php echo e($system->title); ?></h2>
          </div>
        </div>
        <?php if($system->short_description): ?>
          <p class="portal-lead"><?php echo e(plain_text($system->short_description)); ?></p>
        <?php endif; ?>
        <div class="portal-rich-text"><?php echo rich_text($system->description, '<p>توضیحات این سامانه هنوز تکمیل نشده است.</p>'); ?></div>
        <?php if($system->link): ?>
          <a class="portal-primary-action portal-main-link" href="<?php echo e($system->link); ?>" target="<?php echo e($system->target); ?>" <?php if($system->target === '_blank'): ?> rel="noopener" <?php endif; ?>>ورود به سامانه</a>
        <?php endif; ?>
      </div>
    </article>

    <aside class="portal-detail-sidebar">
      <div class="portal-sidebar-card">
        <h3>اطلاعات سریع</h3>
        <div class="portal-stat-row"><span>دسته‌بندی</span><strong><?php echo e($system->category?->title ?: 'سامانه'); ?></strong></div>
        <div class="portal-stat-row"><span>نوع دسترسی</span><strong><?php echo e($system->link ? 'دارای لینک ورود' : 'اطلاعاتی'); ?></strong></div>
        <a class="portal-secondary-action portal-full-action" href="<?php echo e(route('systems.index')); ?>">بازگشت به سامانه‌ها</a>
      </div>

      <div class="portal-sidebar-card">
        <h3>سامانه‌های مرتبط</h3>
        <div class="portal-related-list">
          <?php $__empty_1 = true; $__currentLoopData = $relatedSystems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('systems.show', $related->slug)); ?>" class="portal-related-item">
              <span><?php echo e($related->icon ?: '💻'); ?></span>
              <div><strong><?php echo e($related->title); ?></strong><small><?php echo e($related->category?->title ?: 'سامانه'); ?></small></div>
            </a>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-muted mb-0">سامانه مرتبطی برای نمایش وجود ندارد.</p>
          <?php endif; ?>
        </div>
      </div>
    </aside>
  </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/systems/show.blade.php ENDPATH**/ ?>