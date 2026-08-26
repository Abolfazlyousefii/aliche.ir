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
<nav class="breadcrumb-nav">
<a href="{{ route('home') }}">خانه</a>
<span class="breadcrumb-sep">/</span>
<a href="{{ route('posts.index') }}">اخبار</a>
<span class="breadcrumb-sep">/</span>
<span>{{ $post->title }}</span>
</nav>
</div>
</div>

<main class="single-post-page">
<div class="site-container single-post-layout">
<article class="single-post-article">
<div class="single-post-heading">
<h1 class="single-post-title">{{ $post->title }}</h1>
<div class="post-meta">
<span>تاریخ انتشار: {{ jalali_date($post->published_at) ?: jalali_date($post->created_at) }}</span>
<span class="dot"></span>
<span>بازدید: {{ fa_number($post->views_count) }}</span>
@if($post->type === 'video')
<span class="dot"></span>
<span>🎥 ویدیویی</span>
@elseif($post->galleries_count > 0)
<span class="dot"></span>
<span>🖼 دارای گالری</span>
@endif
</div>
</div>

<img alt="{{ $post->featuredMedia?->alt_text ?: $post->title }}" class="post-featured-img" src="{{ $post->featured_image_url }}" loading="eager" fetchpriority="high" decoding="async" @if($post->featuredMedia?->srcset) srcset="{{ $post->featuredMedia->srcset }}" sizes="(max-width: 768px) 100vw, 1200px" @endif @if($post->featuredMedia?->width && $post->featuredMedia?->height) width="{{ $post->featuredMedia->width }}" height="{{ $post->featuredMedia->height }}" @endif/>

<div class="single-post-body">
@if(trim(strip_tags($decodedShortDescription)) !== '')
<div class="post-excerpt">
{!! $decodedShortDescription !!}
</div>
@endif
<div class="post-content">
{!! $decodedBody ?: '<p>محتوایی برای این نوشته ثبت نشده است.</p>' !!}
</div>

@if($post->galleries->isNotEmpty() || $post->mediaGallery->isNotEmpty())
<div class="post-gallery" data-gallery-group="post-{{ $post->id }}">
<h3>گالری تصاویر</h3>
<div class="post-gallery-grid">
@foreach($post->mediaGallery as $media)
<div class="post-gallery-item" data-gallery-item="{{ $media->url }}"><img src="{{ $media->url }}" alt="{{ $media->alt_text ?: $post->title }}" loading="lazy" decoding="async" @if($media->srcset) srcset="{{ $media->srcset }}" sizes="(max-width: 768px) 100vw, 50vw" @endif @if($media->width && $media->height) width="{{ $media->width }}" height="{{ $media->height }}" @endif/></div>
@endforeach
@foreach($post->galleries as $image)
<div class="post-gallery-item" data-gallery-item="{{ $image->image_url }}"><img src="{{ $image->image_url }}" alt="{{ $image->caption ?? $post->title }}" loading="lazy" decoding="async" @if(image_srcset($image->image)) srcset="{{ image_srcset($image->image) }}" sizes="(max-width: 768px) 100vw, 50vw" @endif/></div>
@endforeach
</div>
</div>
@endif

@if(count($post->tags))
<section class="post-keywords-section" aria-labelledby="post-keywords-title">
<h3 id="post-keywords-title">برچسب‌ها</h3>
<div class="post-tags post-tags-dynamic">
@foreach($post->tags as $tag)
<a class="post-tag" href="{{ route('posts.index', ['search' => $tag]) }}">{{ $tag }}</a>
@endforeach
</div>
</section>
@endif

<div class="post-taxonomy" aria-label="مشخصات خبر">
@if($post->union)
<a class="post-taxonomy-chip" href="{{ route('posts.index', ['union_id' => $post->union_id]) }}">
<span>اتحادیه</span>
<strong>{{ $post->union->display_title }}</strong>
</a>
@endif
@if($post->category)
<a class="post-taxonomy-chip" href="{{ route('posts.index', ['category_id' => $post->category_id]) }}">
<span>دسته‌بندی</span>
<strong>{{ $post->category->title }}</strong>
</a>
@endif
<div class="post-taxonomy-chip">
<span>نوع محتوا</span>
<strong>{{ $post->type_label }}</strong>
</div>
</div>

<div class="post-nav">
@if($previousPost)
<a class="post-nav-link post-nav-prev" href="{{ route('posts.show', $previousPost->slug) }}">
<span>→ نوشته قبلی</span>
<strong>{{ $previousPost->title }}</strong>
</a>
@else
<span class="post-nav-link post-nav-prev"><span>→ نوشته قبلی</span><strong>وجود ندارد</strong></span>
@endif
@if($nextPost)
<a class="post-nav-link post-nav-next" href="{{ route('posts.show', $nextPost->slug) }}">
<span>نوشته بعدی ←</span>
<strong>{{ $nextPost->title }}</strong>
</a>
@else
<span class="post-nav-link post-nav-next"><span>نوشته بعدی ←</span><strong>وجود ندارد</strong></span>
@endif
</div>
</div>
</article>

<aside class="single-post-sidebar">
<div class="sidebar-card latest-news-card">
<div class="latest-news-card-header">
<h3>آخرین اخبار</h3>
@if($latestPosts->isNotEmpty())
<span>{{ fa_number($latestPosts->count()) }} خبر</span>
@endif
</div>
<div class="latest-news-scroll">
<ul class="sidebar-list latest-news-list">
@forelse($latestPosts as $latestPost)
<li>
<a href="{{ route('posts.show', $latestPost->slug) }}">
<span class="latest-news-title">{{ $latestPost->title }}</span>
<small>{{ jalali_date($latestPost->published_at) }}</small>
</a>
</li>
@empty
<li class="latest-news-empty">خبر دیگری برای نمایش وجود ندارد.</li>
@endforelse
</ul>
</div>
<a class="latest-news-all" href="{{ route('posts.index') }}">مشاهده همه اخبار <span>←</span></a>
</div>
</aside>
</div>
</main>
@endsection

@section('after_footer')
<div class="lightbox">
  <button class="lightbox-close" aria-label="بستن">✕</button>
  <button class="lightbox-nav lightbox-prev" aria-label="قبلی">‹</button>
  <button class="lightbox-nav lightbox-next" aria-label="بعدی">›</button>
  <img class="lightbox-img" src="" alt="تصویر بزرگ"/>
  <div class="lightbox-counter"></div>
</div>
@endsection
