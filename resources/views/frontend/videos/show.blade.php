@extends('frontend.layouts.app')

@section('title', $video->title.' | اتاق اصناف مرکز استان گلستان')
@section('meta_description', plain_text($video->description, 160))

@section('content')
<section class="page-header page-header-alt">
  <div class="site-container">
    <h1>{{ $video->title }}</h1>
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">خانه</a>
      <a href="{{ route('videos.index') }}">چندرسانه‌ای</a>
      <span>{{ $video->title }}</span>
    </nav>
  </div>
</section>

<section class="site-container">
  <div class="video-single-layout">
    <div class="video-single-main">
      <div class="video-player-wrap">
        <div class="video-player">
          @if($video->video_type === 'upload' && $video->video_file)
            <video class="video-player" controls poster="{{ image_url($video->cover_image) }}">
              <source src="{{ image_url($video->video_file, '') }}" type="video/mp4">
            </video>
          @elseif($video->video_type === 'aparat' && $video->aparat_embed_url)
            <iframe class="video-player" src="{{ $video->aparat_embed_url }}" title="{{ $video->title }}" allowfullscreen loading="lazy"></iframe>
          @elseif($video->aparat_url)
            <a href="{{ $video->aparat_url }}" target="_blank" rel="noopener">
              <img src="{{ image_url($video->cover_image) }}" alt="{{ $video->title }}" loading="eager" fetchpriority="high" decoding="async"/>
              <div class="video-player-overlay"></div>
              <button class="video-big-play" type="button" aria-label="مشاهده ویدیو"></button>
            </a>
          @else
            <img src="{{ image_url($video->cover_image) }}" alt="{{ $video->title }}" loading="eager" fetchpriority="high" decoding="async"/>
            <div class="video-player-overlay"></div>
            <button class="video-big-play" type="button" aria-label="ویدیویی ثبت نشده است"></button>
          @endif
        </div>
      </div>
      <div class="video-single-body">
        <div class="video-meta">
          <span>📅 {{ jalali_date($video->published_at) ?: jalali_date($video->created_at) }}</span>
          <span>🎞 {{ $video->type_label }}</span>
          @if($video->union)<span>🏢 {{ $video->union->display_title }}</span>@endif
        </div>
        <h2>{{ $video->title }}</h2>
        <p>{{ plain_text($video->description) ?: 'توضیحی برای این ویدیو ثبت نشده است.' }}</p>
        <div class="video-tags">
          <span class="post-tag">ویدیو</span>
          <span class="post-tag">{{ $video->type_label }}</span>
          @if($video->union)<span class="post-tag">{{ $video->union->display_title }}</span>@endif
        </div>
      </div>
    </div>
    <aside class="video-sidebar">
      <div class="video-sidebar-card">
        <h4>ویدیوهای مرتبط</h4>
        <div class="video-related-list">
          @forelse($relatedVideos as $relatedVideo)
            <a href="{{ route('videos.show', $relatedVideo->slug) }}" class="video-related-item">
              <div class="vri-thumb"><img src="{{ image_url($relatedVideo->cover_image) }}" alt="{{ $relatedVideo->title }}" loading="lazy" decoding="async"/><span class="vri-play-icon"></span></div>
              <div class="vri-body">
                <strong>{{ $relatedVideo->title }}</strong>
                <span>{{ $relatedVideo->type_label }}</span>
              </div>
            </a>
          @empty
            <p>ویدیوی مرتبطی برای نمایش وجود ندارد.</p>
          @endforelse
        </div>
      </div>
    </aside>
  </div>
</section>
@endsection
