<section class="fractions-section section-gray" id="systems">
    <div class="site-container">
        <div class="section-heading"><h2>{{ $section->title }}</h2><a class="tab-pill" href="{{ route('systems.index') }}">مشاهده همه سامانه‌ها</a></div>
        <div class="fraction-grid">
            @forelse ($systems ?? collect() as $system)
                @php
                    $systemHref = filled($system->link) ? $system->link : route('systems.show', $system->slug);
                    $systemTarget = filled($system->link) ? ($system->target ?? '_blank') : '_self';
                @endphp
                <a href="{{ $systemHref }}" target="{{ $systemTarget }}" @if($systemTarget === '_blank') rel="noopener" @endif class="fraction-link"><span>{{ $system->icon ?: '💻' }}</span> {{ $system->title }}</a>
            @empty
                <a href="{{ route('systems.index') }}" class="fraction-link"><span>💻</span> موردی موجود نیست</a>
            @endforelse
        </div>
    </div>
</section>
