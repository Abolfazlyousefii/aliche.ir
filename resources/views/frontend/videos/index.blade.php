@extends('frontend.layouts.app')

@section('title', 'آرشیو ویدئوها | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'آرشیو ویدئوهای آموزشی، خبری و گزارش‌های تصویری اتاق اصناف مرکز استان گلستان')
@section('footer_links_variant', 'short')

@push('styles')
    @php
        $videoArchiveCss = public_path('assets/css/video-archive.css');
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/video-archive.css') }}?v={{ is_file($videoArchiveCss) ? filemtime($videoArchiveCss) : '1' }}">
@endpush

@section('content')
<section class="page-header page-header-alt video-archive-hero">
    <div class="site-container">
        <h1>آرشیو ویدئوها</h1>
        <nav class="breadcrumb" aria-label="مسیر صفحه">
            <a href="{{ route('home') }}">خانه</a>
            <span aria-current="page">ویدئوها</span>
        </nav>
    </div>
</section>

<section class="site-container video-archive-page" aria-label="فهرست ویدئوها">
    <div class="video-archive-intro">
        <div class="video-archive-intro__copy">
            <span class="video-archive-eyebrow">رسانه تصویری اتاق اصناف</span>
            <h2>ویدئوهای منتشرشده</h2>
            <p>گزارش‌های تصویری، رویدادها، گفت‌وگوها و محتوای چندرسانه‌ای اتاق اصناف</p>
        </div>
        <span class="video-archive-count">{{ number_format($videos->total()) }} ویدئو</span>
    </div>

    <form class="video-archive-search" action="{{ route('videos.index') }}" method="GET" role="search">
        <label class="video-archive-search__label" for="videoArchiveSearch">جستجو در آرشیو</label>
        <div class="video-archive-search__control">
            <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="11" cy="11" r="7"></circle><path d="m16 16 5 5"></path>
            </svg>
            <input id="videoArchiveSearch" name="search" type="search" value="{{ $search }}" placeholder="عنوان یا موضوع ویدئو را جستجو کنید..." autocomplete="off">
        </div>
        <button class="video-archive-search__button" type="submit">جستجو</button>
        @if($search !== '')
            <a class="video-archive-search__clear" href="{{ route('videos.index') }}">حذف فیلتر</a>
        @endif
    </form>

    @if($search !== '')
        <p class="video-archive-results">نتایج جستجو برای «{{ $search }}»</p>
    @endif

    <div class="video-archive-grid">
        @forelse($videos as $video)
            <article class="video-archive-card {{ $loop->first && $videos->currentPage() === 1 ? 'video-archive-card--featured' : '' }}" data-video-archive-card>
                <a class="video-archive-card__link" href="{{ route('videos.show', $video->slug) }}"
                   aria-label="تماشای ویدئو: {{ $video->title }}">
                    <img class="video-archive-card__image" src="{{ $video->cover_image_url }}"
                         alt="{{ $video->title }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                         @if($loop->first) fetchpriority="high" @endif decoding="async">
                    <span class="video-archive-card__shade" aria-hidden="true"></span>
                    <span class="video-archive-card__play" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                            <path d="M8 5.8c0-.8.9-1.3 1.6-.9l9 6.2c.6.4.6 1.4 0 1.8l-9 6.2c-.7.4-1.6-.1-1.6-.9V5.8Z"></path>
                        </svg>
                    </span>
                    <div class="video-archive-card__content">
                        @if($video->published_at || $video->created_at)
                            <span class="video-archive-card__date">
                                {{ jalali_date($video->published_at ?: $video->created_at) }}
                            </span>
                        @endif
                        <h3 class="video-archive-card__title">{{ $video->title }}</h3>
                        @if($loop->first && $videos->currentPage() === 1)
                            @php($excerpt = plain_text($video->description, 120))
                            @if($excerpt)
                                <p class="video-archive-card__excerpt">{{ $excerpt }}</p>
                            @endif
                        @endif
                        <span class="video-archive-card__watch">مشاهده ویدئو <span aria-hidden="true">←</span></span>
                    </div>
                </a>
            </article>
        @empty
            <div class="video-archive-empty">
                <span aria-hidden="true">▶</span>
                <h3>{{ $search !== '' ? 'ویدئویی با این عبارت پیدا نشد' : 'هنوز ویدئویی منتشر نشده است' }}</h3>
                <p>{{ $search !== '' ? 'عبارت جستجو را تغییر دهید یا همه ویدئوها را ببینید.' : 'ویدئوهای جدید پس از انتشار در این صفحه نمایش داده می‌شوند.' }}</p>
                @if($search !== '')
                    <a href="{{ route('videos.index') }}">مشاهده همه ویدئوها</a>
                @endif
            </div>
        @endforelse
    </div>

    @if($videos->hasPages())
        <nav class="video-archive-pagination" aria-label="صفحه‌های آرشیو ویدئو">
            {{ $videos->onEachSide(1)->links('pagination::bootstrap-5') }}
        </nav>
    @endif
</section>
@endsection
