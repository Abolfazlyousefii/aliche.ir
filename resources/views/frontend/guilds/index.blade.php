@extends('frontend.layouts.app')

@section('title', 'اتحادیه‌های صنفی | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'فهرست اتحادیه‌های صنفی فعال استان گلستان بر اساس نوع و دسته‌بندی')

@php
    $assetImage = fn (?string $path) => image_url($path, 'assets/img/asnaf-gorgan-default.jpg');
    $typeLabels = ($unionTypes ?? collect())->pluck('title', 'slug')->all() ?: \App\Models\GuildUnion::typeLabels();
    $hasTypeTabs = ($typeTabs ?? collect())->filter(fn ($tab) => collect($tab['items'] ?? [])->isNotEmpty())->isNotEmpty();
@endphp

@section('content')
<div class="page-header">
    <div class="site-container">
        <nav class="breadcrumb-nav"><a href="{{ route('home') }}">خانه</a><span class="breadcrumb-sep">/</span><span>اتحادیه‌ها</span></nav>
        <h1>اتحادیه‌های صنفی</h1>
    </div>
</div>

<main class="archive-page">
    <div class="site-container">
        <div class="archive-header"><h1>فهرست اتحادیه‌های فعال</h1></div>

        <form class="archive-filters archive-filter-panel" action="{{ route('guilds.index') }}" method="GET">
            <div class="archive-filter-field archive-filter-search">
                <label for="guildSearch">جستجو</label>
                <input id="guildSearch" class="form-control" name="search" value="{{ $search }}" placeholder="نام اتحادیه یا مدیر..." type="search">
            </div>
            <div class="archive-filter-field">
                <label for="guildType">نوع اتحادیه</label>
                <select id="guildType" class="form-control" name="type" aria-label="فیلتر نوع اتحادیه">
                    <option value="">همه نوع‌ها</option>
                    @foreach($typeLabels as $key => $label)
                        <option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="archive-filter-field">
                <label for="guildCategory">دسته‌بندی</label>
                <select id="guildCategory" class="form-control" name="category_id" aria-label="فیلتر دسته‌بندی اتحادیه">
                    <option value="">همه دسته‌بندی‌ها</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="archive-filter-actions">
                <button class="tab-pill active" type="submit">اعمال فیلتر</button>
                @if ($search !== '' || $type !== '' || $categoryId !== '')<a class="tab-pill" href="{{ route('guilds.index') }}">حذف فیلتر</a>@endif
            </div>
        </form>


        @if ($search === '' && $type === '' && $categoryId === '' && $hasTypeTabs)
            <div class="media-tabs" data-tab-group="guild-types">
                @foreach ($typeTabs as $key => $tab)
                    <button class="media-tab @if($loop->first) active @endif" data-tab-target="guild-type-{{ $key }}" type="button">{{ trim(($tab['icon'] ?? '').' '.$tab['label']) }}</button>
                @endforeach
            </div>
            <div class="tab-panels" data-tab-panels="guild-types">
                @foreach ($typeTabs as $key => $tab)
                    <div class="tab-panel @if($loop->first) active @endif" data-tab-panel="guild-type-{{ $key }}">
                        <div class="archive-grid">
                            @forelse ($tab['items'] as $union)
                                @include('frontend.guilds.partials.card', ['union' => $union, 'assetImage' => $assetImage])
                            @empty
                                <p class="text-center">در این نوع اتحادیه فعالی ثبت نشده است.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="archive-grid">
                @forelse ($unions as $union)
                    @include('frontend.guilds.partials.card', ['union' => $union, 'assetImage' => $assetImage])
                @empty
                    <p class="text-center">اتحادیه فعالی با معیارهای انتخاب‌شده یافت نشد.</p>
                @endforelse
            </div>
            {{ $unions->links('frontend.partials.pagination') }}
        @endif
    </div>
</main>
@endsection
