@extends('admin.layouts.app')

@section('title', 'مدیریت اخبار')

@section('content')
<div class="admin-posts-index" data-posts-index>
    <div class="admin-page-toolbar">
        <div>
            <p class="admin-eyebrow">CMS اخبار</p>
            <h2>مدیریت اخبار</h2>
        </div>
        <a class="admin-primary-btn" href="{{ route('admin.posts.create') }}">ایجاد خبر جدید</a>
    </div>

    @php
        $hasFilters = $search !== ''
            || $status !== ''
            || $type !== ''
            || filled($categoryId)
            || filled($unionId)
            || $homepagePosition !== ''
            || $from !== ''
            || $to !== ''
            || $today;

        $activeFilterCount = collect([
            $search !== '',
            $status !== '',
            $type !== '',
            filled($categoryId),
            filled($unionId),
            $homepagePosition !== '',
            $from !== '',
            $to !== '',
            $today,
        ])->filter()->count();

        $shortcutClass = fn (bool $active) => 'admin-post-shortcut'.($active ? ' is-active' : '');
    @endphp

    <div class="admin-panel-card admin-post-filters-card mb-3">
        <div class="admin-post-filter-head">
            <div>
                <strong>فیلتر و جستجوی اخبار</strong>
                <p>نتایج را بر اساس وضعیت، نوع، اتحادیه، دسته‌بندی، جایگاه و بازه تاریخ انتشار محدود کنید.</p>
            </div>
            <span
                class="admin-post-filter-count"
                data-post-filter-count
                @if(! $hasFilters) hidden @endif
            >{{ fa_number($activeFilterCount) }} فیلتر فعال</span>
        </div>

        <nav class="admin-post-shortcuts" aria-label="فیلترهای سریع اخبار" data-post-shortcuts>
            <a
                href="{{ route('admin.posts.index') }}"
                class="{{ $shortcutClass(! $hasFilters) }}"
                data-post-filter-link
            >همه اخبار</a>

            <a
                href="{{ route('admin.posts.index', ['status' => 'published']) }}"
                class="{{ $shortcutClass($status === 'published' && ! $today && $homepagePosition === '') }}"
                data-post-filter-link
            >منتشرشده <span>{{ fa_number($statusCounts['published'] ?? 0) }}</span></a>

            <a
                href="{{ route('admin.posts.index', ['status' => 'draft']) }}"
                class="{{ $shortcutClass($status === 'draft') }}"
                data-post-filter-link
            >پیش‌نویس <span>{{ fa_number($statusCounts['draft'] ?? 0) }}</span></a>

            <a
                href="{{ route('admin.posts.index', ['status' => 'pending']) }}"
                class="{{ $shortcutClass($status === 'pending') }}"
                data-post-filter-link
            >در انتظار تایید <span>{{ fa_number($statusCounts['pending'] ?? 0) }}</span></a>

            <a
                href="{{ route('admin.posts.index', ['homepage_position' => 'top']) }}"
                class="{{ $shortcutClass($homepagePosition === 'top') }}"
                data-post-filter-link
            >خبرهای تاپ <span>{{ fa_number($topNewsCount ?? 0) }}</span></a>

            <a
                href="{{ route('admin.posts.index', ['homepage_position' => 'featured']) }}"
                class="{{ $shortcutClass($homepagePosition === 'featured') }}"
                data-post-filter-link
            >خبرهای ویژه <span>{{ fa_number($featuredNewsCount ?? 0) }}</span></a>

            <a
                href="{{ route('admin.posts.index', ['today' => 1]) }}"
                class="{{ $shortcutClass($today) }}"
                data-post-filter-link
            >اخبار امروز <span>{{ fa_number($todayPublishedCount ?? 0) }}</span></a>
        </nav>

        <form
            class="admin-post-filter-form"
            action="{{ route('admin.posts.index') }}"
            method="GET"
            data-post-filter-form
            autocomplete="off"
        >
            <input
                type="hidden"
                name="today"
                value="1"
                data-post-today-filter
                @disabled(! $today)
            >

            <div class="admin-post-filter-field admin-post-filter-search">
                <label for="post-filter-search">جستجو</label>
                <div class="admin-post-search-wrap">
                    <input
                        class="form-control"
                        id="post-filter-search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="عنوان، اسلاگ یا خلاصه خبر..."
                        data-post-search-input
                    >
                    <span aria-hidden="true">⌕</span>
                </div>
            </div>

            <div class="admin-post-filter-field">
                <label for="post-filter-status">وضعیت</label>
                <select class="form-select" id="post-filter-status" name="status" data-post-auto-filter>
                    <option value="">همه وضعیت‌ها</option>
                    @foreach (\App\Models\Post::STATUSES as $itemStatus)
                        <option value="{{ $itemStatus }}" @selected($status === $itemStatus)>
                            {{ \App\Models\Post::statusLabels()[$itemStatus] ?? $itemStatus }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-post-filter-field">
                <label for="post-filter-type">نوع محتوا</label>
                <select class="form-select" id="post-filter-type" name="type" data-post-auto-filter>
                    <option value="">همه نوع‌ها</option>
                    @foreach (\App\Models\Post::TYPES as $itemType)
                        <option value="{{ $itemType }}" @selected($type === $itemType)>
                            {{ \App\Models\Post::typeLabels()[$itemType] ?? $itemType }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-post-filter-field">
                <label for="post-filter-union">اتحادیه</label>
                <select class="form-select" id="post-filter-union" name="union_id" data-post-auto-filter>
                    <option value="">همه اتحادیه‌ها</option>
                    @foreach($filterUnions as $filterUnion)
                        <option value="{{ $filterUnion->id }}" @selected((int) $unionId === (int) $filterUnion->id)>
                            {{ $filterUnion->display_title }}{{ $filterUnion->is_active ? '' : ' — غیرفعال' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-post-filter-field">
                <label for="post-filter-category">دسته‌بندی</label>
                <select class="form-select" id="post-filter-category" name="category_id" data-post-auto-filter>
                    <option value="">همه دسته‌بندی‌ها</option>
                    @foreach($filterCategories as $filterCategory)
                        <option value="{{ $filterCategory->id }}" @selected((int) $categoryId === (int) $filterCategory->id)>
                            {{ $filterCategory->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-post-filter-field">
                <label for="post-filter-homepage">جایگاه صفحه اصلی</label>
                <select class="form-select" id="post-filter-homepage" name="homepage_position" data-post-auto-filter>
                    <option value="">همه جایگاه‌ها</option>
                    @foreach($homepagePositionLabels as $positionKey => $positionLabel)
                        <option value="{{ $positionKey }}" @selected($homepagePosition === $positionKey)>
                            {{ $positionLabel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-post-filter-field admin-post-date-field">
                <label for="post-filter-from">از تاریخ انتشار</label>
                <div class="admin-post-date-wrap">
                    <input
                        class="form-control"
                        id="post-filter-from"
                        name="from"
                        type="text"
                        value="{{ $from }}"
                        placeholder="۱۴۰۴/۰۱/۰۱"
                        data-jalali-datepicker
                        data-jalali-date-only
                        data-post-date-filter
                    >
                    <span aria-hidden="true">▣</span>
                </div>
            </div>

            <div class="admin-post-filter-field admin-post-date-field">
                <label for="post-filter-to">تا تاریخ انتشار</label>
                <div class="admin-post-date-wrap">
                    <input
                        class="form-control"
                        id="post-filter-to"
                        name="to"
                        type="text"
                        value="{{ $to }}"
                        placeholder="۱۴۰۴/۱۲/۲۹"
                        data-jalali-datepicker
                        data-jalali-date-only
                        data-post-date-filter
                    >
                    <span aria-hidden="true">▣</span>
                </div>
            </div>

            <div class="admin-post-filter-actions">
                <button class="admin-primary-btn" type="submit" data-post-filter-submit>
                    <span data-post-filter-submit-text>اعمال فیلتر</span>
                </button>

                <a
                    class="admin-secondary-btn {{ $hasFilters ? '' : 'disabled' }}"
                    href="{{ route('admin.posts.index') }}"
                    data-post-clear-filters
                    @if(! $hasFilters) aria-disabled="true" tabindex="-1" @endif
                >
                    پاک کردن فیلترها
                </a>
            </div>
        </form>

        <div class="admin-post-ajax-status" data-post-filter-status aria-live="polite" role="status"></div>
    </div>

    <div
        class="admin-post-results"
        data-post-results
        aria-live="polite"
        aria-busy="false"
    >
        @include('admin.posts.partials.results')
    </div>
</div>

<style>
.admin-post-filters-card{padding:22px}.admin-post-filter-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:16px}.admin-post-filter-head strong{font-size:1rem;color:#1d3347}.admin-post-filter-head p{margin:4px 0 0;color:#718096;font-size:.82rem}.admin-post-filter-count{white-space:nowrap;background:#edf6ff;color:#1769aa;border:1px solid #cfe7fb;border-radius:999px;padding:6px 10px;font-size:.78rem;font-weight:700}.admin-post-shortcuts{display:flex;flex-wrap:wrap;gap:8px;padding-bottom:18px;margin-bottom:18px;border-bottom:1px solid #e8edf2}.admin-post-shortcut{display:inline-flex;align-items:center;gap:6px;min-height:36px;padding:7px 11px;border:1px solid #dbe3ea;border-radius:9px;background:#fff;color:#40566b;text-decoration:none;font-size:.8rem;font-weight:650;transition:.16s ease}.admin-post-shortcut:hover{border-color:#9fc9ea;color:#0d6fae;background:#f8fcff}.admin-post-shortcut span{min-width:22px;text-align:center;padding:1px 6px;border-radius:999px;background:#f0f4f7;font-size:.72rem}.admin-post-shortcut.is-active{background:#1679b8;color:#fff;border-color:#1679b8}.admin-post-shortcut.is-active span{background:rgba(255,255,255,.18);color:#fff}.admin-post-filter-form{display:grid;grid-template-columns:minmax(220px,1.6fr) repeat(2,minmax(150px,1fr));gap:14px 12px;align-items:end}.admin-post-filter-field{min-width:0}.admin-post-filter-field label{display:block;margin-bottom:6px;color:#40566b;font-size:.78rem;font-weight:700}.admin-post-filter-field .form-control,.admin-post-filter-field .form-select{min-height:42px;border-color:#d8e1e8;border-radius:9px;font-size:.82rem}.admin-post-filter-field .form-control:focus,.admin-post-filter-field .form-select:focus{border-color:#77b9e4;box-shadow:0 0 0 3px rgba(32,142,207,.1)}.admin-post-search-wrap,.admin-post-date-wrap{position:relative}.admin-post-search-wrap>span,.admin-post-date-wrap>span{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#8ba0b2;pointer-events:none}.admin-post-search-wrap input,.admin-post-date-wrap input{padding-left:36px}.admin-post-filter-actions{display:flex;gap:8px;align-items:center;grid-column:span 3}.admin-post-filter-actions .admin-primary-btn,.admin-post-filter-actions .admin-secondary-btn{min-width:132px;justify-content:center}.admin-post-filter-actions .disabled{opacity:.5;pointer-events:none}.admin-post-ajax-status{min-height:20px;margin-top:10px;color:#668096;font-size:.78rem}.admin-post-results{position:relative;transition:opacity .18s ease}.admin-post-results.is-loading{opacity:.45;pointer-events:none}.admin-post-results-error{margin-bottom:14px}.admin-post-results-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}.admin-post-results-count{font-size:.8rem;color:#687d8f}.admin-post-results-count strong{color:#223b50}.admin-post-empty-filter{padding:34px 16px;text-align:center}.admin-post-empty-filter strong{display:block;color:#334d62;margin-bottom:6px}.admin-post-empty-filter span{color:#8393a2;font-size:.82rem}@media(max-width:1100px){.admin-post-filter-form{grid-template-columns:repeat(2,minmax(0,1fr))}.admin-post-filter-search{grid-column:span 2}.admin-post-filter-actions{grid-column:span 2}}@media(max-width:720px){.admin-post-filters-card{padding:16px}.admin-post-filter-head{flex-direction:column}.admin-post-filter-form{grid-template-columns:1fr}.admin-post-filter-search,.admin-post-filter-actions{grid-column:span 1}.admin-post-filter-actions{flex-direction:column;align-items:stretch}.admin-post-filter-actions .admin-primary-btn,.admin-post-filter-actions .admin-secondary-btn{width:100%}.admin-post-shortcuts{flex-wrap:nowrap;overflow-x:auto;padding-bottom:12px}.admin-post-shortcut{white-space:nowrap;flex:none}}
</style>
@endsection

@push('scripts')
@php
    $postsFilterScriptPath = public_path('assets/admin/js/posts-index-filters.js');
    $postsFilterScriptVersion = is_file($postsFilterScriptPath) ? filemtime($postsFilterScriptPath) : '1';
@endphp
<script src="{{ asset('assets/admin/js/posts-index-filters.js') }}?v={{ $postsFilterScriptVersion }}"></script>
@endpush
