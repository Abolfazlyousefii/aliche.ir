@php
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
@endphp
<header class="site-header">
<div class="header-top site-container">
<a class="brand-note" href="{{ route('home') }}" aria-label="{{ $siteTitle }}">
<span class="brand-note-media">
<img alt="{{ $siteTitle }}" class="header-logo-simple" src="{{ $logo }}"/>
</span>
<span class="brand-note-copy">
<span>{{ $todayLabel }}</span>
<strong>{{ $topText }}</strong>
<span class="brand-note-title">{{ $siteTitle }}</span>
</span>
</a>

<div class="header-left-actions" aria-label="راه‌های دسترسی سریع هدر">
@forelse($headerButtons as $button)
<a class="header-service-pill" href="{{ $button['url'] }}" target="{{ $button['target'] ?? '_self' }}" @if(($button['target'] ?? '_self') === '_blank') rel="noopener" @endif><svg aria-hidden="true" class="ui-icon" viewBox="0 0 24 24"><path d="M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm3 4h8M8 12h8M8 16h5"/></svg><span>{{ $button['title'] }}</span></a>
@empty
@if($topItems->isNotEmpty())
@php($topItem = $topItems->first())
<a class="header-service-pill" href="{{ $topItem->resolved_url }}" target="{{ $topItem->target }}">{{ $topItem->title }}</a>
@endif
@endforelse
<a class="header-contact-card" href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">
<span>{{ $contactText }}</span>
<strong>{{ fa_number($phone) }}</strong>
</a>
</div>
</div>
<nav aria-label="منوی اصلی" class="navbar navbar-expand-lg main-navbar site-container">
<button aria-controls="mainNav" aria-expanded="false" aria-label="باز کردن منو" class="navbar-toggler" data-bs-target="#mainNav" data-bs-toggle="collapse" type="button">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="mainNav">
<ul class="navbar-nav me-auto mb-2 mb-lg-0 nav-dots top-nav-menu">
@if($mainItems->isNotEmpty())
    @foreach($mainItems as $menuItem)
        @include('frontend.partials.dynamic-menu-item', ['menuItem' => $menuItem, 'variant' => 'classic', 'itemClass' => 'nav-item', 'linkClass' => 'nav-link'])
    @endforeach
@else
<li class="nav-item"><a class="nav-link active" href="{{ route('home') }}">صفحه اصلی</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('posts.index') }}">اخبار</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('announcements.index') }}">اطلاعیه‌ها</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('guilds.index') }}">اتحادیه‌ها</a></li>
<li class="nav-item top-nav-item has-top-submenu">
<button aria-expanded="false" class="nav-link top-nav-link" type="button">خدمات الکترونیک<span class="top-submenu-caret"></span></button>
<ul class="top-submenu"><li><a href="{{ route('electronic-services.index') }}">خدمات الکترونیک</a></li><li><a href="{{ route('systems.index') }}">سامانه‌ها</a></li></ul>
</li>
<li class="nav-item"><a class="nav-link" href="{{ route('galleries.index') }}">گالری تصاویر</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('tourism.index') }}">گردشگری</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('contact.create') }}">تماس با ما</a></li>
@endif
</ul>
<div class="header-mobile-actions" aria-label="راه‌های تماس و خدمات">
@forelse($headerButtons as $button)
<a href="{{ $button['url'] }}" target="{{ $button['target'] ?? '_self' }}" @if(($button['target'] ?? '_self') === '_blank') rel="noopener" @endif><svg aria-hidden="true" class="ui-icon" viewBox="0 0 24 24"><path d="M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm3 4h8M8 12h8M8 16h5"/></svg><span>{{ $button['title'] }}</span></a>
@empty
@if($topItems->isNotEmpty())
@php($topItem = $topItems->first())
<a href="{{ $topItem->resolved_url }}" target="{{ $topItem->target }}"><svg aria-hidden="true" class="ui-icon" viewBox="0 0 24 24"><path d="M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm3 4h8M8 12h8M8 16h5"/></svg><span>{{ $topItem->title }}</span></a>
@endif
@endforelse
<a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}"><svg aria-hidden="true" class="ui-icon" viewBox="0 0 24 24"><path d="M7.1 3.5 4.8 5.8c-.8.8.2 3.8 3.1 6.7s5.9 3.9 6.7 3.1l2.3-2.3 3.3 3.3-1.5 1.5c-2.3 2.3-7.5.7-12-3.8S.6 4.6 2.9 2.3L4.4.8l2.7 2.7Z"/></svg><span>{{ $contactText }}: {{ fa_number($phone) }}</span></a>
</div>
</div>
<button aria-controls="headerSearchPanel" aria-expanded="false" aria-label="جستجو در سایت" class="search-trigger" type="button">
<span class="visually-hidden">جستجو</span>
</button>
</nav>
<div class="header-search-panel site-container" hidden="" id="headerSearchPanel">
<form action="{{ route('search') }}" method="GET" class="header-search-form" role="search">
<label class="header-search-label" for="siteSearchInput">جستجو در سایت</label>
<div class="header-search-field">
<input id="siteSearchInput" name="q" value="{{ request('q') }}" placeholder="عبارت مورد نظر را وارد کنید؛ مثل اتحادیه، پروانه کسب، شکایت، آموزش..." type="search"/>
<button type="submit">جستجو</button>
</div>
<div aria-live="polite" class="header-search-results"></div>
</form>
</div>
</header>
