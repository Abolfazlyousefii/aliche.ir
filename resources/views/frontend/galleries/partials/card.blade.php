@php
    $imageCount = (int) $gallery->images_count;
    $description = plain_text($gallery->description, 140);
    $date = jalali_date($gallery->published_at) ?: jalali_date($gallery->created_at);
@endphp
<article class="gallery-directory-card">
    <div class="gallery-directory-media {{ $coverUrl ? 'has-cover' : 'has-fallback' }}" data-gallery-cover-wrap>
        @if($coverUrl)<img src="{{ $coverUrl }}" alt="{{ $gallery->title }}" loading="lazy" decoding="async" @if(image_srcset($gallery->cover_image)) srcset="{{ image_srcset($gallery->cover_image) }}" sizes="(max-width: 768px) 100vw, 400px" @endif data-gallery-cover>@endif
        <span class="gallery-directory-media-fallback" aria-hidden="true">
            <svg viewBox="0 0 48 48"><rect x="7" y="10" width="34" height="28" rx="3"/><circle cx="17" cy="20" r="3"/><path d="m10 34 9-9 6 6 5-5 8 8"/></svg>
        </span>
        <span class="gallery-directory-overlay" aria-hidden="true"></span>
        <span class="gallery-directory-badges">
            <span class="gallery-directory-type">{{ $imageCount > 0 ? 'تصاویر' : 'بدون رسانه' }}</span>
            @if($imageCount > 0)<span class="gallery-directory-media-count">{{ fa_number($imageCount) }} تصویر</span>@endif
        </span>
    </div>
    <div class="gallery-directory-body">
        <h3 class="gallery-directory-title">{{ $gallery->title }}</h3>
        @if($description !== '')<p class="gallery-directory-description">{{ $description }}</p>@endif
        <footer class="gallery-directory-footer">
            <span class="gallery-directory-date">
                <svg viewBox="0 0 20 20" aria-hidden="true"><rect x="3" y="5" width="14" height="12" rx="2"/><path d="M6 3v4m8-4v4M3 9h14"/></svg>
                <span>{{ $date ?: '—' }}</span>
            </span>
            <a class="gallery-directory-action" href="{{ route('galleries.show', $gallery) }}">
                <span>مشاهده گالری</span><svg viewBox="0 0 20 20" aria-hidden="true"><path d="m12 5-5 5 5 5"/></svg>
            </a>
        </footer>
    </div>
</article>
