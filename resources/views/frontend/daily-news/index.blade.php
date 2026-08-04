@extends('frontend.layouts.app')

@section('title', 'اخبار روز '.$selectedDateLabel.' | سایت اصناف')
@section('meta_description', 'همه اخبار منتشرشده سایت اصناف در روز '.$selectedDateLabel.' به‌ترتیب ساعت انتشار.')
@section('canonical', route('daily-news.index', ['date' => $selectedDateParam]))

@section('content')
<section class="daily-news-section daily-news-page site-container">
    <nav class="breadcrumb"><a href="{{ route('home') }}">خانه</a><span>اخبار روزانه</span></nav>
    <div class="daily-news-shell">
        <div class="daily-news-header">
            <div><span class="daily-news-kicker">تایم‌لاین خبری</span><h1>اخبار روزانه</h1><p>{{ $selectedDateLabel }} · اخبار امروز: {{ jalali_to_persian_digits((string) $dailyNewsCount) }}</p></div>
            <div class="daily-news-controls" aria-label="تغییر روز">
                <a href="{{ route('daily-news.index', ['date' => $previousDateParam]) }}">روز قبل</a>
                <a href="{{ route('daily-news.index') }}">امروز</a>
                <a class="{{ $isToday ? 'disabled' : '' }}" @if(!$isToday) href="{{ route('daily-news.index', ['date' => $nextDateParam]) }}" @endif>روز بعد</a>
            </div>
        </div>
        @include('frontend.partials.daily-news-list', ['posts' => $dailyPosts, 'previousDateParam' => $previousDateParam])
        {{ $dailyPosts->links('frontend.partials.pagination') }}
    </div>
</section>
@endsection
