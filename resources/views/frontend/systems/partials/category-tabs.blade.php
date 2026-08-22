@if($categories->count() > 1)
    <nav class="systems-category-tabs" aria-label="فیلتر دسته‌بندی سامانه‌ها" data-systems-category-tabs>
        <a class="systems-category-tab {{ $activeCategory === '' ? 'is-active' : '' }}" href="{{ route('systems.index', array_filter(['search' => $search])) }}" data-system-category-link data-category="" @if($activeCategory === '') aria-current="page" @endif>
            <span>همه سامانه‌ها</span><small data-category-count="all">{{ fa_number($categoryCounts['all']) }}</small>
        </a>
        @foreach($categories as $category)
            @php($active = $activeCategory === $category->slug)
            <a class="systems-category-tab {{ $active ? 'is-active' : '' }}" href="{{ route('systems.index', array_filter(['category' => $category->slug, 'search' => $search])) }}" data-system-category-link data-category="{{ $category->slug }}" @if($active) aria-current="page" @endif>
                <span>{{ $category->title }}</span><small data-category-count="{{ $category->slug }}">{{ fa_number($categoryCounts[$category->slug] ?? 0) }}</small>
            </a>
        @endforeach
    </nav>
@endif
