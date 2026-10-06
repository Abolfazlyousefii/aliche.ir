@php
    $newsItems = $unionNews ? collect($unionNews->items()) : collect();
    $featured = $newsItems->first();
    $others = $newsItems->skip(1);
    $current = $unionNews?->currentPage() ?? 1;
    $last = $unionNews?->lastPage() ?? 1;
    $start = max(1, min($current - 2, $last - 5));
    $end = min($last, $start + 5);
@endphp

<div class="guild-profile-news-result" data-guild-news-result>
    @if($featured)
        @php($featuredUrl = route('posts.show', $featured->slug))
        <article class="guild-profile-news-feature">
            <a class="guild-profile-news-feature__media" href="{{ $featuredUrl }}">
                <img src="{{ $featured->featured_image_url }}"
                     alt="{{ $featured->featuredMedia?->alt_text ?: $featured->title }}"
                     loading="lazy" decoding="async"
                     @if($featured->featuredMedia?->srcset) srcset="{{ $featured->featuredMedia->srcset }}" sizes="(max-width: 768px) 100vw, 680px" @endif>
            </a>
            <div class="guild-profile-news-feature__body">
                <div class="guild-profile-news-meta">
                    @if(filled($featured->category_title))<span>{{ $featured->category_title }}</span>@endif
                    <time datetime="{{ $featured->published_at?->toIso8601String() }}">{{ jalali_datetime($featured->published_at) ?: 'بدون تاریخ' }}</time>
                </div>
                <h3><a href="{{ $featuredUrl }}">{{ $featured->title }}</a></h3>
                <p>{{ plain_text($featured->excerpt ?: $featured->short_description ?: $featured->summary ?: $featured->body, 190) }}</p>
                <a class="guild-profile-news-feature__link" href="{{ $featuredUrl }}">مطالعه خبر</a>
            </div>
        </article>

        @if($others->isNotEmpty())
            <div class="guild-profile-news-rows" aria-label="سایر اخبار اتحادیه">
                @foreach($others as $post)
                    @php($postUrl = route('posts.show', $post->slug))
                    <article class="latest-news-card">
                        <a class="latest-news-thumb-link" href="{{ $postUrl }}">
                            <img loading="lazy" decoding="async" src="{{ $post->featured_image_url }}"
                                 alt="{{ $post->featuredMedia?->alt_text ?: $post->title }}"
                                 @if($post->featuredMedia?->srcset) srcset="{{ $post->featuredMedia->srcset }}" sizes="(max-width: 768px) 112px, 156px" @endif>
                            @if(filled($post->category_title))
                                <span class="latest-news-category">{{ $post->category_title }}</span>
                            @endif
                        </a>
                        <div class="latest-news-card-body">
                            <time datetime="{{ $post->published_at?->toIso8601String() }}">{{ jalali_datetime($post->published_at) }}</time>
                            <h3><a href="{{ $postUrl }}">{{ $post->title }}</a></h3>
                            <p>{{ plain_text($post->excerpt ?: $post->short_description ?: $post->summary ?: $post->body, 135) }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    @else
        <p class="guild-profile-news-empty">خبری در این صفحه برای نمایش موجود نیست.</p>
    @endif

    @if($unionNews && $unionNews->lastPage() > 1)
        <nav class="guild-profile-news-pagination" aria-label="صفحه‌بندی اخبار اتحادیه" data-guild-news-pagination>
            @if($current > 1)
                <a href="{{ $unionNews->url($current - 1) }}" data-guild-news-page rel="prev">قبلی</a>
            @else
                <span class="is-disabled" aria-disabled="true">قبلی</span>
            @endif

            @for($page = $start; $page <= $end; $page++)
                @if($page === $current)
                    <span class="is-current" aria-current="page">{{ fa_number($page) }}</span>
                @else
                    <a href="{{ $unionNews->url($page) }}" data-guild-news-page aria-label="صفحه {{ fa_number($page) }}">{{ fa_number($page) }}</a>
                @endif
            @endfor

            @if($current < $last)
                <a href="{{ $unionNews->url($current + 1) }}" data-guild-news-page rel="next">بعدی</a>
            @else
                <span class="is-disabled" aria-disabled="true">بعدی</span>
            @endif
        </nav>
    @endif
</div>
