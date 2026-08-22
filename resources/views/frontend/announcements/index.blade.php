@extends('frontend.layouts.app')

@section('title', 'اطلاعیه‌های اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'آخرین اطلاعیه‌ها، بخشنامه‌ها، فراخوان‌ها و اطلاع‌رسانی‌های اتاق اصناف و اتحادیه‌های صنفی استان گلستان.')
@section('canonical', $announcements->url($announcements->currentPage()))

@section('content')
<div class="announcements-archive-page" data-announcements-archive>
    <header class="page-header announcements-page-header">
        <div class="site-container">
            <nav class="breadcrumb-nav" aria-label="مسیر صفحه">
                <a href="{{ route('home') }}">خانه</a>
                <span class="breadcrumb-sep" aria-hidden="true">/</span>
                <span aria-current="page">اطلاعیه‌ها</span>
            </nav>
            <h1>اطلاعیه‌های اتاق اصناف</h1>
            <p>آخرین بخشنامه‌ها، فراخوان‌ها و اطلاع‌رسانی‌های عمومی و صنفی</p>
        </div>
    </header>

    <main class="announcements-archive-main">
        <div class="site-container">
            <section class="announcements-toolbar" aria-labelledby="announcements-heading">
                <div class="announcements-toolbar-heading">
                    <h2 id="announcements-heading">همه اطلاعیه‌های فعال</h2>
                    <p>{{ fa_number($announcements->total()) }} {{ $hasActiveFilters ? 'اطلاعیه یافت شد' : 'اطلاعیه فعال' }}</p>
                </div>

                <form class="announcements-filters" action="{{ route('announcements.index') }}" method="GET" role="search">
                    <div class="announcements-filter-field announcements-search-field">
                        <label for="announcementSearch">جستجو</label>
                        <input id="announcementSearch" name="search" value="{{ $search }}" placeholder="عنوان یا متن اطلاعیه..." type="search">
                    </div>

                    <button class="announcements-filter-toggle" type="button" aria-expanded="false" aria-controls="announcementsFilterPanel" data-announcements-filter-toggle>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                        <span>فیلتر اطلاعیه‌ها</span>
                    </button>

                    <div class="announcements-filter-panel" id="announcementsFilterPanel" data-announcements-filter-panel>
                        <div class="announcements-filter-field announcements-category-field">
                            <label for="announcementCategory">دسته‌بندی</label>
                            <select id="announcementCategory" name="category_id">
                                <option value="">همه دسته‌بندی‌های اطلاعیه</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="announcements-filter-field announcements-union-field">
                            <label for="announcementUnion">اتحادیه</label>
                            <select id="announcementUnion" name="union_id">
                                <option value="">همه اتحادیه‌ها و اطلاعیه‌های عمومی</option>
                                @foreach($unions as $union)
                                    <option value="{{ $union->id }}" @selected((string) $unionId === (string) $union->id)>{{ $union->display_title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="announcements-filter-actions">
                            <button type="submit">اعمال فیلتر</button>
                            @if($hasActiveFilters)
                                <a href="{{ route('announcements.index') }}">پاک‌کردن فیلترها</a>
                            @endif
                        </div>
                    </div>
                </form>
            </section>

            @include('frontend.announcements.partials.results')
        </div>
    </main>
</div>
@endsection
