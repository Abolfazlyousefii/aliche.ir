@php
    $hasImage = filled($announcement->featured_image);
    $imageUrl = $hasImage ? image_url($announcement->featured_image, '') : null;
    $summary = plain_text($announcement->excerpt ?: $announcement->body, 180);
@endphp

<article class="announcement-card {{ $hasImage ? 'has-image' : 'is-no-image' }} {{ $announcement->is_important ? 'is-important' : '' }}">
    <a class="announcement-card-link" href="{{ route('announcements.show', $announcement->slug) }}" aria-label="مشاهده اطلاعیه: {{ $announcement->title }}">
        @if($hasImage && $imageUrl)
            <div class="announcement-card-media">
                <img src="{{ $imageUrl }}" alt="{{ $announcement->title }}" loading="lazy" decoding="async">
                @if($announcement->category)<span class="announcement-category-badge">{{ $announcement->category->title }}</span>@endif
            </div>
        @endif

        <div class="announcement-card-body">
            @if(!$hasImage)
                <span class="announcement-card-document" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M7 3h7l4 4v14H7zM14 3v5h5M10 12h5M10 16h5"/></svg>
                </span>
            @endif

            <div class="announcement-card-badges">
                @if(!$hasImage && $announcement->category)<span class="announcement-category-badge">{{ $announcement->category->title }}</span>@endif
                @if($announcement->is_important)<span class="announcement-important-badge">مهم</span>@endif
            </div>

            <h2>{{ $announcement->title }}</h2>
            @if($summary !== '')<p>{{ $summary }}</p>@endif

            <div class="announcement-card-meta">
                @if($announcement->published_at)
                    <span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 3v3M18 3v3M4 8h16M5 5h14v16H5z"/></svg>{{ jalali_date($announcement->published_at) }}</span>
                @endif
                @if($announcement->union)
                    <span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 21h16M6 21V9h12v12M9 13h2M13 13h2M9 17h2M13 17h2M8 9V5h8v4"/></svg>{{ $announcement->union->display_title }}</span>
                @endif
            </div>

            <span class="announcement-card-action">مشاهده اطلاعیه <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m14 7-5 5 5 5"/></svg></span>
        </div>
    </a>
</article>
