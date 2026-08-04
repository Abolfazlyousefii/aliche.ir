@extends('frontend.layouts.app')

@section('title', 'خدمات الکترونیک | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'لیست خدمات الکترونیک صنفی اتاق اصناف مرکز استان گلستان')
@section('frontend_variant', 'compact')
@section('footer_links_variant', 'short')

@section('content')
<section class="page-header page-header-alt portal-hero service-hero">
  <div class="site-container">
    <span class="portal-eyebrow">خدمات آنلاین</span>
    <h1>خدمات الکترونیک</h1>
    <p>راهنماها، فرآیندها و خدمات آنلاین مرتبط با امور صنفی را در یک صفحه مرتب دنبال کنید.</p>
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">خانه</a>
      <span>خدمات الکترونیک</span>
    </nav>
  </div>
</section>

<section class="site-container portal-page">
  <div class="portal-intro-card service-intro-card">
    <div>
      <span class="portal-eyebrow">فهرست خدمات</span>
      <h2>خدمات الکترونیک صنفی</h2>
      <p>خدمت موردنظر را از طریق جستجو یا دسته‌بندی پیدا کنید و جزئیات، راهنما و لینک ورود را مشاهده کنید.</p>
    </div>
    <div class="portal-count-box service-count-box">
      <strong>{{ $services->total() }}</strong>
      <span>خدمت فعال</span>
    </div>
  </div>

  <div class="portal-filter-panel">
    <form class="portal-search-form" action="{{ route('electronic-services.index') }}" method="GET">
      @if ($activeCategory !== '')
        <input type="hidden" name="category" value="{{ $activeCategory }}">
      @endif
      <input class="form-control" name="search" value="{{ $search }}" placeholder="نام یا توضیح خدمت را جستجو کنید..." type="search">
      <button class="portal-primary-action" type="submit">جستجو</button>
      @if ($search !== '' || $activeCategory !== '')
        <a class="portal-secondary-action" href="{{ route('electronic-services.index') }}">نمایش همه</a>
      @endif
    </form>

    <div class="portal-tabs" aria-label="فیلتر دسته‌بندی خدمات">
      <a class="portal-tab {{ $activeCategory === '' ? 'active' : '' }}" href="{{ route('electronic-services.index', array_filter(['search' => $search])) }}">همه خدمات</a>
      @foreach ($categories as $category)
        <a class="portal-tab {{ $activeCategory === $category->slug || $activeCategory === (string) $category->id ? 'active' : '' }}" href="{{ route('electronic-services.index', array_filter(['category' => $category->slug, 'search' => $search])) }}">{{ $category->title }}</a>
      @endforeach
    </div>
  </div>

  <div class="portal-card-grid">
    @forelse ($services as $service)
      <article class="portal-card service-card-modern">
        <div class="portal-card-icon">{{ $service->icon ?: '⚡' }}</div>
        <div class="portal-card-body">
          <span class="portal-card-category">{{ $service->category?->title ?: 'خدمات الکترونیک' }}</span>
          <h3>{{ $service->title }}</h3>
          <p>{{ plain_text($service->short_description ?: $service->body, 130) ?: 'توضیحات این خدمت به‌زودی تکمیل می‌شود.' }}</p>
        </div>
        <div class="portal-card-actions">
          <a class="portal-secondary-action" href="{{ route('electronic-services.show', $service->slug) }}">مشاهده جزئیات</a>
          @if ($service->link_type !== 'none' && $service->link)
            <a class="portal-primary-action" href="{{ $service->link }}" target="{{ $service->target }}" @if($service->target === '_blank') rel="noopener" @endif>ورود به خدمت</a>
          @endif
        </div>
      </article>
    @empty
      <div class="portal-empty-state">
        <strong>خدمت الکترونیکی فعالی برای نمایش یافت نشد.</strong>
        <p>عبارت جستجو یا دسته‌بندی انتخاب‌شده را تغییر دهید.</p>
      </div>
    @endforelse
  </div>

  <div class="portal-pagination">{{ $services->links('frontend.partials.pagination') }}</div>
</section>
@endsection
