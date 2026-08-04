@extends('frontend.layouts.app')

@section('title', 'جستجو | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'جستجوی کلی در محتوای سایت اتاق اصناف مرکز استان گلستان')
@section('frontend_variant', 'compact')
@section('footer_links_variant', 'short')

@section('content')
<section class="page-header page-header-alt">
  <div class="site-container">
    <h1>جستجوی سایت</h1>
    <nav class="breadcrumb"><a href="{{ route('home') }}">خانه</a><span>جستجو</span></nav>
  </div>
</section>

<section class="site-container">
  <form class="archive-filters mb-4" action="{{ route('search') }}" method="GET" role="search">
    <input class="form-control" name="q" value="{{ $query }}" placeholder="عبارت مورد نظر را وارد کنید..." type="search" autofocus>
    <button class="tab-pill active" type="submit">جستجو</button>
  </form>

  @if ($query === '')
    <p class="text-muted text-center">برای جستجو، عبارت مورد نظر را وارد کنید.</p>
  @elseif (mb_strlen($query) < 2)
    <p class="text-muted text-center">عبارت جستجو باید حداقل ۲ کاراکتر باشد.</p>
  @else
    @php($total = collect($results)->sum(fn ($section) => count($section['items'])))
    @if ($total === 0)
      <p class="text-muted text-center">نتیجه‌ای برای «{{ $query }}» یافت نشد.</p>
    @else
      <div class="section-heading"><h2>نتایج برای «{{ $query }}»</h2><p>{{ $total }} نتیجه در بخش‌های مختلف سایت یافت شد.</p></div>
      @foreach ($results as $section)
        @continue(empty($section['items']))
        <div class="admin-panel-card mb-4">
          <h3>{{ $section['title'] }}</h3>
          <div class="row g-3 mt-2">
            @foreach ($section['items'] as $item)
              <div class="col-md-6">
                <a class="related-post-item" href="{{ $item['url'] }}">
                  <div class="related-post-thumb">
                    @if ($item['image'])<img src="{{ Storage::url($item['image']) }}" alt="{{ $item['title'] }}" loading="lazy">@else<span style="font-size:1.8rem">🔎</span>@endif
                  </div>
                  <div><strong>{{ $item['title'] }}</strong><span>{{ $item['type'] }}</span><p>{{ $item['summary'] ?: 'خلاصه‌ای برای این نتیجه ثبت نشده است.' }}</p></div>
                </a>
              </div>
            @endforeach
          </div>
        </div>
      @endforeach
    @endif
  @endif
</section>
@endsection
