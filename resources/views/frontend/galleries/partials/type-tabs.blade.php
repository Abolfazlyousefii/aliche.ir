@if($showTypeTabs)
    @php($typeLabels = ['' => 'همه گالری‌ها', 'image' => 'تصاویر', 'video' => 'ویدئوها', 'mixed' => 'ترکیبی'])
    <nav class="galleries-type-tabs" aria-label="فیلتر نوع رسانه" data-galleries-type-tabs>
        @foreach($typeLabels as $value => $label)
            @continue($value !== '' && ($typeCounts[$value] ?? 0) === 0)
            @php($active = $type === $value)
            <a class="galleries-type-tab {{ $active ? 'is-active' : '' }}" href="{{ route('galleries.index', array_filter(['type' => $value, 'search' => $search])) }}" data-gallery-type-link data-type="{{ $value }}" @if($active) aria-current="page" @endif>
                <span>{{ $label }}</span><small data-gallery-type-count="{{ $value === '' ? 'all' : $value }}">{{ fa_number($typeCounts[$value === '' ? 'all' : $value] ?? 0) }}</small>
            </a>
        @endforeach
    </nav>
@endif
