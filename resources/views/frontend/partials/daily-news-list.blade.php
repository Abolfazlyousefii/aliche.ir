<div class="daily-news-list" data-testid="daily-news-list">
@forelse($posts as $post)
    <article class="daily-news-item">
        <a class="daily-news-card" href="{{ route('posts.show', $post->slug) }}">
            <time class="daily-news-time" datetime="{{ $post->published_at?->toIso8601String() }}">{{ jalali_to_persian_digits($post->published_at?->format('H:i')) }}</time>
            <img class="daily-news-thumb" src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" loading="lazy">
            <div class="daily-news-content">
                <div class="daily-news-meta">
                    @if($post->category)<span class="daily-news-category" onclick="event.preventDefault(); window.location='{{ route('posts.index', ['category_id' => $post->category_id]) }}'">{{ $post->category->title }}</span>@else<span class="daily-news-category">عمومی</span>@endif
                    @if($post->is_important)<span class="daily-news-badge urgent">فوری</span><span class="daily-news-badge important">مهم</span>@endif
                    @if($post->is_featured)<span class="daily-news-badge featured">ویژه</span>@endif
                </div>
                <h3>{{ $post->title }}</h3>
                @if($post->summary)<p>{{ $post->summary }}</p>@endif
                <span class="daily-news-more">مشاهده خبر</span>
            </div>
        </a>
    </article>
@empty
    <div class="daily-news-empty">
        <p>برای این روز هنوز خبری منتشر نشده است.</p>
        <a class="tab-pill" href="{{ route('daily-news.index', ['date' => $previousDateParam]) }}">مشاهده روز قبل</a>
    </div>
@endforelse
</div>
