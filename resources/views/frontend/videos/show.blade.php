@extends('frontend.layouts.app')

@section('title', $video->title.' | اتاق اصناف مرکز استان گلستان')
@section('meta_description', plain_text($video->description, 160))
@if(filled($video->meta_keywords))
@section('meta_keywords', $video->meta_keywords)
@endif

@section('content')
@include('frontend.partials.media-detail-heading', ['title' => $video->title, 'indexUrl' => route('videos.index'), 'indexLabel' => 'ویدئوها'])

<section class="site-container">
  <div class="video-single-layout">
    <div class="video-single-main">
      <div class="video-player-wrap">
        <div class="video-player">
          @if($video->video_type === 'upload' && $video->video_file)
            <video class="video-player" controls poster="{{ $video->cover_image_url }}">
              <source src="{{ $video->video_file_url }}" type="{{ $video->video_mime_type }}">
            </video>
          @elseif($video->video_type === 'aparat' && $video->aparat_embed_url)
            <iframe class="video-player" src="{{ $video->aparat_embed_url }}" title="{{ $video->title }}" allowfullscreen loading="lazy"></iframe>
          @elseif($video->aparat_url)
            <a href="{{ $video->aparat_url }}" target="_blank" rel="noopener">
              <img src="{{ $video->cover_image_url }}" alt="{{ $video->title }}" loading="eager" fetchpriority="high" decoding="async"/>
              <div class="video-player-overlay"></div>
              <button class="video-big-play" type="button" aria-label="مشاهده ویدیو"></button>
            </a>
          @else
            <img src="{{ $video->cover_image_url }}" alt="{{ $video->title }}" loading="eager" fetchpriority="high" decoding="async"/>
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
      @if($video->tags !== [])
        <div class="media-detail-tags" aria-label="کلیدواژه‌های ویدئوها">
          <strong class="media-detail-tags__label">کلیدواژه‌ها:</strong>
          @foreach($video->tags as $keyword)
            <span class="post-tag">{{ $keyword }}</span>
          @endforeach
        </div>
      @endif
      </div>
    </div>
    <aside class="video-sidebar">
      <div class="video-sidebar-card">
        <h4>ویدیوهای مرتبط</h4>
        <div class="video-related-list">
          @forelse($relatedVideos as $relatedVideo)
            <a href="{{ route('videos.show', $relatedVideo->slug) }}" class="video-related-item">
              <div class="vri-thumb"><img src="{{ $relatedVideo->cover_image_url }}" alt="{{ $relatedVideo->title }}" loading="lazy" decoding="async"/><span class="vri-play-icon"></span></div>
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
