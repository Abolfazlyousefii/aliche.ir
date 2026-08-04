<?php
    $settings = app(\App\Services\SettingService::class);
    $topItems = app(\App\Services\MenuService::class)->items('top');
    $mainItems = app(\App\Services\MenuService::class)->items('main');
    $topText = $settings->get('header.top_text', 'اتاق اصناف مرکز استان گلستان؛ پشتیبان کسب‌وکارهای صنفی');
    $headerButtons = collect($settings->get('header.header_buttons', [[
        'title' => 'سامانه خدمات صنفی',
        'url' => route('systems.index'),
        'icon' => '●',
        'target' => '_self',
        'is_active' => true,
    ]]))->filter(fn ($button) => (bool) ($button['is_active'] ?? true) && filled($button['title'] ?? null) && filled($button['url'] ?? null))->values();
    $siteTitle = $settings->get('site.site_title', 'اتاق اصناف مرکز استان گلستان');
    $logo = image_url(
        $settings->get('site.site_logo')
            ?: $settings->get('header.desktop_logo')
            ?: $settings->get('header.header_logo'),
        'assets/img/asnaf-logo.svg'
    );
    $phone = $settings->get('site.phone', '01732152912');
    $contactText = $settings->get('header.contact_button_text', 'تماس با اتاق');
    $jalaliParts = explode('/', jalali_format(now(), 'Y/m/d'));
    $weekdays = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];
    $months = [1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    $todayLabel = ($weekdays[now()->dayOfWeek] ?? '') . '، ' . (int) ($jalaliParts[2] ?? 1) . ' ' . ($months[(int) ($jalaliParts[1] ?? 1)] ?? '') . ' ' . ($jalaliParts[0] ?? '');
    $todayLabel = fa_number($todayLabel);
?>
<header class="site-header">
<div class="header-top site-container">
<a class="brand-note" href="<?php echo e(route('home')); ?>" aria-label="<?php echo e($siteTitle); ?>">
<span class="brand-note-media">
<img alt="<?php echo e($siteTitle); ?>" class="header-logo-simple" src="<?php echo e($logo); ?>"/>
</span>
<span class="brand-note-copy">
<span><?php echo e($todayLabel); ?></span>
<strong><?php echo e($topText); ?></strong>
<span class="brand-note-title"><?php echo e($siteTitle); ?></span>
</span>
</a>

<div class="header-left-actions" aria-label="راه‌های دسترسی سریع هدر">
<?php $__empty_1 = true; $__currentLoopData = $headerButtons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $button): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<a class="header-service-pill" href="<?php echo e($button['url']); ?>" target="<?php echo e($button['target'] ?? '_self'); ?>" <?php if(($button['target'] ?? '_self') === '_blank'): ?> rel="noopener" <?php endif; ?>><span><?php echo e($button['icon'] ?? ''); ?></span> <?php echo e($button['title']); ?></a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<?php if($topItems->isNotEmpty()): ?>
<?php ($topItem = $topItems->first()); ?>
<a class="header-service-pill" href="<?php echo e($topItem->resolved_url); ?>" target="<?php echo e($topItem->target); ?>"><?php echo e($topItem->title); ?></a>
<?php endif; ?>
<?php endif; ?>
<a class="header-contact-card" href="tel:<?php echo e(preg_replace('/[^0-9+]/', '', $phone)); ?>">
<span><?php echo e($contactText); ?></span>
<strong><?php echo e(fa_number($phone)); ?></strong>
</a>
</div>
</div>
<nav aria-label="منوی اصلی" class="navbar navbar-expand-lg main-navbar site-container">
<button aria-controls="mainNav" aria-expanded="false" aria-label="باز کردن منو" class="navbar-toggler" data-bs-target="#mainNav" data-bs-toggle="collapse" type="button">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="mainNav">
<ul class="navbar-nav me-auto mb-2 mb-lg-0 nav-dots top-nav-menu">
<?php if($mainItems->isNotEmpty()): ?>
    <?php $__currentLoopData = $mainItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menuItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $__env->make('frontend.partials.dynamic-menu-item', ['menuItem' => $menuItem, 'variant' => 'classic', 'itemClass' => 'nav-item', 'linkClass' => 'nav-link'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
<li class="nav-item"><a class="nav-link active" href="<?php echo e(route('home')); ?>">صفحه اصلی</a></li>
<li class="nav-item"><a class="nav-link" href="<?php echo e(route('posts.index')); ?>">اخبار</a></li>
<li class="nav-item"><a class="nav-link" href="<?php echo e(route('announcements.index')); ?>">اطلاعیه‌ها</a></li>
<li class="nav-item"><a class="nav-link" href="<?php echo e(route('guilds.index')); ?>">اتحادیه‌ها</a></li>
<li class="nav-item top-nav-item has-top-submenu">
<button aria-expanded="false" class="nav-link top-nav-link" type="button">خدمات الکترونیک<span class="top-submenu-caret"></span></button>
<ul class="top-submenu"><li><a href="<?php echo e(route('electronic-services.index')); ?>">خدمات الکترونیک</a></li><li><a href="<?php echo e(route('systems.index')); ?>">سامانه‌ها</a></li></ul>
</li>
<li class="nav-item"><a class="nav-link" href="<?php echo e(route('galleries.index')); ?>">گالری تصاویر</a></li>
<li class="nav-item"><a class="nav-link" href="<?php echo e(route('tourism.index')); ?>">گردشگری</a></li>
<li class="nav-item"><a class="nav-link" href="<?php echo e(route('contact.create')); ?>">تماس با ما</a></li>
<?php endif; ?>
</ul>
</div>
<button aria-controls="headerSearchPanel" aria-expanded="false" aria-label="جستجو در سایت" class="search-trigger" type="button">
<span class="visually-hidden">جستجو</span>
</button>
</nav>
<div class="header-search-panel site-container" hidden="" id="headerSearchPanel">
<form action="<?php echo e(route('search')); ?>" method="GET" class="header-search-form" role="search">
<label class="header-search-label" for="siteSearchInput">جستجو در سایت</label>
<div class="header-search-field">
<input id="siteSearchInput" name="q" value="<?php echo e(request('q')); ?>" placeholder="عبارت مورد نظر را وارد کنید؛ مثل اتحادیه، پروانه کسب، شکایت، آموزش..." type="search"/>
<button type="submit">جستجو</button>
</div>
<div aria-live="polite" class="header-search-results"></div>
</form>
</div>
</header>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/partials/header.blade.php ENDPATH**/ ?>