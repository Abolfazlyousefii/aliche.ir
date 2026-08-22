<div class="latest-news-grid" data-latest-news-grid>
@forelse($latestPosts as $post)
@php
    $postUrl = route('posts.show', ['slug' => $post->slug, 'news_page' => $latestPosts->currentPage()]);
@endphp
<article class="latest-news-card">
<a class="latest-news-thumb-link" href="{{ $postUrl }}">
<img loading="lazy" decoding="async" src="{{ $post->featured_image_url }}" alt="{{ $post->featuredMedia?->alt_text ?: $post->title }}" @if($post->featuredMedia?->srcset) srcset="{{ $post->featuredMedia->srcset }}" sizes="(max-width: 768px) 100vw, 400px" @endif @if($post->featuredMedia?->width && $post->featuredMedia?->height) width="{{ $post->featuredMedia->width }}" height="{{ $post->featuredMedia->height }}" @endif>
@if(filled($post->category_title))
<span class="latest-news-category">{{ $post->category_title }}</span>
@endif
</a>
<div class="latest-news-card-body">
<time>{{ jalali_datetime($post->published_at) }}</time>
<h3><a href="{{ $postUrl }}">{{ $post->title }}</a></h3>
<p>{{ \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?: $post->short_description ?: $post->body), 135) }}</p>
</div>
</article>
@empty
<div class="empty-state">هنوز خبری برای نمایش ثبت نشده است.</div>
@endforelse
</div>
@if($latestPosts->lastPage() > 1)
<nav class="latest-news-pagination" aria-label="صفحه‌بندی آخرین اخبار">
@if($latestPosts->onFirstPage())
<span class="latest-news-page-button is-disabled" aria-disabled="true" title="اخبار قبلی"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg></span>
@else
<a class="latest-news-page-button" href="{{ $latestPosts->previousPageUrl() }}" aria-label="اخبار قبلی" title="اخبار قبلی"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg></a>
@endif
@if($latestPosts->hasMorePages())
<a class="latest-news-page-button" href="{{ $latestPosts->nextPageUrl() }}" aria-label="اخبار بعدی" title="اخبار بعدی"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m15 5-7 7 7 7"/></svg></a>
@else
<span class="latest-news-page-button is-disabled" aria-disabled="true" title="اخبار بعدی"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m15 5-7 7 7 7"/></svg></span>
@endif
</nav>
@endif
