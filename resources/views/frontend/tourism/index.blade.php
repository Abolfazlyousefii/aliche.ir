@extends('frontend.layouts.app')

@section('title', 'گردشگری گرگان | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'معرفی جاذبه‌های طبیعی، تاریخی، بازارها و خدمات گردشگری شهر گرگان به همراه اطلاعات بازدید و راه‌های دسترسی.')
@section('canonical', route('tourism.index'))

@section('content')
<main class="tourism-directory-page" data-tourism-directory>
  <section class="page-header page-header-alt page-header-tourism tourism-directory-header">
    <div class="site-container">
      <h1>گردشگری گرگان</h1>
      <p>راهنمای جاذبه‌های طبیعی، تاریخی، بازارها و خدمات گردشگری شهر گرگان</p>
      <nav class="breadcrumb" aria-label="مسیر راهنما">
        <a href="{{ route('home') }}">خانه</a>
        <span aria-current="page">گردشگری گرگان</span>
      </nav>
    </div>
  </section>

  <section class="tourism-directory-intro" aria-labelledby="tourism-intro-title">
    <div class="site-container tourism-directory-intro-grid">
      <div class="tourism-directory-intro-copy">
        <span class="tourism-directory-eyebrow">راهنمای شهر گرگان</span>
        <h2 id="tourism-intro-title">{{ $tourismSettings['tourism.intro_title'] ?? 'به شهر گرگان خوش آمدید' }}</h2>
        <p>{{ plain_text($tourismSettings['tourism.intro_text'] ?? 'گرگان با طبیعت هیرکانی، بافت تاریخی و بازارهای فعال، یکی از مقصدهای مهم گردشگری استان گلستان است.') }}</p>
        @if(filled($tourismSettings['tourism.intro_subtext'] ?? null))
          <p class="tourism-directory-intro-subtext">{{ plain_text($tourismSettings['tourism.intro_subtext']) }}</p>
        @endif
        <div class="tourism-directory-intro-actions">
          <a class="tourism-directory-primary-action" href="#tourism-attractions">مشاهده جاذبه‌ها</a>
          @if(($typeCounts['shopping'] ?? 0) > 0)
            <a class="tourism-directory-secondary-action" href="{{ route('tourism.index', ['type' => 'shopping']) }}#tourism-attractions">بازار و خرید</a>
          @endif
        </div>
        <div class="tourism-directory-stats" aria-label="آمار جاذبه‌های فعال">
          <div class="tourism-directory-stat"><strong>{{ $typeCounts['all'] }}</strong><span>جاذبه فعال</span></div>
          <div class="tourism-directory-stat"><strong>{{ $typeCounts['nature'] }}</strong><span>طبیعت‌گردی</span></div>
          <div class="tourism-directory-stat"><strong>{{ $typeCounts['historic'] }}</strong><span>تاریخی و فرهنگی</span></div>
          <div class="tourism-directory-stat"><strong>{{ $typeCounts['shopping'] }}</strong><span>بازار و خرید</span></div>
        </div>
      </div>
      <figure class="tourism-directory-intro-media">
        <img src="{{ $introImageUrl }}" alt="نمایی از جاذبه‌های گردشگری گرگان" loading="eager" fetchpriority="high" decoding="async">
      </figure>
    </div>
  </section>

  <section class="tourism-directory-attractions" id="tourism-attractions" aria-labelledby="tourism-results-title">
    <div class="site-container">
      <div class="tourism-directory-section-heading">
        <div><span>انتخاب مقصد</span><h2 id="tourism-results-title" tabindex="-1">جاذبه‌های گردشگری</h2></div>
        <p>جاذبه‌های فعال را بر اساس نوع مقصد مرور کنید.</p>
      </div>
      @include('frontend.tourism.partials.type-tabs')
      <p class="tourism-directory-status" data-tourism-status role="status" aria-live="polite"></p>
      @include('frontend.tourism.partials.results')
    </div>
  </section>

  @include('frontend.tourism.partials.gallery')

  <section class="tourism-directory-cta" aria-labelledby="tourism-cta-title">
    <div class="site-container">
      <div class="tourism-directory-cta-box">
        <div><h2 id="tourism-cta-title">اصناف و خدمات مرتبط با گردشگری</h2><p>معرفی اتحادیه‌ها و خدمات مورد نیاز مسافران و فعالان گردشگری</p></div>
        <a href="{{ route('guilds.index') }}">مشاهده اتحادیه‌های صنفی</a>
      </div>
    </div>
  </section>
</main>
@endsection
