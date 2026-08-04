@extends('frontend.layouts.app')

@section('title', $message->title.' | پیام تبریک')
@section('meta_description', plain_text($message->body, 160))

@section('content')
<section class="page-header page-header-alt">
    <div class="site-container">
        <h1>{{ $message->title }}</h1>
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">خانه</a>
            @if($message->union)
                <a href="{{ route('guilds.show', $message->union->slug) }}">{{ $message->union->display_title }}</a>
            @endif
            <span>پیام تبریک</span>
        </nav>
    </div>
</section>

<section class="site-container congratulation-single-section">
    <div class="news-single-layout congratulation-single-layout">
        <article class="news-single-main">
            <div class="news-single-body">
                <div class="post-meta">
                    <span>{{ $message->union?->display_title ?: 'پیام عمومی' }}</span>
                    <span>{{ jalali_date($message->published_at) }}</span>
                </div>
                <h2>{{ $message->title }}</h2>
                <div>{!! $message->body ?: '<p>متن پیام به‌زودی تکمیل می‌شود.</p>' !!}</div>
            </div>
        </article>
        <aside class="news-sidebar congratulation-sidebar">
            <div class="news-sidebar-card congratulation-manager-card">
                @if($message->manager_image)
                    <img src="{{ Storage::url($message->manager_image) }}" alt="{{ $message->manager_name ?: $message->title }}" class="congratulation-manager-image mb-3">
                @endif
                <div class="congratulation-manager-info">
                    <h4>{{ $message->manager_name ?: 'مدیر اصناف' }}</h4>
                    <p>{{ $message->manager_position ?: 'مدیر' }}</p>
                </div>
                @if($message->union)
                    <a class="tab-pill congratulation-union-link" href="{{ route('guilds.show', $message->union->slug) }}">صفحه اتحادیه</a>
                @endif
            </div>
        </aside>
    </div>
</section>
@endsection
