@extends('frontend.layouts.app')

@section('title', 'اخبار اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'آخرین اخبار، گزارش‌ها و رویدادهای مرتبط با اصناف، اتحادیه‌ها و بازار استان گلستان را دنبال کنید.')
@section('canonical', $posts->url($posts->currentPage()))

@section('content')
@php
    $preservedFilters = array_filter([
        'search' => $search ?: null,
        'category_id' => $categoryId ?: null,
        'union_id' => $unionId ?: null,
        'date' => $date ?: null,
    ], fn ($value) => filled($value));
    $resultCount = fa_number($posts->total());
@endphp

<div class="page-header news-archive-hero">
    <div class="site-container">
        <nav class="breadcrumb-nav" aria-label="مسیر صفحه">
            <a href="{{ route('home') }}">خانه</a>
            <span class="breadcrumb-sep" aria-hidden="true">/</span>
            <span aria-current="page">اخبار</span>
        </nav>
        <h1>اخبار اتاق اصناف</h1>
        <p>آخرین خبرها، گزارش‌ها و رویدادهای مرتبط با اصناف و بازار استان گلستان</p>
    </div>
</div>

<main class="news-archive-page" data-news-archive>
    <div class="site-container">
        <section class="news-archive-toolbar" aria-labelledby="news-archive-heading">
            <div class="news-archive-toolbar-heading">
                <h2 id="news-archive-heading">آخرین اخبار</h2>
                <p>{{ $resultCount }} {{ $hasActiveFilters ? 'خبر یافت شد' : 'خبر منتشرشده' }}</p>
            </div>

            <form class="news-archive-search" action="{{ route('posts.index') }}" method="GET" role="search">
                <label class="visually-hidden" for="newsArchiveSearch">جستجو در اخبار</label>
                <input id="newsArchiveSearch" name="search" value="{{ $search }}" type="search" placeholder="عنوان یا عبارت موردنظر را جستجو کنید">
                @if($categoryId)<input type="hidden" name="category_id" value="{{ $categoryId }}">@endif
                @if($unionId)<input type="hidden" name="union_id" value="{{ $unionId }}">@endif
                @if($date)<input type="hidden" name="date" value="{{ $date }}">@endif
                <button type="submit">جستجو</button>
            </form>

            @if($hasActiveFilters)
                <a class="news-archive-clear" href="{{ route('posts.index') }}">پاک‌کردن فیلترها</a>
            @endif
        </section>

        <button class="news-archive-filter-toggle" type="button" aria-expanded="false" aria-controls="newsArchiveMobileFilters" data-news-filter-toggle>
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
            <span>فیلتر اخبار</span>
        </button>

        <section class="news-archive-mobile-filters" id="newsArchiveMobileFilters" aria-label="فیلتر اخبار بر اساس اتحادیه" data-news-filter-panel>
            <form class="news-archive-union-form" action="{{ route('posts.index') }}" method="GET">
                <label for="newsArchiveMobileUnion">فیلتر بر اساس اتحادیه</label>
                <select id="newsArchiveMobileUnion" name="union_id">
                    <option value="">همه اتحادیه‌ها</option>
                    @foreach($unions as $union)
                        <option value="{{ $union->id }}" @selected((string) $unionId === (string) $union->id)>{{ $union->display_title }}</option>
                    @endforeach
                </select>
                @if($search !== '')<input type="hidden" name="search" value="{{ $search }}">@endif
                @if($categoryId)<input type="hidden" name="category_id" value="{{ $categoryId }}">@endif
                @if($date)<input type="hidden" name="date" value="{{ $date }}">@endif
                <div class="news-archive-filter-actions">
                    <button type="submit">اعمال فیلتر</button>
                    @if($unionId)<a href="{{ route('posts.index', array_filter(['search' => $search ?: null])) }}">پاک‌کردن فیلتر</a>@endif
                </div>
            </form>
        </section>

        <div class="news-archive-layout">
            <section class="news-archive-main" aria-label="فهرست اخبار">
                @include('frontend.posts.partials.results')
            </section>

            <aside class="news-archive-sidebar" aria-label="ابزارهای آرشیو اخبار">
                <section class="news-archive-sidebar-card">
                    <h2>جستجو در اخبار</h2>
                    <form class="news-archive-sidebar-search" action="{{ route('posts.index') }}" method="GET" role="search">
                        <label for="newsArchiveSidebarSearch">عنوان یا عبارت خبر</label>
                        <input id="newsArchiveSidebarSearch" name="search" value="{{ $search }}" type="search" placeholder="جستجو در اخبار">
                        @if($unionId)<input type="hidden" name="union_id" value="{{ $unionId }}">@endif
                        <button type="submit">جستجو</button>
                    </form>
                </section>

                <section class="news-archive-sidebar-card">
                    <h2>فیلتر بر اساس اتحادیه</h2>
                    <form class="news-archive-union-form" action="{{ route('posts.index') }}" method="GET">
                        <label for="newsArchiveUnion">انتخاب اتحادیه</label>
                        <select id="newsArchiveUnion" name="union_id">
                            <option value="">همه اتحادیه‌ها</option>
                            @foreach($unions as $union)
                                <option value="{{ $union->id }}" @selected((string) $unionId === (string) $union->id)>{{ $union->display_title }} ({{ fa_number($union->published_news_count) }})</option>
                            @endforeach
                        </select>
                        @if($search !== '')<input type="hidden" name="search" value="{{ $search }}">@endif
                        <button type="submit">اعمال فیلتر</button>
                        @if($unionId)<a class="news-archive-sidebar-clear" href="{{ route('posts.index', array_filter(['search' => $search ?: null])) }}">همه اتحادیه‌ها</a>@endif
                    </form>
                </section>
            </aside>
        </div>
    </div>
</main>
@endsection
