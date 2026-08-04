@extends('frontend.layouts.app')

@section('title', ($union->display_title ?? 'اتحادیه صنفی').' | اتاق اصناف مرکز استان گلستان')
@section('meta_description', $union->meta_description ?? $union->short_description ?? 'اطلاعات اتحادیه صنفی، اعضا، کمیسیون‌ها، اخبار، اطلاعیه‌ها و راه‌های تماس')

@php
    $defaultImage = asset('assets/img/asnaf-gorgan-default.jpg');
    $assetImage = function (?string $path) use ($defaultImage) {
        return image_url($path, 'assets/img/asnaf-gorgan-default.jpg') ?: $defaultImage;
    };
    $heroBgImage = $assetImage($union->cover_image ?: $union->logo);
    $heroBgStyle = $union->cover_image
        ? "background-image:url('{$heroBgImage}')"
        : "background-image:url('{$heroBgImage}');background-size:contain;background-repeat:no-repeat;background-position:center";
    $plain = fn ($value, $limit = 140) => plain_text($value, $limit);
    $initial = fn ($value) => mb_substr(trim((string) $value) ?: 'ا', 0, 1);
    $newsMode = $union->news_mode ?? 'auto';
    $posts = $newsMode === 'manual' ? $union->selectedPosts->where('type', 'news')->values() : ($newsMode === 'disabled' ? collect() : $union->posts->where('type', 'news')->values());
    $articles = $union->posts->where('type', 'article')->values();
    $sliderPosts = $posts->take(5);
    $socialLinks = collect($union->social_links ?? [])->filter(fn ($url) => filled($url));
    $presidentButtons = collect($union->active_president_buttons);
    $unionMessages = collect($unionMessages ?? []);
    $heroStats = [
        ['label' => 'اعضای فعال', 'value' => $union->members->count()],
        ['label' => 'کمیسیون‌ها', 'value' => $union->commissions->count()],
        ['label' => 'اخبار و اطلاعیه‌ها', 'value' => $posts->count() + $union->announcements->count()],
    ];
    $navItems = collect([
        ['key' => 'show_manager', 'default' => true, 'id' => 'guild-manager', 'label' => 'رئیس اتحادیه', 'visible' => filled($union->manager_name)],
        ['key' => 'show_board_members', 'default' => true, 'id' => 'guild-board', 'label' => 'هیئت مدیره', 'visible' => $union->members->isNotEmpty()],
        ['key' => 'show_commissions', 'default' => true, 'id' => 'guild-commissions', 'label' => 'کمیسیون‌ها', 'visible' => $union->commissions->isNotEmpty()],
        ['key' => 'show_rules', 'default' => true, 'id' => 'guild-rules', 'label' => 'قوانین', 'visible' => $union->rules->isNotEmpty()],
        ['key' => 'show_news_slider', 'default' => true, 'id' => 'guild-news-slider', 'label' => 'اسلایدر خبری', 'visible' => $sliderPosts->isNotEmpty()],
        ['key' => 'show_news', 'default' => true, 'id' => 'guild-news', 'label' => 'اخبار', 'visible' => $posts->isNotEmpty()],
        ['key' => 'show_articles', 'default' => true, 'id' => 'guild-articles', 'label' => 'مقاله‌ها', 'visible' => $articles->isNotEmpty()],
        ['key' => 'show_prices', 'default' => false, 'id' => 'guild-prices', 'label' => 'نرخ‌نامه', 'visible' => $union->prices->isNotEmpty()],
        ['key' => 'show_complaint', 'default' => true, 'id' => 'guild-complaint', 'label' => 'ثبت شکایت', 'visible' => true],
        ['key' => 'show_minutes', 'default' => true, 'id' => 'guild-minutes', 'label' => 'صورتجلسه‌ها', 'visible' => $union->minutes->isNotEmpty()],
        ['key' => 'show_education', 'default' => true, 'id' => 'guild-education', 'label' => 'آموزش‌ها', 'visible' => $union->educations->isNotEmpty()],
        ['key' => 'show_announcements', 'default' => true, 'id' => 'guild-announcements', 'label' => 'اطلاعیه‌ها', 'visible' => $union->announcements->isNotEmpty()],
        ['key' => 'show_gallery', 'default' => true, 'id' => 'guild-gallery', 'label' => 'گالری', 'visible' => $union->galleries->isNotEmpty() || $union->videos->isNotEmpty()],
        ['key' => 'show_congratulation_messages', 'default' => true, 'id' => 'guild-messages', 'label' => 'پیام‌ها', 'visible' => $unionMessages->isNotEmpty()],
        ['key' => 'show_contact', 'default' => true, 'id' => 'guild-contact', 'label' => 'تماس', 'visible' => true],
    ])->filter(fn ($item) => $union->isSectionEnabled($item['key'], $item['default']) && $item['visible']);
@endphp

@section('content')
<main class="guild-page">
    <section class="guild-hero">
        <div class="guild-hero-bg" style="{{ $heroBgStyle }}"></div>
        <div class="site-container guild-hero-content">
            <div class="guild-hero-logo">
                @if ($union->logo)<img alt="{{ $union->display_title }}" src="{{ $assetImage($union->logo) }}">@else<span>{{ $initial($union->display_title) }}</span>@endif
            </div>
            <div class="guild-hero-text">
                <span>{{ $union->category?->title ?: $union->union_type_label }}</span>
                <h1>{{ $union->display_title }}</h1>
                <p>{{ plain_text($union->short_description) ?: $plain($union->description, 220) ?: 'اطلاعات این اتحادیه به‌زودی به‌روزرسانی می‌شود.' }}</p>
            </div>
            <div class="guild-hero-stats">
                @foreach ($heroStats as $stat)
                    <div><strong>{{ number_format($stat['value']) }}</strong><span>{{ $stat['label'] }}</span></div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="site-container guild-layout">
        <aside class="guild-side-nav">
            <strong>راهنمای سریع</strong>
            <ul>
                @foreach ($navItems as $item)
                    <li><a href="#{{ $item['id'] }}">{{ $item['label'] }}</a></li>
                @endforeach
            </ul>
        </aside>

        <div>
            @if ($union->isSectionEnabled('show_manager', true))
                <section class="guild-section guild-section-alt" id="guild-manager" style="padding-top:0">
                    <h3 class="guild-section-title">رئیس {{ $union->display_title }}</h3>
                    <div class="guild-head-card">
                        <div class="guild-head-avatar">@if($union->manager_image)<img alt="{{ $union->manager_name }}" src="{{ $assetImage($union->manager_image) }}">@else{{ $initial($union->manager_name ?: $union->display_title) }}@endif</div>
                        <div class="guild-head-info">
                            <strong>{{ $union->manager_name ?: 'نام رئیس اتحادیه ثبت نشده است' }}</strong>
                            <span>رئیس {{ $union->display_title }}</span>
                            <p>{{ $union->description ? $plain($union->description, 260) : 'توضیحات رئیس اتحادیه به‌زودی منتشر می‌شود.' }}</p>
                            <div class="guild-head-contact">
                                @forelse ($presidentButtons as $button)
                                    <a href="{{ $button['url'] }}" target="{{ $button['target'] ?? '_self' }}" @if(($button['target'] ?? '_self') === '_blank') rel="noopener" @endif>{{ $button['icon'] ?? '' }} {{ $button['title'] }}</a>
                                @empty
                                    @if ($union->phone)<a href="tel:{{ $union->phone }}">تماس با اتحادیه</a>@endif
                                    @if ($union->email)<a href="mailto:{{ $union->email }}">ارسال ایمیل</a>@endif
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            @if ($union->isSectionEnabled('show_board_members', true))
                <section class="guild-section guild-section-alt" id="guild-board">
                    <h3 class="guild-section-title">اعضای هیئت مدیره اتحادیه</h3>
                    <div class="guild-members-grid guild-members-slider">
                        @forelse($union->members as $member)
                            <div class="guild-member-card"><div class="member-avatar">{{ $initial($member->full_name) }}</div><strong>{{ $member->full_name }}</strong><small>{{ $member->position ?: $member->business_name ?: 'عضو اتحادیه' }}</small></div>
                        @empty
                            <div class="guild-info-card"><h4>عضوی برای نمایش ثبت نشده است.</h4></div>
                        @endforelse
                    </div>
                </section>
            @endif

            @if ($union->isSectionEnabled('show_commissions', true))
                <section class="guild-section guild-section-alt" id="guild-commissions">
                    <h3 class="guild-section-title">کمیسیون‌های اتحادیه</h3>
                    <div class="guild-commission-list">
                        @forelse($union->commissions as $commission)
                            <div class="guild-commission-item"><div class="com-num">{{ $loop->iteration }}</div><div><strong>{{ $commission->title }}</strong><small>{{ plain_text($commission->description, 120) ?: 'شرح کمیسیون ثبت نشده است.' }}</small>
                                @if($union->isSectionEnabled('show_commission_tasks', true) && $commission->tasks->isNotEmpty())<ul>@foreach($commission->tasks as $task)<li>{{ $task->title }}</li>@endforeach</ul>@endif
                            </div></div>
                        @empty
                            <div class="guild-info-card"><h4>کمیسیونی برای این اتحادیه ثبت نشده است.</h4></div>
                        @endforelse
                    </div>
                </section>
            @endif

            @if ($union->isSectionEnabled('show_rules', true))
                <section class="guild-section guild-section-alt" id="guild-rules"><h3 class="guild-section-title">قوانین و دستورالعمل‌ها</h3><div class="guild-2col"><div class="guild-rules-list">
                    @forelse($union->rules as $rule)<div class="guild-rule-item"><div class="rule-icon">{{ $rule->icon ?: '📋' }}</div><div><strong>{{ $rule->title }}</strong><small>{{ plain_text($rule->description, 120) ?: 'توضیحات تکمیلی ثبت نشده است.' }}</small>@if($rule->file)<a href="{{ $assetImage($rule->file) }}" target="_blank" rel="noopener">دانلود فایل</a>@endif</div></div>@empty<div class="guild-info-card"><h4>قانونی برای نمایش ثبت نشده است.</h4></div>@endforelse
                </div></div></section>
            @endif

            @if($union->isSectionEnabled('show_news_slider', true) && $sliderPosts->isNotEmpty())
                <section class="guild-section" id="guild-news-slider"><h3 class="guild-section-title">اسلایدر خبری اتحادیه</h3><div class="guild-news-slider">@foreach($sliderPosts as $post)<a class="guild-news-slide" href="{{ route('posts.show', $post->slug) }}"><img alt="{{ $post->title }}" src="{{ $post->featured_image_url }}"><strong>{{ $post->title }}</strong><span>{{ jalali_date($post->published_at) ?: '—' }}</span></a>@endforeach</div></section>
            @endif

            @if($union->isSectionEnabled('show_news', true))
                <section class="guild-section" id="guild-news"><h3 class="guild-section-title">آخرین اخبار اتحادیه</h3><div class="guild-article-list">@forelse($posts as $post)<article><a href="{{ route('posts.show', $post->slug) }}"><img alt="{{ $post->title }}" src="{{ $post->featured_image_url }}"><div><strong>{{ $post->title }}</strong><p>{{ $post->summary }}</p></div></a></article>@empty<div class="guild-info-card"><h4>خبری برای این اتحادیه ثبت نشده است.</h4></div>@endforelse</div></section>
            @endif

            @if($union->isSectionEnabled('show_articles', true))
                <section class="guild-section guild-section-alt" id="guild-articles"><h3 class="guild-section-title">مقاله‌ها و مطالب آموزشی</h3><div class="guild-article-list">@forelse($articles as $article)<article><a href="{{ route('posts.show', $article->slug) }}"><img alt="{{ $article->title }}" src="{{ $article->featured_image_url }}"><div><strong>{{ $article->title }}</strong><p>{{ $article->summary }}</p></div></a></article>@empty<div class="guild-info-card"><h4>مقاله‌ای برای این اتحادیه ثبت نشده است.</h4></div>@endforelse</div></section>
            @endif

            @if($union->isSectionEnabled('show_prices', false))
                <section class="guild-section guild-section-alt" id="guild-prices"><h3 class="guild-section-title">نرخ‌نامه اختصاصی اتحادیه</h3>@if(($union->price_list_mode ?? 'table') === 'image') @if($union->price_list_image)<img src="{{ $assetImage($union->price_list_image) }}" alt="نرخنامه {{ $union->display_title }}" style="width:100%;border-radius:18px">@else<div class="guild-info-card"><h4>عکس نرخنامه ثبت نشده است.</h4></div>@endif @else <div class="price-table"><table><thead><tr><th>عنوان</th><th>نوع</th><th>قیمت</th><th>تاریخ بروزرسانی</th></tr></thead><tbody>@forelse($union->prices as $price)<tr><td>{{ $price->title }}</td><td>{{ $price->type ?: 'عمومی' }}</td><td>{{ $price->price ? number_format((float) $price->price).' '.$price->currency : 'اعلام نشده' }}</td><td>{{ $price->updated_on ? jalali_date($price->updated_on) : '—' }}</td></tr>@empty<tr><td colspan="4">نرخی برای نمایش ثبت نشده است.</td></tr>@endforelse</tbody></table></div>@endif</section>
            @endif

            @if($union->isSectionEnabled('show_complaint', true))
                <section class="guild-section guild-section-alt" id="guild-complaint"><h3 class="guild-section-title">ثبت شکایت صنفی</h3><div class="guild-2col"><div class="guild-info-card"><h4>نحوه ثبت شکایت</h4><p>شهروندان می‌توانند شکایات خود را در خصوص این اتحادیه به صورت آنلاین ثبت و پیگیری نمایند.</p></div><div class="guild-complaint-cta"><strong>ثبت شکایت آنلاین</strong><a class="tab-pill active" href="{{ route('complaints.create', ['union' => $union->id]) }}">ثبت شکایت جدید</a><a class="tab-pill" href="{{ route('complaints.track') }}">پیگیری شکایت قبلی</a></div></div></section>
            @endif

            @if($union->isSectionEnabled('show_congratulation_messages', true) && $unionMessages->isNotEmpty())
                <section class="guild-section guild-section-alt" id="guild-messages"><h3 class="guild-section-title">پیام‌های تبریک و تسلیت</h3><div class="guild-announce-list">@foreach($unionMessages as $message)<a href="{{ route('congratulation_messages.show', $message->slug) }}"><strong>{{ $message->title }}</strong><span>{{ $message->summary ?: $plain($message->body) }}</span></a>@endforeach</div></section>
            @endif

            @if($union->isSectionEnabled('show_minutes', true))
                <section class="guild-section" id="guild-minutes"><h3 class="guild-section-title">صورتجلسه‌ها</h3><div class="guild-minutes-list">@forelse($union->minutes as $minute)<div class="guild-info-card"><h4>{{ $minute->title }}</h4><p>{{ plain_text($minute->description, 140) ?: 'شرح صورتجلسه ثبت نشده است.' }}</p><span>{{ $minute->meeting_date ? jalali_date($minute->meeting_date) : 'بدون تاریخ' }}</span>@if($minute->file)<a href="{{ $assetImage($minute->file) }}" target="_blank" rel="noopener">دانلود صورتجلسه</a>@endif</div>@empty<div class="guild-info-card"><h4>صورتجلسه‌ای برای نمایش ثبت نشده است.</h4></div>@endforelse</div></section>
            @endif

            @if($union->isSectionEnabled('show_education', true))
                <section class="guild-section guild-section-alt" id="guild-education"><h3 class="guild-section-title">آموزش‌های اتحادیه</h3>@forelse($union->educations as $education)<a class="guild-edu-item" href="{{ $education->link ?: route('guilds.show', $union->slug) }}"><span>{{ $education->icon ?: '📚' }}</span><div><strong>{{ $education->title }}</strong><p>{{ plain_text($education->description, 140) ?: 'توضیحات آموزشی ثبت نشده است.' }}</p></div></a>@empty<div class="guild-info-card"><h4>آموزشی برای این اتحادیه ثبت نشده است.</h4></div>@endforelse</section>
            @endif

            @if($union->isSectionEnabled('show_announcements', true))
                <section class="guild-section" id="guild-announcements"><h3 class="guild-section-title">اطلاعیه‌ها و بخشنامه‌ها</h3><div class="guild-announce-list">@forelse($union->announcements as $announcement)<a href="{{ route('announcements.show', $announcement->slug) }}"><strong>{{ $announcement->title }}</strong><span>{{ plain_text($announcement->excerpt ?: $announcement->body, 140) }}</span></a>@empty<div class="guild-info-card"><h4>اطلاعیه‌ای برای این اتحادیه ثبت نشده است.</h4></div>@endforelse</div></section>
            @endif

            @if($union->isSectionEnabled('show_gallery', true) || $union->isSectionEnabled('show_videos', true))
                <section class="guild-section guild-section-alt" id="guild-gallery"><h3 class="guild-section-title">گالری تصاویر و ویدیوها</h3><div class="guild-gallery-grid">@if($union->isSectionEnabled('show_gallery', true))@foreach($union->galleries as $gallery)<a class="guild-gallery-item" href="{{ route('galleries.show', $gallery->slug) }}"><img alt="{{ $gallery->title }}" src="{{ $gallery->cover_image_url }}"><span>{{ $gallery->title }}</span></a>@endforeach @endif @if($union->isSectionEnabled('show_videos', true))@foreach($union->videos as $video)<a class="guild-gallery-item video" href="{{ route('videos.show', $video->slug) }}"><img alt="{{ $video->title }}" src="{{ $assetImage($video->cover_image) }}"><span>{{ $video->title }}</span></a>@endforeach @endif @if(($union->isSectionEnabled('show_gallery', true) && $union->galleries->isEmpty()) && ($union->isSectionEnabled('show_videos', true) && $union->videos->isEmpty()))<div class="guild-info-card"><h4>گالری یا ویدیویی برای این اتحادیه ثبت نشده است.</h4></div>@endif</div></section>
            @endif


            @if($union->isSectionEnabled('show_contact', true))
                <section class="guild-section" id="guild-contact"><h3 class="guild-section-title">تماس با اتحادیه و شبکه‌های اجتماعی</h3><div class="guild-contact-grid"><div class="guild-contact-card"><div class="contact-icon">📍</div><strong>آدرس</strong><span>{{ $union->address ?: 'آدرس ثبت نشده است.' }}</span></div><div class="guild-contact-card"><div class="contact-icon">📞</div><strong>تلفن</strong><span>{{ $union->phone ?: $union->mobile ?: 'شماره تماس ثبت نشده است.' }}</span></div><div class="guild-contact-card"><div class="contact-icon">✉️</div><strong>ایمیل</strong><span>{{ $union->email ?: 'ایمیل ثبت نشده است.' }}</span></div><div class="guild-contact-card"><div class="contact-icon">🕘</div><strong>ساعات کاری</strong><span>{{ $union->working_hours ?: 'ساعات کاری ثبت نشده است.' }}</span></div></div>
                    @if($union->isSectionEnabled('show_social_links', true) && $socialLinks->isNotEmpty())<div class="guild-social">@foreach($socialLinks as $name => $url)@if($url)<a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $name }}">@switch($name)@case('instagram') 📷 @break @case('telegram') ✈️ @break @case('whatsapp') 💬 @break @case('eitaa') 📱 @break @case('bale') 🔵 @break @case('rubika') 🟣 @break @case('website') 🌐 @break @default 🔗 @endswitch</a>@endif @endforeach</div>@endif
                </section>
            @endif
        </div>
    </div>
</main>
@endsection
