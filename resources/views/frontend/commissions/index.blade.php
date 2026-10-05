@extends('frontend.layouts.app')

@section('title', 'کمیسیون‌ها | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'معرفی کمیسیون‌های اتاق اصناف مرکز استان گلستان، وظایف، اعضا و جلسات مرتبط')
@section('frontend_variant', 'compact')
@section('footer_links_variant', 'short')

@section('content')
<main class="commissions-directory-v2">
    <header class="commissions-directory-v2__header">
        <div class="site-container commissions-directory-v2__header-inner">
            <div>
                <nav class="commissions-directory-v2__breadcrumb" aria-label="مسیر صفحه">
                    <a href="{{ route('home') }}">خانه</a>
                    <span aria-hidden="true">/</span>
                    <span>کمیسیون‌ها</span>
                </nav>
                <span class="commissions-directory-v2__eyebrow">ساختارهای تخصصی اتاق اصناف</span>
                <h1>کمیسیون‌های تخصصی اتاق اصناف</h1>
                <p>معرفی کمیسیون‌ها، وظایف و جلسات منتشرشده اتاق اصناف مرکز استان گلستان.</p>
            </div>

            <div class="commissions-directory-v2__header-mark" aria-hidden="true">
                <svg viewBox="0 0 96 96">
                    <circle cx="48" cy="48" r="42"/>
                    <path d="M31 31h34v34H31zM38 40h20M38 48h20M38 56h13"/>
                </svg>
            </div>
        </div>
    </header>

    <section class="site-container commissions-directory-v2__main" aria-labelledby="commissions-directory-title">
        <div class="commissions-directory-v2__toolbar">
            <div class="commissions-directory-v2__heading">
                <span>فهرست کمیسیون‌ها</span>
                <h2 id="commissions-directory-title">کمیسیون‌های فعال</h2>
                <p>برای مشاهده معرفی، وظایف، اعضا و جلسات هر کمیسیون، کارت موردنظر را انتخاب کنید.</p>
            </div>

            <div class="commissions-directory-v2__count" aria-label="{{ $commissions->total() }} کمیسیون فعال">
                <strong>{{ fa_number($commissions->total()) }}</strong>
                <span>کمیسیون فعال</span>
            </div>
        </div>

        <div class="commissions-directory-v2__grid">
            @forelse($commissions as $commission)
                <a class="commission-directory-card" href="{{ route('commissions.show', $commission->slug) }}">
                    <div class="commission-directory-card__top">
                        <span class="commission-directory-card__media {{ $commission->image ? 'has-image' : 'is-fallback' }}">
                            @if($commission->image)
                                <img src="{{ $commission->image_url }}" alt="" loading="lazy" decoding="async">
                            @else
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/>
                                </svg>
                            @endif
                        </span>

                        <div class="commission-directory-card__identity">
                            <span class="commission-directory-card__type">کمیسیون تخصصی</span>
                            <h3>{{ $commission->title }}</h3>
                        </div>
                    </div>

                    <p class="commission-directory-card__description">
                        {{ plain_text($commission->description, 145) ?: 'اطلاعات این کمیسیون در حال تکمیل است.' }}
                    </p>

                    <div class="commission-directory-card__meta">
                        <span>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4h10v16H7zM10 8h4M10 12h4"/></svg>
                            <b>{{ fa_number($commission->tasks_count ?? 0) }}</b>
                            وظیفه
                        </span>
                        <span>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 5h12v14H6zM9 3v4M15 3v4M6 9h12"/></svg>
                            <b>{{ fa_number($commission->sessions_count ?? 0) }}</b>
                            جلسه منتشرشده
                        </span>
                    </div>

                    <div class="commission-directory-card__footer">
                        <span>مشاهده کمیسیون</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
                    </div>
                </a>
            @empty
                <div class="commissions-directory-v2__empty">
                    <span aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M5 4h14v16H5zM8 8h8M8 12h8"/></svg>
                    </span>
                    <strong>کمیسیونی برای نمایش وجود ندارد.</strong>
                    <p>پس از انتشار کمیسیون‌های فعال، اطلاعات آن‌ها در این صفحه نمایش داده می‌شود.</p>
                </div>
            @endforelse
        </div>

        @if($commissions->hasPages())
            <div class="commissions-directory-v2__pagination">
                {{ $commissions->links() }}
            </div>
        @endif
    </section>
</main>
@endsection
