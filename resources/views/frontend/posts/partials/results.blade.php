<div
    id="newsArchiveResults"
    class="news-archive-results"
    data-news-archive-results
    aria-busy="false"
    tabindex="-1"
>
    <div class="news-archive-results-content" data-news-archive-content>
        @if($posts->isNotEmpty())
            <div class="news-archive-grid">
                @foreach($posts as $post)
                    @php
                        $badge = $post->union?->display_title ?: ($post->category?->title ?: 'اخبار عمومی');
                        $summary = plain_text($post->excerpt ?: $post->body, 240);
                    @endphp
                    <article class="news-archive-card">
                        <a class="news-archive-card-link" href="{{ route('posts.show', $post->slug) }}" aria-label="مشاهده خبر: {{ $post->title }}">
                            <div class="news-archive-card-media">
                                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" loading="lazy" decoding="async">
                                @if(filled($badge))<span class="news-archive-card-badge">{{ $badge }}</span>@endif
                            </div>
                            <div class="news-archive-card-content">
                                <h2>{{ $post->title }}</h2>
                                @if($summary !== '')<p>{{ $summary }}</p>@endif
                                <div class="news-archive-card-footer">
                                    <time datetime="{{ optional($post->published_at)->toDateString() }}">{{ jalali_date($post->published_at) }}</time>
                                    <span>ادامه خبر <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m14 7-5 5 5 5"/></svg></span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            @include('frontend.posts.partials.pagination')
        @else
            <section class="news-archive-empty" aria-labelledby="news-empty-title">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/></svg>
                <h2 id="news-empty-title">خبری پیدا نشد</h2>
                <p>{{ $hasActiveFilters ? 'خبری مطابق جستجو یا فیلتر انتخاب‌شده پیدا نشد.' : 'هنوز خبر منتشرشده‌ای برای نمایش وجود ندارد.' }}</p>
                @if($hasActiveFilters)<a href="{{ route('posts.index') }}">پاک‌کردن فیلترها</a>@endif
            </section>
        @endif
    </div>

    <p class="news-archive-feedback" data-news-archive-feedback role="status" aria-live="polite" aria-atomic="true"></p>
</div>
