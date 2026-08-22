@if($systems->lastPage() > 1)
    @php
        $currentPage = $systems->currentPage();
        $lastPage = $systems->lastPage();
        $pageUrl = static fn (int $page): string => $page === 1
            ? route('systems.index', \Illuminate\Support\Arr::except(request()->query(), 'page'))
            : $systems->url($page);
        $visiblePages = $lastPage <= 7 ? range(1, $lastPage) : array_values(array_unique(array_filter([
            1, $currentPage - 2, $currentPage - 1, $currentPage, $currentPage + 1, $currentPage + 2, $lastPage,
        ], static fn (int $page): bool => $page >= 1 && $page <= $lastPage)));
        sort($visiblePages);
    @endphp
    <nav class="systems-directory-pagination" aria-label="صفحه‌بندی سامانه‌ها">
        @if($systems->onFirstPage())<span class="systems-page-edge is-disabled" aria-disabled="true">قبلی</span>@else<a class="systems-page-edge" href="{{ $systems->previousPageUrl() }}" rel="prev">قبلی</a>@endif
        <span class="systems-page-numbers">
            @foreach($visiblePages as $index => $page)
                @if($index > 0 && $page - $visiblePages[$index - 1] > 1)<span class="systems-page-ellipsis" aria-hidden="true">…</span>@endif
                @if($page === $currentPage)<span class="systems-page-link is-current" aria-current="page">{{ fa_number($page) }}</span>@else<a class="systems-page-link" href="{{ $pageUrl($page) }}">{{ fa_number($page) }}</a>@endif
            @endforeach
        </span>
        <span class="systems-page-summary">صفحه {{ fa_number($currentPage) }} از {{ fa_number($lastPage) }}</span>
        @if($systems->hasMorePages())<a class="systems-page-edge" href="{{ $systems->nextPageUrl() }}" rel="next">بعدی</a>@else<span class="systems-page-edge is-disabled" aria-disabled="true">بعدی</span>@endif
    </nav>
@endif
