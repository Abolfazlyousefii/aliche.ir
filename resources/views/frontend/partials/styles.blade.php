@php
    $mainStylesPath = public_path('assets/css/styles.css');
    $layoutLockPath = public_path('assets/css/home-layout-lock.css');
    $mainStylesVersion = is_file($mainStylesPath) ? filemtime($mainStylesPath) : '1';
    $layoutLockVersion = is_file($layoutLockPath) ? filemtime($layoutLockPath) : '1';
@endphp
<link href="https://cdn.jsdelivr.net" rel="preconnect"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet"/>
<link href="{{ asset('assets/css/styles.css') }}?v={{ $mainStylesVersion }}" rel="stylesheet"/>
<link href="{{ asset('assets/css/home-layout-lock.css') }}?v={{ $layoutLockVersion }}" rel="stylesheet"/>

@if(request()->routeIs('guilds.show'))
    @php
        $guildProfileStylesPath = public_path('assets/css/guild-profile.css');
        $guildProfileStylesVersion = is_file($guildProfileStylesPath) ? filemtime($guildProfileStylesPath) : '1';
    @endphp
    <link href="{{ asset('assets/css/guild-profile.css') }}?v={{ $guildProfileStylesVersion }}" rel="stylesheet"/>
@endif

@if(request()->routeIs('posts.index'))
    @php
        $newsArchivePaginationStylesPath = public_path('assets/css/news-archive-pagination.css');
        $newsArchivePaginationStylesVersion = is_file($newsArchivePaginationStylesPath)
            ? filemtime($newsArchivePaginationStylesPath)
            : '1';
    @endphp
    <link href="/assets/css/news-archive-pagination.css?v={{ $newsArchivePaginationStylesVersion }}" rel="stylesheet"/>
@endif
