<section class="fractions-section section-gray">
    <div class="site-container">
        <div class="section-heading"><h2>{{ $section->title }}</h2><a class="tab-pill" href="{{ route('announcements.index') }}">همه اطلاعیه‌ها</a></div>
        <div class="fraction-grid">
            @forelse ($importantAnnouncements ?? collect() as $announcement)
                <a href="{{ route('announcements.show', $announcement->slug) }}" class="fraction-link">{{ $announcement->title }}</a>
            @empty
                <a href="{{ route('announcements.index') }}" class="fraction-link">موردی موجود نیست</a>
            @endforelse
        </div>
    </div>
</section>
