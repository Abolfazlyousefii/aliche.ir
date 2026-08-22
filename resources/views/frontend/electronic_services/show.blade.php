@extends('frontend.layouts.app')

@section('title', $service->title.' | خدمات الکترونیک')
@section('meta_description', plain_text($service->short_description ?: $service->body, 160))
@section('frontend_variant', 'compact')
@section('footer_links_variant', 'short')

@section('content')
<section class="page-header page-header-alt portal-hero portal-detail-hero service-hero">
  <div class="site-container">
    <span class="portal-eyebrow">جزئیات خدمت</span>
    <h1>{{ $service->title }}</h1>
    <p>{{ plain_text($service->short_description ?: $service->body, 180) ?: 'اطلاعات این خدمت در حال تکمیل است.' }}</p>
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">خانه</a>
      <a href="{{ route('electronic-services.index') }}">خدمات الکترونیک</a>
      <span>{{ $service->title }}</span>
    </nav>
  </div>
</section>

<section class="site-container portal-detail-page">
  <div class="portal-detail-layout">
    <article class="portal-detail-main">
      @if ($service->image)
        <figure class="portal-detail-cover">
          <img src="{{ image_url($service->image) }}" alt="{{ $service->title }}" loading="eager" fetchpriority="high" decoding="async">
        </figure>
      @endif

      <div class="portal-detail-card">
        <div class="portal-detail-heading">
          <div class="portal-detail-icon service-detail-icon">{{ $service->icon ?: '⚡' }}</div>
          <div>
            <span class="portal-card-category">{{ $service->category?->title ?: 'خدمات الکترونیک' }}</span>
            <h2>{{ $service->title }}</h2>
          </div>
        </div>
        @if ($service->short_description)
          <p class="portal-lead">{{ plain_text($service->short_description) }}</p>
        @endif
        <div class="portal-rich-text">{!! rich_text($service->body, '<p>توضیحات این خدمت هنوز تکمیل نشده است.</p>') !!}</div>
        @if ($service->link_type !== 'none' && $service->link)
          <a class="portal-primary-action portal-main-link" href="{{ $service->link }}" target="{{ $service->target }}" @if($service->target === '_blank') rel="noopener" @endif>ورود به خدمت</a>
        @endif
      </div>
    </article>

    <aside class="portal-detail-sidebar">
      <div class="portal-sidebar-card">
        <h3>اطلاعات سریع</h3>
        <div class="portal-stat-row"><span>دسته‌بندی</span><strong>{{ $service->category?->title ?: 'خدمات الکترونیک' }}</strong></div>
        <div class="portal-stat-row"><span>نوع دسترسی</span><strong>{{ $service->link_type !== 'none' && $service->link ? 'دارای لینک ورود' : 'راهنمای اطلاعاتی' }}</strong></div>
        <a class="portal-secondary-action portal-full-action" href="{{ route('electronic-services.index') }}">بازگشت به خدمات</a>
      </div>

      <div class="portal-sidebar-card">
        <h3>خدمات مرتبط</h3>
        <div class="portal-related-list">
          @forelse ($relatedServices as $related)
            <a href="{{ route('electronic-services.show', $related->slug) }}" class="portal-related-item">
              <span>{{ $related->icon ?: '⚡' }}</span>
              <div><strong>{{ $related->title }}</strong><small>{{ $related->category?->title ?: 'خدمات الکترونیک' }}</small></div>
            </a>
          @empty
            <p class="text-muted mb-0">خدمت مرتبطی برای نمایش وجود ندارد.</p>
          @endforelse
        </div>
      </div>
    </aside>
  </div>
</section>
@endsection
