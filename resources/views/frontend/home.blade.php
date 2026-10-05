@extends('frontend.layouts.app')

@section('title', 'اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'آخرین اخبار، اطلاعیه‌ها، خدمات، اتحادیه‌ها و جاذبه‌های گردشگری اتاق اصناف مرکز استان گلستان.')

@php
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
    $complaintsEnabled = \App\Support\Features::complaintsEnabled();
    // $checkTitle=false for news-based lists, so headlines that merely mention «شکایت» stay visible.
    $withoutComplaintLinks = function ($items, bool $checkTitle = true) use (&$withoutComplaintLinks, $complaintsEnabled) {
        if ($complaintsEnabled) return $items;

        return collect($items)
            ->reject(fn ($item) => \App\Support\Features::isComplaintUrl(data_get($item, 'url'))
                || ($checkTitle && \App\Support\Features::isComplaintTitle(data_get($item, 'title'))))
            ->map(function ($item) use ($withoutComplaintLinks, $checkTitle) {
                if (is_array($item) && isset($item['children'])) {
                    $item['children'] = $withoutComplaintLinks($item['children'], $checkTitle);
                }

                return $item;
            })
            ->values();
    };
    $normalizeInternalUrl = static function (?string $value): string {
        $value = trim((string) $value);
        if ($value === '') return '';

        $parts = parse_url($value);
        $host = mb_strtolower((string) ($parts['host'] ?? ''));
        if (! in_array($host, ['localhost', '127.0.0.1'], true)) return $value;

        $path = '/'.ltrim((string) ($parts['path'] ?? '/'), '/');
        $knownRoutes = [
            '/contact' => route('contact.create'),
            '/complaints/create' => route('complaints.create'),
            '/guilds' => route('guilds.index'),
            '/announcements' => route('announcements.index'),
        ];
        $resolved = $knownRoutes[$path] ?? url($path);
        if (filled($parts['query'] ?? null)) $resolved .= '?'.$parts['query'];
        if (filled($parts['fragment'] ?? null)) $resolved .= '#'.$parts['fragment'];

        return $resolved;
    };

    $heroFallbacks = collect([
        ['title' => 'راهنمای صدور، تمدید و انتقال پروانه کسب برای فعالان صنفی گرگان', 'kicker' => 'خدمات صنفی', 'url' => $servicesUrl, 'image' => $defaultImage],
        ['title' => 'پیگیری شکایات مردمی و صیانت از حقوق مصرف‌کنندگان و واحدهای صنفی', 'kicker' => 'نظارت و بازرسی', 'url' => $complaintsUrl, 'image' => $defaultImage],
        ['title' => 'آخرین خبرها و اطلاعیه‌های اتاق اصناف مرکز استان گلستان', 'kicker' => 'اخبار اتاق', 'url' => $postsUrl, 'image' => $defaultImage],
    ]);
    $heroItems = ($heroPosts ?? collect())->take(5)->map(fn ($post) => [
        'title' => $post->title,
        'kicker' => $post->category?->title ?? 'خبر',
        'url' => route('posts.show', $post->slug),
        'image' => $post->featured_image_url,
    ])->values();
    if ($heroItems->isEmpty()) {
        $heroItems = ($importantAnnouncements ?? collect())->take(5)->map(fn ($announcement) => [
            'title' => $announcement->title,
            'kicker' => $announcement->category?->title ?? 'اطلاعیه',
            'url' => route('announcements.show', $announcement->slug),
            'image' => $assetImage($announcement->featured_image),
        ])->values();
    }
    $heroItems = $heroItems->isNotEmpty() ? $heroItems : $heroFallbacks;
    $heroItems = $withoutComplaintLinks($heroItems, false);

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
        ['title' => 'اتحادیه‌های صنفی', 'url' => $guildsUrl, 'children' => collect([['title' => 'فهرست اتحادیه‌های گرگان', 'url' => $guildsUrl], ['title' => 'اطلاعات تماس اتحادیه‌ها', 'url' => $guildsUrl], ['title' => 'رسته‌های شغلی', 'url' => '#representatives']])],
        ['title' => 'بازرسی و نظارت', 'url' => $complaintsUrl, 'children' => collect([['title' => 'ثبت شکایت صنفی', 'url' => $complaintsUrl], ['title' => 'گزارش تخلف', 'url' => $complaintsUrl], ['title' => 'پیگیری بازرسی‌ها', 'url' => route('complaints.track')]])],
        ['title' => 'آموزش و احکام تجارت', 'url' => $servicesUrl, 'children' => collect([['title' => 'دوره‌های آموزشی', 'url' => $servicesUrl], ['title' => 'احکام تجارت و کسب‌وکار', 'url' => $servicesUrl], ['title' => 'راهنمای متقاضیان', 'url' => $servicesUrl]])],
        ['title' => 'اطلاعیه‌ها', 'url' => route('announcements.index'), 'children' => collect([['title' => 'بخشنامه‌ها', 'url' => route('announcements.index')], ['title' => 'اخبار اتاق اصناف', 'url' => $postsUrl], ['title' => 'رویدادهای صنفی', 'url' => $postsUrl]])],
        ['title' => 'سامانه‌ها', 'url' => $systemsUrl, 'children' => collect([['title' => 'سامانه نوین اصناف', 'url' => $systemsUrl], ['title' => 'سامانه آموزش اصناف', 'url' => $systemsUrl], ['title' => 'فرم‌ها و درخواست‌ها', 'url' => $servicesUrl]])],
        ['title' => 'ارتباط با ما', 'url' => $contactUrl, 'children' => collect([['title' => 'آدرس و تلفن', 'url' => $contactUrl], ['title' => 'ارسال پیام', 'url' => $contactUrl], ['title' => 'راهنمای مراجعه حضوری', 'url' => $contactUrl]])],
    ]);
    $quickItems = ($quickMenuItems ?? collect())->map(fn ($item) => [
        'title' => trim($item->title),
        'url' => $normalizeInternalUrl($item->resolved_url ?: '#'),
        'children' => $item->children->map(fn ($child) => [
            'title' => trim($child->title),
            'url' => $normalizeInternalUrl($child->resolved_url ?: '#'),
        ]),
    ])->values();
    $quickItems = $quickItems->isNotEmpty() ? $quickItems : $quickFallbacks;
    $quickItems = $withoutComplaintLinks($quickItems);

    $serviceItems = ($electronicServices ?? collect())->map(function ($service) use ($plain) {
        $publicLink = $service->public_link;

        return [
            'icon' => $service->icon ?: '📋',
            'title' => $service->title,
            'description' => $plain($service->short_description ?: $service->body, 120),
            'url' => $publicLink ?: route('electronic-services.show', $service->slug),
            'target' => $publicLink ? ($service->target ?: '_blank') : '_self',
            'label' => $publicLink ? 'ورود به خدمت ←' : 'مشاهده راهنما ←',
        ];
    })->take(6)->values();
    $serviceItems = $withoutComplaintLinks($serviceItems);

    $systemItems = ($systems ?? collect())->map(function ($system) use ($plain) {
        $publicLink = $system->public_link;

        return [
            'icon' => $system->icon ?: '💻',
            'image' => $system->image ? $system->image_url : null,
            'title' => $system->title,
            'description' => $plain($system->short_description ?: $system->description, 120),
            'url' => $publicLink ?: route('systems.show', $system->slug),
            'target' => $publicLink && in_array($system->target, \App\Models\System::TARGETS, true) ? $system->target : '_self',
            'label' => $publicLink ? 'ورود به سامانه' : 'مشاهده جزئیات',
        ];
    })->take(6)->values();
    $systemItems = $withoutComplaintLinks($systemItems);

    $adItems = ($homeAdvertisements ?? collect())->take(4)->filter(fn ($ad) => filled($ad->image))->map(fn ($ad) => ['title' => $ad->title ?: 'تبلیغات', 'url' => $ad->link, 'image' => $assetImage($ad->image), 'target' => $ad->target ?: '_self', 'alt' => data_get($ad, 'alt') ?: ($ad->title ?: 'تبلیغات')])->values();

    $commissionItems = ($commissions ?? collect())->take(8)->map(fn ($commission) => ['icon' => '⚖️', 'title' => $commission->title, 'description' => $plain($commission->description, 130), 'tasks' => $commission->activeTasks->take(3)->map(fn ($task) => ['title' => $task->title, 'description' => $plain($task->description, 120)])->values(), 'url' => route('commissions.show', $commission->slug)])->values();

    $tourismPanels = $tourismPanels ?? collect();

    $followTopicSettings = collect($sectionSetting('systems', 'topics', []));
    $safeHomeLink = function ($value, string $fallback): string {
        $safe = \App\Models\System::normalizePublicLink(is_string($value) ? $value : null);

        if ($safe === null) {
            return $fallback;
        }

        return str_starts_with($safe, '/') ? url($safe) : $safe;
    };
    $followTopics = $followTopicSettings->map(fn ($topic) => is_array($topic) ? $topic : ['title' => $topic, 'url' => $servicesUrl])
        ->filter(fn ($topic) => filled($topic['title'] ?? null))
        ->map(fn ($topic) => [
            'title' => $topic['title'],
            'url' => $safeHomeLink($topic['url'] ?? null, $servicesUrl),
        ])
        ->values();
    if ($followTopics->isEmpty()) {
        $followTopics = collect($serviceItems)->concat($systems ?? collect())->concat($announcements ?? collect())
            ->map(function ($item) use ($servicesUrl, $systemsUrl) {
                if (is_array($item)) {
                    return ['title' => $item['title'] ?? null, 'url' => $item['url'] ?? $servicesUrl];
                }

                if ($item instanceof \App\Models\Announcement) {
                    return ['title' => $item->title, 'url' => route('announcements.show', $item->slug)];
                }

                if ($item instanceof \App\Models\System) {
                    return ['title' => $item->title, 'url' => $item->public_link ?: route('systems.show', $item->slug)];
                }

                return ['title' => $item->title ?? null, 'url' => $systemsUrl];
            })
            ->filter(fn ($topic) => filled($topic['title'] ?? null))
            ->unique('title')
            ->take(18)
            ->values();
    }
    $followTopics = $withoutComplaintLinks($followTopics);
@endphp


@push('styles')
<style>
    .home-main { display: flex; flex-direction: column; }
    @php
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
            '#friendship' => ['important_news'],
            '#tourism' => ['tourism'],
            '#multimedia' => ['videos', 'galleries'],
            '#chamber-members' => ['chamber_members'],
        ];
        $orderedHomeSections = ($homeSections ?? collect())->sortBy('sort_order')->values();
        $homeSectionOrderByKey = $orderedHomeSections->pluck('sort_order', 'key');
    @endphp
    @foreach($homeSectionSelectorOptions as $selector => $keys)
        @php
            $matchingOrders = collect($keys)
                ->map(fn ($key) => $homeSectionOrderByKey->get($key))
                ->filter(fn ($order) => $order !== null);
        @endphp
        @if($matchingOrders->isNotEmpty())
            .home-main > {{ $selector }} { order: {{ (int) $matchingOrders->min() }}; }
        @endif
    @endforeach

    /* Isolated homepage union-news section; no selector escapes .home-union-news. */
    .home-union-news {
        padding-block: 58px;
        background: #fff;
    }
    .home-union-news .home-union-news__heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 20px;
    }
    .home-union-news .home-union-news__heading h2 {
        margin: 0;
        color: #172f40;
        font-size: 21px;
        font-weight: 700;
        line-height: 1.6;
    }
    .home-union-news .home-union-news__heading p {
        margin: 4px 0 0;
        color: #75838d;
        font-size: 12px;
        line-height: 1.8;
    }
    .home-union-news .home-union-news__archive {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 7px 15px;
        border: 1px solid rgba(12, 116, 185, .25);
        border-radius: 7px;
        color: #0c74b9;
        background: #fff;
        font-size: 11.5px;
        font-weight: 600;
        text-decoration: none;
        transition: border-color .18s ease, background-color .18s ease;
    }
    .home-union-news .home-union-news__archive:hover {
        border-color: #0c74b9;
        background: #f6fafc;
    }
    .home-union-news .home-union-news__layout {
        display: grid;
        grid-template-columns: minmax(320px, .82fr) minmax(0, 1.18fr);
        grid-template-areas: "list feature";
        gap: 18px;
        min-width: 0;
        direction: ltr;
    }
    .home-union-news .home-union-news__feature {
        grid-area: feature;
        position: relative;
        display: block;
        min-width: 0;
        min-height: 410px;
        overflow: hidden;
        border: 1px solid rgba(15, 35, 52, .1);
        border-radius: 10px;
        background: #e8eef2;
        color: #fff;
        text-decoration: none;
        direction: rtl;
        isolation: isolate;
    }
    .home-union-news .home-union-news__feature img {
        position: absolute;
        inset: 0;
        display: block;
        width: 100%;
        height: 100%;
        max-width: none;
        object-fit: cover;
        object-position: center;
        transition: transform .3s ease;
    }
    .home-union-news .home-union-news__feature:hover img {
        transform: scale(1.02);
    }
    .home-union-news .home-union-news__shade {
        position: absolute;
        z-index: 1;
        inset: 0;
        background: linear-gradient(180deg, rgba(5, 23, 36, .04) 28%, rgba(5, 23, 36, .88) 100%);
    }
    .home-union-news .home-union-news__feature-copy {
        position: absolute;
        z-index: 2;
        right: 22px;
        bottom: 20px;
        left: 22px;
    }
    .home-union-news .home-union-news__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
        margin-bottom: 7px;
        color: rgba(255,255,255,.8);
        font-size: 10.5px;
        line-height: 1.7;
    }
    .home-union-news .home-union-news__meta span + span::before {
        content: "";
        display: inline-block;
        width: 3px;
        height: 3px;
        margin-inline-end: 7px;
        border-radius: 50%;
        background: rgba(255,255,255,.58);
        vertical-align: middle;
    }
    .home-union-news .home-union-news__feature h3 {
        display: -webkit-box;
        max-width: 92%;
        overflow: hidden;
        margin: 0;
        color: #fff;
        font-size: clamp(17px, 1.8vw, 23px);
        font-weight: 700;
        line-height: 1.7;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
    .home-union-news .home-union-news__feature p {
        display: -webkit-box;
        max-width: 88%;
        overflow: hidden;
        margin: 7px 0 0;
        color: rgba(255,255,255,.78);
        font-size: 11.5px;
        line-height: 1.85;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
    .home-union-news .home-union-news__list-panel {
        grid-area: list;
        min-width: 0;
        height: 410px;
        overflow: hidden;
        border: 1px solid rgba(15, 35, 52, .1);
        border-radius: 10px;
        background: #fff;
        direction: rtl;
    }
    .home-union-news .home-union-news__list {
        height: 100%;
        margin: 0;
        padding: 6px;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-width: thin;
        scrollbar-color: rgba(12,116,185,.35) transparent;
        list-style: none;
    }
    .home-union-news .home-union-news__list::-webkit-scrollbar {
        width: 5px;
    }
    .home-union-news .home-union-news__list::-webkit-scrollbar-thumb {
        border-radius: 5px;
        background: rgba(12,116,185,.35);
    }
    .home-union-news .home-union-news__item + .home-union-news__item {
        border-top: 1px solid rgba(15, 35, 52, .075);
    }
    .home-union-news .home-union-news__item a {
        display: grid;
        grid-template-columns: 72px minmax(0, 1fr);
        align-items: center;
        gap: 11px;
        min-height: 76px;
        padding: 9px 7px;
        color: inherit;
        text-decoration: none;
        transition: background-color .16s ease;
    }
    .home-union-news .home-union-news__item a:hover {
        background: #f7fafc;
    }
    .home-union-news .home-union-news__thumb {
        display: block;
        width: 72px;
        height: 56px;
        overflow: hidden;
        border-radius: 7px;
        background: #edf2f5;
    }
    .home-union-news .home-union-news__thumb img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .home-union-news .home-union-news__item-copy {
        min-width: 0;
    }
    .home-union-news .home-union-news__item-meta {
        display: block;
        overflow: hidden;
        margin-bottom: 2px;
        color: #7f8d97;
        font-size: 9.5px;
        line-height: 1.6;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .home-union-news .home-union-news__item strong {
        display: -webkit-box;
        overflow: hidden;
        color: #213746;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.75;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
    .home-union-news .home-union-news__empty {
        grid-column: 1 / -1;
        padding: 34px;
        border: 1px solid rgba(15, 35, 52, .1);
        border-radius: 10px;
        color: #6f7e88;
        background: #f8fafb;
        font-size: 12px;
        line-height: 1.9;
        text-align: center;
        direction: rtl;
    }
    @media (max-width: 991.98px) {
        .home-union-news .home-union-news__layout {
            grid-template-columns: minmax(280px, .88fr) minmax(0, 1.12fr);
            gap: 14px;
        }
        .home-union-news .home-union-news__feature,
        .home-union-news .home-union-news__list-panel {
            min-height: 360px;
            height: 360px;
        }
    }
    @media (max-width: 767.98px) {
        .home-union-news {
            padding-block: 44px;
        }
        .home-union-news .home-union-news__heading {
            align-items: center;
            margin-bottom: 16px;
        }
        .home-union-news .home-union-news__heading h2 {
            font-size: 18px;
        }
        .home-union-news .home-union-news__heading p {
            font-size: 10.5px;
        }
        .home-union-news .home-union-news__archive {
            min-height: 34px;
            padding-inline: 11px;
            font-size: 10.5px;
        }
        .home-union-news .home-union-news__layout {
            grid-template-columns: minmax(0, 1fr);
            grid-template-areas: "feature" "list";
            gap: 12px;
        }
        .home-union-news .home-union-news__feature {
            min-height: 245px;
            height: 245px;
        }
        .home-union-news .home-union-news__list-panel {
            min-height: 0;
            height: 382px;
        }
        .home-union-news .home-union-news__feature-copy {
            right: 15px;
            bottom: 14px;
            left: 15px;
        }
        .home-union-news .home-union-news__feature h3 {
            max-width: 100%;
            font-size: 16px;
        }
        .home-union-news .home-union-news__feature p {
            display: none;
        }
    }
    @media (max-width: 479.98px) {
        .home-union-news .home-union-news__heading p {
            display: none;
        }
        .home-union-news .home-union-news__item a {
            grid-template-columns: 66px minmax(0, 1fr);
        }
        .home-union-news .home-union-news__thumb {
            width: 66px;
            height: 52px;
        }
    }
</style>
@endpush

@section('content')
<main class="home-main">
<section class="hero-section site-container">
<div class="hero-grid">
<aside aria-label="دسترسی‌های عمودی" class="quick-menu">
<ul class="quick-menu-list">
@foreach($quickItems as $item)
@php
    $children = collect(data_get($item, 'children', []));
@endphp
<li class="quick-menu-item {{ $children->isNotEmpty() ? 'has-submenu' : '' }}">
@if($children->isNotEmpty())
<div class="quick-menu-link quick-menu-combo">
<a class="quick-menu-title-link" href="{{ $item['url'] }}"><span>{{ $item['title'] }}</span></a>
<button aria-expanded="false" class="quick-menu-toggle" type="button"><b></b></button>
</div>
<ul class="quick-submenu">
@foreach($children as $child)
<li><a href="{{ $child['url'] }}">{{ $child['title'] }}</a></li>
@endforeach
</ul>
@else
<a class="quick-menu-link" href="{{ $item['url'] }}"><span>{{ $item['title'] }}</span><b></b></a>
@endif
</li>
@endforeach
</ul>
</aside>
<div aria-label="اسلایدر خبرهای اصلی" class="hero-slider swiper" dir="ltr">
<div class="swiper-wrapper">
@foreach($heroItems as $item)
<article class="news-card news-card-main swiper-slide">
<a href="{{ $item['url'] }}">
<img alt="{{ $item['title'] }}" src="{{ $item['image'] }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" decoding="async" @if($loop->first) fetchpriority="high" @endif/>
<div class="news-overlay"></div>
<div class="news-content">
<span class="news-kicker">{{ $item['kicker'] }}</span>
<h1 class="hero-news-title" title="{{ $item['title'] }}">{{ $item['title'] }}</h1>
</div>
</a>
</article>
@endforeach
</div>
@if($heroItems->count() > 1)
<button aria-label="خبر بعدی" class="hero-slider-arrow hero-slider-next" type="button"></button>
<button aria-label="خبر قبلی" class="hero-slider-arrow hero-slider-prev" type="button"></button>
<div class="hero-slider-pagination"></div>
@endif
</div>
<div aria-label="خبرهای کناری" class="side-news">
@foreach($sideItems as $item)
<article class="news-card side-card">
<a href="{{ $item['url'] }}">
<img alt="{{ $item['title'] }}" src="{{ $item['image'] }}" loading="lazy" decoding="async"/>
<div class="news-overlay"></div>
<div class="news-content"><h2>{{ $item['title'] }}</h2></div>
</a>
</article>
@endforeach
</div>
</div>
</section>

<section class="site-container howto-section home-services-refined">
<div class="section-heading home-refined-heading">
<div>
<h2>{{ $sectionTitle('electronic_services', 'خدمات الکترونیک صنفی') }}</h2>
<p>{{ $sectionSubtitle('electronic_services', 'نحوه انجام خدمات و دریافت مجوزها و ثبت درخواست‌ها') }}</p>
</div>
</div>
<div class="howto-grid">
@forelse($serviceItems as $item)
<a class="howto-card" href="{{ $item['url'] }}" target="{{ $item['target'] ?? '_self' }}" @if(($item['target'] ?? '_self') === '_blank') rel="noopener noreferrer" @endif>
<div class="howto-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 3.5h8M9 2v3M15 2v3M6 4.5h12a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-13a2 2 0 0 1 2-2Z"/><path d="m8 12 2.2 2.2L16 8.5M8 18h8"/></svg></div>
<h3>{{ $item['title'] }}</h3>
<p>{{ $item['description'] }}</p>
<span class="howto-link"><span class="howto-link-mobile">مشاهده</span><span class="howto-link-desktop">{{ $item['label'] }}</span></span>
</a>
@empty
<div class="home-content-empty">خدمت الکترونیکی فعالی برای نمایش ثبت نشده است.</div>
@endforelse
</div>
</section>


<section class="latest-news-section section-white" id="latest-news">
<div class="site-container">
<div class="latest-news-shell">
<div class="section-heading latest-news-heading"><div><h2 id="latest-news-title" tabindex="-1">آخرین اخبار</h2><p>جدیدترین خبرهای اتاق اصناف با چینش هماهنگ با سایر بخش‌های صفحه اصلی</p></div><a class="latest-news-archive" href="{{ route('posts.index') }}">آرشیو اخبار</a></div>
<div class="latest-news-layout">
<div class="latest-news-list" data-latest-news data-endpoint="{{ route('home.latest-news') }}" aria-busy="false">
@include('frontend.home.partials.latest-news-grid', ['latestPosts' => $latestPosts])
</div>
<p class="latest-news-status visually-hidden" data-latest-news-status aria-live="polite"></p>
@php
    $sidebarAdItems = collect($sidebarAdvertisements ?? collect())->take(4);
@endphp
@if($sidebarAdItems->isNotEmpty())
<aside class="latest-news-ads">
@foreach($sidebarAdItems as $ad)
@php
    $sidebarAdUrl = $normalizeInternalUrl($ad->link ?? data_get($ad, 'url', ''));
    $sidebarAdTarget = $ad->target ?? data_get($ad, 'target', '_self');
    $sidebarAdTitle = $ad->title ?? data_get($ad, 'title', 'تبلیغات');
    $sidebarAdImage = $ad->image_url ?? data_get($ad, 'image');
@endphp
@if($sidebarAdUrl !== '')
<a class="latest-news-ad" href="{{ $sidebarAdUrl }}" target="{{ $sidebarAdTarget }}" @if($sidebarAdTarget === '_blank') rel="noopener noreferrer" @endif><img loading="lazy" decoding="async" alt="{{ $sidebarAdTitle }}" src="{{ $sidebarAdImage }}" onerror="this.onerror=null;this.src='{{ $defaultImage }}';"></a>
@else
<div class="latest-news-ad latest-news-ad-placeholder"><img loading="lazy" decoding="async" alt="{{ $sidebarAdTitle }}" src="{{ $sidebarAdImage }}" onerror="this.onerror=null;this.src='{{ $defaultImage }}';"></div>
@endif
@endforeach
</aside>
@endif
</div>
</div>
</div>
</section>

<section class="home-ad-banners site-container">
@php
    $bannerAdItems = collect($bannerAdvertisements ?? collect())->take(4);
@endphp
@foreach($bannerAdItems as $ad)
@php
    $bannerAdUrl = $normalizeInternalUrl($ad->link ?? data_get($ad, 'url', ''));
    $bannerAdTarget = $ad->target ?? data_get($ad, 'target', '_self');
    $bannerAdImage = $ad->image_url ?? data_get($ad, 'image');
    $bannerAdTitle = $ad->title ?? data_get($ad, 'title', 'تبلیغات');
@endphp
@if($bannerAdUrl !== '')
<a class="ad-banner" href="{{ $bannerAdUrl }}" target="{{ $bannerAdTarget }}" @if($bannerAdTarget === '_blank') rel="noopener noreferrer" @endif>
<img alt="{{ $bannerAdTitle }}" src="{{ $bannerAdImage }}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ $defaultImage }}';"/>
</a>
@else
<div class="ad-banner ad-banner-placeholder">
<img alt="{{ $bannerAdTitle }}" src="{{ $bannerAdImage }}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ $defaultImage }}';"/>
</div>
@endif
@endforeach
</section>

<section class="home-union-news" id="representatives" aria-labelledby="home-union-news-title">
<div class="site-container">
<div class="home-union-news__heading">
<div>
<h2 id="home-union-news-title">آخرین اخبار اتحادیه‌ها</h2>
<p>جدیدترین خبرهای منتشرشده از اتحادیه‌های صنفی استان گلستان</p>
</div>
<a class="home-union-news__archive" href="{{ $postsUrl }}">آرشیو اخبار</a>
</div>

<div class="home-union-news__layout">
@if($featuredUnionNews)
@php
    $featuredUnionNewsUrl = route('posts.show', $featuredUnionNews->slug);
    $featuredUnionNewsImage = $featuredUnionNews->featured_image_url ?: $defaultImage;
@endphp
<a class="home-union-news__feature" href="{{ $featuredUnionNewsUrl }}">
<img
    src="{{ $featuredUnionNewsImage }}"
    alt="{{ $featuredUnionNews->title }}"
    loading="lazy"
    decoding="async"
    fetchpriority="low"
>
<span class="home-union-news__shade" aria-hidden="true"></span>
<div class="home-union-news__feature-copy">
<div class="home-union-news__meta">
<span>{{ $featuredUnionNews->union?->title ?? 'اتحادیه صنفی' }}</span>
@if($featuredUnionNews->published_at)
<span>{{ $featuredUnionNews->published_at->format('Y/m/d') }}</span>
@endif
</div>
<h3>{{ $featuredUnionNews->title }}</h3>
@if(filled($featuredUnionNews->excerpt))
<p>{{ $plain($featuredUnionNews->excerpt, 150) }}</p>
@endif
</div>
</a>

@if($unionNewsList->isNotEmpty())
<aside class="home-union-news__list-panel" aria-label="هشت خبر اخیر اتحادیه‌ها">
<ul class="home-union-news__list">
@foreach($unionNewsList as $unionNewsItem)
<li class="home-union-news__item">
<a href="{{ route('posts.show', $unionNewsItem->slug) }}">
<span class="home-union-news__thumb">
<img
    src="{{ $unionNewsItem->featured_image_url ?: $defaultImage }}"
    alt=""
    loading="lazy"
    decoding="async"
    fetchpriority="low"
>
</span>
<span class="home-union-news__item-copy">
<span class="home-union-news__item-meta">
{{ $unionNewsItem->union?->title ?? 'اتحادیه صنفی' }}
@if($unionNewsItem->published_at)
 · {{ $unionNewsItem->published_at->format('Y/m/d') }}
@endif
</span>
<strong>{{ $unionNewsItem->title }}</strong>
</span>
</a>
</li>
@endforeach
</ul>
</aside>
@endif
@else
<div class="home-union-news__empty">هنوز خبری برای اتحادیه‌های صنفی منتشر نشده است.</div>
@endif
</div>
</div>
</section>

<section class="commissions-section ds-tint-block office-services-section home-systems-section" id="commissions">
<div class="site-container">
<div class="section-heading systems-section-heading">
<div><h2>سامانه‌ها و خدمات مرتبط</h2><p>{{ $sectionSubtitle('systems', 'دسترسی سریع به سامانه‌ها و پیوندهای کاربردی اصناف') }}</p></div>
<a class="home-corporate-action" href="{{ $systemsUrl }}">مشاهده همه سامانه‌ها</a>
</div>
<div class="systems-unified-shell">
<div class="systems-primary-group"><h3 class="systems-subtitle">سامانه‌های اصلی</h3><div class="commission-grid compact-grid systems-primary-grid">
@forelse($systemItems as $item)
<a class="commission-item system-home-card {{ filled($item['image'] ?? null) ? 'has-image' : '' }}" href="{{ $item['url'] }}" target="{{ $item['target'] ?? '_self' }}" @if(($item['target'] ?? '_self') === '_blank') rel="noopener noreferrer" @endif>
@if(filled($item['image'] ?? null))
<span class="system-card-icon system-card-image" aria-hidden="true"><img src="{{ $item['image'] }}" alt="" loading="lazy" decoding="async"></span>
@else
<span class="system-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 21h8M12 18v3M7 9h10M7 13h6"/></svg></span>
@endif
<strong>{{ $item['title'] }}</strong>
<span class="system-card-description">{{ $item['description'] }}</span>
<span class="system-card-cta">{{ $item['label'] ?? 'مشاهده' }} <span aria-hidden="true">←</span></span>
</a>
@empty
<div class="home-content-empty">سامانه فعالی برای نمایش در صفحه نخست ثبت نشده است.</div>
@endforelse
</div></div>
<div class="systems-links-group" id="fractions"><h3 class="systems-subtitle">پیوندها و اطلاعیه‌های کاربردی</h3><div class="systems-links-grid">
@forelse($followTopics as $topic)
<a href="{{ $topic['url'] }}" class="systems-utility-link">{{ $topic['title'] }}</a>
@empty
<p class="empty-state">موضوعی برای نمایش در پنل مدیریت ثبت نشده است.</p>
@endforelse
</div></div>
</div>
</div>
</section>
<section class="commissions-real home-commissions-section" id="commissions-real">
<div class="site-container">
<div class="section-heading commissions-section-heading">
<div><h2>{{ $sectionTitle('commissions', 'کمیسیون‌های اتاق اصناف مرکز استان گلستان') }}</h2>
<p>{{ $sectionSubtitle('commissions', 'آخرین اطلاعات کمیسیون‌های تخصصی اتاق اصناف مرکز استان گلستان را در این بخش دنبال کنید.') }}</p>
</div><a class="home-corporate-action" href="{{ $commissionsUrl }}">مشاهده همه کمیسیون‌ها</a>
</div>
<div class="comreal-grid">
@forelse($commissionItems as $item)
<a href="{{ $item['url'] ?? $commissionsUrl }}" class="comreal-card">
<div class="comreal-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3v18M6 6h12M5 6l-3 7h6L5 6Zm14 0-3 7h6l-3-7ZM8 21h8"/></svg></div>
<h3>{{ $item['title'] }}</h3>
<p>{{ $item['description'] }}</p>
@if(($item['tasks'] ?? collect())->isNotEmpty())
<ul class="commission-task-mini">@foreach($item['tasks'] as $task)<li>{{ $task['title'] }}</li>@endforeach</ul>
@else
<ul class="commission-task-mini"><li>اطلاعات تکمیلی این کمیسیون به‌زودی منتشر می‌شود.</li></ul>
@endif
</a>
@empty
<div class="home-content-empty">کمیسیون فعالی برای نمایش در صفحه نخست ثبت نشده است.</div>
@endforelse
</div>
</div>
</section>

@if($tourismPanels->isNotEmpty())
<section class="tourism-section" id="tourism">
<div class="site-container">
<div class="tourism-tab-controls" data-tab-group="tourism">
<div class="section-heading home-refined-heading tourism-heading">
<div><h2>{{ $sectionTitle('tourism', 'گردشگری گرگان و گلستان') }}</h2></div>
<div class="tourism-heading-actions">
@php
    $primaryTourismPanel = $tourismPanels->keys()->first();
@endphp
@if($primaryTourismPanel !== null)
<button class="tab-pill active" data-tab-target="{{ $primaryTourismPanel }}" type="button">{{ $tourismPanels->get($primaryTourismPanel)['label'] }}</button>
@endif
<a class="home-refined-action" href="{{ $tourismUrl }}">مشاهده همه جاذبه‌ها</a>
</div>
 </div>
@if($tourismPanels->count() > 1)
<div aria-label="دسته‌بندی گردشگری" class="tabs tourism-tabs" role="tablist">
@foreach($tourismPanels->skip(1) as $panel => $panelData)
<button class="tab-pill" data-tab-target="{{ $panel }}" type="button">{{ $panelData['label'] }}</button>
@endforeach
</div>
@endif
</div>
<div class="tab-panels" data-tab-panels="tourism">
@foreach($tourismPanels as $panel => $panelData)
<div class="tab-panel {{ $loop->first ? 'active' : '' }}" data-tab-panel="{{ $panel }}">
<div class="tourism-grid">
@foreach($panelData['items'] as $place)
@php
    $placeDesc = plain_text($place->home_description, 120) ?: 'توضیحی برای این جاذبه ثبت نشده است.';
@endphp
<div class="tourism-card">
<a href="{{ route('tourism.show', $place->slug) }}">
<div class="tourism-img-wrap {{ $place->directory_image_url ? 'has-image' : 'is-missing-image' }}">
@if($place->directory_image_url)
<img alt="{{ $place->title }}" src="{{ $place->directory_image_url }}" loading="lazy" decoding="async"/>
@else
<div class="tourism-image-placeholder" aria-label="تصویر این جاذبه ثبت نشده است">
<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19 9 13l3 3 3-4 5 7M7 8h.01"/><rect x="3" y="4" width="18" height="16" rx="2"/></svg>
<span>تصویر ثبت نشده</span>
</div>
@endif
<div class="tourism-badge">{{ $place->home_badge }}</div>
</div>
<div class="tourism-card-body">
<h3>{{ $place->title }}</h3>
<p>{{ $placeDesc }}</p>
</div>
</a>
</div>
@endforeach
</div>
</div>
@endforeach
</div>
</div>
</section>
@endif


<section class="multimedia-section" id="multimedia">
<div class="site-container">
<div class="media-header" data-tab-group="media">
<h2>{{ $sectionTitle('videos', 'چندرسانه‌ای') }}</h2>
<div class="media-tab-group">
<button class="media-tab active" data-tab-target="media-video" type="button">ویدیوها</button>
<button class="media-tab" data-tab-target="media-image" type="button">تصاویر</button>
</div>
</div>
<div class="tab-panels" data-tab-panels="media">
<div class="tab-panel active" data-tab-panel="media-video">
      <div class="media-grid">
@forelse(($latestVideos ?? collect())->take(5) as $video)
      <a href="{{ route('videos.show', $video->slug) }}" class="media-card {{ $loop->first ? 'media-card-lg' : '' }}">
        <img alt="{{ $video->title }}" src="{{ $video->cover_image_url }}" loading="lazy" decoding="async"/>
        <div class="media-card-overlay"></div>
        <span class="media-play-btn"></span>
        <div class="media-card-footer">
          <h3>{{ $video->title }}</h3>
        </div>
      </a>
@empty
<div class="home-media-empty">ویدیوی منتشرشده‌ای برای نمایش وجود ندارد.</div>
@endforelse
      </div>
      <a class="media-view-all" href="{{ $videosUrl }}">مشاهده همه ویدیوها</a>
</div>
<div class="tab-panel" data-tab-panel="media-image">
<div class="media-grid">
@forelse(($latestGalleries ?? collect())->take(8) as $gallery)
<a href="{{ route('galleries.show', $gallery->slug) }}" class="media-card">
<img alt="{{ $gallery->title }}" src="{{ $gallery->cover_image_url }}" loading="lazy" decoding="async"/>
<div class="media-card-overlay"></div>
<div class="media-card-footer">
<h3>{{ $gallery->title }}</h3>
</div>
</a>
@empty
<div class="home-media-empty">گالری منتشرشده‌ای برای نمایش وجود ندارد.</div>
@endforelse
</div>
<a class="media-view-all" href="{{ $galleriesUrl }}">مشاهده همه تصاویر</a>
</div>
</div>
</div>
</section>


<section class="chamber-members-home" id="chamber-members">
<div class="site-container">
<div class="section-heading chamber-members-heading members-section-heading">
<div>
<h2>{{ $sectionTitle('chamber_members', 'هیئت مدیره اصناف') }}</h2>
<p>{{ $sectionSubtitle('chamber_members', 'معرفی اعضای هیئت مدیره اصناف مرکز استان گلستان') }}</p>
</div>
</div>
<div class="members-swiper swiper" data-members-swiper>
<div class="chamber-members-grid swiper-wrapper">
@forelse(($chamberMembers ?? collect())->take(6) as $member)
<article class="chamber-member-card swiper-slide">
<div class="chamber-member-card-glow"></div>
<a href="{{ route('chamber-members.index') }}" aria-label="مشاهده معرفی {{ $member->full_name }}">
<div class="chamber-member-photo-wrap">
<img alt="{{ $member->full_name }}" src="{{ $member->photo_url }}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ $defaultImage }}';"/>
<span class="chamber-member-number">{{ $loop->iteration }}</span>
</div>
<div class="chamber-member-body">
<span class="chamber-member-label">عضو هیئت‌مدیره</span>
<h3>{{ $member->full_name }}</h3>
<p class="chamber-member-position">{{ $member->position }}</p>
<div class="chamber-member-meta">
<span>معرفی کامل</span>
<strong>مشاهده ←</strong>
</div>
</div>
</a>
</article>
@empty
<div class="chamber-members-empty">
<img alt="اعضای هیئت‌مدیره" src="{{ $defaultImage }}" loading="lazy" decoding="async"/>
<div>
<span class="chamber-member-label">در انتظار تکمیل محتوا</span>
<h3>هنوز عضوی برای نمایش در صفحه نخست ثبت نشده است.</h3>
<p>از بخش «اعضای اتاق اصناف» در پنل مدیریت، اعضای هیئت‌مدیره را همراه با تصویر، سمت و ترتیب نمایش ثبت کنید.</p>
</div>
</div>
@endforelse
</div>
<div class="members-swiper-pagination swiper-pagination" aria-label="صفحه‌بندی اعضای هیئت‌مدیره"></div>
</div>
</div>
</section>

</main>
@endsection
