@extends('frontend.layouts.app')

@section('title', 'گالری تصاویر و ویدئوهای اتاق اصناف استان گلستان')
@section('meta_description', 'مجموعه تصاویر، ویدئوها و گزارش‌های تصویری رویدادها، جلسات و فعالیت‌های اتاق اصناف و اتحادیه‌های صنفی استان گلستان.')
@section('canonical', $galleries->currentPage() === 1 ? route('galleries.index', \Illuminate\Support\Arr::except(request()->query(), 'page')) : $galleries->url($galleries->currentPage()))

@section('content')
<div class="galleries-directory-page" data-galleries-directory>
    <header class="page-header galleries-directory-header">
        <div class="site-container">
            <nav class="breadcrumb-nav" aria-label="مسیر صفحه">
                <a href="{{ route('home') }}">خانه</a>
                <span class="breadcrumb-sep" aria-hidden="true">/</span>
                <span aria-current="page">گالری</span>
            </nav>
            <h1>گالری تصاویر و ویدئوها</h1>
            <p>مجموعه تصاویر، ویدئوها و گزارش‌های تصویری رویدادها و فعالیت‌های اتاق اصناف</p>
        </div>
    </header>

    <main class="galleries-directory-main">
        <div class="site-container">
            <section class="galleries-directory-toolbar" aria-labelledby="galleries-directory-heading">
                <div class="galleries-directory-heading">
                    <span>آرشیو رسانه‌ای</span>
                    <h2 id="galleries-directory-heading" tabindex="-1">گالری رویدادها</h2>
                    <p>گزارش‌های تصویری رویدادها، جلسات و فعالیت‌های صنفی را مرور کنید.</p>
                </div>
                <div class="galleries-directory-count" aria-label="{{ fa_number($galleries->total()) }} آلبوم">
                    <strong data-galleries-count>{{ fa_number($galleries->total()) }}</strong>
                    <span>آلبوم</span>
                </div>
            </section>

            <section class="galleries-filter-toolbar" aria-label="جستجو و فیلتر گالری‌ها">
                <div class="galleries-filter-row">
                    <form class="galleries-directory-search" action="{{ route('galleries.index') }}" method="GET" role="search" data-galleries-search-form>
                        <label class="visually-hidden" for="galleryDirectorySearch">جستجو در گالری‌ها</label>
                        <input id="galleryDirectorySearch" name="search" value="{{ $search }}" type="search" placeholder="نام رویداد یا گالری را جستجو کنید..." autocomplete="off" data-gallery-search>
                        @if($type !== '')<input type="hidden" name="type" value="{{ $type }}">@endif
                        <button type="submit">جستجو</button>
                    </form>
                    <a class="galleries-directory-clear" href="{{ route('galleries.index') }}" data-galleries-clear @if($search === '' && $type === '') hidden @endif>پاک‌کردن فیلترها</a>
                </div>
                @include('frontend.galleries.partials.type-tabs')
            </section>

            @include('frontend.galleries.partials.results')
        </div>
    </main>
</div>
@endsection
