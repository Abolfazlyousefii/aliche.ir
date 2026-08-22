@if($galleries->lastPage() > 1)
    @php
        $currentPage = $galleries->currentPage();
        $lastPage = $galleries->lastPage();
        $pageUrl = static fn (int $page): string => $page === 1
            ? route('galleries.index', array_filter(['search' => $search]))
            : $galleries->url($page);
        $visiblePages = $lastPage <= 7 ? range(1, $lastPage) : array_values(array_unique(array_filter([
            1, $currentPage - 2, $currentPage - 1, $currentPage, $currentPage + 1, $currentPage + 2, $lastPage,
        ], static fn (int $page): bool => $page >= 1 && $page <= $lastPage)));
        sort($visiblePages);
    @endphp
    <nav class="galleries-directory-pagination" aria-label="صفحه‌بندی گالری‌ها">
        @if($galleries->onFirstPage())<span class="galleries-page-edge is-disabled" aria-disabled="true">قبلی</span>@else<a class="galleries-page-edge" href="{{ $galleries->previousPageUrl() }}" rel="prev">قبلی</a>@endif
        <span class="galleries-page-numbers">
            @foreach($visiblePages as $index => $page)
                @if($index > 0 && $page - $visiblePages[$index - 1] > 1)<span class="galleries-page-ellipsis" aria-hidden="true">…</span>@endif
                @if($page === $currentPage)<span class="galleries-page-link is-current" aria-current="page">{{ fa_number($page) }}</span>@else<a class="galleries-page-link" href="{{ $pageUrl($page) }}">{{ fa_number($page) }}</a>@endif
            @endforeach
        </span>
        <span class="galleries-page-summary">صفحه {{ fa_number($currentPage) }} از {{ fa_number($lastPage) }}</span>
        @if($galleries->hasMorePages())<a class="galleries-page-edge" href="{{ $galleries->nextPageUrl() }}" rel="next">بعدی</a>@else<span class="galleries-page-edge is-disabled" aria-disabled="true">بعدی</span>@endif
    </nav>
@endif
