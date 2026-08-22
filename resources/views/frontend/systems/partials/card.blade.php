@php
    $validCategory = $system->category && $system->category->is_active && $system->category->type === 'system'
        ? $system->category
        : null;
    $description = plain_text($system->short_description ?: $system->description, 150);
@endphp
<article class="system-directory-card">
    <header class="system-directory-card-header">
        <span class="system-directory-icon">
            @include('frontend.systems.partials.icon', ['system' => $system])
        </span>
        <div class="system-directory-identity">
            <span class="system-directory-category">{{ $validCategory?->title ?: 'سامانه صنفی' }}</span>
            <h3 class="system-directory-title">{{ $system->title }}</h3>
        </div>
    </header>
    <div class="system-directory-body">
        @if($description !== '')<p class="system-directory-description">{{ $description }}</p>@endif
        <div class="system-directory-actions">
            <a class="system-directory-details" href="{{ route('systems.show', $system) }}">جزئیات</a>
            @if($entryLink)
                <a class="system-directory-entry" href="{{ $entryLink['url'] }}" @if($entryLink['external']) target="_blank" rel="noopener noreferrer" @endif>
                    <span>ورود به سامانه</span>
                    @if($entryLink['external'])<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M8 5H5v10h10v-3M11 4h5v5M16 4l-7 7"/></svg>@endif
                </a>
            @endif
        </div>
    </div>
</article>
