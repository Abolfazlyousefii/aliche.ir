@php
    $imageUrl = fn ($path) => $path ? (Str::startsWith($path, ['http://', 'https://', '/', 'assets/']) ? (Str::startsWith($path, 'assets/') ? asset($path) : $path) : Storage::url($path)) : asset('assets/img/asnaf-gorgan-default.jpg');
@endphp
<section class="tourism-section" id="videos">
    <div class="site-container">
        <div class="section-heading"><h2>{{ $section->title }}</h2><a class="tab-pill" href="{{ route('videos.index') }}">آرشیو ویدیوها</a></div>
        <div class="tourism-grid">
            @forelse ($latestVideos ?? collect() as $video)
                <div class="tourism-card"><a href="{{ route('videos.show', $video->slug) }}"><div class="tourism-img-wrap"><img alt="{{ $video->title }}" src="{{ $imageUrl($video->cover_image) }}" loading="lazy"/><div class="tourism-badge">{{ $video->type_label }}</div></div><div class="tourism-card-body"><h3>{{ $video->title }}</h3><p>{{ plain_text($video->description, 100) }}</p></div></a></div>
            @empty
                <div class="tourism-card"><a href="{{ route('videos.index') }}"><div class="tourism-img-wrap"><img alt="موردی موجود نیست" src="{{ asset('assets/img/asnaf-gorgan-default.jpg') }}" loading="lazy"/><div class="tourism-badge">ویدیو</div></div><div class="tourism-card-body"><h3>موردی موجود نیست</h3><p>در حال حاضر ویدیویی برای نمایش منتشر نشده است.</p></div></a></div>
            @endforelse
        </div>
    </div>
</section>
