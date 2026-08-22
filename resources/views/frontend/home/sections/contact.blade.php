<section class="friendship-section section-white" id="friendship">
    <div class="site-container">
        <div class="section-heading friendship-heading">
            <h2>{{ $section->title ?: 'ارتباط با اتاق و دستگاه‌های همکار' }}</h2>
            <a class="tab-pill" href="{{ route('contact.create') }}">راهنمای تماس</a>
        </div>

        <div class="friendship-layout">
            <div class="world-map-wrap">
                <img alt="اتاق اصناف مرکز استان گلستان" class="world-map-img" src="{{ asset('assets/img/asnaf-gorgan-default.jpg') }}" loading="lazy" decoding="async">
            </div>

            <aside class="friend-list">
                <div class="friend-scroll-wrap">
                    <ul>
                        @forelse($orgLinks ?? collect() as $link)
                            <li>
                                <a href="{{ $link->url ?: '#' }}" target="{{ $link->target ?? '_self' }}" class="text-decoration-none" @if(($link->target ?? '_self') === '_blank') rel="noopener" @endif>
                                    @if($link->icon)
                                        <span>{{ $link->icon }}</span>
                                    @endif

                                    <strong>{{ $link->title }}</strong>

                                    @if($link->description)
                                        <small>{{ $link->description }}</small>
                                    @endif
                                </a>
                            </li>
                        @empty
                            <li>
                                <a href="{{ route('contact.create') }}" class="text-decoration-none">
                                    مشاهده اطلاعات تماس و راهنمای مراجعه حضوری اتاق اصناف مرکز استان گلستان
                                </a>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>
