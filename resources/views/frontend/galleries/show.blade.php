@extends('frontend.layouts.app')

@section('title', $gallery->title.' | اتاق اصناف مرکز استان گلستان')
@section('meta_description', plain_text($gallery->description, 160))
@if(filled($gallery->meta_keywords))
@section('meta_keywords', $gallery->meta_keywords)
@endif

@section('content')
@include('frontend.partials.media-detail-heading', ['title' => $gallery->title, 'indexUrl' => route('galleries.index'), 'indexLabel' => 'گالری تصاویر'])

<section class="site-container">
  <div class="gallery-single-layout">

    <div class="gallery-single-main">
      <div class="gallery-desc">{{ plain_text($gallery->description) ?: 'توضیحی برای این گالری ثبت نشده است.' }}</div>

      <div class="gallery-thumbs" data-gallery-group="gallery-{{ $gallery->id }}">
        @forelse ($gallery->images as $image)
          <button class="gallery-thumb" type="button" data-gallery-item="{{ $image->image_url }}" aria-label="مشاهده تصویر {{ $loop->iteration }} از گالری {{ $gallery->title }}">
            <img src="{{ $image->image_url }}" alt="{{ $image->caption ?? $gallery->title }}" loading="lazy" decoding="async" @if(image_srcset($image->image)) srcset="{{ image_srcset($image->image) }}" sizes="(max-width: 768px) 100vw, 50vw" @endif/>
          </button>
        @empty
          <button class="gallery-thumb" type="button" data-gallery-item="{{ $gallery->cover_image_url }}" aria-label="مشاهده تصویر گالری {{ $gallery->title }}">
            <img src="{{ $gallery->cover_image_url }}" alt="{{ $gallery->title }}" loading="lazy" decoding="async" @if(image_srcset($gallery->display_cover_path)) srcset="{{ image_srcset($gallery->display_cover_path) }}" sizes="100vw" @endif/>
          </button>
        @endforelse
      </div>
      @if($gallery->tags !== [])
        <div class="media-detail-tags" aria-label="کلیدواژه‌های گالری تصاویر">
          <strong class="media-detail-tags__label">کلیدواژه‌ها:</strong>
          @foreach($gallery->tags as $keyword)
            <span class="post-tag">{{ $keyword }}</span>
          @endforeach
        </div>
      @endif
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
<div class="lightbox" role="dialog" aria-modal="true" aria-label="نمایش تصویر گالری" aria-hidden="true" tabindex="-1">
  <button class="lightbox-close" type="button" aria-label="بستن نمایش تصویر">✕</button>
  <button class="lightbox-nav lightbox-prev" type="button" aria-label="تصویر قبلی">‹</button>
  <button class="lightbox-nav lightbox-next" type="button" aria-label="تصویر بعدی">›</button>
  <img class="lightbox-img" src="" alt="تصویر بزرگ"/>
  <div class="lightbox-counter"></div>
</div>
@endsection
