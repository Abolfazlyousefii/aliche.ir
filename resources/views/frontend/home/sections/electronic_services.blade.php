@php
    $items = $electronicServices ?? collect();
@endphp
<section class="howto-section section-white" id="electronic-services">
    <div class="site-container">
        <div class="section-heading section-heading-centered"><h2>{{ $section->title }}</h2><p>{{ $section->subtitle }}</p></div>
        <div class="howto-grid">
        @forelse ($items as $service)
            @php
                $serviceHref = $service->link_type === 'external' && filled($service->link) && $service->link !== '#' ? $service->link : route('electronic-services.show', $service->slug);
                $serviceTarget = $service->link_type === 'external' ? ($service->target ?? '_blank') : '_self';
            @endphp
            <a class="howto-card" href="{{ $serviceHref }}" target="{{ $serviceTarget }}" @if($serviceTarget === '_blank') rel="noopener" @endif>
                <div class="howto-icon">{{ $service->icon ?: '⚡' }}</div><h3>{{ $service->title }}</h3><p>{{ plain_text($service->short_description ?: $service->body, 120) }}</p><span class="howto-link">مشاهده خدمت ←</span>
            </a>
        @empty
            <a class="howto-card" href="{{ route('electronic-services.index') }}"><div class="howto-icon">⚡</div><h3>موردی موجود نیست</h3><p>در حال حاضر خدمت الکترونیکی برای نمایش ثبت نشده است.</p><span class="howto-link">مشاهده خدمات ←</span></a>
        @endforelse
        </div>
    </div>
</section>
