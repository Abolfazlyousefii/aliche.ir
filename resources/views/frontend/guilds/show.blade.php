@extends('frontend.layouts.app')

@section('title', ($union->meta_title ?: ($union->display_title ?: 'اتحادیه صنفی')).' | اتاق اصناف مرکز استان گلستان')
@section('meta_description', $union->meta_description ?: ($union->short_description ?: 'اطلاعات اتحادیه صنفی، مدیران، قوانین، اخبار، اطلاعیه‌ها، خدمات و راه‌های تماس'))
@section('canonical', route('guilds.show', $union->slug))

@php
    $assetImage = fn (?string $path, string $fallback = 'assets/img/asnaf-gorgan-default.jpg') => image_url($path, $fallback);
    $plain = fn ($value, $limit = 160) => plain_text($value, $limit);
    $initial = fn ($value) => mb_substr(trim((string) $value) ?: 'ا', 0, 1);
    $normalizePhone = function (?string $value): string {
        $ascii = strtr((string) $value, [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ]);
        $normalized = preg_replace('/[^0-9+]/', '', $ascii) ?? '';
        return (str_starts_with($normalized, '+') ? '+' : '').str_replace('+', '', $normalized);
    };

    $posts = collect($unionNews ?? []);
    $articles = $union->posts->where('type', 'article')->values();
    $socialLinks = collect($union->social_links ?? [])->filter(fn ($url) => filled($url));
    $presidentButtons = collect($union->active_president_buttons);
    $unionMessages = collect($unionMessages ?? []);
    $unionPhone = $union->phone ?: $union->mobile;
    $unionPhoneHref = $normalizePhone($unionPhone);
    $unionTypeLabel = trim((string) ($union->category?->title ?: $union->union_type_label));
    $showUnionTypeLabel = filled($unionTypeLabel) && ! in_array($unionTypeLabel, ['نامشخص', 'ثبت نشده', 'بدون دسته‌بندی'], true);

    $showManager = $union->isSectionEnabled('show_manager', true) && filled($union->manager_name);
    $showMembers = $union->members_enabled && $union->isSectionEnabled('show_board_members', true) && $union->members->isNotEmpty();
    $showCommissions = $union->isSectionEnabled('show_commissions', true) && $union->commissions->isNotEmpty();
    $showRules = $union->isSectionEnabled('show_rules', true) && $union->rules->isNotEmpty();
    // show_news is the current setting; show_news_slider remains a legacy fallback
    // until an old record is saved and both values are normalized by the admin form.
    $showNews = $union->news_enabled && ($union->isSectionEnabled('show_news', true) || $union->isSectionEnabled('show_news_slider', false)) && $posts->isNotEmpty();
    $showArticles = $union->news_enabled && $union->isSectionEnabled('show_articles', true) && $articles->isNotEmpty();
    $showPrices = $union->isSectionEnabled('show_prices', false) && ($union->prices->isNotEmpty() || filled($union->price_list_image));
    $showComplaint = $union->complaint_enabled && $union->isSectionEnabled('show_complaint', true);
    $showMessages = $union->congratulations_enabled && $union->isSectionEnabled('show_congratulation_messages', true) && $unionMessages->isNotEmpty();
    $showMinutes = $union->isSectionEnabled('show_minutes', true) && $union->minutes->isNotEmpty();
    $showEducation = $union->isSectionEnabled('show_education', true) && $union->educations->isNotEmpty();
    $showAnnouncements = $union->announcements_enabled && $union->isSectionEnabled('show_announcements', true) && $union->announcements->isNotEmpty();
    $showGalleries = $union->gallery_enabled && $union->isSectionEnabled('show_gallery', true) && $union->galleries->isNotEmpty();
    $showVideos = $union->videos_enabled && $union->isSectionEnabled('show_videos', true) && $union->videos->isNotEmpty();
    $showGallerySection = $showGalleries || $showVideos;
    $hasContactData = filled($union->address) || filled($unionPhone) || filled($union->email) || filled($union->working_hours) || filled($union->website) || $socialLinks->isNotEmpty();
    $showContact = $union->isSectionEnabled('show_contact', true) && $hasContactData;

    $heroStats = collect([
        ['label' => 'اعضای هیئت‌مدیره', 'value' => $union->members->count()],
        ['label' => 'کمیسیون فعال', 'value' => $union->commissions->count()],
        ['label' => 'خبر و اطلاعیه', 'value' => $posts->count() + $union->announcements->count()],
    ])->filter(fn ($item) => $item['value'] > 0)->values();

    $contentLinks = collect([
        ['visible' => $showMembers, 'id' => 'guild-board', 'title' => 'اعضای هیئت‌مدیره', 'description' => 'معرفی اعضا و مسئولیت‌های سازمانی', 'icon' => 'members'],
        ['visible' => $showCommissions, 'id' => 'guild-commissions', 'title' => 'کمیسیون‌های اتحادیه', 'description' => 'کمیسیون‌ها و وظایف ثبت‌شده', 'icon' => 'commission'],
        ['visible' => $showRules, 'id' => 'guild-rules', 'title' => 'قوانین و دستورالعمل‌ها', 'description' => 'آیین‌نامه‌ها و فایل‌های قابل دریافت', 'icon' => 'rules'],
        ['visible' => $showNews, 'id' => 'guild-news', 'title' => 'اخبار اتحادیه', 'description' => 'آخرین خبرهای مرتبط با اتحادیه', 'icon' => 'news'],
        ['visible' => $showArticles || $showEducation, 'id' => $showArticles ? 'guild-articles' : 'guild-education', 'title' => 'آموزش‌های صنفی', 'description' => 'مقاله‌ها و محتوای آموزشی', 'icon' => 'education'],
        ['visible' => $showAnnouncements, 'id' => 'guild-announcements', 'title' => 'اطلاعیه‌ها و بخشنامه‌ها', 'description' => 'آخرین ابلاغیه‌ها و اطلاع‌رسانی‌ها', 'icon' => 'announcement'],
        ['visible' => $showMinutes, 'id' => 'guild-minutes', 'title' => 'صورت‌جلسه‌ها', 'description' => 'مصوبات و گزارش جلسات', 'icon' => 'minutes'],
        ['visible' => $showPrices, 'id' => 'guild-prices', 'title' => 'نرخ‌نامه اتحادیه', 'description' => 'قیمت‌ها و نرخ‌های ثبت‌شده', 'icon' => 'prices'],
        ['visible' => $showGallerySection, 'id' => 'guild-gallery', 'title' => 'گالری رسانه‌ای', 'description' => 'تصاویر و ویدئوهای اتحادیه', 'icon' => 'gallery'],
    ])->filter(fn ($item) => $item['visible'])->values();
    $showServices = $union->services_enabled && $contentLinks->isNotEmpty();

    $navItems = collect([
        ['visible' => $showServices, 'id' => 'guild-services', 'label' => 'خدمات و محتوا'],
        ['visible' => $showManager, 'id' => 'guild-manager', 'label' => 'رئیس اتحادیه'],
        ['visible' => $showMembers, 'id' => 'guild-board', 'label' => 'هیئت‌مدیره'],
        ['visible' => $showCommissions, 'id' => 'guild-commissions', 'label' => 'کمیسیون‌ها'],
        ['visible' => $showRules, 'id' => 'guild-rules', 'label' => 'قوانین'],
        ['visible' => $showNews, 'id' => 'guild-news', 'label' => 'اخبار'],
        ['visible' => $showArticles, 'id' => 'guild-articles', 'label' => 'مقاله‌ها'],
        ['visible' => $showPrices, 'id' => 'guild-prices', 'label' => 'نرخ‌نامه'],
        ['visible' => $showComplaint, 'id' => 'guild-complaint', 'label' => 'ثبت شکایت'],
        ['visible' => $showMessages, 'id' => 'guild-messages', 'label' => 'پیام‌ها'],
        ['visible' => $showMinutes, 'id' => 'guild-minutes', 'label' => 'صورت‌جلسه‌ها'],
        ['visible' => $showEducation, 'id' => 'guild-education', 'label' => 'آموزش‌ها'],
        ['visible' => $showAnnouncements, 'id' => 'guild-announcements', 'label' => 'اطلاعیه‌ها'],
        ['visible' => $showGallerySection, 'id' => 'guild-gallery', 'label' => 'گالری'],
        ['visible' => $showContact, 'id' => 'guild-contact', 'label' => 'تماس'],
    ])->filter(fn ($item) => $item['visible'])->values();

    $quickActions = collect([
        ['visible' => $showManager, 'id' => 'guild-manager', 'title' => 'رئیس اتحادیه', 'subtitle' => $union->manager_name, 'icon' => 'manager'],
        ['visible' => $showComplaint, 'id' => 'guild-complaint', 'title' => 'ثبت شکایت', 'subtitle' => 'ثبت و پیگیری آنلاین', 'icon' => 'complaint'],
        ['visible' => $showContact, 'id' => 'guild-contact', 'title' => 'راه‌های ارتباطی', 'subtitle' => $hasContactData ? 'تماس و اطلاعات اتحادیه' : 'اطلاعات در حال تکمیل', 'icon' => 'contact'],
        ['visible' => $showServices, 'id' => 'guild-services', 'title' => 'خدمات اتحادیه', 'subtitle' => 'دسترسی سریع به بخش‌ها', 'icon' => 'services'],
    ])->filter(fn ($item) => $item['visible'])->values();

    $settings = app(\App\Services\SettingService::class);
    $officePhone = $settings->get('site.phone', '01732152912');
    $officePhoneHref = $normalizePhone($officePhone);
    $officeEmail = $settings->get('site.email', 'info@asnaf-gorgan.ir');
@endphp

@section('content')
<main class="guild-profile-page" data-guild-profile>
    <section class="guild-profile-hero" @if($union->primary_image) style="--guild-profile-cover:url('{{ $assetImage($union->primary_image, '') }}')" @endif>
        <div class="site-container guild-profile-hero__inner">
            <div class="guild-profile-hero__content">
                <nav class="guild-profile-breadcrumb" aria-label="مسیر صفحه">
                    <a href="{{ route('home') }}">خانه</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('guilds.index') }}">اتحادیه‌ها</a>
                    <span aria-hidden="true">/</span>
                    <span>{{ $union->display_title }}</span>
                </nav>

                @if($showUnionTypeLabel)
                    <span class="guild-profile-kicker"><i aria-hidden="true"></i>{{ $unionTypeLabel }}</span>
                @endif
                <h1>{{ $union->display_title }}</h1>
                <p>{{ $plain($union->short_description, 240) ?: $plain($union->description, 240) ?: 'مرجع اطلاع‌رسانی، خدمات و راه‌های ارتباطی این اتحادیه صنفی.' }}</p>

                <div class="guild-profile-hero__actions">
                    @if($showComplaint)
                        <a class="guild-profile-btn guild-profile-btn--light" href="{{ route('complaints.create', ['union' => $union->id]) }}">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/></svg>
                            ثبت شکایت صنفی
                        </a>
                    @endif
                    @if(filled($unionPhoneHref))
                        <a class="guild-profile-btn guild-profile-btn--outline" href="tel:{{ $unionPhoneHref }}">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M7 3 4 6c0 6 8 14 14 14l3-3-4-4-3 2c-2-1-4-3-5-5l2-3-4-4Z"/></svg>
                            تماس با اتحادیه
                        </a>
                    @elseif($showManager)
                        <a class="guild-profile-btn guild-profile-btn--outline" href="#guild-manager">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3"/><path d="M5 20c0-4 3-7 7-7s7 3 7 7"/></svg>
                            معرفی رئیس اتحادیه
                        </a>
                    @endif
                </div>
            </div>

            <div class="guild-profile-hero__identity {{ $union->logo ? '' : 'guild-profile-hero__identity--fallback' }}">
                <div class="guild-profile-emblem {{ $union->logo ? '' : 'guild-profile-emblem--fallback' }}" data-guild-profile-image-wrap>
                    @if($union->logo)
                        <img src="{{ $assetImage($union->logo, '') }}" alt="لوگوی {{ $union->display_title }}" loading="eager" decoding="async" data-guild-profile-optional-image>
                    @endif
                    <span @if($union->logo) hidden @endif data-guild-profile-image-fallback>{{ $initial($union->display_title) }}</span>
                </div>
                @if($heroStats->isNotEmpty())
                    <div class="guild-profile-hero__stats" aria-label="آمار اتحادیه">
                        @foreach($heroStats as $stat)
                            <div><strong>{{ fa_number(number_format($stat['value'])) }}</strong><span>{{ $stat['label'] }}</span></div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if($quickActions->isNotEmpty())
        <div class="guild-profile-quickbar">
            <div class="site-container guild-profile-quickbar__inner">
                @foreach($quickActions as $action)
                    <a href="#{{ $action['id'] }}" class="guild-profile-quickbar__item">
                        <span class="guild-profile-quickbar__icon">
                            @switch($action['icon'])
                                @case('manager')<svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3"/><path d="M5 20c0-4 3-7 7-7s7 3 7 7"/></svg>@break
                                @case('complaint')<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 4h16v12H7l-3 3V4Z"/><path d="M8 8h8M8 12h5"/></svg>@break
                                @case('contact')<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M7 3 4 6c0 6 8 14 14 14l3-3-4-4-3 2c-2-1-4-3-5-5l2-3-4-4Z"/></svg>@break
                                @default<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 5h16v14H4zM8 9h8M8 13h8M8 17h5"/></svg>
                            @endswitch
                        </span>
                        <span><strong>{{ $action['title'] }}</strong><small>{{ $action['subtitle'] }}</small></span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="site-container guild-profile-layout">
        <div class="guild-profile-content">
            @if($showServices)
                <section class="guild-profile-section" id="guild-services" data-guild-profile-section>
                    <header class="guild-profile-section__head">
                        <div><span>دسترسی سریع</span><h2>خدمات و محتوای اتحادیه</h2><p>بخش‌های دارای اطلاعات به‌صورت خودکار در این صفحه نمایش داده می‌شوند.</p></div>
                    </header>
                    <div class="guild-profile-services">
                        @foreach($contentLinks as $item)
                            <a href="#{{ $item['id'] }}" class="guild-profile-service">
                                <span class="guild-profile-service__icon">
                                    @switch($item['icon'])
                                        @case('members')<svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="8" cy="8" r="3"/><circle cx="17" cy="9" r="2"/><path d="M2 20c0-4 2.7-7 6-7s6 3 6 7M14 15c3 0 5 2 5 5"/></svg>@break
                                        @case('commission')<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h16v12H4zM8 7V4h8v3M9 12h6"/></svg>@break
                                        @case('rules')<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/></svg>@break
                                        @case('news')<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 5h16v14H4zM7 9h4v4H7zM13 9h4M13 12h4M7 16h10"/></svg>@break
                                        @case('education')<svg aria-hidden="true" viewBox="0 0 24 24"><path d="m3 9 9-5 9 5-9 5-9-5Z"/><path d="M6 11v5c3 2 9 2 12 0v-5"/></svg>@break
                                        @case('announcement')<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 12h3l9-5v10l-9-5H4zM7 15l2 5"/></svg>@break
                                        @case('minutes')<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 6h16v14H4zM8 3v6M16 3v6M4 10h16"/></svg>@break
                                        @case('prices')<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/><circle cx="16" cy="16" r="3"/></svg>@break
                                        @default<svg aria-hidden="true" viewBox="0 0 24 24"><path d="m4 18 4-5 4 3 4-6 4 8H4Z"/><circle cx="8" cy="8" r="2"/></svg>
                                    @endswitch
                                </span>
                                <strong>{{ $item['title'] }}</strong>
                                <small>{{ $item['description'] }}</small>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($showManager)
                <section class="guild-profile-section" id="guild-manager" data-guild-profile-section>
                    <header class="guild-profile-section__head">
                        <div><span>مدیریت اتحادیه</span><h2>رئیس اتحادیه</h2><p>معرفی مسئول اجرایی {{ $union->display_title }}</p></div>
                    </header>
                    <div class="guild-profile-manager">
                        <div class="guild-profile-manager__avatar" data-guild-profile-image-wrap>
                            @if($union->manager_image)
                                <img src="{{ $assetImage($union->manager_image, '') }}" alt="{{ $union->manager_name }}" loading="lazy" decoding="async" data-guild-profile-optional-image>
                            @endif
                            <span @if($union->manager_image) hidden @endif data-guild-profile-image-fallback>{{ $initial($union->manager_name) }}</span>
                            <small>{{ $union->manager_position ?: 'رئیس اتحادیه' }}</small>
                        </div>
                        <div class="guild-profile-manager__info">
                            <h3>{{ $union->manager_name }}</h3>
                            <strong>{{ $union->manager_position ?: 'رئیس اتحادیه' }}</strong>
                            @if(filled($union->manager_description))
                                <p>{{ $union->manager_description }}</p>
                            @endif
                            @if($presidentButtons->isNotEmpty() || filled($unionPhoneHref) || filled($union->email))
                                <div class="guild-profile-manager__actions">
                                    @forelse($presidentButtons as $button)
                                        <a href="{{ $button['url'] }}" target="{{ $button['target'] ?? '_self' }}" @if(($button['target'] ?? '_self') === '_blank') rel="noopener noreferrer" @endif><x-ui-icon :name="$button['icon'] ?: 'link'" /> <span>{{ $button['title'] }}</span></a>
                                    @empty
                                        @if(filled($unionPhoneHref))<a href="tel:{{ $unionPhoneHref }}"><x-ui-icon name="phone" /> <span>تماس با اتحادیه</span></a>@endif
                                        @if($union->email)<a href="mailto:{{ $union->email }}"><x-ui-icon name="email" /> <span>ارسال ایمیل</span></a>@endif
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            @if($showMembers)
                <section class="guild-profile-section" id="guild-board" data-guild-profile-section>
                    <header class="guild-profile-section__head"><div><span>ساختار مدیریتی</span><h2>اعضای هیئت‌مدیره اتحادیه</h2></div></header>
                    <div class="guild-profile-members">
                        @foreach($union->members as $member)
                            <article class="guild-profile-member">
                                <div class="guild-profile-member__avatar" data-guild-profile-image-wrap>
                                    @if($member->image)
                                        <img src="{{ $member->image_url }}" alt="{{ $member->full_name }}" loading="lazy" decoding="async" data-guild-profile-optional-image>
                                    @endif
                                    <span @if($member->image) hidden @endif data-guild-profile-image-fallback>{{ $initial($member->full_name) }}</span>
                                </div>
                                <div><h3>{{ $member->full_name }}</h3><strong>{{ $member->position ?: 'عضو اتحادیه' }}</strong>@if($member->business_name)<p>{{ $member->business_name }}</p>@endif</div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($showCommissions)
                <section class="guild-profile-section" id="guild-commissions" data-guild-profile-section>
                    <header class="guild-profile-section__head"><div><span>کمیسیون‌های تخصصی</span><h2>کمیسیون‌های اتحادیه</h2></div></header>
                    <div class="guild-profile-commissions">
                        @foreach($union->commissions as $commission)
                            <article class="guild-profile-commission">
                                <span class="guild-profile-commission__number">@if(filled($commission->icon))<x-ui-icon :name="$commission->icon" />@else{{ fa_number($loop->iteration) }}@endif</span>
                                <div><h3>{{ $commission->title }}</h3>@if($commission->description)<p>{{ $plain($commission->description, 180) }}</p>@endif
                                    @if($union->isSectionEnabled('show_commission_tasks', true) && $commission->tasks->isNotEmpty())
                                        <ul>@foreach($commission->tasks as $task)<li>{{ $task->title }}</li>@endforeach</ul>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($showRules)
                <section class="guild-profile-section" id="guild-rules" data-guild-profile-section>
                    <header class="guild-profile-section__head"><div><span>اسناد و مقررات</span><h2>قوانین و دستورالعمل‌ها</h2></div></header>
                    <div class="guild-profile-documents">
                        @foreach($union->rules as $rule)
                            <article class="guild-profile-document">
                                <span><x-ui-icon :name="$rule->icon ?: 'document'" /></span>
                                <div><h3>{{ $rule->title }}</h3>@if($rule->description)<p>{{ $plain($rule->description, 160) }}</p>@endif</div>
                                @if($rule->file)<a href="{{ $assetImage($rule->file, '') }}" target="_blank" rel="noopener noreferrer">دانلود فایل</a>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($showNews)
                <section class="guild-profile-section" id="guild-news" data-guild-profile-section>
                    <header class="guild-profile-section__head"><div><span>رسانه اتحادیه</span><h2>آخرین اخبار اتحادیه</h2></div></header>
                    <div class="guild-profile-posts guild-profile-news-list">
                        @foreach($posts->take(6) as $post)
                            <article class="guild-profile-post">
                                <a href="{{ route('posts.show', $post->slug) }}">
                                    <div class="guild-profile-post__media"><img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" loading="lazy" decoding="async"></div>
                                    <div class="guild-profile-post__body"><span>{{ jalali_date($post->published_at) ?: 'بدون تاریخ' }}</span><h3>{{ $post->title }}</h3>@if($post->summary)<p>{{ $post->summary }}</p>@endif</div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($showArticles)
                <section class="guild-profile-section" id="guild-articles" data-guild-profile-section>
                    <header class="guild-profile-section__head"><div><span>دانش صنفی</span><h2>مقاله‌ها و مطالب آموزشی</h2></div></header>
                    <div class="guild-profile-posts guild-profile-posts--compact">
                        @foreach($articles->take(6) as $article)
                            <article class="guild-profile-post"><a href="{{ route('posts.show', $article->slug) }}"><div class="guild-profile-post__media"><img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" loading="lazy" decoding="async"></div><div class="guild-profile-post__body"><span>مقاله آموزشی</span><h3>{{ $article->title }}</h3>@if($article->summary)<p>{{ $article->summary }}</p>@endif</div></a></article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($showPrices)
                <section class="guild-profile-section" id="guild-prices" data-guild-profile-section>
                    <header class="guild-profile-section__head"><div><span>اطلاعات قیمت</span><h2>نرخ‌نامه اختصاصی اتحادیه</h2></div></header>
                    @if(($union->price_list_mode ?? 'table') === 'image' && $union->price_list_image)
                        <a class="guild-profile-price-image" href="{{ $assetImage($union->price_list_image, '') }}" target="_blank" rel="noopener noreferrer"><img src="{{ $assetImage($union->price_list_image, '') }}" alt="نرخ‌نامه {{ $union->display_title }}" loading="lazy" decoding="async"></a>
                    @else
                        <div class="guild-profile-table-wrap"><table class="guild-profile-table"><thead><tr><th>عنوان</th><th>نوع</th><th>قیمت</th><th>بروزرسانی</th></tr></thead><tbody>@foreach($union->prices as $price)<tr><td>{{ $price->title }}</td><td>{{ $price->type ?: 'عمومی' }}</td><td>{{ $price->price ? fa_number(number_format((float)$price->price)).' '.$price->currency : 'اعلام نشده' }}</td><td>{{ $price->updated_on ? jalali_date($price->updated_on) : '—' }}</td></tr>@endforeach</tbody></table></div>
                    @endif
                </section>
            @endif

            @if($showComplaint)
                <section class="guild-profile-complaint" id="guild-complaint" data-guild-profile-section>
                    <div><span>خدمات الکترونیکی</span><h2>ثبت و پیگیری شکایت صنفی</h2><p>شکایت مرتبط با این اتحادیه را آنلاین ثبت کنید و با کد پیگیری، وضعیت رسیدگی را مشاهده نمایید.</p></div>
                    <div><a class="guild-profile-btn guild-profile-btn--light" href="{{ route('complaints.create', ['union' => $union->id]) }}">ثبت شکایت جدید</a><a class="guild-profile-btn guild-profile-btn--outline" href="{{ route('complaints.track') }}">پیگیری شکایت</a></div>
                </section>
            @endif

            @if($showMessages)
                <section class="guild-profile-section" id="guild-messages" data-guild-profile-section>
                    <header class="guild-profile-section__head"><div><span>پیام‌های رسمی</span><h2>پیام‌های تبریک و تسلیت</h2></div></header>
                    <div class="guild-profile-list">
                        @foreach($unionMessages as $message)<a href="{{ route('congratulation_messages.show', $message->slug) }}"><span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 5h16v14H4zM7 9h10M7 13h7"/></svg></span><div><h3>{{ $message->title }}</h3><p>{{ $message->summary ?: $plain($message->body, 150) }}</p></div></a>@endforeach
                    </div>
                </section>
            @endif

            @if($showMinutes)
                <section class="guild-profile-section" id="guild-minutes" data-guild-profile-section>
                    <header class="guild-profile-section__head"><div><span>گزارش جلسات</span><h2>صورت‌جلسه‌ها</h2></div></header>
                    <div class="guild-profile-documents">
                        @foreach($union->minutes as $minute)<article class="guild-profile-document"><span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 6h16v14H4zM8 3v6M16 3v6M4 10h16"/></svg></span><div><h3>{{ $minute->title }}</h3><p>{{ $minute->meeting_date ? jalali_date($minute->meeting_date) : 'بدون تاریخ' }}@if($minute->description) · {{ $plain($minute->description, 130) }}@endif</p></div>@if($minute->file)<a href="{{ $assetImage($minute->file, '') }}" target="_blank" rel="noopener noreferrer">دانلود</a>@endif</article>@endforeach
                    </div>
                </section>
            @endif

            @if($showEducation)
                <section class="guild-profile-section" id="guild-education" data-guild-profile-section>
                    <header class="guild-profile-section__head"><div><span>توانمندسازی اعضا</span><h2>آموزش‌های اتحادیه</h2></div></header>
                    <div class="guild-profile-education">
                        @foreach($union->educations as $education)
                            @php $educationUrl = $education->link ?: '#guild-education'; @endphp
                            <a href="{{ $educationUrl }}" @if(str_starts_with($educationUrl, 'http')) target="_blank" rel="noopener noreferrer" @endif><span><x-ui-icon :name="$education->icon ?: 'education'" /></span><div><h3>{{ $education->title }}</h3>@if($education->description)<p>{{ $plain($education->description, 150) }}</p>@endif</div></a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($showAnnouncements)
                <section class="guild-profile-section" id="guild-announcements" data-guild-profile-section>
                    <header class="guild-profile-section__head"><div><span>اطلاع‌رسانی رسمی</span><h2>اطلاعیه‌ها و بخشنامه‌ها</h2></div></header>
                    <div class="guild-profile-list">
                        @foreach($union->announcements->take(8) as $announcement)<a href="{{ route('announcements.show', $announcement->slug) }}"><span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 12h3l9-5v10l-9-5H4zM7 15l2 5"/></svg></span><div><h3>{{ $announcement->title }}</h3><p>{{ $plain($announcement->excerpt ?: $announcement->body, 150) }}</p></div></a>@endforeach
                    </div>
                </section>
            @endif

            @if($showGallerySection)
                <section class="guild-profile-section" id="guild-gallery" data-guild-profile-section>
                    <header class="guild-profile-section__head"><div><span>رسانه‌های اتحادیه</span><h2>گالری تصاویر و ویدئوها</h2></div></header>
                    <div class="guild-profile-gallery">
                        @if($showGalleries)
                            @foreach($union->galleries->take(6) as $gallery)<a href="{{ route('galleries.show', $gallery->slug) }}"><img src="{{ $gallery->cover_image_url }}" alt="{{ $gallery->title }}" loading="lazy" decoding="async"><span>{{ $gallery->title }}</span></a>@endforeach
                        @endif
                        @if($showVideos)
                            @foreach($union->videos->take(6) as $video)<a href="{{ route('videos.show', $video->slug) }}" class="is-video"><img src="{{ $assetImage($video->cover_image) }}" alt="{{ $video->title }}" loading="lazy" decoding="async"><span>{{ $video->title }}</span></a>@endforeach
                        @endif
                    </div>
                </section>
            @endif

            @if($showContact)
                <section class="guild-profile-section" id="guild-contact" data-guild-profile-section>
                    <header class="guild-profile-section__head"><div><span>راه‌های ارتباطی</span><h2>تماس با اتحادیه</h2><p>اطلاعات تماس فقط در صورت ثبت و تأیید توسط مدیریت اتحادیه نمایش داده می‌شود.</p></div></header>
                    @if($hasContactData)
                        <div class="guild-profile-contact-grid">
                            @if($union->address)<div class="guild-profile-contact-card"><span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 21s7-6 7-12A7 7 0 1 0 5 9c0 6 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg></span><div><strong>نشانی اتحادیه</strong><p>{{ $union->address }}</p></div></div>@endif
                            @if(filled($unionPhoneHref))<a class="guild-profile-contact-card" href="tel:{{ $unionPhoneHref }}"><span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M7 3 4 6c0 6 8 14 14 14l3-3-4-4-3 2c-2-1-4-3-5-5l2-3-4-4Z"/></svg></span><div><strong>شماره تماس</strong><p>{{ fa_number($unionPhone) }}</p></div></a>@endif
                            @if($union->email)<a class="guild-profile-contact-card" href="mailto:{{ $union->email }}"><span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 5h18v14H3zM4 7l8 6 8-6"/></svg></span><div><strong>پست الکترونیکی</strong><p>{{ $union->email }}</p></div></a>@endif
                            @if($union->working_hours)<div class="guild-profile-contact-card"><span><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg></span><div><strong>ساعات کاری</strong><p>{{ $union->working_hours }}</p></div></div>@endif
                            @if($union->website)<a class="guild-profile-contact-card" href="{{ $union->website }}" target="_blank" rel="noopener noreferrer"><span><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3 4 6 4 9s-1 6-4 9c-3-3-4-6-4-9s1-6 4-9Z"/></svg></span><div><strong>وب‌سایت اتحادیه</strong><p>مشاهده وب‌سایت رسمی</p></div></a>@endif
                        </div>
                        @if($union->isSectionEnabled('show_social_links', true) && $socialLinks->isNotEmpty())
                            <div class="guild-profile-socials">
                                @foreach($socialLinks as $name => $url)<a class="guild-profile-social guild-profile-social--{{ $name }}" href="{{ $url }}" target="_blank" rel="noopener noreferrer"><x-social-icon :name="$name" /><span>{{ ['instagram'=>'اینستاگرام','telegram'=>'تلگرام','whatsapp'=>'واتساپ','eitaa'=>'ایتا','bale'=>'بله','rubika'=>'روبیکا','website'=>'وب‌سایت'][$name] ?? $name }}</span></a>@endforeach
                            </div>
                        @endif
                    @endif
                </section>
            @endif
        </div>

        <aside class="guild-profile-sidebar">
            @if($navItems->isNotEmpty())
                <nav class="guild-profile-side-card" aria-label="راهنمای سریع صفحه">
                    <h2>راهنمای سریع صفحه</h2>
                    <ul>@foreach($navItems as $item)<li><a href="#{{ $item['id'] }}" data-guild-profile-nav><span>{{ $item['label'] }}</span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg></a></li>@endforeach</ul>
                </nav>
            @endif

            <div class="guild-profile-side-card guild-profile-side-card--status">
                <h2>وضعیت اطلاعات اتحادیه</h2>
                <div class="guild-profile-status"><i aria-hidden="true"></i><span><strong>صفحه فعال است</strong><small>{{ $hasContactData ? 'اطلاعات ارتباطی ثبت شده است' : 'اطلاعات تکمیلی در حال بروزرسانی' }}</small></span></div>
            </div>

            <div class="guild-profile-side-card guild-profile-side-card--office">
                <h2>ارتباط با اتاق اصناف</h2>
                <div class="guild-profile-side-contact">
                    @if(filled($officePhoneHref))<a href="tel:{{ $officePhoneHref }}"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M7 3 4 6c0 6 8 14 14 14l3-3-4-4-3 2c-2-1-4-3-5-5l2-3-4-4Z"/></svg><span><strong>تلفن اتاق اصناف</strong><small>{{ fa_number($officePhone) }}</small></span></a>@endif
                    @if($officeEmail)<a href="mailto:{{ $officeEmail }}"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 5h18v14H3zM4 7l8 6 8-6"/></svg><span><strong>پست الکترونیکی</strong><small>{{ $officeEmail }}</small></span></a>@endif
                </div>
            </div>
        </aside>
    </div>
</main>
@endsection
