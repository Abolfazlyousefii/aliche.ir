@extends('frontend.layouts.app')

@section('title', ($place->title ?? 'مکان گردشگری') . ' | گردشگری گرگان')
@section('meta_description', plain_text($place->short_description ?: ($place->description ?? ''), 160))
@section('frontend_variant', 'compact')
@section('footer_links_variant', 'short')

@section('content')
@php
    $title = $place->title ?? 'مکان گردشگری';
    $categoryTitle = $place->category?->title ?: ($place->tourism_type_label ?: 'گردشگری گرگان');
    $summary = plain_text($place->short_description ?: $place->description, 220);
    $featuredImage = $place->home_image_url;

    $galleryImages = collect($place->gallery_items ?? [])
        ->filter(fn ($image) => filled($image['url'] ?? null))
        ->unique('url')
        ->values();

    $latitude = filled($place->latitude) ? (string) $place->latitude : null;
    $longitude = filled($place->longitude) ? (string) $place->longitude : null;
    $coordinates = $latitude && $longitude ? $latitude.', '.$longitude : null;
    $mapUrl = filled($place->map_url) ? (string) $place->map_url : null;
    $coordinateMapUrl = $coordinates ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($coordinates) : null;
    $mapLink = $mapUrl ?: $coordinateMapUrl;
    $mapParts = $mapUrl ? parse_url($mapUrl) : [];
    $mapHost = strtolower((string) ($mapParts['host'] ?? ''));
    $mapPath = (string) ($mapParts['path'] ?? '');
    $isEmbeddableMap = (
        in_array($mapHost, ['google.com', 'www.google.com', 'maps.google.com'], true)
        && ($mapPath === '/maps/embed' || str_starts_with($mapPath, '/maps/embed/'))
    ) || (
        in_array($mapHost, ['openstreetmap.org', 'www.openstreetmap.org'], true)
        && $mapPath === '/export/embed.html'
    );
    $phoneHref = filled($place->phone) ? 'tel:'.preg_replace('/[^0-9+]/', '', (string) $place->phone) : null;

    $visitInfoCards = collect([
        ['key' => 'address', 'title' => 'آدرس', 'value' => $place->address ?: $place->location, 'link' => $mapLink, 'linkLabel' => $mapLink ? 'مشاهده روی نقشه' : null, 'icon' => 'pin'],
        ['key' => 'hours', 'title' => 'ساعت بازدید', 'value' => $place->working_hours, 'link' => null, 'linkLabel' => null, 'icon' => 'clock'],
        ['key' => 'price', 'title' => 'هزینه بازدید', 'value' => $place->visit_price, 'link' => null, 'linkLabel' => null, 'icon' => 'wallet'],
        ['key' => 'phone', 'title' => 'تلفن تماس', 'value' => $place->phone, 'link' => $phoneHref, 'linkLabel' => $phoneHref ? 'تماس مستقیم' : null, 'icon' => 'phone'],
        ['key' => 'coords', 'title' => 'مختصات', 'value' => $coordinates, 'link' => $coordinateMapUrl, 'linkLabel' => $coordinateMapUrl ? 'مسیریابی' : null, 'icon' => 'compass', 'dir' => $coordinates ? 'ltr' : 'rtl'],
    ])->filter(fn ($item) => filled($item['value']))->values();

    $publishedLabel = jalali_date($place->published_at) ?: jalali_date($place->created_at);
@endphp

<main class="tourism-detail-v2">
    <header class="tourism-detail-v2__hero">
        <div class="site-container tourism-detail-v2__hero-grid">
            <div class="tourism-detail-v2__hero-copy">
                <nav class="tourism-detail-v2__breadcrumb" aria-label="مسیر صفحه">
                    <a href="{{ route('home') }}">خانه</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('tourism.index') }}">گردشگری</a>
                    <span aria-hidden="true">/</span>
                    <span>{{ $title }}</span>
                </nav>

                <span class="tourism-detail-v2__eyebrow">{{ $categoryTitle }}</span>
                <h1>{{ $title }}</h1>
                <p>{{ $summary ?: 'توضیح کوتاهی برای این مکان گردشگری ثبت نشده است.' }}</p>

                <div class="tourism-detail-v2__hero-meta">
                    @if($publishedLabel)
                        <span>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 5h12v14H6zM9 3v4M15 3v4M6 9h12"/></svg>
                            {{ $publishedLabel }}
                        </span>
                    @endif
                    @if($place->location || $place->address)
                        <span>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s6-5.2 6-11a6 6 0 1 0-12 0c0 5.8 6 11 6 11z"/><circle cx="12" cy="10" r="2"/></svg>
                            {{ $place->location ?: \Illuminate\Support\Str::limit($place->address, 58) }}
                        </span>
                    @endif
                </div>

                <div class="tourism-detail-v2__hero-actions">
                    @if($mapLink)
                        <a class="tourism-detail-v2__primary-action" href="{{ $mapLink }}" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s6-5.2 6-11a6 6 0 1 0-12 0c0 5.8 6 11 6 11z"/><circle cx="12" cy="10" r="2"/></svg>
                            مسیریابی
                        </a>
                    @endif
                    @if($phoneHref)
                        <a class="tourism-detail-v2__secondary-action" href="{{ $phoneHref }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4h3l1 4-2 1a14 14 0 0 0 6 6l1-2 4 1v3c0 1-1 2-2 2A15 15 0 0 1 5 6c0-1 1-2 2-2z"/></svg>
                            تماس
                        </a>
                    @endif
                </div>
            </div>

            <figure class="tourism-detail-v2__hero-media">
                <img src="{{ $featuredImage }}" alt="{{ $title }}" loading="eager" fetchpriority="high" decoding="async">
            </figure>
        </div>
    </header>

    <section class="site-container tourism-detail-v2__body">
        <div class="tourism-detail-v2__layout">
            <article class="tourism-detail-v2__main">
                <section class="tourism-detail-v2__card tourism-detail-v2__article">
                    <header class="tourism-detail-v2__section-heading">
                        <span>معرفی مقصد</span>
                        <h2>درباره {{ $title }}</h2>
                    </header>

                    <div class="tourism-detail-v2__rich-text">
                        {!! filled(plain_text($place->description)) ? app(\App\Services\RichTextSanitizer::class)->sanitize($place->description) : '<p>توضیحات کامل این مکان هنوز در پایگاه داده ثبت نشده است.</p>' !!}
                    </div>
                </section>

                @if($visitInfoCards->isNotEmpty() || $mapLink)
                    <section class="tourism-detail-v2__card tourism-detail-v2__visit" aria-labelledby="tourism-visit-heading">
                        <header class="tourism-detail-v2__section-heading tourism-detail-v2__section-heading--row">
                            <div>
                                <span>اطلاعات بازدید</span>
                                <h2 id="tourism-visit-heading">دسترسی و اطلاعات کاربردی</h2>
                            </div>
                            @if($mapLink)
                                <a href="{{ $mapLink }}" target="_blank" rel="noopener">باز کردن نقشه</a>
                            @endif
                        </header>

                        @if($visitInfoCards->isNotEmpty())
                            <div class="tourism-detail-v2__facts">
                                @foreach($visitInfoCards as $info)
                                    <div class="tourism-detail-v2__fact">
                                        <span class="tourism-detail-v2__fact-icon" aria-hidden="true">
                                            @switch($info['icon'])
                                                @case('pin')
                                                    <svg viewBox="0 0 24 24"><path d="M12 21s6-5.2 6-11a6 6 0 1 0-12 0c0 5.8 6 11 6 11z"/><circle cx="12" cy="10" r="2"/></svg>
                                                    @break
                                                @case('clock')
                                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="M12 8v5l3 2"/></svg>
                                                    @break
                                                @case('wallet')
                                                    <svg viewBox="0 0 24 24"><path d="M4 7h16v11H4zM7 7V5h10v2M16 12h4"/></svg>
                                                    @break
                                                @case('phone')
                                                    <svg viewBox="0 0 24 24"><path d="M7 4h3l1 4-2 1a14 14 0 0 0 6 6l1-2 4 1v3c0 1-1 2-2 2A15 15 0 0 1 5 6c0-1 1-2 2-2z"/></svg>
                                                    @break
                                                @default
                                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="M12 7v5l3 2"/></svg>
                                            @endswitch
                                        </span>
                                        <div>
                                            <small>{{ $info['title'] }}</small>
                                            <strong dir="{{ $info['dir'] ?? 'rtl' }}">{{ $info['value'] }}</strong>
                                            @if($info['link'])
                                                <a href="{{ $info['link'] }}" @if(! str_starts_with($info['link'], 'tel:')) target="_blank" rel="noopener" @endif>{{ $info['linkLabel'] }}</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($isEmbeddableMap)
                            <div class="tourism-detail-v2__map">
                                <iframe src="{{ $mapUrl }}" title="نقشه {{ $title }}" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        @endif
                    </section>
                @endif

                @if($galleryImages->isNotEmpty())
                    <section class="tourism-detail-v2__card tourism-detail-v2__gallery" aria-labelledby="tourism-gallery-heading">
                        <header class="tourism-detail-v2__section-heading">
                            <span>گالری تصاویر</span>
                            <h2 id="tourism-gallery-heading">تصاویر {{ $title }}</h2>
                        </header>

                        <div class="tourism-detail-v2__gallery-grid" data-gallery-group="tourism-place-{{ $place->id }}">
                            @foreach($galleryImages as $image)
                                <button
                                    type="button"
                                    class="tourism-detail-v2__gallery-item"
                                    data-gallery-item="{{ $image['url'] }}"
                                    aria-label="مشاهده تصویر {{ $image['caption'] ?? $title }}"
                                >
                                    <img src="{{ $image['url'] }}" alt="{{ $image['caption'] ?? $title }}" loading="lazy" decoding="async">
                                    <span>{{ $image['caption'] ?? $title }}</span>
                                </button>
                            @endforeach
                        </div>
                    </section>
                @endif
            </article>

            <aside class="tourism-detail-v2__sidebar">
                <section class="tourism-detail-v2__side-card">
                    <header>
                        <span>خلاصه مقصد</span>
                        <h2>اطلاعات سریع</h2>
                    </header>

                    <dl class="tourism-detail-v2__quick-facts">
                        <div>
                            <dt>دسته‌بندی</dt>
                            <dd>{{ $categoryTitle }}</dd>
                        </div>
                        @if(filled($place->location))
                            <div>
                                <dt>محدوده</dt>
                                <dd>{{ $place->location }}</dd>
                            </div>
                        @endif
                        @if($galleryImages->isNotEmpty())
                            <div>
                                <dt>تعداد تصاویر</dt>
                                <dd>{{ fa_number($galleryImages->count()) }}</dd>
                            </div>
                        @endif
                    </dl>

                    <a class="tourism-detail-v2__archive-link" href="{{ route('tourism.index') }}">
                        <span>بازگشت به گردشگری</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
                    </a>
                </section>

                @if($relatedPlaces->isNotEmpty())
                    <section class="tourism-detail-v2__side-card tourism-detail-v2__related">
                        <header class="tourism-detail-v2__related-heading">
                            <div>
                                <span>پیشنهادهای نزدیک</span>
                                <h2>مکان‌های مرتبط</h2>
                            </div>
                            <a href="{{ route('tourism.index') }}">همه</a>
                        </header>

                        <div class="tourism-detail-v2__related-list">
                            @foreach($relatedPlaces as $related)
                                @php($relatedImage = $related->directory_image_url)
                                <a href="{{ route('tourism.show', $related->slug) }}" class="tourism-detail-v2__related-item">
                                    <span class="tourism-detail-v2__related-media {{ $relatedImage ? 'has-image' : 'is-fallback' }}">
                                        @if($relatedImage)
                                            <img src="{{ $relatedImage }}" alt="" loading="lazy" decoding="async">
                                        @else
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 18l5-6 4 4 3-3 4 5M4 5h16v14H4z"/></svg>
                                        @endif
                                    </span>
                                    <span class="tourism-detail-v2__related-copy">
                                        <small>{{ $related->category?->title ?: $related->tourism_type_label ?: 'گردشگری' }}</small>
                                        <strong>{{ $related->title }}</strong>
                                    </span>
                                    <svg class="tourism-detail-v2__related-arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </section>
</main>
@endsection

@section('after_footer')
<div class="lightbox" aria-hidden="true">
    <button class="lightbox-close" aria-label="بستن">✕</button>
    <button class="lightbox-nav lightbox-prev" aria-label="قبلی">‹</button>
    <button class="lightbox-nav lightbox-next" aria-label="بعدی">›</button>
    <img class="lightbox-img" src="" alt="تصویر بزرگ"/>
    <div class="lightbox-counter"></div>
</div>
@endsection
