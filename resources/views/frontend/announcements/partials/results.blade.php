<div
    id="announcementsResults"
    class="announcements-results"
    data-announcements-results
    aria-busy="false"
    tabindex="-1"
>
    <div class="announcements-results-content">
        @if($announcements->isNotEmpty())
            <div class="announcements-grid">
                @foreach($announcements as $announcement)
                    @include('frontend.announcements.partials.card', ['announcement' => $announcement])
                @endforeach
            </div>

            @include('frontend.announcements.partials.pagination')
        @else
            <section class="announcements-empty" aria-labelledby="announcements-empty-title">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/></svg>
                <h2 id="announcements-empty-title">اطلاعیه‌ای پیدا نشد</h2>
                <p>{{ $hasActiveFilters ? 'اطلاعیه‌ای مطابق جستجو یا فیلتر انتخاب‌شده وجود ندارد.' : 'در حال حاضر اطلاعیه فعال و قابل نمایشی وجود ندارد.' }}</p>
                @if($hasActiveFilters)<a href="{{ route('announcements.index') }}">پاک‌کردن فیلترها</a>@endif
            </section>
        @endif
    </div>

    <p class="announcements-feedback" data-announcements-feedback role="status" aria-live="polite" aria-atomic="true"></p>
</div>
