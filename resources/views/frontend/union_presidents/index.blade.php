@extends('frontend.layouts.app')

@section('title', 'رؤسای اتحادیه‌ها | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'فهرست رؤسای اتحادیه‌های صنفی و راه‌های ارتباط با هر اتحادیه')

@php
    $assetImage = fn (?string $path) => image_url($path, 'assets/img/asnaf-gorgan-default.jpg');
    $initial = fn ($value) => mb_substr(trim((string) $value) ?: 'ا', 0, 1);
@endphp

@section('content')
<div class="page-header">
    <div class="site-container">
        <nav class="breadcrumb-nav"><a href="{{ route('home') }}">خانه</a><span class="breadcrumb-sep">/</span><span>رؤسای اتحادیه‌ها</span></nav>
        <h1>رؤسای اتحادیه‌ها</h1>
    </div>
</div>

<main class="archive-page">
    <div class="site-container">
        <div class="archive-header"><h1>فهرست رؤسای اتحادیه‌های صنفی</h1></div>
        <div class="archive-grid">
            @forelse ($unions as $union)
                @php($socialLinks = collect($union->social_links ?? [])->filter(fn ($url) => filled($url)))
                <article class="guild-card">
                    <div class="guild-card-logo">
                        @if ($union->manager_image)
                            <img alt="{{ $union->manager_name }}" src="{{ $assetImage($union->manager_image) }}">
                        @else
                            <span>{{ $initial($union->manager_name ?: $union->display_title) }}</span>
                        @endif
                    </div>
                    <div class="guild-card-body">
                        <span class="guild-card-type">{{ $union->union_type_label }}</span>
                        <h2>{{ $union->manager_name }}</h2>
                        <p>{{ $union->display_title }}</p>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a class="tab-pill active" href="{{ route('guilds.show', $union->slug) }}">صفحه اتحادیه</a>
                            @if ($union->phone || $union->mobile)
                                <a class="tab-pill" href="tel:{{ $union->phone ?: $union->mobile }}">تماس</a>
                            @endif
                            @foreach ($socialLinks as $label => $url)
                                <a class="tab-pill" href="{{ $url }}" target="_blank" rel="noopener">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-center">رئیسی برای نمایش ثبت نشده است.</p>
            @endforelse
        </div>
    </div>
</main>
@endsection
