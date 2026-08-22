@extends('frontend.layouts.app')

@section('title', $system->title.' | سامانه‌ها')
@section('meta_description', plain_text($system->short_description ?: $system->description, 160))
@section('frontend_variant', 'compact')
@section('footer_links_variant', 'short')

@section('content')
<section class="page-header page-header-alt portal-hero portal-detail-hero">
  <div class="site-container">
    <span class="portal-eyebrow">جزئیات سامانه</span>
    <h1>{{ $system->title }}</h1>
    <p>{{ plain_text($system->short_description ?: $system->description, 180) ?: 'اطلاعات این سامانه در حال تکمیل است.' }}</p>
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">خانه</a>
      <a href="{{ route('systems.index') }}">سامانه‌ها</a>
      <span>{{ $system->title }}</span>
    </nav>
  </div>
</section>

<section class="site-container portal-detail-page">
  <div class="portal-detail-layout">
    <article class="portal-detail-main">
      @if ($system->image)
        <figure class="portal-detail-cover">
          <img src="{{ $system->image_url }}" alt="{{ $system->title }}" loading="eager" fetchpriority="high" decoding="async">
        </figure>
      @endif

      <div class="portal-detail-card">
        <div class="portal-detail-heading">
          <div class="portal-detail-icon">{{ $system->icon ?: '💻' }}</div>
          <div>
            <span class="portal-card-category">{{ $system->category?->title ?: 'سامانه' }}</span>
            <h2>{{ $system->title }}</h2>
          </div>
        </div>
        @if ($system->short_description)
          <p class="portal-lead">{{ plain_text($system->short_description) }}</p>
        @endif
        <div class="portal-rich-text">{!! rich_text($system->description, '<p>توضیحات این سامانه هنوز تکمیل نشده است.</p>') !!}</div>
        @if ($system->link)
          <a class="portal-primary-action portal-main-link" href="{{ $system->link }}" target="{{ $system->target }}" @if($system->target === '_blank') rel="noopener" @endif>ورود به سامانه</a>
        @endif
      </div>
    </article>

    <aside class="portal-detail-sidebar">
      <div class="portal-sidebar-card">
        <h3>اطلاعات سریع</h3>
        <div class="portal-stat-row"><span>دسته‌بندی</span><strong>{{ $system->category?->title ?: 'سامانه' }}</strong></div>
        <div class="portal-stat-row"><span>نوع دسترسی</span><strong>{{ $system->link ? 'دارای لینک ورود' : 'اطلاعاتی' }}</strong></div>
        <a class="portal-secondary-action portal-full-action" href="{{ route('systems.index') }}">بازگشت به سامانه‌ها</a>
      </div>

      <div class="portal-sidebar-card">
        <h3>سامانه‌های مرتبط</h3>
        <div class="portal-related-list">
          @forelse ($relatedSystems as $related)
            <a href="{{ route('systems.show', $related->slug) }}" class="portal-related-item">
              <span>{{ $related->icon ?: '💻' }}</span>
              <div><strong>{{ $related->title }}</strong><small>{{ $related->category?->title ?: 'سامانه' }}</small></div>
            </a>
          @empty
            <p class="text-muted mb-0">سامانه مرتبطی برای نمایش وجود ندارد.</p>
          @endforelse
        </div>
      </div>
    </aside>
  </div>
</section>
@endsection
