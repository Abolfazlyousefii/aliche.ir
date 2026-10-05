@extends('frontend.layouts.app')

@section('title', $service->title.' | خدمات الکترونیک')
@section('meta_description', plain_text($service->short_description ?: $service->body, 160))
@section('frontend_variant', 'compact')
@section('footer_links_variant', 'short')

@section('content')
@php
    $serviceCategory = $service->category?->title ?: 'خدمات الکترونیک';
    $serviceDescription = plain_text($service->short_description ?: $service->body, 190) ?: 'اطلاعات این خدمت در حال تکمیل است.';
    $hasPublicLink = filled($service->public_link);
    $updatedAtLabel = jalali_datetime($service->updated_at) ?: 'ثبت نشده';
@endphp

<main class="service-detail-v2">
  <section class="service-detail-v2__hero">
    <div class="site-container service-detail-v2__hero-grid">
      <div class="service-detail-v2__hero-copy">
        <nav class="service-detail-v2__breadcrumb" aria-label="مسیر صفحه">
          <a href="{{ route('home') }}">خانه</a>
          <span aria-hidden="true">/</span>
          <a href="{{ route('electronic-services.index') }}">خدمات الکترونیک</a>
          <span aria-hidden="true">/</span>
          <span>{{ $service->title }}</span>
        </nav>

        <span class="service-detail-v2__eyebrow">خدمت الکترونیکی</span>
        <h1>{{ $service->title }}</h1>
        <p>{{ $serviceDescription }}</p>

        <div class="service-detail-v2__hero-actions">
          @if($hasPublicLink)
            <a
              class="service-detail-v2__primary-action"
              href="{{ $service->public_link }}"
              target="{{ $service->target }}"
              @if($service->target === '_blank') rel="noopener noreferrer" @endif
            >
              <span>ورود به سامانه {{ $service->title }}</span>
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
          @endif

          <span class="service-detail-v2__status {{ $hasPublicLink ? 'is-online' : 'is-guide' }}">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              @if($hasPublicLink)
                <path d="M4 12a8 8 0 0 1 16 0M7 12a5 5 0 0 1 10 0M10 12a2 2 0 0 1 4 0M12 17h.01"/>
              @else
                <path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/>
              @endif
            </svg>
            <span>{{ $hasPublicLink ? 'دسترسی آنلاین' : 'راهنمای اطلاعاتی' }}</span>
          </span>

          <span class="service-detail-v2__category-chip">{{ $serviceCategory }}</span>
        </div>
      </div>

      <div class="service-detail-v2__hero-visual" aria-hidden="true">
        <div class="service-detail-v2__visual-orb service-detail-v2__visual-orb--one"></div>
        <div class="service-detail-v2__visual-orb service-detail-v2__visual-orb--two"></div>
        <svg viewBox="0 0 360 260" role="presentation">
          <path d="M45 210h270" class="ground"/>
          <path d="M95 210v-58l85-52 85 52v58" class="building"/>
          <path d="M122 210v-40h38v40M199 210v-40h38v40" class="building-detail"/>
          <rect x="132" y="40" width="122" height="146" rx="18" class="paper"/>
          <path d="M157 78h70M157 104h58M157 130h46" class="paper-line"/>
          <circle cx="221" cy="151" r="24" class="seal"/>
          <path d="m209 173-7 31 19-10 19 10-7-31" class="ribbon"/>
        </svg>
      </div>
    </div>
  </section>

  <section class="site-container service-detail-v2__body">
    <div class="service-detail-v2__layout">
      <article class="service-detail-v2__main">
        <section class="service-detail-v2__content-card">
          <header class="service-detail-v2__section-heading">
            <span class="service-detail-v2__section-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M6 3h9l3 3v15H6zM15 3v4h4M9 11h6M9 15h6"/></svg>
            </span>
            <div>
              <span>راهنمای خدمت</span>
              <h2>معرفی و نحوه استفاده</h2>
            </div>
          </header>

          @if($service->short_description)
            <p class="service-detail-v2__lead">{{ plain_text($service->short_description) }}</p>
          @endif

          <div class="service-detail-v2__rich-text">
            {!! rich_text($service->body, '<p>توضیحات این خدمت هنوز تکمیل نشده است.</p>') !!}
          </div>

          @if($hasPublicLink)
            <div class="service-detail-v2__content-cta">
              <div>
                <strong>برای شروع آماده‌اید؟</strong>
                <span>از لینک رسمی ثبت‌شده برای این خدمت وارد سامانه شوید.</span>
              </div>
              <a
                href="{{ $service->public_link }}"
                target="{{ $service->target }}"
                @if($service->target === '_blank') rel="noopener noreferrer" @endif
              >
                ورود به خدمت
              </a>
            </div>
          @endif
        </section>
      </article>

      <aside class="service-detail-v2__sidebar">
        <section class="service-detail-v2__side-card">
          <header class="service-detail-v2__side-heading">
            <span aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 10v6M12 7h.01"/></svg></span>
            <h2>اطلاعات خدمت</h2>
          </header>

          <dl class="service-detail-v2__facts">
            <div>
              <dt>نوع دسترسی</dt>
              <dd>{{ $hasPublicLink ? 'ورود آنلاین' : 'راهنمای اطلاعاتی' }}</dd>
            </div>
            <div>
              <dt>دسته‌بندی</dt>
              <dd>{{ $serviceCategory }}</dd>
            </div>
            @if($hasPublicLink)
              <div>
                <dt>نحوه باز شدن</dt>
                <dd>{{ $service->target === '_blank' ? 'پنجره جدید' : 'همین صفحه' }}</dd>
              </div>
            @endif
            <div>
              <dt>آخرین بروزرسانی</dt>
              <dd>{{ $updatedAtLabel }}</dd>
            </div>
          </dl>

          <a class="service-detail-v2__secondary-action" href="{{ route('electronic-services.index') }}">
            <span>همه خدمات الکترونیک</span>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5l7 7-7 7"/></svg>
          </a>
        </section>

        <section class="service-detail-v2__side-card service-detail-v2__help-card">
          <header class="service-detail-v2__side-heading">
            <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12a7 7 0 0 1 14 0v5a2 2 0 0 1-2 2h-2v-6h4M5 13h4v6H7a2 2 0 0 1-2-2zM12 21h3"/></svg></span>
            <h2>نیاز به راهنمایی دارید؟</h2>
          </header>
          <p>اگر درباره این خدمت یا نحوه استفاده از سامانه سؤال دارید، از بخش تماس با اتاق اصناف پیام بفرستید.</p>
          <a href="{{ route('contact.create') }}">تماس و راهنمایی</a>
        </section>
      </aside>
    </div>

    @if($relatedServices->isNotEmpty())
      <section class="service-detail-v2__related">
        <header class="service-detail-v2__related-heading">
          <div>
            <span>خدمات پیشنهادی</span>
            <h2>خدمات مرتبط</h2>
          </div>
          <a href="{{ route('electronic-services.index') }}">مشاهده همه خدمات</a>
        </header>

        <div class="service-detail-v2__related-grid">
          @foreach($relatedServices as $related)
            <a class="service-detail-v2__related-card" href="{{ route('electronic-services.show', $related->slug) }}">
              <span class="service-detail-v2__related-icon" aria-hidden="true">{{ $related->icon ?: '⚡' }}</span>
              <div>
                <strong>{{ $related->title }}</strong>
                <small>{{ $related->category?->title ?: 'خدمات الکترونیک' }}</small>
              </div>
              <span class="service-detail-v2__related-arrow" aria-hidden="true">
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
