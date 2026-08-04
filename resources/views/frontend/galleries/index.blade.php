@extends('frontend.layouts.app')

@section('title', 'گالری تصاویر و ویدیوها | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'گالری تصاویر و گزارش‌های تصویری اتاق اصناف مرکز استان گلستان')

@section('content')
<section class="page-header page-header-alt">
  <div class="site-container">
    <h1>گالری تصاویر و ویدیوها</h1>
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">خانه</a>
      <span>گالری</span>
    </nav>
  </div>

  {{ $galleries->links('frontend.partials.pagination') }}
</section>

<section class="site-container">
  <div class="section-heading section-heading-centered">
    <h2>دسته‌بندی گالری‌ها</h2>
    <p>مجموعه تصاویر و ویدیوهای رویدادها، جلسات و فعالیت‌های اتاق اصناف</p>
  </div>
  <div class="gallery-albums-grid">
    @forelse ($galleries as $gallery)
      <a href="{{ route('galleries.show', $gallery->slug) }}" class="gallery-album-card">
        <img class="gallery-album-img" src="{{ $gallery->cover_image_url }}" alt="{{ $gallery->title }}" loading="lazy"/>
        <div class="gallery-album-body">
          <h3>{{ $gallery->title }}</h3>
          <p>{{ plain_text($gallery->description, 120) ?: 'توضیحی برای این گالری ثبت نشده است.' }}</p>
          <div class="gallery-album-meta">
            <span>{{ $gallery->images_count }} تصویر</span>
            <span>{{ jalali_date($gallery->published_at) ?: jalali_date($gallery->created_at) }}</span>
          </div>
        </div>
      </a>
    @empty
      <a href="{{ route('galleries.index') }}" class="gallery-album-card">
        <img class="gallery-album-img" src="{{ asset('assets/img/asnaf-gorgan-default.jpg') }}" alt="موردی موجود نیست" loading="lazy"/>
        <div class="gallery-album-body">
          <h3>موردی موجود نیست</h3>
          <p>در حال حاضر گالری تصویری برای نمایش منتشر نشده است.</p>
          <div class="gallery-album-meta"><span>۰ تصویر</span><span>—</span></div>
        </div>
      </a>
    @endforelse
  </div>

  {{ $galleries->links('frontend.partials.pagination') }}
</section>
@endsection
