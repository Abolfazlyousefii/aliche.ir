<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1" name="viewport"/>
@php
    $settings = app(\App\Services\SettingService::class);
    $siteTitle = $settings->get('site.site_title', 'اتاق اصناف مرکز استان گلستان');
    $favicon = $settings->get('site.site_favicon') ? image_url($settings->get('site.site_favicon'), '') : null;
@endphp
<title>@yield('title', $siteTitle)</title>
<meta content="@yield('meta_description', 'اتاق اصناف مرکز استان گلستان')" name="description"/>
@if($favicon)<link rel="icon" href="{{ $favicon }}">@endif
@hasSection('canonical')<link rel="canonical" href="@yield('canonical')"/>@endif
@include('frontend.partials.styles')
@stack('styles')
</head>
<body>
@include('frontend.partials.header')
@include('frontend.partials.market-ticker')
@yield('content')
@include('frontend.partials.footer')
@yield('after_footer')
@include('frontend.partials.scripts')
@stack('scripts')
</body>
</html>
