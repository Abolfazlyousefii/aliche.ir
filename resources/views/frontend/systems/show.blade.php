@extends('frontend.layouts.app')

@section('title', $system->title.' | سامانه‌ها')
@section('meta_description', plain_text($system->short_description ?: $system->description, 160))
@section('frontend_variant', 'compact')
@section('footer_links_variant', 'short')

@section('content')
@php
    $validCategory = $system->category
        && $system->category->is_active
        && $system->category->type === 'system'
            ? $system->category
            : null;

    $categoryTitle = $validCategory?->title ?: 'سامانه صنفی';
    $summary = plain_text($system->short_description ?: $system->description, 180)
        ?: 'اطلاعات این سامانه در حال تکمیل است.';
    $hasPublicLink = filled($system->public_link);
@endphp

<main class="system-detail-v2">
    <header class="system-detail-v2__hero">
        <div class="site-container system-detail-v2__hero-grid">
            <div class="system-detail-v2__hero-copy">
                <nav class="system-detail-v2__breadcrumb" aria-label="مسیر صفحه">
                    <a href="{{ route('home') }}">خانه</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('systems.index') }}">سامانه‌ها</a>
                    <span aria-hidden="true">/</span>
                    <span>{{ $system->title }}</span>
                </nav>

                <div class="system-detail-v2__identity">
                    <span class="system-detail-v2__icon" aria-hidden="true">
                        @include('frontend.systems.partials.icon', ['system' => $system])
                    </span>
                    <div>
                        <span class="system-detail-v2__eyebrow">{{ $categoryTitle }}</span>
                        <h1>{{ $system->title }}</h1>
                    </div>
                </div>

                <p>{{ $summary }}</p>

                <div class="system-detail-v2__hero-actions">
                    @if($hasPublicLink)
                        <a
                            class="system-detail-v2__primary-action"
                            href="{{ $system->public_link }}"
                            target="{{ $system->target }}"
                            @if($system->target === '_blank') rel="noopener noreferrer" @endif
                        >
                            <span>ورود به سامانه</span>
                            <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M8 5H5v10h10v-3M11 4h5v5M16 4l-7 7"/></svg>
                        </a>
                    @endif

                    <span class="system-detail-v2__access {{ $hasPublicLink ? 'is-online' : 'is-info' }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            @if($hasPublicLink)
                                <path d="M5 12a7 7 0 0 1 14 0M8 12a4 4 0 0 1 8 0M11 12a1 1 0 0 1 2 0M12 17h.01"/>
                            @else
                                <circle cx="12" cy="12" r="9"/><path d="M12 10v6M12 7h.01"/>
                            @endif
                        </svg>
                        <span>{{ $hasPublicLink ? 'دسترسی آنلاین' : 'اطلاعات سامانه' }}</span>
                    </span>
                </div>
            </div>

            <div class="system-detail-v2__visual">
                @if($system->image)
                    <figure>
                        <img src="{{ $system->image_url }}" alt="{{ $system->title }}" loading="eager" fetchpriority="high" decoding="async">
                    </figure>
                @else
                    <div class="system-detail-v2__visual-fallback" aria-hidden="true">
                        @include('frontend.systems.partials.icon', ['system' => $system])
                    </div>
                @endif
            </div>
        </div>
    </header>

    <section class="site-container system-detail-v2__main">
        <div class="system-detail-v2__layout">
            <article class="system-detail-v2__content">
                <section class="system-detail-v2__card">
                    <header class="system-detail-v2__section-heading">
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M6 3h9l3 3v15H6zM15 3v4h4M9 11h6M9 15h6"/></svg>
                        </span>
                        <div>
                            <small>راهنمای سامانه</small>
                            <h2>معرفی و نحوه استفاده</h2>
                        </div>
                    </header>

                    @if($system->short_description)
                        <p class="system-detail-v2__lead">{{ plain_text($system->short_description) }}</p>
                    @endif

                    <div class="system-detail-v2__rich-text">
                        {!! rich_text($system->description, '<p>توضیحات این سامانه هنوز تکمیل نشده است.</p>') !!}
                    </div>

                    @if($hasPublicLink)
                        <div class="system-detail-v2__content-action">
                            <div>
                                <strong>دسترسی به سامانه</strong>
                                <span>برای ادامه، از لینک رسمی ثبت‌شده وارد سامانه شوید.</span>
                            </div>
                            <a
                                href="{{ $system->public_link }}"
                                target="{{ $system->target }}"
                                @if($system->target === '_blank') rel="noopener noreferrer" @endif
                            >
                                ورود به سامانه
                            </a>
                        </div>
                    @else
                        <div class="system-detail-v2__info-note">
                            لینک ورود معتبر برای این سامانه ثبت نشده است؛ راهنمای موجود در همین صفحه قابل استفاده است.
                        </div>
                    @endif
                </section>
            </article>

            <aside class="system-detail-v2__sidebar">
                <section class="system-detail-v2__side-card">
                    <header>
                        <span aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 10v6M12 7h.01"/></svg></span>
                        <h2>اطلاعات سامانه</h2>
                    </header>

                    <dl>
                        <div>
                            <dt>دسته‌بندی</dt>
                            <dd>{{ $categoryTitle }}</dd>
                        </div>
                        <div>
                            <dt>نوع دسترسی</dt>
                            <dd>{{ $hasPublicLink ? 'ورود آنلاین' : 'اطلاعاتی' }}</dd>
                        </div>
                        @if($hasPublicLink)
                            <div>
                                <dt>نحوه باز شدن</dt>
                                <dd>{{ $system->target === '_blank' ? 'پنجره جدید' : 'همین صفحه' }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt>آخرین بروزرسانی</dt>
                            <dd>{{ jalali_datetime($system->updated_at) ?: '—' }}</dd>
                        </div>
                    </dl>

                    <a class="system-detail-v2__back-link" href="{{ route('systems.index') }}">
                        <span>بازگشت به فهرست سامانه‌ها</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5l7 7-7 7"/></svg>
                    </a>
                </section>
            </aside>
        </div>

        @if($relatedSystems->isNotEmpty())
            <section class="system-detail-v2__related" aria-labelledby="related-systems-title">
                <header class="system-detail-v2__related-heading">
                    <div>
                        <span>دسترسی سریع</span>
                        <h2 id="related-systems-title">سامانه‌های مرتبط</h2>
                    </div>
                    <a href="{{ route('systems.index') }}">مشاهده همه سامانه‌ها</a>
                </header>

                <div class="system-detail-v2__related-grid">
                    @foreach($relatedSystems as $related)
                        @php
                            $relatedCategory = $related->category
                                && $related->category->is_active
                                && $related->category->type === 'system'
                                    ? $related->category
                                    : null;
                        @endphp
                        <a class="system-detail-v2__related-card" href="{{ route('systems.show', $related->slug) }}">
                            <span class="system-detail-v2__related-icon" aria-hidden="true">
                                @include('frontend.systems.partials.icon', ['system' => $related])
                            </span>
                            <div>
                                <strong>{{ $related->title }}</strong>
                                <small>{{ $relatedCategory?->title ?: 'سامانه صنفی' }}</small>
                            </div>
                            <span class="system-detail-v2__related-arrow" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M15 5l-7 7 7 7"/></svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </section>
</main>
@endsection
