@php
    $imageUrl = fn ($path) => $path ? (Str::startsWith($path, ['http://', 'https://', '/', 'assets/']) ? (Str::startsWith($path, 'assets/') ? asset($path) : $path) : Storage::url($path)) : asset('assets/img/asnaf-gorgan-default.jpg');
@endphp
<section class="hero-section site-container">
    <div class="hero-grid">
        <aside aria-label="دسترسی‌های عمودی" class="quick-menu">
            <ul class="quick-menu-list">
                @forelse ($quickMenuItems ?? collect() as $item)
                    <li class="quick-menu-item {{ $item->children->isNotEmpty() ? 'has-submenu' : '' }}">
                        @if ($item->children->isNotEmpty())
                            <button aria-expanded="false" class="quick-menu-link" type="button"><span>{{ $item->icon }} {{ $item->title }}</span><b></b></button>
                            <ul class="quick-submenu">
                                @foreach ($item->children as $child)
                                    <li><a href="{{ $child->resolved_url }}" target="{{ $child->target }}" @if($child->target === '_blank') rel="noopener" @endif>{{ $child->icon }} {{ $child->title }}</a></li>
                                @endforeach
                            </ul>
                        @else
                            <a class="quick-menu-link" href="{{ $item->resolved_url }}" target="{{ $item->target }}" @if($item->target === '_blank') rel="noopener" @endif><span>{{ $item->icon }} {{ $item->title }}</span><b></b></a>
                        @endif
                    </li>
                @empty
                    <li class="quick-menu-item"><a class="quick-menu-link" href="{{ route('contact.create') }}"><span>ارتباط با اتاق اصناف</span><b></b></a></li>
                @endforelse
            </ul>
        </aside>
        <div aria-label="اسلایدر خبرهای اصلی" class="hero-slider swiper" dir="ltr">
            <div class="swiper-wrapper">
                @forelse (($heroPosts ?? $importantPosts ?? collect()) as $post)
                    <article class="news-card news-card-main swiper-slide">
                        <a href="{{ route('posts.show', $post->slug) }}">
                            <img alt="{{ $post->title }}" src="{{ $post->featured_image_url }}" loading="lazy"/>
                            <div class="news-overlay"></div>
                            <div class="news-content"><span class="news-kicker">خبر تاپ</span><h1>{{ $post->title }}</h1></div>
                        </a>
                    </article>
                @empty
                    <article class="news-card news-card-main swiper-slide">
                        <img alt="اتاق اصناف مرکز استان گلستان" src="{{ asset('assets/img/asnaf-gorgan-default.jpg') }}"/>
                        <div class="news-overlay"></div>
                        <div class="news-content"><span class="news-kicker">{{ $section->title }}</span><h1>{{ $section->subtitle ?: 'اطلاع‌رسانی خدمات صنفی، آموزش و پیگیری درخواست‌های کسب‌وکارهای استان گلستان' }}</h1></div>
                    </article>
                @endforelse
            </div>
            <button aria-label="خبر بعدی" class="hero-slider-arrow hero-slider-next" type="button"></button>
            <button aria-label="خبر قبلی" class="hero-slider-arrow hero-slider-prev" type="button"></button>
            <div class="hero-slider-pagination"></div>
        </div>
    </div>
</section>
