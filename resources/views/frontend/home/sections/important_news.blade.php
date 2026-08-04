<section class="representatives-section section-white">
    <div class="site-container">
        <div class="section-heading"><h2>{{ $section->title }}</h2><a class="tab-pill" href="{{ route('posts.index') }}">همه اخبار</a></div>
        <div class="archive-grid">
            @forelse ($importantPosts ?? collect() as $post)
                <article class="archive-card"><a href="{{ route('posts.show', $post->slug) }}"><img class="archive-card-img" src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" loading="lazy"><div class="archive-card-body"><span class="card-cat">{{ $post->category_title }} @if($post->type === 'video') 🎥 @elseif($post->galleries_count > 0) 🖼 @endif</span><h2>{{ $post->title }}</h2><p>{{ plain_text($post->excerpt ?: $post->short_description ?: $post->body, 120) }}</p></div></a></article>
            @empty
                <article class="archive-card"><a href="{{ route('posts.index') }}"><img class="archive-card-img" src="{{ asset('assets/img/asnaf-gorgan-default.jpg') }}" alt="موردی موجود نیست" loading="lazy"><div class="archive-card-body"><span class="card-cat">خبر</span><h2>موردی موجود نیست</h2><p>در حال حاضر خبری برای نمایش در صفحه اصلی منتشر نشده است.</p></div></a></article>
            @endforelse
        </div>
    </div>
</section>
