@extends('frontend.layouts.app')
@section('title', 'کمیسیون‌ها | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'معرفی کمیسیون‌های اتاق اصناف مرکز استان گلستان، وظایف، اعضا و جلسات مرتبط')
@section('frontend_variant', 'compact')
@section('footer_links_variant', 'short')

@section('content')
<section class="page-header page-header-alt commissions-hero">
    <div class="site-container">
        <span class="commissions-eyebrow">کمیسیون‌های تخصصی</span>
        <h1>کمیسیون‌ها</h1>
        <p>مسیر دسترسی به اطلاعات کمیسیون‌ها، وظایف، اعضا و جلسات منتشرشده اتاق اصناف.</p>
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">خانه</a>
            <span>کمیسیون‌ها</span>
        </nav>
    </div>
</section>

<section class="site-container commissions-page">
    <div class="commissions-intro-card">
        <div>
            <span class="commissions-eyebrow">نمای کلی</span>
            <h2>کمیسیون‌های اتاق اصناف</h2>
            <p>هر کارت شامل شرح کوتاه و تعداد جلسات منتشرشده است. برای مشاهده جزئیات، روی کارت موردنظر کلیک کنید.</p>
        </div>
        <div class="commissions-count-box">
            <strong>{{ $commissions->total() }}</strong>
            <span>کمیسیون فعال</span>
        </div>
    </div>

    <div class="commissions-list-grid">
        @forelse($commissions as $index => $commission)
            <a class="commission-list-card" href="{{ route('commissions.show', $commission->slug) }}">
                <span class="commission-list-number">{{ str_pad((string) ($commissions->firstItem() + $index), 2, '0', STR_PAD_LEFT) }}</span>
                <div class="commission-list-content">
                    <h3>{{ $commission->title }}</h3>
                    <p>{{ plain_text($commission->description, 150) ?: 'اطلاعات این کمیسیون به‌زودی تکمیل می‌شود.' }}</p>
                    <div class="commission-list-meta">
                        <span>{{ $commission->sessions_count }} جلسه منتشرشده</span>
                        <span>مشاهده جزئیات ←</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="commissions-empty-state">
                <strong>کمیسیونی برای نمایش وجود ندارد.</strong>
                <p>پس از انتشار کمیسیون‌ها، اطلاعات آن‌ها در این بخش نمایش داده می‌شود.</p>
            </div>
        @endforelse
    </div>

    <div class="commissions-pagination">
        {{ $commissions->links() }}
    </div>
</section>
@endsection
