<?php $__env->startSection('title', 'خدمات الکترونیک | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', 'لیست خدمات الکترونیک صنفی اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('frontend_variant', 'compact'); ?>
<?php $__env->startSection('footer_links_variant', 'short'); ?>

<?php $__env->startSection('content'); ?>
<section class="page-header page-header-alt portal-hero service-hero">
  <div class="site-container">
    <span class="portal-eyebrow">خدمات آنلاین</span>
    <h1>خدمات الکترونیک</h1>
    <p>راهنماها، فرآیندها و خدمات آنلاین مرتبط با امور صنفی را در یک صفحه مرتب دنبال کنید.</p>
    <nav class="breadcrumb">
      <a href="<?php echo e(route('home')); ?>">خانه</a>
      <span>خدمات الکترونیک</span>
    </nav>
  </div>
</section>

<section class="site-container portal-page">
  <div class="portal-intro-card service-intro-card">
    <div>
      <span class="portal-eyebrow">فهرست خدمات</span>
      <h2>خدمات الکترونیک صنفی</h2>
      <p>خدمت موردنظر را از طریق جستجو یا دسته‌بندی پیدا کنید و جزئیات، راهنما و لینک ورود را مشاهده کنید.</p>
    </div>
    <div class="portal-count-box service-count-box">
      <strong><?php echo e($services->total()); ?></strong>
      <span>خدمت فعال</span>
    </div>
  </div>

  <div class="portal-filter-panel">
    <form class="portal-search-form" action="<?php echo e(route('electronic-services.index')); ?>" method="GET">
      <?php if($activeCategory !== ''): ?>
        <input type="hidden" name="category" value="<?php echo e($activeCategory); ?>">
      <?php endif; ?>
      <input class="form-control" name="search" value="<?php echo e($search); ?>" placeholder="نام یا توضیح خدمت را جستجو کنید..." type="search">
      <button class="portal-primary-action" type="submit">جستجو</button>
      <?php if($search !== '' || $activeCategory !== ''): ?>
        <a class="portal-secondary-action" href="<?php echo e(route('electronic-services.index')); ?>">نمایش همه</a>
      <?php endif; ?>
    </form>

    <div class="portal-tabs" aria-label="فیلتر دسته‌بندی خدمات">
      <a class="portal-tab <?php echo e($activeCategory === '' ? 'active' : ''); ?>" href="<?php echo e(route('electronic-services.index', array_filter(['search' => $search]))); ?>">همه خدمات</a>
      <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a class="portal-tab <?php echo e($activeCategory === $category->slug || $activeCategory === (string) $category->id ? 'active' : ''); ?>" href="<?php echo e(route('electronic-services.index', array_filter(['category' => $category->slug, 'search' => $search]))); ?>"><?php echo e($category->title); ?></a>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>

  <div class="portal-card-grid">
    <?php $__empty_1 = true; $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <article class="portal-card service-card-modern">
        <div class="portal-card-icon"><?php echo e($service->icon ?: '⚡'); ?></div>
        <div class="portal-card-body">
          <span class="portal-card-category"><?php echo e($service->category?->title ?: 'خدمات الکترونیک'); ?></span>
          <h3><?php echo e($service->title); ?></h3>
          <p><?php echo e(plain_text($service->short_description ?: $service->body, 130) ?: 'توضیحات این خدمت به‌زودی تکمیل می‌شود.'); ?></p>
        </div>
        <div class="portal-card-actions">
          <a class="portal-secondary-action" href="<?php echo e(route('electronic-services.show', $service->slug)); ?>">مشاهده جزئیات</a>
          <?php if($service->link_type !== 'none' && $service->link): ?>
            <a class="portal-primary-action" href="<?php echo e($service->link); ?>" target="<?php echo e($service->target); ?>" <?php if($service->target === '_blank'): ?> rel="noopener" <?php endif; ?>>ورود به خدمت</a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="portal-empty-state">
        <strong>خدمت الکترونیکی فعالی برای نمایش یافت نشد.</strong>
        <p>عبارت جستجو یا دسته‌بندی انتخاب‌شده را تغییر دهید.</p>
      </div>
    <?php endif; ?>
  </div>

  <div class="portal-pagination"><?php echo e($services->links('frontend.partials.pagination')); ?></div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/electronic_services/index.blade.php ENDPATH**/ ?>