@php
    $storedIcon = trim((string) $system->icon);
    $title = (string) $system->title;
    $iconType = match (true) {
        $storedIcon === '🎓' || str_contains($title, 'آموزش') => 'education',
        $storedIcon === '📨' || str_contains($title, 'شکایت') => 'complaint',
        $storedIcon === '🔍' || str_contains($title, 'بازرسی') || str_contains($title, 'استعلام') => 'inspection',
        $storedIcon === '📢' || str_contains($title, 'اطلاع') => 'notice',
        $storedIcon === '🌐' => 'globe',
        $storedIcon === '🧾' => 'receipt',
        $storedIcon === '⚖️' => 'legal',
        $storedIcon === '🔐' => 'lock',
        $storedIcon === '📊' => 'chart',
        $storedIcon === '🏢' => 'building',
        $storedIcon === '✅' || str_contains($title, 'مجوز') => 'license',
        default => 'system',
    };
@endphp
@switch($iconType)
    @case('education')<svg viewBox="0 0 32 32" aria-hidden="true"><path d="m4 12 12-6 12 6-12 6-12-6zm5 3v7c4 3 10 3 14 0v-7m5-3v9"/></svg>@break
    @case('complaint')<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M6 5h20v18H14l-6 5v-5H6V5zm5 6h10m-10 5h7"/></svg>@break
    @case('inspection')<svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="14" cy="14" r="8"/><path d="m20 20 7 7M11 14h6m-3-3v6"/></svg>@break
    @case('notice')<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M5 14v5h5l11 6V8l-11 6H5zm16-2c3 2 3 7 0 9M9 19l2 7h4"/></svg>@break
    @case('globe')<svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="16" r="12"/><path d="M4 16h24M16 4c4 4 4 20 0 24M16 4c-4 4-4 20 0 24"/></svg>@break
    @case('receipt')<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M8 4h16v24l-4-3-4 3-4-3-4 3V4zm5 7h6m-6 5h6"/></svg>@break
    @case('legal')<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16 5v22M8 9h16M4 9l-3 8h6L4 9zm24 0-3 8h6l-3-8zM9 27h14"/></svg>@break
    @case('lock')<svg viewBox="0 0 32 32" aria-hidden="true"><rect x="7" y="14" width="18" height="14" rx="2"/><path d="M11 14V9a5 5 0 0 1 10 0v5m-5 5v4"/></svg>@break
    @case('chart')<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M5 27V5m0 22h23M10 23v-7h4v7m3 0V9h4v14m3 0V13h4v10"/></svg>@break
    @case('building')<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M5 27h22M8 27V10l8-5 8 5v17M12 13h2m4 0h2m-8 5h2m4 0h2m-5 9v-5h3v5"/></svg>@break
    @case('license')<svg viewBox="0 0 32 32" aria-hidden="true"><rect x="5" y="6" width="22" height="20" rx="2"/><path d="m11 16 3 3 7-8M10 23h12"/></svg>@break
    @default<svg viewBox="0 0 32 32" aria-hidden="true"><rect x="4" y="6" width="24" height="20" rx="3"/><path d="M4 12h24M9 9h.1m4 0h.1M10 18h5m-5 4h12"/></svg>
@endswitch
