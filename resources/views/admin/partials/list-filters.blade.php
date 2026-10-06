@php
    // Configured per-page: preserve existing server-side query names and GET behavior.
    $listConfiguredFilters = $listFilters ?? [];
    $listHasSearch = trim((string) ($listSearch ?? '')) !== '';
    $listActiveExtra = collect($listConfiguredFilters)->filter(
        fn (array $filter) => filled($filter['value'] ?? null)
    )->count();
    $listActiveCount = $listActiveExtra + ($listHasSearch ? 1 : 0);
    $listFilterFormId = 'admin-list-search-'.str_replace('.', '-', $listRoute);
@endphp

<section class="admin-panel-card admin-list-filter-card mb-3" aria-label="جستجو و فیلتر {{ $listTitle }}">
    <div class="admin-list-filter-heading">
        <div>
            <span class="admin-list-filter-eyebrow">مدیریت فهرست</span>
            <h3>{{ $listTitle }}</h3>
            @if(! empty($listDescription))<p>{{ $listDescription }}</p>@endif
        </div>
        <div class="admin-list-filter-summary">
            <span class="admin-list-total">{{ fa_number($listPaginator->total()) }} نتیجه</span>
            @if($listActiveCount > 0)
                <span class="admin-list-active-filters">{{ fa_number($listActiveCount) }} فیلتر فعال</span>
            @endif
        </div>
    </div>

    <form id="{{ $listFilterFormId }}" class="admin-list-filter-form" action="{{ route($listRoute) }}" method="GET" role="search">
        <div class="admin-list-primary-search">
            <div class="admin-list-primary-search__field">
                <label for="{{ $listFilterFormId }}-input">{{ $listSearchLabel ?? 'جستجو در فهرست' }}</label>
                <input id="{{ $listFilterFormId }}-input" class="form-control" name="search" type="search"
                    value="{{ $listSearch ?? '' }}" placeholder="{{ $listPlaceholder ?? 'جستجو...' }}" autocomplete="off">
            </div>
            <button type="submit" class="admin-primary-btn">جستجو</button>
            @if($listActiveCount > 0)
                <a class="admin-secondary-btn" href="{{ route($listRoute) }}">پاک‌کردن فیلترها</a>
            @endif
        </div>

        @if(count($listConfiguredFilters) > 0)
            <details class="admin-list-more-filters" @if($listActiveExtra > 0) open @endif>
                <summary>
                    <span>فیلترهای بیشتر</span>
                    @if($listActiveExtra > 0)<span class="admin-list-filter-count">{{ fa_number($listActiveExtra) }}</span>@endif
                    <span aria-hidden="true" class="admin-list-filter-caret">⌄</span>
                </summary>
                <div class="admin-list-extra-grid">
                    @foreach($listConfiguredFilters as $filter)
                        <div class="admin-list-extra-field">
                            <label for="{{ $listFilterFormId.'-'.$filter['name'] }}">{{ $filter['label'] }}</label>
                            <select class="form-select" name="{{ $filter['name'] }}" id="{{ $listFilterFormId.'-'.$filter['name'] }}">
                                <option value="">{{ $filter['empty'] ?? 'همه موارد' }}</option>
                                @foreach($filter['options'] as $filterValue => $filterLabel)
                                    <option value="{{ $filterValue }}" @selected((string) ($filter['value'] ?? '') === (string) $filterValue)>{{ $filterLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                    <div class="admin-list-extra-apply">
                        <button class="admin-primary-btn" type="submit">اعمال فیلترها</button>
                    </div>
                </div>
            </details>
        @endif
    </form>
</section>
