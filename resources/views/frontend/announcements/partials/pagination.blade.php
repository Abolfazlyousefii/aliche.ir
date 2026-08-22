@if($announcements->hasPages())
    @php
        $currentPage = $announcements->currentPage();
        $lastPage = $announcements->lastPage();
        $pageUrl = static fn (int $page): string => $page === 1
            ? route('announcements.index', \Illuminate\Support\Arr::except(request()->query(), 'page'))
            : $announcements->url($page);

        if ($lastPage <= 7) {
            $visiblePages = range(1, $lastPage);
        } else {
            $visiblePages = array_values(array_unique(array_filter([
                1,
                $currentPage - 2,
                $currentPage - 1,
                $currentPage,
                $currentPage + 1,
                $currentPage + 2,
                $lastPage,
            ], static fn (int $page): bool => $page >= 1 && $page <= $lastPage)));
            sort($visiblePages);
        }
    @endphp

    <nav class="announcements-pagination" aria-label="صفحه‌بندی اطلاعیه‌ها">
        @if($announcements->onFirstPage())
            <span class="announcements-page-edge is-disabled" aria-disabled="true" aria-label="صفحه قبلی در دسترس نیست">قبلی</span>
        @else
            <a class="announcements-page-edge" href="{{ $announcements->previousPageUrl() }}" rel="prev" aria-label="صفحه قبلی اطلاعیه‌ها">قبلی</a>
        @endif

        <span class="announcements-page-numbers" aria-label="شماره صفحات">
            @foreach($visiblePages as $index => $page)
                @if($index > 0 && $page - $visiblePages[$index - 1] > 1)
                    <span class="announcements-page-ellipsis" aria-hidden="true">…</span>
                @endif
                @if($page === $currentPage)
                    <span class="announcements-page-link is-current" aria-current="page">{{ fa_number($page) }}</span>
                @else
                    <a class="announcements-page-link" href="{{ $pageUrl($page) }}" aria-label="صفحه {{ fa_number($page) }} اطلاعیه‌ها">{{ fa_number($page) }}</a>
                @endif
            @endforeach
        </span>

        <span class="announcements-page-summary">صفحه {{ fa_number($currentPage) }} از {{ fa_number($lastPage) }}</span>

        @if($announcements->hasMorePages())
            <a class="announcements-page-edge" href="{{ $announcements->nextPageUrl() }}" rel="next" aria-label="صفحه بعدی اطلاعیه‌ها">بعدی</a>
        @else
            <span class="announcements-page-edge is-disabled" aria-disabled="true" aria-label="صفحه بعدی در دسترس نیست">بعدی</span>
        @endif
    </nav>
@endif
