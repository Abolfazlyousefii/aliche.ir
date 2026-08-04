@php
    $location = $location ?? 'main';
    $variant = $variant ?? 'classic';
    $items = app(\App\Services\MenuService::class)->items($location);
    $linkClass = $variant === 'compact' ? 'top-nav-link' : 'nav-link';
    $itemClass = $variant === 'compact' ? 'top-nav-item' : 'nav-item';
@endphp

@foreach ($items as $menuItem)
    @include('frontend.partials.dynamic-menu-item', ['menuItem' => $menuItem, 'variant' => $variant, 'itemClass' => $itemClass, 'linkClass' => $linkClass])
@endforeach
