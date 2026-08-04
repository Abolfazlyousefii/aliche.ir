@extends('frontend.layouts.app')

@section('title', $gallery->title.' | اتاق اصناف مرکز استان گلستان')
@section('meta_description', plain_text($gallery->description, 160))

@section('content')
<section class="page-header page-header-alt">
  <div class="site-container">
    <h1>{{ $gallery->title }}</h1>
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">خانه</a>
      <a href="{{ route('galleries.index') }}">گالری</a>
      <span>{{ $gallery->title }}</span>
    </nav>
  </div>
</section>

<section class="site-container">
  <div class="gallery-single-layout">

    <div class="gallery-single-main">
      <div class="gallery-desc">{{ plain_text($gallery->description) ?: 'توضیحی برای این گالری ثبت نشده است.' }}</div>

      <div class="gallery-thumbs" data-gallery-group="gallery-{{ $gallery->id }}">
        @forelse ($gallery->images as $image)
          <div class="gallery-thumb" data-gallery-item="{{ $image->image_url }}">
            <img src="{{ $image->image_url }}" alt="{{ $image->caption ?? $gallery->title }}" loading="lazy"/>
          </div>
        @empty
          <div class="gallery-thumb" data-gallery-item="{{ $gallery->cover_image_url }}">
            <img src="{{ $gallery->cover_image_url }}" alt="{{ $gallery->title }}" loading="lazy"/>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="gallery-sidebar">
      <div class="gallery-sidebar-card">
        <h4>سایر گالری‌ها</h4>
        <ul class="gallery-sidebar-list">
          @forelse ($relatedGalleries as $relatedGallery)
            <li><a href="{{ route('galleries.show', $relatedGallery->slug) }}">{{ $relatedGallery->title }}</a></li>
          @empty
            <li>گالری مرتبطی برای نمایش وجود ندارد.</li>
          @endforelse
        </ul>
      </div>
      <div class="gallery-sidebar-card">
        <h4>آمار گالری</h4>
        <ul class="gallery-sidebar-list">
          <li>تعداد تصاویر: {{ $gallery->images->count() }}</li>
          <li>تاریخ انتشار: {{ jalali_date($gallery->published_at) ?: '—' }}</li>
          <li>آخرین بروزرسانی: {{ jalali_date($gallery->updated_at) ?: '—' }}</li>
        </ul>
      </div>
    </aside>

  </div>
</section>
@endsection

@section('after_footer')
<div class="lightbox">
  <button class="lightbox-close" aria-label="بستن">✕</button>
  <button class="lightbox-nav lightbox-prev" aria-label="قبلی">‹</button>
  <button class="lightbox-nav lightbox-next" aria-label="بعدی">›</button>
  <img class="lightbox-img" src="" alt="تصویر بزرگ"/>
  <div class="lightbox-counter"></div>
</div>
@endsection
