@extends('frontend.layouts.app')

@section('title', 'سامانه‌های صنفی اتاق اصناف استان گلستان')
@section('meta_description', 'دسترسی به سامانه‌ها و درگاه‌های الکترونیکی مرتبط با مجوزهای صنفی، شکایات، بازرسی، آموزش و اطلاع‌رسانی اتحادیه‌های استان گلستان.')
@section('canonical', $systems->currentPage() === 1 ? route('systems.index', \Illuminate\Support\Arr::except(request()->query(), 'page')) : $systems->url($systems->currentPage()))

@section('content')
<div class="systems-directory-page" data-systems-directory>
    <header class="page-header systems-directory-header">
        <div class="site-container">
            <nav class="breadcrumb-nav" aria-label="مسیر صفحه">
                <a href="{{ route('home') }}">خانه</a>
                <span class="breadcrumb-sep" aria-hidden="true">/</span>
                <span aria-current="page">سامانه‌ها</span>
            </nav>
            <h1>سامانه‌ها</h1>
            <p>دسترسی سریع به سامانه‌ها و درگاه‌های پرکاربرد مرتبط با امور صنفی</p>
        </div>
    </header>

    <main class="systems-directory-main">
        <div class="site-container">
            <section class="systems-directory-toolbar" aria-labelledby="systems-directory-heading">
                <div class="systems-directory-heading">
                    <span>فهرست سامانه‌ها</span>
                    <h2 id="systems-directory-heading" tabindex="-1">سامانه‌های صنفی</h2>
                    <p>سامانه موردنیاز خود را جستجو کنید و به درگاه معتبر آن دسترسی داشته باشید.</p>
                </div>
                <div class="systems-directory-count" aria-label="{{ fa_number($activeTotal) }} سامانه فعال">
                    <strong data-systems-active-count>{{ fa_number($activeTotal) }}</strong>
                    <span>سامانه فعال</span>
                </div>
            </section>

            <section class="systems-filter-toolbar" aria-label="جستجو و فیلتر سامانه‌ها">
                <div class="systems-filter-row">
                    <form class="systems-directory-search" action="{{ route('systems.index') }}" method="GET" role="search" data-systems-search-form>
                        <label class="visually-hidden" for="systemDirectorySearch">جستجو در سامانه‌ها</label>
                        <input id="systemDirectorySearch" name="search" value="{{ $search }}" type="search" placeholder="نام یا کاربرد سامانه را جستجو کنید..." autocomplete="off" data-system-search>
                        @if($activeCategory !== '')<input type="hidden" name="category" value="{{ $activeCategory }}">@endif
                        <button type="submit">جستجو</button>
                    </form>
                    <p data-systems-result-count>{{ fa_number($systems->total()) }} {{ $search !== '' || $activeCategory !== '' ? 'سامانه یافت شد' : 'سامانه در فهرست' }}</p>
                    <a class="systems-directory-clear" href="{{ route('systems.index') }}" data-systems-clear @if($search === '' && $activeCategory === '') hidden @endif>پاک‌کردن فیلترها</a>
                </div>
                @include('frontend.systems.partials.category-tabs')
            </section>

            @include('frontend.systems.partials.results')
        </div>
    </main>
</div>
@endsection
