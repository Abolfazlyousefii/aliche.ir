@php
    /** @var \Illuminate\Contracts\Pagination\Paginator|\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator */
@endphp

@if ($paginator->hasPages())
    <div class="admin-pagination-wrapper" data-admin-pagination>
        @if (method_exists($paginator, 'firstItem'))
            <div class="admin-pagination-summary">
                نمایش {{ fa_number($paginator->firstItem()) }} تا {{ fa_number($paginator->lastItem()) }} از {{ fa_number($paginator->total()) }} مورد
            </div>
        @endif

        <div class="admin-pagination-links">
            {{ $paginator->onEachSide(1)->links('vendor.pagination.bootstrap-rtl', ['ariaLabel' => 'صفحه‌بندی مدیریت']) }}
        </div>
    </div>
@elseif (method_exists($paginator, 'total'))
    <div class="admin-pagination-wrapper admin-pagination-wrapper-empty">
        <div class="admin-pagination-summary">نمایش {{ fa_number($paginator->total()) }} مورد</div>
    </div>
@endif
