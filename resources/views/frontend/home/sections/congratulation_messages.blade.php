@if (($congratulationMessages ?? collect())->isNotEmpty())
<section class="representatives-section section-white" id="congratulations">
    <div class="site-container">
        <div class="section-heading section-heading-centered"><h2>{{ $section->title }}</h2>@if($section->subtitle)<p>{{ $section->subtitle }}</p>@endif</div>
        <div class="howto-grid">
            @foreach ($congratulationMessages as $message)
                <a class="howto-card" href="{{ route('congratulation_messages.show', $message->slug) }}"><div class="howto-icon">🎉</div><h3>{{ $message->title }}</h3><p>{{ plain_text($message->body, 120) ?: ($message->union?->display_title ?: 'پیام مناسبتی') }}</p><span class="howto-link">مشاهده پیام ←</span></a>
            @endforeach
        </div>
    </div>
</section>
@endif
