@extends('frontend.layouts.app')

@section('title', $announcement->title.' | اتاق اصناف مرکز استان گلستان')
@section('meta_description', plain_text($announcement->excerpt ?: $announcement->body, 160))

@section('content')
<div class="page-header">
    <div class="site-container">
        <nav class="breadcrumb-nav">
            <a href="{{ route('home') }}">خانه</a>
            <span class="breadcrumb-sep">/</span>
            <a href="{{ route('announcements.index') }}">آرشیو اطلاعیه‌ها</a>
            <span class="breadcrumb-sep">/</span>
            <span>{{ $announcement->title }}</span>
        </nav>
        <h1>{{ $announcement->title }}</h1>
    </div>
</div>

<main class="single-post-page">
    <div class="site-container single-post-layout">
        <article class="single-post-article">
            <img class="post-featured-img" src="{{ $announcement->featured_image ? image_url($announcement->featured_image) : asset('assets/img/asnaf-gorgan-default.jpg') }}" alt="{{ $announcement->title }}">

            <div class="single-post-body">
                <div class="post-meta">
                    <span>{{ $announcement->category?->title ?: 'اطلاعیه' }}</span>
                    <span>{{ jalali_date($announcement->published_at) }}</span>
                    @if ($announcement->union)<span>{{ $announcement->union->name }}</span>@endif
                    @if ($announcement->is_important)<span>اطلاعیه مهم</span>@endif
                    @if ($announcement->expires_at)<span>مهلت: {{ jalali_date($announcement->expires_at) }}</span>@endif
                </div>

                @if ($announcement->excerpt)
                    <div class="post-excerpt">{{ plain_text($announcement->excerpt) }}</div>
                @endif

                <div class="post-content">
                    {!! $announcement->body !!}
                </div>

                @if ($announcement->attachment)
                    <div class="post-tags">
                        <a class="post-tag" href="{{ image_url($announcement->attachment, '') }}" target="_blank">دانلود فایل پیوست</a>
                    </div>
                @endif
            </div>
        </article>

        <aside class="single-post-sidebar">
            <div class="sidebar-card">
                <h3>اطلاعات اطلاعیه</h3>
                <ul>
                    <li>دسته‌بندی: {{ $announcement->category?->title ?: 'بدون دسته‌بندی' }}</li>
                    <li>اتحادیه: {{ $announcement->union?->name ?: 'اطلاعیه عمومی' }}</li>
                    <li>شروع نمایش: {{ jalali_datetime($announcement->starts_at) ?: 'بدون محدودیت' }}</li>
                    <li>انقضا: {{ jalali_datetime($announcement->expires_at) ?: 'بدون انقضا' }}</li>
                </ul>
            </div>

            @if ($relatedAnnouncements->isNotEmpty())
                <div class="sidebar-card">
                    <h3>اطلاعیه‌های مرتبط</h3>
                    <ul>
                        @foreach ($relatedAnnouncements as $relatedAnnouncement)
                            <li><a href="{{ route('announcements.show', $relatedAnnouncement->slug) }}">{{ $relatedAnnouncement->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>
    </div>
</main>
@endsection
