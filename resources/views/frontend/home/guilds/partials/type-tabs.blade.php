@php
    $tabs = [
        '' => ['label' => 'همه اتحادیه‌ها', 'count' => $typeCounts['all'], 'icon' => 'M4 21h16M6 21V9h12v12M9 13h2M13 13h2M9 17h2M13 17h2M8 9V5h8v4'],
        'production' => ['label' => 'تولیدی', 'count' => $typeCounts['production'], 'icon' => 'M3 21h18M5 21V10l5 3V8l5 3V5h4v16M8 17h2M13 17h2'],
        'distribution' => ['label' => 'توزیعی', 'count' => $typeCounts['distribution'], 'icon' => 'M3 5h2l2 10h10l3-7H7M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2ZM17 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z'],
        'service' => ['label' => 'خدماتی', 'count' => $typeCounts['service'], 'icon' => 'M9 6V4h6v2M5 7h14v13H5zM5 12h14M10 12v2h4v-2'],
        'specialized' => ['label' => 'تخصصی', 'count' => $typeCounts['specialized'], 'icon' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18ZM12 17a5 5 0 1 0 0-10 5 5 0 0 0 0 10ZM12 13a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z'],
    ];
@endphp

<nav class="guilds-type-tabs" aria-label="فیلتر نوع اتحادیه" data-guild-type-tabs>
    @foreach($tabs as $key => $tab)
        @php
            $query = array_filter([
                'search' => $search ?: null,
                'type' => $key ?: null,
                'category_id' => $categoryId ?: null,
            ], fn ($value) => filled($value));
        @endphp
        <a href="{{ route('guilds.index', $query) }}" class="guilds-type-tab {{ $type === $key ? 'is-active' : '' }}" data-guild-type-link data-type="{{ $key }}" @if($type === $key) aria-current="page" @endif>
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="{{ $tab['icon'] }}"/></svg>
            <span>{{ $tab['label'] }}</span>
            <small data-type-count="{{ $key ?: 'all' }}">{{ fa_number($tab['count']) }}</small>
        </a>
    @endforeach
</nav>
