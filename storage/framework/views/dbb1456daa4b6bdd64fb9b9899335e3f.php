<?php $__env->startSection('title', 'سامانه‌ها | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', 'لیست سامانه‌های پرکاربرد صنفی، خدمات الکترونیک و درگاه‌های مرتبط با اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('frontend_variant', 'compact'); ?>
<?php $__env->startSection('footer_links_variant', 'short'); ?>

<?php $__env->startSection('content'); ?>
<section class="page-header page-header-alt portal-hero">
  <div class="site-container">
    <span class="portal-eyebrow">درگاه‌های آنلاین</span>
    <h1>سامانه‌ها</h1>
    <p>دسترسی سریع و مرتب به سامانه‌ها و درگاه‌های پرکاربرد مرتبط با امور صنفی.</p>
    <nav class="breadcrumb">
      <a href="<?php echo e(route('home')); ?>">خانه</a>
      <span>سامانه‌ها</span>
    </nav>
  </div>
</section>

<section class="site-container portal-page">
  <div class="portal-intro-card">
    <div>
      <span class="portal-eyebrow">فهرست سامانه‌ها</span>
      <h2>سامانه‌های صنفی</h2>
      <p>با جستجو یا انتخاب دسته‌بندی، سامانه مورد نیاز خود را پیدا کنید و در صورت وجود لینک مستقیم وارد آن شوید.</p>
    </div>
    <div class="portal-count-box">
      <strong><?php echo e($systems->total()); ?></strong>
      <span>سامانه فعال</span>
    </div>
  </div>

  <div class="portal-filter-panel">
    <form class="portal-search-form" action="<?php echo e(route('systems.index')); ?>" method="GET">
      <?php if($activeCategory !== ''): ?>
        <input type="hidden" name="category" value="<?php echo e($activeCategory); ?>">
      <?php endif; ?>
      <input class="form-control" name="search" value="<?php echo e($search); ?>" placeholder="نام یا توضیح سامانه را جستجو کنید..." type="search">
      <button class="portal-primary-action" type="submit">جستجو</button>
      <?php if($search !== '' || $activeCategory !== ''): ?>
        <a class="portal-secondary-action" href="<?php echo e(route('systems.index')); ?>">نمایش همه</a>
      <?php endif; ?>
    </form>

    <div class="portal-tabs" aria-label="فیلتر دسته‌بندی سامانه‌ها">
      <a class="portal-tab <?php echo e($activeCategory === '' ? 'active' : ''); ?>" href="<?php echo e(route('systems.index', array_filter(['search' => $search]))); ?>">همه سامانه‌ها</a>
      <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a class="portal-tab <?php echo e($activeCategory === $category->slug || $activeCategory === (string) $category->id ? 'active' : ''); ?>" href="<?php echo e(route('systems.index', array_filter(['category' => $category->slug, 'search' => $search]))); ?>"><?php echo e($category->title); ?></a>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>

  <div class="portal-card-grid">
    <?php $__empty_1 = true; $__currentLoopData = $systems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $system): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <article class="portal-card system-card-modern">
        <div class="portal-card-icon"><?php echo e($system->icon ?: '💻'); ?></div>
        <div class="portal-card-body">
          <span class="portal-card-category"><?php echo e($system->category?->title ?: 'سامانه صنفی'); ?></span>
          <h3><?php echo e($system->title); ?></h3>
          <p><?php echo e(plain_text($system->short_description ?: $system->description, 130) ?: 'توضیحات این سامانه به‌زودی تکمیل می‌شود.'); ?></p>
        </div>
        <div class="portal-card-actions">
          <a class="portal-secondary-action" href="<?php echo e(route('systems.show', $system->slug)); ?>">جزئیات</a>
          <?php if($system->link): ?>
            <a class="portal-primary-action" href="<?php echo e($system->link); ?>" target="<?php echo e($system->target); ?>" <?php if($system->target === '_blank'): ?> rel="noopener" <?php endif; ?>>ورود به سامانه</a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="portal-empty-state">
        <strong>سامانه فعالی برای نمایش یافت نشد.</strong>
        <p>عبارت جستجو یا دسته‌بندی انتخاب‌شده را تغییر دهید.</p>
      </div>
    <?php endif; ?>
  </div>

  <div class="portal-pagination"><?php echo e($systems->links('frontend.partials.pagination')); ?></div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/systems/index.blade.php ENDPATH**/ ?>