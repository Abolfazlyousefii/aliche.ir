@if($posts->hasPages())
    @php
        $currentPage = $posts->currentPage();
        $lastPage = $posts->lastPage();
        $pageUrl = static fn (int $page): string => $page === 1
            ? route('posts.index', \Illuminate\Support\Arr::except(request()->query(), 'page'))
            : $posts->url($page);

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

    <nav class="news-archive-pagination" aria-label="صفحه‌بندی اخبار">
        @if($posts->onFirstPage())
            <span class="news-archive-page-edge is-disabled" aria-disabled="true" aria-label="صفحه قبلی در دسترس نیست">قبلی</span>
        @else
            <a class="news-archive-page-edge" href="{{ $posts->previousPageUrl() }}" rel="prev" aria-label="رفتن به صفحه قبلی اخبار">قبلی</a>
        @endif

        <span class="news-archive-page-numbers" aria-label="شماره صفحات">
            @foreach($visiblePages as $index => $page)
                @if($index > 0 && $page - $visiblePages[$index - 1] > 1)
                    <span class="news-archive-page-ellipsis" aria-hidden="true">…</span>
                @endif

                @if($page === $currentPage)
                    <span class="news-archive-pagination-link is-current" aria-current="page" aria-label="صفحه {{ fa_number($page) }}، صفحه فعلی">{{ fa_number($page) }}</span>
                @else
                    <a class="news-archive-pagination-link" href="{{ $pageUrl($page) }}" aria-label="رفتن به صفحه {{ fa_number($page) }} اخبار">{{ fa_number($page) }}</a>
                @endif
            @endforeach
        </span>

        <span class="news-archive-page-summary" aria-current="page">صفحه {{ fa_number($currentPage) }} از {{ fa_number($lastPage) }}</span>

        @if($posts->hasMorePages())
            <a class="news-archive-page-edge" href="{{ $posts->nextPageUrl() }}" rel="next" aria-label="رفتن به صفحه بعدی اخبار">بعدی</a>
        @else
            <span class="news-archive-page-edge is-disabled" aria-disabled="true" aria-label="صفحه بعدی در دسترس نیست">بعدی</span>
        @endif
    </nav>
@endif
