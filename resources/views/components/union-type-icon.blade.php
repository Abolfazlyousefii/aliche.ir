@props([
    'icon' => 'storefront',
    'label' => null,
])

@php
    $iconKey = in_array($icon, [
        'factory',
        'cart',
        'briefcase',
        'target',
        'storefront',
        'tools',
        'food',
        'transport',
    ], true) ? $icon : 'storefront';
@endphp

<svg
    {{ $attributes->merge(['class' => 'union-type-icon']) }}
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.8"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    @if($label) role="img" aria-label="{{ $label }}" @endif
>
    @switch($iconKey)
        @case('factory')
            <path d="M3 21V10l5 3V9l5 3V5h4v16"/>
            <path d="M2 21h20M7 17h2M12 17h2M17 17h2"/>
            @break
        @case('cart')
            <circle cx="9" cy="20" r="1"/>
            <circle cx="18" cy="20" r="1"/>
            <path d="M3 4h2l2.4 10.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 8H7"/>
            @break
        @case('briefcase')
            <rect x="3" y="7" width="18" height="13" rx="2"/>
            <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18M10 12v2h4v-2"/>
            @break
        @case('target')
            <circle cx="12" cy="12" r="8"/>
            <circle cx="12" cy="12" r="4"/>
            <path d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
            @break
        @case('tools')
            <path d="m14.7 6.3 3-3a4 4 0 0 1-5 5l-7.8 7.8a2.1 2.1 0 0 0 3 3l7.8-7.8a4 4 0 0 1 5-5l-3 3"/>
            <path d="m5 4 4 4"/>
            @break
        @case('food')
            <path d="M7 3v8M4 3v5a3 3 0 0 0 6 0V3M7 11v10M16 3v18M16 3c3 2 4 5 4 8h-4"/>
            @break
        @case('transport')
            <path d="M3 17h18M5 17V8a2 2 0 0 1 2-2h7l4 4h1a2 2 0 0 1 2 2v5"/>
            <circle cx="8" cy="17" r="2"/>
            <circle cx="17" cy="17" r="2"/>
            @break
        @default
            <path d="M3 10h18M5 10v10h14V10M4 10l2-6h12l2 6"/>
            <path d="M9 20v-6h6v6"/>
    @endswitch
</svg>
