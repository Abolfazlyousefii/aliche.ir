@extends('frontend.layouts.app')

@section('title', $post->title.' | اتاق اصناف مرکز استان گلستان')
@section('meta_description', plain_text($post->short_description ?? $post->description, 160))

@push('styles')
@php
    $postSingleStylesPath = public_path('assets/css/post-single.css');
    $postSingleStylesVersion = is_file($postSingleStylesPath) ? filemtime($postSingleStylesPath) : '1';
@endphp
<link href="{{ asset('assets/css/post-single.css') }}?v={{ $postSingleStylesVersion }}" rel="stylesheet"/>
@endpush

@section('content')
@php
    $decodedShortDescription = html_entity_decode((string) $post->short_description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $decodedBody = html_entity_decode((string) $post->body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
@endphp
<div class="page-header single-post-breadcrumb-bar">
    <div class="site-container">
        <nav class="breadcrumb-nav" aria-label="مسیر صفحه">
            <a href="{{ route('home') }}">خانه</a>
            <span class="breadcrumb-sep" aria-hidden="true">/</span>
            <a href="{{ route('posts.index') }}">اخبار</a>
            <span class="breadcrumb-sep" aria-hidden="true">/</span>
            <span aria-current="page">{{ $post->title }}</span>
        </nav>
    </div>
</div>

<main class="single-post-page">
    <div class="site-container single-post-layout">
        <article class="single-post-article">
            <header class="single-post-heading">
                <div class="post-heading-eyebrow" aria-label="مشخصات خبر">
                    @if($post->category)
                        <a class="post-heading-link" href="{{ route('posts.index', ['category_id' => $post->category_id]) }}">{{ $post->category->title }}</a>
                    @endif
                    @if($post->union)
                        <a class="post-heading-link post-heading-link-muted" href="{{ route('posts.index', ['union_id' => $post->union_id]) }}">{{ $post->union->display_title }}</a>
                    @endif
                    <span class="post-type-label">{{ $post->type_label }}</span>
                </div>

                <h1 class="single-post-title">{{ $post->title }}</h1>

                <div class="post-meta">
                    <span>تاریخ انتشار: {{ jalali_date($post->published_at) ?: jalali_date($post->created_at) }}</span>
                    <span class="dot" aria-hidden="true"></span>
                    <span>بازدید: {{ fa_number($post->views_count) }}</span>
                    @if($post->type === 'video')
                        <span class="dot" aria-hidden="true"></span>
                        <span class="post-status-label">محتوای ویدیویی</span>
                    @elseif($post->galleries_count > 0)
                        <span class="dot" aria-hidden="true"></span>
                        <span class="post-status-label">دارای گالری تصاویر</span>
                    @endif
                </div>
            </header>

            <div class="post-hero-media">
                <img alt="{{ $post->featuredMedia?->alt_text ?: $post->title }}" class="post-featured-img" src="{{ $post->featured_image_url }}" loading="eager" fetchpriority="high" decoding="async" @if($post->featuredMedia?->srcset) srcset="{{ $post->featuredMedia->srcset }}" sizes="(max-width: 768px) 100vw, 900px" @endif @if($post->featuredMedia?->width && $post->featuredMedia?->height) width="{{ $post->featuredMedia->width }}" height="{{ $post->featuredMedia->height }}" @endif/>
            </div>

            <div class="single-post-body">
                @if(trim(strip_tags($decodedShortDescription)) !== '')
                    <div class="post-excerpt post-lead">
                        {!! $decodedShortDescription !!}
                    </div>
                @endif

                <div class="post-content">
                    {!! $decodedBody ?: '<p>محتوایی برای این نوشته ثبت نشده است.</p>' !!}
                </div>

                @if($post->galleries->isNotEmpty() || $post->mediaGallery->isNotEmpty())
                    <section class="post-gallery" data-gallery-group="post-{{ $post->id }}" aria-labelledby="post-gallery-title">
                        <div class="post-section-heading">
                            <h2 id="post-gallery-title">گالری تصاویر</h2>
                        </div>
                        <div class="post-gallery-grid">
                            @foreach($post->mediaGallery as $media)
                                <button class="post-gallery-item" type="button" data-gallery-item="{{ $media->url }}" aria-label="مشاهده تصویر بزرگ">
                                    <img src="{{ $media->url }}" alt="{{ $media->alt_text ?: $post->title }}" loading="lazy" decoding="async" @if($media->srcset) srcset="{{ $media->srcset }}" sizes="(max-width: 768px) 100vw, 50vw" @endif @if($media->width && $media->height) width="{{ $media->width }}" height="{{ $media->height }}" @endif/>
                                </button>
                            @endforeach
                            @foreach($post->galleries as $image)
                                <button class="post-gallery-item" type="button" data-gallery-item="{{ $image->image_url }}" aria-label="مشاهده تصویر بزرگ">
                                    <img src="{{ $image->image_url }}" alt="{{ $image->caption ?? $post->title }}" loading="lazy" decoding="async" @if(image_srcset($image->image)) srcset="{{ image_srcset($image->image) }}" sizes="(max-width: 768px) 100vw, 50vw" @endif/>
                                </button>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if(count($post->tags))
                    <section class="post-keywords-section" aria-labelledby="post-keywords-title">
                        <h2 id="post-keywords-title">برچسب‌های این خبر</h2>
                        <div class="post-tags post-tags-dynamic">
                            @foreach($post->tags as $tag)
                                <a class="post-tag" href="{{ route('posts.index', ['search' => $tag]) }}">{{ $tag }}</a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <nav class="post-nav" aria-label="خبر قبلی و بعدی">
                    @if($previousPost)
                        <a class="post-nav-link post-nav-prev" href="{{ route('posts.show', $previousPost->slug) }}">
                            <span class="post-nav-label">→ خبر قبلی</span>
                            <strong>{{ $previousPost->title }}</strong>
                        </a>
                    @else
                        <span class="post-nav-link post-nav-prev is-disabled">
                            <span class="post-nav-label">→ خبر قبلی</span>
                            <strong>خبر قبلی وجود ندارد</strong>
                        </span>
                    @endif

                    @if($nextPost)
                        <a class="post-nav-link post-nav-next" href="{{ route('posts.show', $nextPost->slug) }}">
                            <span class="post-nav-label">خبر بعدی ←</span>
                            <strong>{{ $nextPost->title }}</strong>
                        </a>
                    @else
                        <span class="post-nav-link post-nav-next is-disabled">
                            <span class="post-nav-label">خبر بعدی ←</span>
                            <strong>خبر بعدی وجود ندارد</strong>
                        </span>
                    @endif
                </nav>
            </div>
        </article>

        <aside class="single-post-sidebar" aria-label="آخرین اخبار">
            <section class="sidebar-card latest-news-card">
                <div class="latest-news-card-header">
                    <div>
                        <span class="latest-news-kicker">تازه‌ترین مطالب</span>
                        <h2>آخرین اخبار</h2>
                    </div>
                    @if($latestPosts->isNotEmpty())
                        <span class="latest-news-count">{{ fa_number($latestPosts->count()) }}</span>
                    @endif
                </div>

                <div class="latest-news-scroll">
                    <ol class="sidebar-list latest-news-list">
                        @forelse($latestPosts as $latestPost)
                            <li>
                                <a href="{{ route('posts.show', $latestPost->slug) }}">
                                    <span class="latest-news-index" aria-hidden="true">{{ fa_number($loop->iteration) }}</span>
                                    <span class="latest-news-copy">
                                        <span class="latest-news-title">{{ $latestPost->title }}</span>
                                        <small>{{ jalali_date($latestPost->published_at) }}</small>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="latest-news-empty">خبر دیگری برای نمایش وجود ندارد.</li>
                        @endforelse
                    </ol>
                </div>

                <a class="latest-news-all" href="{{ route('posts.index') }}">
                    <span>مشاهده آرشیو اخبار</span>
                    <span aria-hidden="true">←</span>
                </a>
            </section>
        </aside>
    </div>
</main>
@endsection

@section('after_footer')
<div class="lightbox">
    <button class="lightbox-close" aria-label="بستن">✕</button>
    <button class="lightbox-nav lightbox-prev" aria-label="تصویر قبلی">‹</button>
    <button class="lightbox-nav lightbox-next" aria-label="تصویر بعدی">›</button>
    <img class="lightbox-img" src="" alt="تصویر بزرگ"/>
    <div class="lightbox-counter"></div>
</div>
@endsection
