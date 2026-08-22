@if ($paginator->hasPages())
    @php
        $paginationClass = trim('pagination-nav pagination-rtl '.($class ?? ''));
        $pageLinkClass = trim('page-link '.($linkClass ?? ''));
        $ellipsisClass = trim('pagination-ellipsis '.($ellipsisClass ?? ''));
        $pageName = $paginator->getPageName();
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $visiblePages = $lastPage <= 7
            ? range(1, $lastPage)
            : array_values(array_unique(array_filter([
                1,
                $currentPage - 2,
                $currentPage - 1,
                $currentPage,
                $currentPage + 1,
                $currentPage + 2,
                $lastPage,
            ], static fn (int $page): bool => $page >= 1 && $page <= $lastPage)));
        sort($visiblePages);

        $pageUrl = static function (int $page) use ($paginator, $pageName): string {
            $url = $paginator->url($page);

            if ($page !== 1) {
                return $url;
            }

            $parts = parse_url($url);
            parse_str($parts['query'] ?? '', $query);
            unset($query[$pageName]);

            $authority = isset($parts['host'])
                ? (($parts['scheme'] ?? 'http').'://'.($parts['user'] ?? '').(isset($parts['pass']) ? ':'.$parts['pass'] : '').(isset($parts['user']) ? '@' : '').$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : ''))
                : '';
            $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

            return $authority.($parts['path'] ?? '').($queryString !== '' ? '?'.$queryString : '').(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
        };
    @endphp

    <nav class="{{ $paginationClass }}" role="navigation" aria-label="{{ $ariaLabel ?? 'صفحه‌بندی' }}" dir="rtl" data-pagination>
        <ul class="pagination" role="list">
            <li class="page-item page-item-previous{{ $paginator->onFirstPage() ? ' disabled' : '' }}">
                @if ($paginator->onFirstPage())
                    <span class="{{ $pageLinkClass }}" aria-disabled="true" aria-label="صفحه قبلی در دسترس نیست">
                        <span aria-hidden="true">→</span>
                        <span>قبلی</span>
                    </span>
                @else
                    <a class="{{ $pageLinkClass }}" href="{{ $pageUrl($paginator->currentPage() - 1) }}" rel="prev" aria-label="رفتن به صفحه قبلی">
                        <span aria-hidden="true">→</span>
                        <span>قبلی</span>
                    </a>
                @endif
            </li>

            @foreach ($visiblePages as $index => $page)
                @if ($index > 0 && $page - $visiblePages[$index - 1] > 1)
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link {{ $ellipsisClass }}" aria-hidden="true">…</span>
                    </li>
                @endif

                @if ($page === $currentPage)
                    <li class="page-item active" aria-current="page">
                        <span class="{{ $pageLinkClass }}" aria-label="صفحه {{ fa_number($page) }}، صفحه فعلی">{{ fa_number($page) }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="{{ $pageLinkClass }}" href="{{ $pageUrl($page) }}" aria-label="رفتن به صفحه {{ fa_number($page) }}">{{ fa_number($page) }}</a>
                    </li>
                @endif
            @endforeach

            <li class="page-item page-item-next{{ $paginator->hasMorePages() ? '' : ' disabled' }}">
                @if ($paginator->hasMorePages())
                    <a class="{{ $pageLinkClass }}" href="{{ $pageUrl($paginator->currentPage() + 1) }}" rel="next" aria-label="رفتن به صفحه بعدی">
                        <span>بعدی</span>
                        <span aria-hidden="true">←</span>
                    </a>
                @else
                    <span class="{{ $pageLinkClass }}" aria-disabled="true" aria-label="صفحه بعدی در دسترس نیست">
                        <span>بعدی</span>
                        <span aria-hidden="true">←</span>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
