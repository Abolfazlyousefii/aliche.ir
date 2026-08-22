@props(['name' => 'link'])

@php($icon = strtolower(trim((string) $name)))

<svg {{ $attributes->merge(['class' => 'ui-icon', 'aria-hidden' => 'true', 'viewBox' => '0 0 24 24']) }}>
    @switch($icon)
        @case('instagram')
            <rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.7" r=".8" class="ui-icon__fill"/>
            @break
        @case('telegram')
            <path d="m3 11 17-7-4.4 16-5.1-5-3.2 2.5.8-5.3L17 7.1 9.7 13"/>
            @break
        @case('whatsapp')
            <path d="M20 11.6a8 8 0 0 1-11.7 7.1L4 20l1.3-4.1A8 8 0 1 1 20 11.6Z"/><path d="M9 8.2c.4 3 2.2 4.8 5.1 5.7l1.2-1.3 2 .9c-.5 1.5-1.5 2.2-3 2-3.8-.7-6.4-3.3-7.2-7.1-.2-1.5.5-2.5 2-3l1 2-1.1.8Z"/>
            @break
        @case('eitaa')
            <circle cx="12" cy="12" r="9"/><path d="M8 8.5h7.5L14 12H8l1.5 3.5H16"/>
            @break
        @case('bale')
            <path d="M4 5h16v11H9l-5 4V5Z"/><path d="m8 11 2.2 2.1L16 8"/>
            @break
        @case('rubika')
            <path d="m12 3 7 4v9l-7 5-7-5V7l7-4Z"/><path d="m5 7 7 4 7-4M12 11v10"/>
            @break
        @case('website')
        @case('globe')
            <circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.7 2.7 4 5.7 4 9s-1.3 6.3-4 9c-2.7-2.7-4-5.7-4-9s1.3-6.3 4-9Z"/>
            @break
        @case('phone')
            <path d="M7 3 4 6c0 6 8 14 14 14l3-3-4-4-3 2c-2-1-4-3-5-5l2-3-4-4Z"/>
            @break
        @case('mobile')
            <rect x="7" y="2.5" width="10" height="19" rx="2"/><path d="M10 5h4M11 18.5h2"/>
            @break
        @case('email')
            <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>
            @break
        @case('external')
            <path d="M13 5h6v6M19 5l-9 9"/><path d="M18 14v5H5V6h5"/>
            @break
        @case('document')
        @case('rules')
            <path d="M6 3h9l4 4v14H6V3Z"/><path d="M15 3v5h5M9 13h7M9 17h5"/>
            @break
        @case('message')
            <path d="M4 5h16v12H9l-5 4V5Z"/><path d="M8 9h8M8 13h5"/>
            @break
        @case('education')
            <path d="m3 9 9-5 9 5-9 5-9-5Z"/><path d="M6 11v5c3 2 9 2 12 0v-5"/>
            @break
        @case('commission')
        @case('briefcase')
            <rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V4h8v3M3 12h18M10 12v2h4v-2"/>
            @break
        @default
            <path d="M8 12a4 4 0 0 0 4 4h4a4 4 0 0 0 0-8h-2M16 12a4 4 0 0 0-4-4H8a4 4 0 0 0 0 8h2"/>
    @endswitch
</svg>
