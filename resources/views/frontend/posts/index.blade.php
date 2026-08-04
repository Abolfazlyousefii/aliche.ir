@extends('frontend.layouts.app')

@section('title', 'آرشیو نوشته‌ها | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'آرشیو کامل اخبار، اطلاعیه‌ها و نوشته‌های اتاق اصناف مرکز استان گلستان')

@section('content')
<div class="page-header">
<div class="site-container">
<nav class="breadcrumb-nav">
<a href="{{ route('home') }}">خانه</a>
<span class="breadcrumb-sep">/</span>
<span>آرشیو نوشته‌ها</span>
</nav>
<h1>آرشیو نوشته‌ها</h1>
</div>
</div>

<main class="archive-page">
<div class="site-container">
<div class="archive-header">
<h1>همه نوشته‌ها</h1>
</div>

@php
    $baseFilterQuery = array_filter([
        'search' => $search ?: null,
        'union_id' => $unionId ?: null,
        'date' => $date ?: null,
    ], fn ($value) => filled($value));
@endphp
<section class="archive-category-section" aria-labelledby="archive-category-title">
<div class="archive-category-head">
<div>
<span class="archive-category-kicker">مرتب‌سازی اخبار</span>
<h2 id="archive-category-title">دسته‌بندی‌های اخبار</h2>
</div>
<a class="archive-category-reset {{ $categoryId ? '' : 'active' }}" href="{{ route('posts.index', $baseFilterQuery) }}">همه اخبار</a>
</div>
<div class="archive-category-grid">
@forelse($categories as $category)
@php
    $categoryQuery = array_merge($baseFilterQuery, ['category_id' => $category->id]);
@endphp
<a class="archive-category-chip {{ (string) $categoryId === (string) $category->id ? 'active' : '' }}" href="{{ route('posts.index', $categoryQuery) }}">
<span>{{ $category->icon ?: 'خبر' }}</span>
<strong>{{ $category->title }}</strong>
<small>{{ number_format($category->published_posts_count) }} نوشته</small>
</a>
@empty
<div class="archive-category-empty">هنوز دسته‌بندی فعالی برای اخبار ثبت نشده است.</div>
@endforelse
</div>
</section>

<div class="archive-layout"><div class="archive-main"><div class="archive-grid">
@forelse($posts as $post)
<article class="archive-card">
<a href="{{ route('posts.show', $post->slug) }}">
<img alt="{{ $post->title }}" class="archive-card-img" src="{{ $post->featured_image_url }}" loading="lazy"/>
<div class="archive-card-body">
@if($post->type === 'video')<span class="card-cat">🎥 ویدیو</span>@elseif($post->galleries_count > 0)<span class="card-cat">🖼 گالری</span>@endif
<h2>{{ $post->title }}</h2>
<p>{{ plain_text($post->excerpt ?: $post->short_description ?: $post->body, 120) }}</p>
<span class="card-date">{{ jalali_date($post->published_at) ?: jalali_date($post->created_at) }}</span>
</div>
</a>
</article>
@empty
<p class="text-muted">هیچ پست فعالی برای نمایش وجود ندارد.</p>
@endforelse
</div>
{{ $posts->links('frontend.partials.pagination') }}
</div>
<aside class="archive-sidebar">
<div class="sidebar-card">
<h3>جستجو در نوشته‌ها</h3>
<form action="{{ route('posts.index') }}" method="GET">
<input class="search-input" name="search" type="search" value="{{ $search }}" placeholder="جستجوی خبر یا نوشته...">
@if($categoryId)<input type="hidden" name="category_id" value="{{ $categoryId }}">@endif
@if($unionId)<input type="hidden" name="union_id" value="{{ $unionId }}">@endif
@if($date)<input type="hidden" name="date" value="{{ $date }}">@endif
<button class="tab-pill active" type="submit">جستجو</button>
</form>
</div>
<div class="sidebar-card">
<h3>اتحادیه‌ها</h3>
<ul class="sidebar-list">
@forelse ($unions->take(8) as $union)
<li><a href="{{ route('posts.index', ['union_id' => $union->id]) }}">{{ $union->display_title }}</a></li>
@empty
<li>اتحادیه‌ای برای فیلتر وجود ندارد.</li>
@endforelse
</ul>
</div>
</aside>
</div>
</div>
</main>
@endsection
