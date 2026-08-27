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

        $hasAdvancedFilters = $type !== ''
            || filled($categoryId)
            || filled($unionId)
            || $homepagePosition !== ''
            || $from !== ''
            || $to !== '';

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
                <strong>جستجو و فیلتر اخبار</strong>
                <p>جستجو به‌صورت زنده انجام می‌شود؛ فیلترهای تکمیلی را فقط در صورت نیاز باز کنید.</p>
            </div>

            <div class="admin-post-filter-head-actions">
                <span
                    class="admin-post-filter-count"
                    data-post-filter-count
                    @if(! $hasFilters) hidden @endif
                >{{ fa_number($activeFilterCount) }} فیلتر فعال</span>

                <button
                    class="admin-post-filter-toggle"
                    type="button"
                    data-post-filter-toggle
                    aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}"
                    aria-controls="adminPostAdvancedFilters"
                >
                    <span data-post-filter-toggle-text>{{ $hasAdvancedFilters ? 'بستن فیلترها' : 'فیلترهای بیشتر' }}</span>
                    <span class="admin-post-filter-toggle-icon" aria-hidden="true">⌄</span>
                </button>
            </div>
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

            <div class="admin-post-live-search-row">
                <div class="admin-post-filter-field admin-post-filter-search">
                    <label for="post-filter-search">جستجوی زنده</label>
                    <div class="admin-post-search-wrap">
                        <input
                            class="form-control"
                            id="post-filter-search"
                            name="search"
                            type="search"
                            value="{{ $search }}"
                            placeholder="عنوان، اسلاگ یا خلاصه خبر..."
                            data-post-search-input
                            spellcheck="false"
                        >
                        <span aria-hidden="true">⌕</span>
                    </div>
                </div>

                <a
                    class="admin-post-inline-clear {{ $hasFilters ? '' : 'is-disabled' }}"
                    href="{{ route('admin.posts.index') }}"
                    data-post-clear-filters
                    @if(! $hasFilters) aria-disabled="true" tabindex="-1" @endif
                >
                    پاک کردن
                </a>
            </div>

            <div
                class="admin-post-advanced-filters"
                id="adminPostAdvancedFilters"
                data-post-advanced-filters
                @if(! $hasAdvancedFilters) hidden @endif
            >
                <div class="admin-post-advanced-grid">
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
                </div>

                <div class="admin-post-filter-actions">
                    <button class="admin-primary-btn" type="submit" data-post-filter-submit>
                        اعمال فیلتر
                    </button>

                    <a
                        class="admin-secondary-btn {{ $hasFilters ? '' : 'disabled' }}"
                        href="{{ route('admin.posts.index') }}"
                        data-post-clear-filters
                        @if(! $hasFilters) aria-disabled="true" tabindex="-1" @endif
                    >
                        پاک کردن همه فیلترها
                    </a>
                </div>
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
.admin-post-filters-card{padding:18px 20px}
.admin-post-filter-head{display:flex;align-items:center;justify-content:space-between;gap:14px}
.admin-post-filter-head strong{font-size:.95rem;color:#1d3347}
.admin-post-filter-head p{margin:3px 0 0;color:#7a8fa2;font-size:.78rem}
.admin-post-filter-head-actions{display:flex;align-items:center;gap:8px}
.admin-post-filter-count{white-space:nowrap;background:#edf6ff;color:#1769aa;border:1px solid #cfe7fb;border-radius:999px;padding:6px 10px;font-size:.74rem;font-weight:750}
.admin-post-filter-toggle{display:inline-flex;align-items:center;gap:8px;min-height:36px;padding:7px 11px;border:1px solid #d8e3eb;border-radius:9px;background:#fff;color:#36566d;font:inherit;font-size:.78rem;font-weight:750;cursor:pointer;transition:.16s ease}
.admin-post-filter-toggle:hover{border-color:#86bee1;color:#116fa9;background:#f9fcff}
.admin-post-filter-toggle-icon{font-size:15px;line-height:1;transition:transform .18s ease}
.admin-post-filter-toggle[aria-expanded="true"] .admin-post-filter-toggle-icon{transform:rotate(180deg)}
.admin-post-shortcuts{display:flex;flex-wrap:nowrap;gap:7px;overflow-x:auto;margin:14px 0;padding:0 0 12px;border-bottom:1px solid #edf1f4;scrollbar-width:thin}
.admin-post-shortcut{display:inline-flex;align-items:center;gap:6px;flex:none;min-height:34px;padding:6px 10px;border:1px solid #dbe3ea;border-radius:9px;background:#fff;color:#40566b;text-decoration:none;font-size:.76rem;font-weight:680;transition:.16s ease;white-space:nowrap}
.admin-post-shortcut:hover{border-color:#9fc9ea;color:#0d6fae;background:#f8fcff}
.admin-post-shortcut span{min-width:21px;text-align:center;padding:1px 5px;border-radius:999px;background:#f0f4f7;font-size:.69rem}
.admin-post-shortcut.is-active{background:#1679b8;color:#fff;border-color:#1679b8}
.admin-post-shortcut.is-active span{background:rgba(255,255,255,.18);color:#fff}
.admin-post-live-search-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;align-items:end}
.admin-post-filter-field{min-width:0}
.admin-post-filter-field label{display:block;margin-bottom:5px;color:#40566b;font-size:.74rem;font-weight:720}
.admin-post-filter-field .form-control,.admin-post-filter-field .form-select{min-height:40px;border-color:#d8e1e8;border-radius:9px;font-size:.8rem}
.admin-post-filter-field .form-control:focus,.admin-post-filter-field .form-select:focus{border-color:#77b9e4;box-shadow:0 0 0 3px rgba(32,142,207,.1)}
.admin-post-search-wrap,.admin-post-date-wrap{position:relative}
.admin-post-search-wrap>span,.admin-post-date-wrap>span{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#8ba0b2;pointer-events:none}
.admin-post-search-wrap input,.admin-post-date-wrap input{padding-left:36px}
.admin-post-inline-clear{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 13px;border:1px solid #d8e1e8;border-radius:9px;color:#526d82;background:#fff;text-decoration:none;font-size:.76rem;font-weight:700}
.admin-post-inline-clear:hover{border-color:#a8c9df;color:#126fa8}
.admin-post-inline-clear.is-disabled{opacity:.45;pointer-events:none}
.admin-post-advanced-filters{margin-top:14px;padding-top:14px;border-top:1px solid #edf1f4}
.admin-post-advanced-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.admin-post-filter-actions{display:flex;gap:8px;margin-top:12px;align-items:center}
.admin-post-filter-actions .admin-primary-btn,.admin-post-filter-actions .admin-secondary-btn{min-width:128px;justify-content:center}
.admin-post-filter-actions .disabled{opacity:.5;pointer-events:none}
.admin-post-ajax-status{min-height:18px;margin-top:7px;color:#688096;font-size:.73rem}
.admin-post-results{position:relative;transition:opacity .18s ease}
.admin-post-results.is-loading{opacity:.48;pointer-events:none}
.admin-post-results-error{margin-bottom:12px}
.admin-post-results-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px}
.admin-post-results-count{font-size:.78rem;color:#687d8f}
.admin-post-results-count strong{color:#223b50}
.admin-post-empty-filter{padding:34px 16px;text-align:center}
.admin-post-empty-filter strong{display:block;color:#334d62;margin-bottom:6px}
.admin-post-empty-filter span{color:#8393a2;font-size:.82rem}
.admin-post-infinite-status{display:flex;align-items:center;justify-content:center;min-height:54px;padding:12px;color:#75899a;font-size:.76rem}
.admin-post-infinite-status[hidden]{display:none}
.admin-post-infinite-spinner{width:18px;height:18px;margin-left:8px;border:2px solid #dbe6ee;border-top-color:#2584bd;border-radius:50%;animation:admin-post-spin .7s linear infinite}
@keyframes admin-post-spin{to{transform:rotate(360deg)}}
@media(max-width:1000px){.admin-post-advanced-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:720px){.admin-post-filters-card{padding:15px}.admin-post-filter-head{align-items:flex-start;flex-direction:column}.admin-post-filter-head-actions{width:100%;justify-content:space-between}.admin-post-shortcuts{margin-top:12px}.admin-post-live-search-row{grid-template-columns:1fr}.admin-post-inline-clear{display:none}.admin-post-advanced-grid{grid-template-columns:1fr}.admin-post-filter-actions{flex-direction:column;align-items:stretch}.admin-post-filter-actions .admin-primary-btn,.admin-post-filter-actions .admin-secondary-btn{width:100%}}
</style>
@endsection

@push('scripts')
@php
    $postsFilterScriptPath = public_path('assets/admin/js/posts-index-filters.js');
    $postsFilterScriptVersion = is_file($postsFilterScriptPath) ? filemtime($postsFilterScriptPath) : '1';
@endphp
<script src="{{ asset('assets/admin/js/posts-index-filters.js') }}?v={{ $postsFilterScriptVersion }}"></script>
@endpush
