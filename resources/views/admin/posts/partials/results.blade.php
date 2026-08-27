@if($filterError)
    <div class="alert alert-warning admin-post-results-error" role="alert">
        {{ $filterError }}
    </div>
@endif

<div
    class="admin-panel-card"
    data-post-results-card
    data-next-page-url="{{ $posts->nextPageUrl() }}"
    data-total="{{ $posts->total() }}"
    data-loaded="{{ $posts->count() }}"
>
    <div class="admin-post-results-toolbar">
        <div class="admin-post-results-count" data-post-results-count>
            @if($posts->total() > 0)
                نمایش <strong>{{ fa_number($posts->count()) }}</strong> از <strong>{{ fa_number($posts->total()) }}</strong> خبر
            @else
                نتیجه‌ای مطابق فیلترهای فعلی پیدا نشد.
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>دسته‌بندی</th>
                    <th>اتحادیه</th>
                    <th>نوع</th>
                    <th>جایگاه صفحه اصلی</th>
                    <th>وضعیت</th>
                    <th>بازدید</th>
                    <th>انتشار</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody data-post-results-body>
                @include('admin.posts.partials.rows')
            </tbody>
        </table>
    </div>

    <div
        class="admin-post-infinite-status"
        data-post-infinite-status
        @if(!$posts->hasMorePages()) hidden @endif
    >
        <span class="admin-post-infinite-spinner" aria-hidden="true"></span>
        <span data-post-infinite-text>برای نمایش خبرهای بیشتر اسکرول کنید</span>
    </div>

    <div data-post-infinite-sentinel aria-hidden="true"></div>
</div>
