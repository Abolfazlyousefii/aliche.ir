@extends('frontend.layouts.app')

@section('title', ($page->meta_title ?: $page->title).' | اتاق اصناف مرکز استان گلستان')
@section('meta_description', $page->meta_description ?: plain_text($page->excerpt ?: $page->body, 160))

@section('content')
<div class="page-header">
<div class="site-container">
<nav class="breadcrumb-nav">
<a href="{{ route('home') }}">خانه</a>
<span class="breadcrumb-sep">/</span>
<span>{{ $page->title ?? 'صفحه' }}</span>
</nav>
<h1>{{ $page->title ?? 'صفحه' }}</h1>
</div>
</div>

<main class="blank-page">
<div class="site-container blank-page-content">
@if($page->featured_image)
<img class="post-featured-img" src="{{ image_url($page->featured_image) }}" alt="{{ $page->title }}" loading="eager" fetchpriority="high" decoding="async" @if(image_srcset($page->featured_image)) srcset="{{ image_srcset($page->featured_image) }}" sizes="100vw" @endif>
@endif
@if($page->excerpt)
<div class="post-excerpt">{!! $page->excerpt !!}</div>
@endif
{!! $page->body ?: '<p>محتوایی برای این صفحه ثبت نشده است.</p>' !!}
</div>
</main>
@endsection
