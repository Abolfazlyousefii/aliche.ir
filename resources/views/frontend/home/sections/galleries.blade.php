<section class="representatives-section section-white" id="galleries">
    <div class="site-container">
        <div class="section-heading"><h2>{{ $section->title }}</h2><a class="tab-pill" href="{{ route('galleries.index') }}">گالری تصاویر</a></div>
        <div class="gallery-albums-grid">
            @forelse (($galleries ?? $latestGalleries) ?? collect() as $gallery)
                <a class="gallery-album-card" href="{{ route('galleries.show', $gallery->slug) }}"><img class="gallery-album-img" src="{{ $gallery->cover_image_url }}" alt="{{ $gallery->title }}" loading="lazy" decoding="async"><div class="gallery-album-body"><h3>{{ $gallery->title }}</h3><p>{{ plain_text($gallery->description, 100) ?: 'توضیحی برای این گالری ثبت نشده است.' }}</p><div class="gallery-album-meta"><span>{{ $gallery->images_count }} تصویر</span><span>{{ jalali_date($gallery->published_at) ?: '—' }}</span></div></div></a>
            @empty
                <a class="gallery-album-card" href="{{ route('galleries.index') }}"><img class="gallery-album-img" src="{{ asset('assets/img/asnaf-gorgan-default.jpg') }}" alt="موردی موجود نیست" loading="lazy" decoding="async"><div class="gallery-album-body"><h3>موردی موجود نیست</h3><p>در حال حاضر گالری تصویری برای نمایش منتشر نشده است.</p><div class="gallery-album-meta"><span>۰ تصویر</span><span>—</span></div></div></a>
            @endforelse
        </div>
    </div>
</section>
