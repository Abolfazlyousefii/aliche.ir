<?php $__env->startSection('title', 'گردشگری گرگان | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', 'معرفی جاذبه‌های گردشگری، تاریخی و بازارهای گرگان'); ?>

<?php $__env->startSection('content'); ?>
<section class="page-header page-header-alt page-header-tourism">
  <div class="site-container">
    <h1>گردشگری در گرگان</h1>
    <nav class="breadcrumb">
      <a href="<?php echo e(route('home')); ?>">خانه</a>
      <span>گردشگری گرگان</span>
    </nav>
  </div>
</section>

<section class="tourism-intro">
  <div class="site-container">
    <div class="tourism-intro-grid">
      <div class="tourism-intro-text">
        <h2><?php echo e($tourismSettings['tourism.intro_title'] ?? 'به شهر گرگان خوش آمدید'); ?></h2>
        <p><?php echo e(plain_text($tourismSettings['tourism.intro_text'] ?? 'گرگان با جاذبه‌های طبیعی، تاریخی و بازارهای متنوع، یکی از مقصدهای مهم گردشگری استان گلستان است.')); ?></p>
        <p><?php echo e(plain_text($tourismSettings['tourism.intro_subtext'] ?? 'اتاق اصناف مرکز استان گلستان با همراهی اتحادیه‌های صنفی، پشتیبان فعالان حوزه گردشگری و مسافران محترم است.')); ?></p>
        <div class="tourism-stats">
          <div class="tourism-stat"><strong><?php echo e($places->count()); ?></strong><span>جاذبه ثبت‌شده</span></div>
          <div class="tourism-stat"><strong><?php echo e($tourismNature->count()); ?></strong><span>طبیعت‌گردی</span></div>
          <div class="tourism-stat"><strong><?php echo e($tourismShop->count()); ?></strong><span>بازار و خرید</span></div>
        </div>
      </div>
      <div class="tourism-intro-img">
        <img src="<?php echo e($places->first()?->home_image_url ?? asset('assets/img/asnaf-gorgan-default.jpg')); ?>" alt="گرگان" loading="lazy"/>
      </div>
    </div>
  </div>
</section>

<section class="tourism-attractions">
  <div class="site-container">
    <div class="section-heading section-heading-centered">
      <h2>جاذبه‌های گردشگری</h2>
      <p>با معروف‌ترین جاذبه‌های طبیعی، تاریخی و تفریحی گرگان آشنا شوید</p>
    </div>
    <div class="tabs" data-tab-group="tour-attractions" role="tablist">
      <?php $__currentLoopData = $tourismPanels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $panel => $panelData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button class="tab-pill <?php echo e($loop->first ? 'active' : ''); ?>" data-tab-target="<?php echo e($panel); ?>" type="button"><?php echo e($panelData['label']); ?></button>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="tab-panels" data-tab-panels="tour-attractions">
      <?php $__currentLoopData = $tourismPanels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $panel => $panelData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php ($panelPlaces = $panelData['items']); ?>
        <div class="tab-panel <?php echo e($loop->first ? 'active' : ''); ?>" data-tab-panel="<?php echo e($panel); ?>">
          <div class="tourism-grid tourism-grid-lg">
            <?php $__empty_1 = true; $__currentLoopData = $panelPlaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $place): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <div class="tourism-card tourism-card-lg">
                <a href="<?php echo e(route('tourism.show', $place->slug)); ?>">
                  <div class="tourism-img-wrap">
                    <img src="<?php echo e($place->home_image_url); ?>" alt="<?php echo e($place->title); ?>" loading="lazy"/>
                    <div class="tourism-badge"><?php echo e($place->home_badge); ?></div>
                  </div>
                </a>
                <div class="tourism-card-body">
                  <h3><?php echo e($place->title); ?></h3>
                  <p><?php echo e(plain_text($place->home_description, 150) ?: 'توضیحی برای این جاذبه ثبت نشده است.'); ?></p>
                  <div class="tourism-card-footer"><span><?php echo e($place->home_badge); ?></span></div>
                </div>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <div class="tourism-card tourism-card-lg">
                <a href="<?php echo e(route('tourism.index')); ?>">
                  <div class="tourism-img-wrap">
                    <img src="<?php echo e(asset('assets/img/asnaf-gorgan-default.jpg')); ?>" alt="موردی موجود نیست" loading="lazy"/>
                    <div class="tourism-badge">گردشگری</div>
                  </div>
                </a>
                <div class="tourism-card-body">
                  <h3>موردی موجود نیست</h3>
                  <p>در حال حاضر آیتمی برای این دسته گردشگری ثبت نشده است.</p>
                  <div class="tourism-card-footer"><span>گردشگری</span></div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>
</section>

<section class="tourism-gallery">
  <div class="site-container">
    <div class="section-heading section-heading-centered">
      <h2>گالری تصاویر گردشگری</h2>
      <p>تصاویری از زیبایی‌های طبیعی و تاریخی گرگان</p>
    </div>
    <div class="tourism-gallery-grid" data-gallery-group="tourism-gallery">
      <?php $__empty_1 = true; $__currentLoopData = $galleryPlaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $place): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="tourism-gallery-item" data-gallery-item="<?php echo e($place->home_image_url); ?>"><img src="<?php echo e($place->home_image_url); ?>" alt="<?php echo e($place->title); ?>" loading="lazy"/></div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="tourism-gallery-item" data-gallery-item="<?php echo e(asset('assets/img/asnaf-gorgan-default.jpg')); ?>"><img src="<?php echo e(asset('assets/img/asnaf-gorgan-default.jpg')); ?>" alt="موردی موجود نیست" loading="lazy"/></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="tourism-cta">
  <div class="site-container">
    <div class="tourism-cta-box">
      <h2>اصناف مرتبط با گردشگری</h2>
      <p>اتاق اصناف مرکز استان گلستان با اتحادیه‌های هتل‌داران، رستوران‌داران، صنایع دستی و آژانس‌های مسافرتی در خدمت فعالان این حوزه است.</p>
      <a href="<?php echo e(route('guilds.index')); ?>" class="cta-button">مشاهده اتحادیه‌های صنفی</a>
    </div>
  </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/tourism/index.blade.php ENDPATH**/ ?>