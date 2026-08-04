<?php $__env->startSection('title', 'اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', 'آخرین اخبار، اطلاعیه‌ها، خدمات، اتحادیه‌ها و جاذبه‌های گردشگری اتاق اصناف مرکز استان گلستان.'); ?>

<?php
    $defaultImage = asset('assets/img/asnaf-gorgan-default.jpg');
    $assetImage = function (?string $path) use ($defaultImage) {
        return image_url($path, 'assets/img/asnaf-gorgan-default.jpg') ?: $defaultImage;
    };
    $plain = fn ($value, $limit = 120) => plain_text($value, $limit);

    $sectionByKey = fn (string $key) => ($homeSections ?? $sections ?? collect())->firstWhere('key', $key);
    $sectionTitle = fn (string $key, string $fallback) => filled($sectionByKey($key)?->title) ? $sectionByKey($key)->title : $fallback;
    $sectionSubtitle = fn (string $key, string $fallback = '') => filled($sectionByKey($key)?->subtitle) ? $sectionByKey($key)->subtitle : $fallback;
    $sectionSetting = fn (string $key, string $setting, mixed $fallback = null) => data_get($sectionByKey($key)?->settings, $setting, $fallback);
    $homeUrl = route('home');
    $postsUrl = route('posts.index');
    $galleriesUrl = route('galleries.index');
    $tourismUrl = route('tourism.index');
    $videosUrl = route('videos.index');
    $guildsUrl = route('guilds.index');
    $contactUrl = route('contact.create');
    $systemsUrl = route('systems.index');
    $servicesUrl = route('electronic-services.index');
    $commissionsUrl = route('commissions.index');
    $complaintsUrl = route('complaints.create');

    $heroFallbacks = collect([
        ['title' => 'راهنمای صدور، تمدید و انتقال پروانه کسب برای فعالان صنفی گرگان', 'kicker' => 'خدمات صنفی', 'url' => $servicesUrl, 'image' => $defaultImage],
        ['title' => 'پیگیری شکایات مردمی و صیانت از حقوق مصرف‌کنندگان و واحدهای صنفی', 'kicker' => 'نظارت و بازرسی', 'url' => $complaintsUrl, 'image' => $defaultImage],
        ['title' => 'آخرین خبرها و اطلاعیه‌های اتاق اصناف مرکز استان گلستان', 'kicker' => 'اخبار اتاق', 'url' => $postsUrl, 'image' => $defaultImage],
    ]);
    $heroItems = ($heroPosts ?? collect())->take(3)->map(fn ($post) => [
        'title' => $post->title,
        'kicker' => $post->category?->title ?? 'خبر',
        'url' => route('posts.show', $post->slug),
        'image' => $post->featured_image_url,
    ])->values();
    if ($heroItems->isEmpty()) {
        $heroItems = ($importantAnnouncements ?? collect())->take(3)->map(fn ($announcement) => [
            'title' => $announcement->title,
            'kicker' => $announcement->category?->title ?? 'اطلاعیه',
            'url' => route('announcements.show', $announcement->slug),
            'image' => $assetImage($announcement->featured_image),
        ])->values();
    }
    $heroItems = $heroItems->isNotEmpty() ? $heroItems : $heroFallbacks;

    $sideItems = ($sidePosts ?? collect())->take(2)->map(fn ($post) => [
        'title' => $post->title,
        'url' => route('posts.show', $post->slug),
        'image' => $post->featured_image_url,
    ])->values();
    if ($sideItems->isEmpty()) {
        $sideItems = collect([
            ['title' => 'آدرس اتاق اصناف مرکز استان گلستان: خیابان مطهری جنوبی، روبروی پمپ بنزین، ساختمان اتاق اصناف', 'url' => $contactUrl, 'image' => $defaultImage],
            ['title' => 'تمرکز اتاق اصناف بر ساماندهی امور اتحادیه‌ها، آموزش متقاضیان و تسهیل خدمات صنفی', 'url' => $guildsUrl, 'image' => $defaultImage],
        ]);
    }

    $quickFallbacks = collect([
        ['title' => 'درباره اتاق اصناف', 'url' => route('pages.show', 'about-gorgan-guild-chamber'), 'children' => collect([['title' => 'معرفی اتاق اصناف گرگان', 'url' => route('pages.show', 'about-gorgan-guild-chamber')], ['title' => 'هیئت رئیسه و ساختار اداری', 'url' => '#chamber-members'], ['title' => 'شرح وظایف و اختیارات', 'url' => route('pages.show', 'about-gorgan-guild-chamber')]])],
        ['title' => 'خدمات متقاضیان', 'url' => $servicesUrl, 'children' => collect([['title' => 'راهنمای صدور پروانه کسب', 'url' => $servicesUrl], ['title' => 'تمدید و انتقال پروانه', 'url' => $servicesUrl], ['title' => 'پیگیری درخواست‌ها', 'url' => $systemsUrl]])],
        ['title' => 'اتحادیه‌های صنفی', 'url' => $guildsUrl, 'children' => collect([['title' => 'فهرست اتحادیه‌های گرگان', 'url' => $guildsUrl], ['title' => 'اطلاعات تماس اتحادیه‌ها', 'url' => '#friendship'], ['title' => 'رسته‌های شغلی', 'url' => '#representatives']])],
        ['title' => 'بازرسی و نظارت', 'url' => $complaintsUrl, 'children' => collect([['title' => 'ثبت شکایت صنفی', 'url' => $complaintsUrl], ['title' => 'گزارش تخلف', 'url' => $complaintsUrl], ['title' => 'پیگیری بازرسی‌ها', 'url' => route('complaints.track')]])],
        ['title' => 'آموزش و احکام تجارت', 'url' => $servicesUrl, 'children' => collect([['title' => 'دوره‌های آموزشی', 'url' => $servicesUrl], ['title' => 'احکام تجارت و کسب‌وکار', 'url' => $servicesUrl], ['title' => 'راهنمای متقاضیان', 'url' => $servicesUrl]])],
        ['title' => 'اطلاعیه‌ها', 'url' => route('announcements.index'), 'children' => collect([['title' => 'بخشنامه‌ها', 'url' => route('announcements.index')], ['title' => 'اخبار اتاق اصناف', 'url' => $postsUrl], ['title' => 'رویدادهای صنفی', 'url' => $postsUrl]])],
        ['title' => 'سامانه‌ها', 'url' => $systemsUrl, 'children' => collect([['title' => 'سامانه نوین اصناف', 'url' => $systemsUrl], ['title' => 'سامانه آموزش اصناف', 'url' => $systemsUrl], ['title' => 'فرم‌ها و درخواست‌ها', 'url' => $servicesUrl]])],
        ['title' => 'ارتباط با ما', 'url' => $contactUrl, 'children' => collect([['title' => 'آدرس و تلفن', 'url' => '#friendship'], ['title' => 'ارسال پیام', 'url' => $contactUrl], ['title' => 'راهنمای مراجعه حضوری', 'url' => '#friendship']])],
    ]);
    $quickItems = ($quickMenuItems ?? collect())->map(fn ($item) => [
        'title' => trim($item->title),
        'url' => $item->resolved_url ?: '#',
        'children' => $item->children->map(fn ($child) => ['title' => trim($child->title), 'url' => $child->resolved_url ?: '#']),
    ])->values();
    $quickItems = $quickItems->isNotEmpty() ? $quickItems : $quickFallbacks;

    $serviceFallbacks = collect([
        ['icon' => '📋', 'title' => 'نحوه صدور پروانه کسب', 'description' => 'راهنمای گام‌به‌گام دریافت پروانه کسب جدید و تشکیل پرونده صنفی برای متقاضیان', 'url' => $servicesUrl, 'label' => 'مشاهده راهنما ←'],
        ['icon' => '🔄', 'title' => 'نحوه تمدید پروانه کسب', 'description' => 'مراحل تمدید سالانه پروانه کسب، مدارک مورد نیاز و فرآیند بررسی در اتحادیه مربوطه', 'url' => $servicesUrl, 'label' => 'مشاهده راهنما ←'],
        ['icon' => '⚖️', 'title' => 'نحوه ثبت شکایت صنفی', 'description' => 'ثبت گزارش تخلفات صنفی، شکایات مردمی و نحوه پیگیری از طریق کمیسیون نظارت', 'url' => $complaintsUrl, 'label' => 'مشاهده راهنما ←'],
        ['icon' => '📁', 'title' => 'فرم‌ها و بخشنامه‌ها', 'description' => 'دانلود فرم‌های مورد نیاز، بخشنامه‌های جاری و اطلاعیه‌های جدید اتاق اصناف', 'url' => route('announcements.index'), 'label' => 'مشاهده فرم‌ها ←'],
        ['icon' => '💻', 'title' => 'سامانه نوین اصناف', 'description' => 'ورود به سامانه الکترونیک اصناف برای پیگیری پرونده و استعلام وضعیت پروانه کسب', 'url' => $systemsUrl, 'label' => 'ورود به سامانه ←'],
        ['icon' => '🎓', 'title' => 'آموزش احکام تجارت', 'description' => 'ثبت‌نام در دوره‌های آموزش احکام تجارت و کسب‌وکار مورد نیاز صدور پروانه کسب', 'url' => $servicesUrl, 'label' => 'ثبت‌نام دوره ←'],
    ]);
    $serviceItems = ($electronicServices ?? collect())->map(fn ($service) => [
        'icon' => $service->icon ?: '📋',
        'title' => $service->title,
        'description' => $plain($service->short_description ?: $service->body, 120),
        'url' => ($service->link_type === 'external' && filled($service->link)) ? $service->link : route('electronic-services.show', $service->slug),
        'target' => ($service->link_type === 'external' && filled($service->link)) ? ($service->target ?: '_blank') : '_self',
        'label' => 'مشاهده راهنما ←',
    ])->take(6)->values();
    $serviceItems = $serviceItems->isNotEmpty() ? $serviceItems : $serviceFallbacks;

    $systemFallbacks = collect([
        ['icon' => '💻', 'title' => 'سامانه نوین اصناف', 'description' => 'ورود به سامانه الکترونیک اصناف برای پیگیری پرونده و استعلام وضعیت پروانه کسب', 'url' => $systemsUrl, 'target' => '_self', 'label' => 'ورود به سامانه ←'],
        ['icon' => '🎓', 'title' => 'سامانه آموزش اصناف', 'description' => 'دسترسی به دوره‌های آموزشی و راهنمای ثبت‌نام متقاضیان صنفی', 'url' => $systemsUrl, 'target' => '_self', 'label' => 'ورود به سامانه ←'],
        ['icon' => '🔍', 'title' => 'سامانه استعلام', 'description' => 'پیگیری و استعلام وضعیت درخواست‌ها و مجوزهای صنفی از درگاه‌های مرتبط', 'url' => $systemsUrl, 'target' => '_self', 'label' => 'ورود به سامانه ←'],
    ]);
    $systemItems = ($systems ?? collect())->map(fn ($system) => [
        'icon' => $system->icon ?: '💻',
        'title' => $system->title,
        'description' => $plain($system->short_description ?: $system->description, 120),
        'url' => filled($system->link) ? $system->link : route('systems.show', $system->slug),
        'target' => filled($system->link) ? ($system->target ?: '_blank') : '_self',
        'label' => 'ورود به سامانه ←',
    ])->take(6)->values();
    $systemItems = $systemItems->isNotEmpty() ? $systemItems : $systemFallbacks;

    $adItems = ($homeAdvertisements ?? collect())->take(4)->filter(fn ($ad) => filled($ad->image))->map(fn ($ad) => ['title' => $ad->title ?: 'تبلیغات', 'url' => $ad->link ?: '#', 'image' => $assetImage($ad->image), 'target' => $ad->target ?: '_self', 'alt' => data_get($ad, 'alt') ?: ($ad->title ?: 'تبلیغات')])->values();

    $unionPanels = ($unionPanels ?? collect());
    if ($unionPanels->isEmpty()) {
        $unionPanels = collect([
            'rep-production' => ['label' => 'اتحادیه‌های تولیدی', 'icon' => '', 'items' => ($productionUnions ?? collect())],
            'rep-distribution' => ['label' => 'اتحادیه‌های توزیعی', 'icon' => '', 'items' => ($distributionUnions ?? collect())],
            'rep-service' => ['label' => 'اتحادیه‌های خدماتی', 'icon' => '', 'items' => ($serviceUnions ?? collect())],
        ]);
    }

    $commissionFallbacks = collect([
        ['icon' => '⚖️', 'title' => 'کمیسیون تشخیص', 'description' => 'نظارت بر عملکرد واحدهای صنفی، اجرای طرح‌های بازرسی دوره‌ای و رسیدگی به تخلفات صنفی در سطح شهرستان'],
        ['icon' => '🎓', 'title' => 'کمیسیون آموزش', 'description' => 'برنامه‌ریزی و برگزاری دوره‌های آموزش احکام تجارت و کسب‌وکار برای متقاضیان پروانه کسب و فعالان صنفی'],
        ['icon' => '🤝', 'title' => 'کمیسیون بازرسی', 'description' => 'رسیدگی به اختلافات صنفی میان اعضای اتحادیه‌ها و ارائه راهکارهای سازش و مصالحه'],
        ['icon' => '📊', 'title' => 'کمیسیون بازاریابی و توسعه', 'description' => 'حمایت از بازاریابی محصولات صنفی، توسعه بازارچه‌های محلی و برگزاری نمایشگاه‌های تخصصی'],
        ['icon' => '🏛', 'title' => 'کمیسیون صنایع دستی', 'description' => 'حمایت از هنرمندان و فعالان صنایع دستی، ساماندهی تولید و فروش محصولات سنتی و محلی'],
        ['icon' => '🌿', 'title' => 'کمیسیون گردشگری', 'description' => 'هماهنگی با فعالان حوزه گردشگری، هتل‌داران، رستوران‌داران و آژانس‌های مسافرتی شهرستان'],
        ['icon' => '💳', 'title' => 'کمیسیون مالی و اداری', 'description' => 'مدیریت منابع مالی، بودجه‌ریزی، امور اداری و پشتیبانی از فعالیت‌های اتاق اصناف شهرستان'],
        ['icon' => '📋', 'title' => 'کمیسیون امور صنفی', 'description' => 'پیگیری مسائل و نیازهای صنفی اتحادیه‌ها، صدور و تمدید پروانه‌های کسب و رسیدگی به درخواست‌ها'],
    ]);
    $commissionItems = ($commissions ?? collect())->take(8)->map(fn ($commission) => ['icon' => '⚖️', 'title' => $commission->title, 'description' => $plain($commission->description, 130), 'tasks' => $commission->activeTasks->take(3)->map(fn ($task) => ['title' => $task->title, 'description' => $plain($task->description, 120)])->values(), 'url' => route('commissions.show', $commission->slug)])->values();
    $commissionItems = $commissionItems->isNotEmpty() ? $commissionItems : $commissionFallbacks;

    $tourismFallbacks = collect([
        'tourism-fallback-nature' => ['label' => 'طبیعت‌گردی', 'items' => collect([['title' => 'جنگل النگدره', 'description' => 'یکی از زیباترین جاذبه‌های طبیعی استان گلستان در جنوب گرگان', 'badge' => 'طبیعت', 'alt' => 'جنگل النگدره'], ['title' => 'تالاب بین‌المللی گمیشان', 'description' => 'تالاب زیبا و زیستگاه پرندگان مهاجر در شمال استان گلستان', 'badge' => 'طبیعت', 'alt' => 'تالاب گمیشان'], ['title' => 'آبشار کبودوال', 'description' => 'آبشار زیبا و خنک در دل جنگل‌های انبوه استان گلستان', 'badge' => 'طبیعت', 'alt' => 'آبشار کبودوال']])],
        'tourism-fallback-historic' => ['label' => 'تاریخی', 'items' => collect([['title' => 'برج گنبد قابوس', 'description' => 'بلندترین برج آجری جهان و میراث جهانی یونسکو در استان گلستان', 'badge' => 'تاریخی', 'alt' => 'برج گنبد قابوس'], ['title' => 'دیوار دفاعی گرگان', 'description' => 'دیوار تاریخی گرگان (مار سرخ)، پس از دیوار چین طولانی‌ترین دیوار جهان', 'badge' => 'تاریخی', 'alt' => 'دیوار دفاعی گرگان']])],
        'tourism-fallback-shop' => ['label' => 'بازار و خرید', 'items' => collect([['title' => 'بازار بزرگ گرگان', 'description' => 'مرکز خرید اصیل و سنتی گرگان با اصناف متنوع و محصولات محلی', 'badge' => 'خرید', 'alt' => 'بازار بزرگ گرگان'], ['title' => 'مرکز خرید گلستان', 'description' => 'مجتمع تجاری مدرن با فروشگاه‌های متنوع و خدمات رفاهی', 'badge' => 'خرید', 'alt' => 'پاساژ گلستان']])],
    ]);
    $tourismPanels = ($tourismPanels ?? collect())->isNotEmpty() ? $tourismPanels : $tourismFallbacks;

    $videoFallbacks = collect(['گزارش تصویری از خدمات اتاق اصناف مرکز استان گلستان به کسبه شهرستان', 'راهنمای مراحل صدور و تمدید پروانه کسب', 'آموزش احکام تجارت برای متقاضیان', 'بازدید میدانی بازرسان از واحدهای صنفی گرگان', 'نشست هماهنگی اتحادیه‌های صنفی استان گلستان']);
    $galleryFallbacks = collect(['نمایی از ساختمان و مراجعه حضوری فعالان صنفی', 'جلسه هم‌اندیشی اتحادیه‌های صنفی استان گلستان', 'ارائه خدمات مشاوره‌ای به متقاضیان پروانه کسب', 'برگزاری دوره آموزشی احکام تجارت و کسب‌وکار', 'پیگیری طرح‌های نظارتی بازار در استان گلستان', 'بخشنامه‌ها و دستورالعمل‌های جدید صنفی', 'بازار سنتی گرگان و اصناف قدیمی شهر', 'نمایشگاه صنایع دستی و سوغات استان گلستان']);
    $followTopicSettings = collect($sectionSetting('systems', 'topics', []));
    $followTopics = $followTopicSettings->map(fn ($topic) => is_array($topic) ? $topic : ['title' => $topic, 'url' => $servicesUrl])
        ->filter(fn ($topic) => filled($topic['title'] ?? null))
        ->map(fn ($topic) => ['title' => $topic['title'], 'url' => $topic['url'] ?? $servicesUrl])
        ->values();
    if ($followTopics->isEmpty()) {
        $followTopics = collect($serviceItems)->concat($systems ?? collect())->concat($announcements ?? collect())
            ->map(function ($item) use ($servicesUrl, $systemsUrl) {
                if (is_array($item)) {
                    return ['title' => $item['title'] ?? null, 'url' => $item['url'] ?? $servicesUrl];
                }

                return ['title' => $item->title ?? null, 'url' => isset($item->slug) ? (filled($item->link ?? null) ? $item->link : $systemsUrl) : $servicesUrl];
            })
            ->filter(fn ($topic) => filled($topic['title'] ?? null))
            ->unique('title')
            ->take(18)
            ->values();
    }
?>


<?php $__env->startPush('styles'); ?>
<style>
    .home-main { display: flex; flex-direction: column; }
    <?php
        $homeSectionSelectorOptions = [
            '.hero-section' => ['hero_slider', 'quick_menu'],
            '.howto-section' => ['electronic_services'],
            '#latest-news' => ['important_news'],
            '.home-ad-banners' => ['advertisements'],
            '#representatives' => ['unions'],
            '.office-services-section' => ['systems'],
            '#commissions-real' => ['commissions'],
            '#daily-news' => ['daily_news'],
            '#fractions' => ['systems'],
            '#friendship' => ['contact'],
            '#tourism' => ['tourism'],
            '#multimedia' => ['videos', 'galleries'],
            '#chamber-members' => ['chamber_members'],
        ];
        $orderedHomeSections = ($homeSections ?? collect())->sortBy('sort_order')->values();
        $homeSectionOrderByKey = $orderedHomeSections->pluck('sort_order', 'key');
    ?>
    <?php $__currentLoopData = $homeSectionSelectorOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $selector => $keys): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $matchingOrders = collect($keys)
                ->map(fn ($key) => $homeSectionOrderByKey->get($key))
                ->filter(fn ($order) => $order !== null);
        ?>
        <?php if($matchingOrders->isNotEmpty()): ?>
            .home-main > <?php echo e($selector); ?> { order: <?php echo e((int) $matchingOrders->min()); ?>; }
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<main class="home-main">
<section class="hero-section site-container">
<div class="hero-grid">
<aside aria-label="دسترسی‌های عمودی" class="quick-menu">
<ul class="quick-menu-list">
<?php $__currentLoopData = $quickItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
    $children = collect(data_get($item, 'children', []));
?>
<li class="quick-menu-item <?php echo e($children->isNotEmpty() ? 'has-submenu' : ''); ?>">
<?php if($children->isNotEmpty()): ?>
<div class="quick-menu-link quick-menu-combo">
<a class="quick-menu-title-link" href="<?php echo e($item['url']); ?>"><span><?php echo e($item['title']); ?></span></a>
<button aria-expanded="false" class="quick-menu-toggle" type="button"><b></b></button>
</div>
<ul class="quick-submenu">
<?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<li><a href="<?php echo e($child['url']); ?>"><?php echo e($child['title']); ?></a></li>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php else: ?>
<a class="quick-menu-link" href="<?php echo e($item['url']); ?>"><span><?php echo e($item['title']); ?></span><b></b></a>
<?php endif; ?>
</li>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
</aside>
<div aria-label="اسلایدر خبرهای اصلی" class="hero-slider swiper" dir="ltr">
<div class="swiper-wrapper">
<?php $__currentLoopData = $heroItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<article class="news-card news-card-main swiper-slide">
<a href="<?php echo e($item['url']); ?>">
<img alt="<?php echo e($item['title']); ?>" src="<?php echo e($item['image']); ?>"/>
<div class="news-overlay"></div>
<div class="news-content">
<span class="news-kicker"><?php echo e($item['kicker']); ?></span>
<h1><?php echo e($item['title']); ?></h1>
</div>
</a>
</article>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<button aria-label="خبر بعدی" class="hero-slider-arrow hero-slider-next" type="button"></button>
<button aria-label="خبر قبلی" class="hero-slider-arrow hero-slider-prev" type="button"></button>
<div class="hero-slider-pagination"></div>
</div>
<div aria-label="خبرهای کناری" class="side-news">
<?php $__currentLoopData = $sideItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<article class="news-card side-card">
<a href="<?php echo e($item['url']); ?>">
<img alt="<?php echo e($item['title']); ?>" src="<?php echo e($item['image']); ?>"/>
<div class="news-overlay"></div>
<div class="news-content"><h2><?php echo e($item['title']); ?></h2></div>
</a>
</article>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
</div>
</section>

<section class="site-container howto-section">
<div class="section-heading section-heading-centered">
<h2><?php echo e($sectionTitle('electronic_services', 'خدمات الکترونیک صنفی')); ?></h2>
<p><?php echo e($sectionSubtitle('electronic_services', 'نحوه انجام خدمات و دریافت مجوزها و ثبت درخواست‌ها')); ?></p>
</div>
<div class="howto-grid">
<?php $__currentLoopData = $serviceItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<a class="howto-card" href="<?php echo e($item['url']); ?>" target="<?php echo e($item['target'] ?? '_self'); ?>">
<div class="howto-icon"><?php echo e($item['icon']); ?></div>
<h3><?php echo e($item['title']); ?></h3>
<p><?php echo e($item['description']); ?></p>
<span class="howto-link"><?php echo e($item['label']); ?></span>
</a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
</section>


<section class="latest-news-section section-white" id="latest-news">
<div class="site-container">
<div class="latest-news-shell">
<div class="section-heading latest-news-heading"><div><span class="section-kicker">اخبار اتاق</span><h2>آخرین اخبار</h2><p>جدیدترین خبرهای اتاق اصناف با چینش هماهنگ با سایر بخش‌های صفحه اصلی</p></div><a class="tab-pill" href="<?php echo e(route('posts.index')); ?>">آرشیو اخبار</a></div>
<div class="latest-news-layout">
<div class="latest-news-list">
<?php $__empty_1 = true; $__currentLoopData = ($latestPosts ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<?php
    $postUrl = route('posts.show', ['slug' => $post->slug, 'news_page' => request('news_page')]);
?>
<article class="latest-news-card">
<a class="latest-news-thumb-link" href="<?php echo e($postUrl); ?>"><img loading="lazy" src="<?php echo e($post->featured_image_url); ?>" alt="<?php echo e($post->title); ?>"></a>
<div class="latest-news-card-body"><div class="latest-news-meta"><time><?php echo e(jalali_datetime($post->published_at)); ?></time><span><?php echo e($post->category_title); ?></span></div><h3><a href="<?php echo e($postUrl); ?>"><?php echo e($post->title); ?></a></h3><p><?php echo e($plain($post->excerpt ?: $post->short_description ?: $post->body, 135)); ?></p><a class="read-more" href="<?php echo e($postUrl); ?>">ادامه مطلب</a></div>
</article>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<div class="empty-state">هنوز خبری برای نمایش ثبت نشده است.</div>
<?php endif; ?>
<?php if(($latestPosts ?? null) instanceof \Illuminate\Contracts\Pagination\Paginator): ?>
<div class="latest-news-pagination"><?php echo e($latestPosts->links('frontend.partials.pagination')); ?></div>
<?php endif; ?>
</div>
<?php
    $sidebarAdItems = collect($sidebarAdvertisements ?? collect())->take(4);
?>
<?php if($sidebarAdItems->isNotEmpty()): ?>
<aside class="latest-news-ads"><?php $__currentLoopData = $sidebarAdItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e($ad['url']); ?>" target="<?php echo e($ad['target']); ?>" rel="<?php echo e($ad['target'] === '_blank' ? 'noopener noreferrer' : ''); ?>"><img loading="lazy" alt="<?php echo e($ad['alt']); ?>" src="<?php echo e($ad['image']); ?>"><span><?php echo e($ad['title']); ?></span></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></aside>
<?php endif; ?>
</div>
</div>
</div>
</section>

<section class="home-ad-banners site-container">
<?php
    $bannerAdItems = collect($bannerAdvertisements ?? collect())->take(4);
?>
<?php $__currentLoopData = $bannerAdItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<a class="ad-banner" href="<?php echo e($ad['url']); ?>" target="<?php echo e($ad['target']); ?>">
<img alt="تبلیغات" src="<?php echo e($ad['image']); ?>"/>
<div class="ad-banner-overlay"></div>
<div class="ad-banner-text"><?php echo e($ad['title']); ?></div>
</a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</section>

<section class="representatives-section section-white" id="representatives" data-union-ajax-url="<?php echo e(route('guilds.ajax-search')); ?>">
<div class="site-container">
<div class="section-heading">
<h2>اتحادیه‌های صنفی استان گلستان</h2>
<?php if(($homeUnions ?? collect())->isNotEmpty()): ?>
<a class="tab-pill" href="<?php echo e($guildsUrl); ?>">فهرست کامل اتحادیه‌ها</a>
<?php endif; ?>
</div>
<?php
    $displayUnionPanels = ($unionPanels ?? collect())->filter(fn ($data) => collect($data['items'] ?? [])->isNotEmpty());
?>
<?php if($displayUnionPanels->isNotEmpty()): ?>
<div aria-label="گروه‌بندی اتحادیه‌ها" class="tabs" data-tab-group="representatives" role="tablist">
<?php $__currentLoopData = $displayUnionPanels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $panel => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<button class="tab-pill <?php echo e($loop->first ? 'active' : ''); ?>" data-tab-target="<?php echo e($panel); ?>" type="button"><?php echo e(trim(($data['icon'] ?? '').' '.$data['label'])); ?></button>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<div class="tab-panels" data-tab-panels="representatives">
<?php $__currentLoopData = $displayUnionPanels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $panel => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="tab-panel <?php echo e($loop->first ? 'active' : ''); ?>" data-tab-panel="<?php echo e($panel); ?>">
<div class="representative-layout">
<div class="representative-map">
<img alt="<?php echo e($data['label']); ?>" class="map-img" src="<?php echo e($defaultImage); ?>"/>
</div>
<aside class="people-panel" data-search-area="">
<div class="searchbox"><span class="search-icon"></span><input data-union-ajax-input="" data-union-type="<?php echo e(str_replace('rep-', '', $panel)); ?>" placeholder="جستجوی سریع اتحادیه..." type="search"/></div>
<div class="people-scroll-wrap">
<ul class="person-list" data-union-results="<?php echo e($panel); ?>">
<?php $__currentLoopData = collect($data['items']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $union): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<li class="union-home-item"><a href="<?php echo e(route('guilds.show', $union->slug)); ?>" class="d-flex align-items-center gap-2 text-decoration-none"><span class="person-avatar avatar-<?php echo e(($loop->iteration % 6) + 1); ?>"><?php if($union->logo || $union->cover_image): ?><img src="<?php echo e(image_url($union->logo ?: $union->cover_image)); ?>" alt="<?php echo e($union->display_title); ?>" loading="lazy"><?php endif; ?></span><div><strong><?php echo e($union->display_title); ?></strong><small><?php echo e($plain($union->short_description ?: $union->manager_name ?: $union->union_type_label, 90)); ?></small></div></a><?php if($union->complaint_enabled): ?><div class="union-home-actions"><a href="<?php echo e(route('complaints.create', ['union_id' => $union->id])); ?>">ثبت شکایت</a></div><?php endif; ?></li>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<div class="union-ajax-status" data-union-status="<?php echo e($panel); ?>" hidden></div>
</div>
</aside>
</div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php else: ?>
<div class="empty-state union-empty-state">
<h3>اتحادیه فعالی برای نمایش در صفحه اصلی ثبت نشده است.</h3>
<p>به‌زودی اطلاعات اتحادیه‌های فعال در این بخش نمایش داده می‌شود.</p>
</div>
<?php endif; ?>
</div>
</section>


<section class="commissions-section ds-tint-block office-services-section" id="commissions">
<div class="site-container">
<div class="section-heading">
<h2><?php echo e($sectionTitle('systems', 'سامانه‌ها')); ?></h2>
<a class="tab-pill" href="<?php echo e($systemsUrl); ?>">مشاهده همه سامانه‌ها</a>
</div>
<div class="commission-card"><div class="commission-grid compact-grid">
<?php $__currentLoopData = $systemItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<a class="commission-item service-color-<?php echo e(($loop->iteration % 4) + 1); ?>" href="<?php echo e($item['url']); ?>" target="<?php echo e($item['target'] ?? '_self'); ?>"><strong><?php echo e($item['title']); ?></strong><span><?php echo e($item['description']); ?></span></a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div></div>
</div>
</section>
<section class="commissions-real" id="commissions-real">
<div class="site-container">
<div class="section-heading section-heading-centered">
<h2><?php echo e($sectionTitle('commissions', 'کمیسیون‌های اتاق اصناف مرکز استان گلستان')); ?></h2>
<p><?php echo e($sectionSubtitle('commissions', 'آخرین اطلاعات کمیسیون‌های تخصصی اتاق اصناف مرکز استان گلستان را در این بخش دنبال کنید.')); ?></p>
</div>
<div class="comreal-grid">
<?php $__currentLoopData = $commissionItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<a href="<?php echo e($item['url'] ?? $commissionsUrl); ?>" class="comreal-card">
<div class="comreal-icon"><?php echo e($item['icon']); ?></div>
<h3><?php echo e($item['title']); ?></h3>
<p><?php echo e($item['description']); ?></p>
<?php if(($item['tasks'] ?? collect())->isNotEmpty()): ?>
<ul class="commission-task-mini"><?php $__currentLoopData = $item['tasks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($task['title']); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
<?php else: ?>
<ul class="commission-task-mini"><li>اطلاعات تکمیلی این کمیسیون به‌زودی منتشر می‌شود.</li></ul>
<?php endif; ?>
</a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
</div>
</section>

<section class="fractions-section section-gray" id="fractions">
<div class="site-container">
<div class="section-heading">
<h2><?php echo e($sectionTitle('systems', 'موضوعات پیگیری اصناف')); ?></h2>
</div>
<div class="fraction-grid">
<?php $__empty_1 = true; $__currentLoopData = $followTopics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<a href="<?php echo e($topic['url']); ?>" class="fraction-link"><?php echo e($topic['title']); ?></a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<p class="empty-state">موضوعی برای نمایش در پنل مدیریت ثبت نشده است.</p>
<?php endif; ?>
</div>
</div>
</section>

<section class="friendship-section section-white" id="friendship">
<div class="site-container">
<div class="section-heading friendship-heading"><h2><?php echo e($sectionTitle('contact', 'ارتباط با اتاق و دستگاه‌های همکار')); ?></h2><a class="tab-pill" href="<?php echo e($contactUrl); ?>">راهنمای تماس</a></div>
<div class="friendship-layout">
<div class="world-map-wrap">
<img alt="تصویر پیش‌فرض اتاق اصناف مرکز استان گلستان" class="world-map-img" src="<?php echo e($defaultImage); ?>"/>
</div>
<aside class="friend-list">
<div class="friend-scroll-wrap">
<ul>
<?php $__empty_1 = true; $__currentLoopData = $orgLinks ?? collect(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<li><a href="<?php echo e($link->url ?: '#'); ?>" target="<?php echo e($link->target ?? '_self'); ?>" class="text-decoration-none" <?php if(($link->target ?? '_self') === '_blank'): ?> rel="noopener" <?php endif; ?>><?php if($link->icon): ?><span><?php echo e($link->icon); ?></span><?php endif; ?> <strong><?php echo e($link->title); ?></strong><?php if($link->description): ?><small><?php echo e(plain_text($link->description, 100)); ?></small><?php endif; ?></a></li>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<li><a href="<?php echo e($contactUrl); ?>">اتاق اصناف مرکز استان گلستان؛ مشاهده اطلاعات تماس و راهنمای مراجعه</a></li>
<?php endif; ?>
</ul>
</div>
</aside>
</div>
</div>
</section>


<section class="tourism-section" id="tourism">
<div class="site-container">
<div class="section-heading">
<h2><?php echo e($sectionTitle('tourism', 'گردشگری گرگان و گلستان')); ?></h2>
<div aria-label="دسته‌بندی گردشگری" class="tabs" data-tab-group="tourism" role="tablist">
<?php $__currentLoopData = $tourismPanels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $panel => $panelData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<button class="tab-pill <?php echo e($loop->first ? 'active' : ''); ?>" data-tab-target="<?php echo e($panel); ?>" type="button"><?php echo e($panelData['label']); ?></button>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
</div>
<div class="tab-panels" data-tab-panels="tourism">
<?php $__currentLoopData = $tourismPanels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $panel => $panelData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="tab-panel <?php echo e($loop->first ? 'active' : ''); ?>" data-tab-panel="<?php echo e($panel); ?>">
<div class="tourism-grid">
<?php $__currentLoopData = $panelData['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $place): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
    $isModel = is_object($place);
    $placeTitle = $isModel ? $place->title : $place['title'];
    $placeDesc = $isModel ? (plain_text($place->home_description, 120) ?: 'توضیحی برای این جاذبه ثبت نشده است.') : plain_text($place['description'], 120);
    $placeBadge = $isModel ? $place->home_badge : $place['badge'];
    $placeAlt = $isModel ? $place->title : $place['alt'];
    $placeImage = $isModel ? $place->home_image_url : $defaultImage;
    $placeUrl = $isModel ? route('tourism.show', $place->slug) : $tourismUrl;
?>
<div class="tourism-card">
<a href="<?php echo e($placeUrl); ?>">
<div class="tourism-img-wrap">
<img alt="<?php echo e($placeAlt); ?>" src="<?php echo e($placeImage); ?>"/>
<div class="tourism-badge"><?php echo e($placeBadge); ?></div>
</div>
<div class="tourism-card-body">
<h3><?php echo e($placeTitle); ?></h3>
<p><?php echo e($placeDesc); ?></p>
</div>
</a>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
</div>
</section>


<section class="multimedia-section" id="multimedia">
<div class="site-container">
<div class="media-header" data-tab-group="media">
<h2><?php echo e($sectionTitle('videos', 'چندرسانه‌ای')); ?></h2>
<div class="media-tab-group">
<button class="media-tab active" data-tab-target="media-video" type="button">ویدیوها</button>
<button class="media-tab" data-tab-target="media-image" type="button">تصاویر</button>
</div>
</div>
<div class="tab-panels" data-tab-panels="media">
<div class="tab-panel active" data-tab-panel="media-video">
      <div class="media-grid">
<?php $__empty_1 = true; $__currentLoopData = ($latestVideos ?? collect())->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <a href="<?php echo e(route('videos.show', $video->slug)); ?>" class="media-card <?php echo e($loop->first ? 'media-card-lg' : ''); ?>">
        <img alt="<?php echo e($video->title); ?>" src="<?php echo e($assetImage($video->cover_image)); ?>"/>
        <div class="media-card-overlay"></div>
        <span class="media-play-btn"></span>
        <div class="media-card-footer">
          <h3><?php echo e($video->title); ?></h3>
        </div>
      </a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<?php $__currentLoopData = $videoFallbacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $title): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <a href="<?php echo e($videosUrl); ?>" class="media-card <?php echo e($loop->first ? 'media-card-lg' : ''); ?>">
        <img alt="<?php echo e($title); ?>" src="<?php echo e($defaultImage); ?>"/>
        <div class="media-card-overlay"></div>
        <span class="media-play-btn"></span>
        <div class="media-card-footer">
          <h3><?php echo e($title); ?></h3>
        </div>
      </a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
      </div>
      <a class="media-view-all" href="<?php echo e($videosUrl); ?>">مشاهده همه ویدیوها</a>
</div>
<div class="tab-panel" data-tab-panel="media-image">
<div class="media-grid">
<?php $__empty_1 = true; $__currentLoopData = ($latestGalleries ?? collect())->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gallery): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<a href="<?php echo e(route('galleries.show', $gallery->slug)); ?>" class="media-card">
<img alt="<?php echo e($gallery->title); ?>" src="<?php echo e($gallery->cover_image_url); ?>"/>
<div class="media-card-overlay"></div>
<div class="media-card-footer">
<h3><?php echo e($gallery->title); ?></h3>
</div>
</a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<?php $__currentLoopData = $galleryFallbacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $title): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<a href="<?php echo e($galleriesUrl); ?>" class="media-card">
<img alt="<?php echo e($title); ?>" src="<?php echo e($defaultImage); ?>"/>
<div class="media-card-overlay"></div>
<div class="media-card-footer">
<h3><?php echo e($title); ?></h3>
</div>
</a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
</div>
<a class="media-view-all" href="<?php echo e($galleriesUrl); ?>">مشاهده همه تصاویر</a>
</div>
</div>
</div>
</section>


<section class="chamber-members-home" id="chamber-members">
<div class="site-container">
<div class="section-heading chamber-members-heading">
<div>
<span class="section-kicker">اعضای اتاق اصناف</span>
<h2><?php echo e($sectionTitle('chamber_members', 'اعضای اتاق اصناف')); ?></h2>
<p><?php echo e($sectionSubtitle('chamber_members', 'معرفی رسمی پنج عضو هیئت‌مدیره با نام، سمت و تصویر.')); ?></p>
</div>
<a class="tab-pill" href="<?php echo e(route('chamber-members.index')); ?>">مشاهده همه اعضا</a>
</div>
<div class="chamber-members-grid">
<?php $__empty_1 = true; $__currentLoopData = ($chamberMembers ?? collect())->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<article class="chamber-member-card">
<div class="chamber-member-card-glow"></div>
<a href="<?php echo e(route('chamber-members.index')); ?>" aria-label="مشاهده معرفی <?php echo e($member->full_name); ?>">
<div class="chamber-member-photo-wrap">
<img alt="<?php echo e($member->full_name); ?>" src="<?php echo e($member->photo_url); ?>" onerror="this.onerror=null;this.src='<?php echo e($defaultImage); ?>';"/>
<span class="chamber-member-number"><?php echo e($loop->iteration); ?></span>
</div>
<div class="chamber-member-body">
<span class="chamber-member-label">عضو اتاق اصناف</span>
<h3><?php echo e($member->full_name); ?></h3>
<p class="chamber-member-position"><?php echo e($member->position); ?></p>
<div class="chamber-member-meta">
<span>معرفی کامل</span>
<strong>مشاهده ←</strong>
</div>
</div>
</a>
</article>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<div class="chamber-members-empty">
<img alt="اعضای اتاق اصناف" src="<?php echo e($defaultImage); ?>"/>
<div>
<span class="chamber-member-label">در انتظار تکمیل محتوا</span>
<h3>هنوز عضوی برای نمایش در صفحه نخست ثبت نشده است.</h3>
<p>از بخش «اعضای اتاق اصناف» در پنل مدیریت، اعضا را همراه با عکس دایره‌ای، سمت، توضیحات و ترتیب نمایش اضافه کنید.</p>
</div>
</div>
<?php endif; ?>
</div>
</div>
</section>

</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\laragon\www\aliche.ir\resources\views/frontend/home.blade.php ENDPATH**/ ?>