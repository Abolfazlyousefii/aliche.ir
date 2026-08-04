@extends('frontend.layouts.app')

@section('title', 'سامانه‌ها | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'لیست سامانه‌های پرکاربرد صنفی، خدمات الکترونیک و درگاه‌های مرتبط با اتاق اصناف مرکز استان گلستان')
@section('frontend_variant', 'compact')
@section('footer_links_variant', 'short')

@section('content')
<section class="page-header page-header-alt portal-hero">
  <div class="site-container">
    <span class="portal-eyebrow">درگاه‌های آنلاین</span>
    <h1>سامانه‌ها</h1>
    <p>دسترسی سریع و مرتب به سامانه‌ها و درگاه‌های پرکاربرد مرتبط با امور صنفی.</p>
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">خانه</a>
      <span>سامانه‌ها</span>
    </nav>
  </div>
</section>

<section class="site-container portal-page">
  <div class="portal-intro-card">
    <div>
      <span class="portal-eyebrow">فهرست سامانه‌ها</span>
      <h2>سامانه‌های صنفی</h2>
      <p>با جستجو یا انتخاب دسته‌بندی، سامانه مورد نیاز خود را پیدا کنید و در صورت وجود لینک مستقیم وارد آن شوید.</p>
    </div>
    <div class="portal-count-box">
      <strong>{{ $systems->total() }}</strong>
      <span>سامانه فعال</span>
    </div>
  </div>

  <div class="portal-filter-panel">
    <form class="portal-search-form" action="{{ route('systems.index') }}" method="GET">
      @if ($activeCategory !== '')
        <input type="hidden" name="category" value="{{ $activeCategory }}">
      @endif
      <input class="form-control" name="search" value="{{ $search }}" placeholder="نام یا توضیح سامانه را جستجو کنید..." type="search">
      <button class="portal-primary-action" type="submit">جستجو</button>
      @if ($search !== '' || $activeCategory !== '')
        <a class="portal-secondary-action" href="{{ route('systems.index') }}">نمایش همه</a>
      @endif
    </form>

    <div class="portal-tabs" aria-label="فیلتر دسته‌بندی سامانه‌ها">
      <a class="portal-tab {{ $activeCategory === '' ? 'active' : '' }}" href="{{ route('systems.index', array_filter(['search' => $search])) }}">همه سامانه‌ها</a>
      @foreach ($categories as $category)
        <a class="portal-tab {{ $activeCategory === $category->slug || $activeCategory === (string) $category->id ? 'active' : '' }}" href="{{ route('systems.index', array_filter(['category' => $category->slug, 'search' => $search])) }}">{{ $category->title }}</a>
      @endforeach
    </div>
  </div>

  <div class="portal-card-grid">
    @forelse ($systems as $system)
      <article class="portal-card system-card-modern">
        <div class="portal-card-icon">{{ $system->icon ?: '💻' }}</div>
        <div class="portal-card-body">
          <span class="portal-card-category">{{ $system->category?->title ?: 'سامانه صنفی' }}</span>
          <h3>{{ $system->title }}</h3>
          <p>{{ plain_text($system->short_description ?: $system->description, 130) ?: 'توضیحات این سامانه به‌زودی تکمیل می‌شود.' }}</p>
        </div>
        <div class="portal-card-actions">
          <a class="portal-secondary-action" href="{{ route('systems.show', $system->slug) }}">جزئیات</a>
          @if ($system->link)
            <a class="portal-primary-action" href="{{ $system->link }}" target="{{ $system->target }}" @if($system->target === '_blank') rel="noopener" @endif>ورود به سامانه</a>
          @endif
        </div>
      </article>
    @empty
      <div class="portal-empty-state">
        <strong>سامانه فعالی برای نمایش یافت نشد.</strong>
        <p>عبارت جستجو یا دسته‌بندی انتخاب‌شده را تغییر دهید.</p>
      </div>
    @endforelse
  </div>

  <div class="portal-pagination">{{ $systems->links('frontend.partials.pagination') }}</div>
</section>
@endsection
