@if($unions->hasPages())
    @php
        $currentPage = $unions->currentPage();
        $lastPage = $unions->lastPage();
        $pageUrl = static fn (int $page): string => $page === 1
            ? route('guilds.index', \Illuminate\Support\Arr::except(request()->query(), 'page'))
            : $unions->url($page);
        $visiblePages = $lastPage <= 7 ? range(1, $lastPage) : array_values(array_unique(array_filter([
            1, $currentPage - 2, $currentPage - 1, $currentPage, $currentPage + 1, $currentPage + 2, $lastPage,
        ], static fn (int $page): bool => $page >= 1 && $page <= $lastPage)));
        sort($visiblePages);
    @endphp
    <nav class="guilds-directory-pagination" aria-label="صفحه‌بندی اتحادیه‌ها">
        @if($unions->onFirstPage())<span class="guilds-page-edge is-disabled" aria-disabled="true">قبلی</span>@else<a class="guilds-page-edge" href="{{ $unions->previousPageUrl() }}" rel="prev">قبلی</a>@endif
        <span class="guilds-page-numbers">
            @foreach($visiblePages as $index => $page)
                @if($index > 0 && $page - $visiblePages[$index - 1] > 1)<span class="guilds-page-ellipsis" aria-hidden="true">…</span>@endif
                @if($page === $currentPage)<span class="guilds-page-link is-current" aria-current="page">{{ fa_number($page) }}</span>@else<a class="guilds-page-link" href="{{ $pageUrl($page) }}">{{ fa_number($page) }}</a>@endif
            @endforeach
        </span>
        <span class="guilds-page-summary">صفحه {{ fa_number($currentPage) }} از {{ fa_number($lastPage) }}</span>
        @if($unions->hasMorePages())<a class="guilds-page-edge" href="{{ $unions->nextPageUrl() }}" rel="next">بعدی</a>@else<span class="guilds-page-edge is-disabled" aria-disabled="true">بعدی</span>@endif
    </nav>
@endif
