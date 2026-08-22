@extends('frontend.layouts.app')

@section('title', 'اتحادیه‌های صنفی استان گلستان')
@section('meta_description', 'فهرست اتحادیه‌های صنفی فعال شهرستان گرگان، اطلاعات مدیران، شماره‌های تماس و راه‌های ارتباط با هر اتحادیه.')
@section('canonical', $unions->url($unions->currentPage()))

@section('content')
<div class="guilds-directory-page" data-guilds-directory>
    <header class="page-header guilds-directory-header">
        <div class="site-container">
            <nav class="breadcrumb-nav" aria-label="مسیر صفحه">
                <a href="{{ route('home') }}">خانه</a>
                <span class="breadcrumb-sep" aria-hidden="true">/</span>
                <span aria-current="page">اتحادیه‌ها</span>
            </nav>
            <h1>اتحادیه‌های صنفی</h1>
            <p>فهرست اتحادیه‌های فعال شهرستان گرگان و راه‌های ارتباط با آن‌ها</p>
        </div>
    </header>

    <main class="guilds-directory-main">
        <div class="site-container">
            <section class="guilds-directory-toolbar" aria-labelledby="guilds-directory-heading">
                <div class="guilds-directory-heading">
                    <h2 id="guilds-directory-heading" tabindex="-1">فهرست اتحادیه‌های فعال</h2>
                    <p data-guilds-result-count>{{ fa_number($unions->total()) }} {{ $search !== '' || $type !== '' || $categoryId ? 'اتحادیه یافت شد' : 'اتحادیه فعال' }}</p>
                </div>

                <form class="guilds-directory-search" action="{{ route('guilds.index') }}" method="GET" role="search" data-guilds-search-form>
                    <label class="visually-hidden" for="guildDirectorySearch">جستجو در اتحادیه‌ها</label>
                    <input id="guildDirectorySearch" name="search" value="{{ $search }}" type="search" placeholder="نام اتحادیه، مدیر یا شماره تماس..." autocomplete="off" data-guild-search>
                    @if($type !== '')<input type="hidden" name="type" value="{{ $type }}">@endif
                    @if($categoryId)<input type="hidden" name="category_id" value="{{ $categoryId }}">@endif
                    <button type="submit">جستجو</button>
                </form>

                <a class="guilds-directory-clear" href="{{ route('guilds.index') }}" data-guilds-clear @if($search === '' && $type === '' && !$categoryId) hidden @endif>پاک‌کردن فیلترها</a>
            </section>

            @include('frontend.guilds.partials.type-tabs')
            @include('frontend.guilds.partials.results')
        </div>
    </main>
</div>
@endsection
