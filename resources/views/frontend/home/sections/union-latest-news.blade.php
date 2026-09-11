<style>
#home-union-news-v2 {
    padding: 72px 0 68px;
    background: #fff;
}
#home-union-news-v2 *,
#home-union-news-v2 *::before,
#home-union-news-v2 *::after {
    box-sizing: border-box;
}
#home-union-news-v2 .hun-head {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
}
#home-union-news-v2 .hun-head h2 {
    margin: 0;
    color: #102b3e;
    font-size: 24px;
    font-weight: 700;
    line-height: 1.6;
}
#home-union-news-v2 .hun-head p {
    margin: 4px 0 0;
    color: #748491;
    font-size: 12px;
    line-height: 1.8;
}
#home-union-news-v2 .hun-all {
    flex: 0 0 auto;
    min-height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 15px;
    border: 1px solid #cfe0eb;
    border-radius: 7px;
    color: #0b6fae;
    background: #fff;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
}
#home-union-news-v2 .hun-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(330px, .85fr);
    gap: 20px;
    align-items: stretch;
    direction: rtl;
}
#home-union-news-v2 .hun-featured {
    min-width: 0;
    min-height: 430px;
    margin: 0;
}
#home-union-news-v2 .hun-featured > a {
    position: relative;
    height: 100%;
    min-height: 430px;
    display: block;
    overflow: hidden;
    border-radius: 10px;
    background: #12364c;
    color: #fff;
    text-decoration: none;
    isolation: isolate;
}
#home-union-news-v2 .hun-featured img {
    position: absolute;
    inset: 0;
    z-index: -2;
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    transition: transform .3s ease;
}
#home-union-news-v2 .hun-featured-shade {
    position: absolute;
    inset: 0;
    z-index: -1;
    background: linear-gradient(180deg, rgba(6, 28, 42, .08) 20%, rgba(6, 28, 42, .93) 100%);
}
#home-union-news-v2 .hun-featured-copy {
    position: absolute;
    inset-inline: 26px;
    bottom: 23px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 7px;
}
#home-union-news-v2 .hun-union {
    padding: 4px 9px;
    border-radius: 5px;
    background: #0c74b9;
    color: #fff;
    font-size: 9.5px;
    font-weight: 600;
}
#home-union-news-v2 .hun-featured h3 {
    max-width: 720px;
    margin: 0;
    color: #fff;
    font-size: clamp(20px, 2.2vw, 27px);
    font-weight: 700;
    line-height: 1.65;
}
#home-union-news-v2 .hun-featured p {
    max-width: 700px;
    margin: 0;
    color: rgba(255,255,255,.78);
    font-size: 11.5px;
    line-height: 1.9;
}
#home-union-news-v2 .hun-featured time {
    color: rgba(255,255,255,.65);
    font-size: 9.5px;
}
#home-union-news-v2 .hun-featured > a:hover img {
    transform: scale(1.025);
}
#home-union-news-v2 .hun-feed {
    min-width: 0;
    height: 430px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #dfe8ee;
    border-radius: 10px;
    background: #f8fafb;
}
#home-union-news-v2 .hun-feed-head {
    min-height: 56px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 15px;
    border-bottom: 1px solid #e3eaee;
    background: #fff;
}
#home-union-news-v2 .hun-feed-head strong {
    color: #173347;
    font-size: 12.5px;
    font-weight: 650;
}
#home-union-news-v2 .hun-feed-head small {
    color: #7c8d99;
    font-size: 9.5px;
}
#home-union-news-v2 .hun-scroll {
    min-height: 0;
    flex: 1;
    overflow-y: auto;
    padding: 3px 11px 9px;
    scrollbar-width: thin;
    scrollbar-color: #9fc8df transparent;
}
#home-union-news-v2 .hun-scroll::-webkit-scrollbar {
    width: 5px;
}
#home-union-news-v2 .hun-scroll::-webkit-scrollbar-thumb {
    border-radius: 10px;
    background: #9fc8df;
}
#home-union-news-v2 .hun-item {
    min-height: 88px;
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 10px 2px;
    border-bottom: 1px solid #e5ebef;
    color: inherit;
    text-decoration: none;
}
#home-union-news-v2 .hun-item:last-child {
    border-bottom: 0;
}
#home-union-news-v2 .hun-thumb {
    position: relative;
    width: 104px;
    height: 66px;
    flex: 0 0 104px;
    overflow: hidden;
    border-radius: 6px;
    background: #e9eef2;
}
#home-union-news-v2 .hun-thumb img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}
#home-union-news-v2 .hun-item-copy {
    min-width: 0;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}
#home-union-news-v2 .hun-item-copy small {
    max-width: 100%;
    overflow: hidden;
    color: #0c74b9;
    font-size: 8.8px;
    font-weight: 550;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#home-union-news-v2 .hun-item-copy strong {
    display: -webkit-box;
    margin-top: 3px;
    overflow: hidden;
    color: #173347;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.7;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}
#home-union-news-v2 .hun-item-copy time {
    margin-top: 3px;
    color: #8b99a3;
    font-size: 8.3px;
}
#home-union-news-v2 .hun-item:hover {
    background: #fff;
}
#home-union-news-v2 .hun-empty {
    padding: 28px;
    border: 1px dashed #cedae2;
    border-radius: 10px;
    color: #687a87;
    background: #fafcfd;
    text-align: center;
}
#home-union-news-v2 .hun-empty h3 {
    margin: 0 0 6px;
    color: #173347;
    font-size: 15px;
    font-weight: 650;
}
#home-union-news-v2 .hun-empty p {
    margin: 0;
    font-size: 11px;
}
@media (max-width: 900px) {
    #home-union-news-v2 .hun-layout {
        grid-template-columns: 1fr;
    }
    #home-union-news-v2 .hun-featured,
    #home-union-news-v2 .hun-featured > a {
        min-height: 360px;
    }
    #home-union-news-v2 .hun-feed {
        height: 390px;
    }
}
@media (max-width: 575.98px) {
    #home-union-news-v2 {
        padding: 46px 0;
    }
    #home-union-news-v2 .hun-head {
        align-items: center;
        margin-bottom: 17px;
    }
    #home-union-news-v2 .hun-head h2 {
        font-size: 18px;
    }
    #home-union-news-v2 .hun-head p {
        display: none;
    }
    #home-union-news-v2 .hun-all {
        min-height: 34px;
        padding-inline: 10px;
        font-size: 10px;
    }
    #home-union-news-v2 .hun-featured,
    #home-union-news-v2 .hun-featured > a {
        min-height: 285px;
    }
    #home-union-news-v2 .hun-featured-copy {
        inset-inline: 16px;
        bottom: 15px;
    }
    #home-union-news-v2 .hun-featured h3 {
        font-size: 17px;
    }
    #home-union-news-v2 .hun-featured p {
        display: none;
    }
    #home-union-news-v2 .hun-feed {
        height: 365px;
    }
    #home-union-news-v2 .hun-thumb {
        width: 92px;
        height: 60px;
        flex-basis: 92px;
    }
}
</style>

<section id="home-union-news-v2" aria-labelledby="home-union-news-title">
<div class="site-container">
<header class="hun-head">
<div>
<h2 id="home-union-news-title">اتحادیه‌های صنفی استان گلستان</h2>
<p>جدیدترین اخبار منتشرشده از اتحادیه‌های صنفی استان</p>
</div>
<a class="hun-all" href="{{ route('guilds.index') }}">فهرست کامل اتحادیه‌ها</a>
</header>

@if($unionFeaturedNews)
<div class="hun-layout">
<article class="hun-featured">
<a href="{{ route('posts.show', $unionFeaturedNews->slug) }}">
<img
    src="{{ $unionFeaturedNews->featured_image_url }}"
    alt="{{ $unionFeaturedNews->featuredMedia?->alt_text ?: $unionFeaturedNews->title }}"
    loading="lazy"
    decoding="async"
>
<span class="hun-featured-shade" aria-hidden="true"></span>
<div class="hun-featured-copy">
@if($unionFeaturedNews->union)
<span class="hun-union">{{ $unionFeaturedNews->union->display_title }}</span>
@endif
<h3>{{ $unionFeaturedNews->title }}</h3>
@if($unionFeaturedNews->summary || $unionFeaturedNews->excerpt)
<p>{{ $plain($unionFeaturedNews->summary ?: $unionFeaturedNews->excerpt, 160) }}</p>
@endif
<time datetime="{{ $unionFeaturedNews->published_at?->toIso8601String() }}">{{ jalali_datetime($unionFeaturedNews->published_at) ?: 'بدون تاریخ' }}</time>
</div>
</a>
</article>

@if($unionRecentNews->isNotEmpty())
<aside class="hun-feed" aria-label="هشت خبر اخیر اتحادیه‌ها">
<div class="hun-feed-head">
<strong>تازه‌ترین اخبار اتحادیه‌ها</strong>
<small>{{ fa_number($unionRecentNews->count()) }} خبر اخیر</small>
</div>
<div class="hun-scroll">
@foreach($unionRecentNews as $post)
<a class="hun-item" href="{{ route('posts.show', $post->slug) }}">
<span class="hun-thumb">
<img src="{{ $post->featured_image_url }}" alt="{{ $post->featuredMedia?->alt_text ?: $post->title }}" loading="lazy" decoding="async">
</span>
<span class="hun-item-copy">
@if($post->union)<small>{{ $post->union->display_title }}</small>@endif
<strong>{{ $post->title }}</strong>
<time datetime="{{ $post->published_at?->toIso8601String() }}">{{ jalali_datetime($post->published_at) ?: 'بدون تاریخ' }}</time>
</span>
</a>
@endforeach
</div>
</aside>
@endif
</div>
@else
<div class="hun-empty">
<h3>هنوز خبر منتشرشده‌ای برای اتحادیه‌ها وجود ندارد.</h3>
<p>با انتشار اولین خبر مرتبط با اتحادیه فعال، این بخش به‌صورت خودکار بروزرسانی می‌شود.</p>
</div>
@endif
</div>
</section>
