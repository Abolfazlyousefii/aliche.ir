@php
    $adminMenuGroups = \App\Support\AdminNavigation::forUser(request()->user());
    $activeCategoryType = (string) (request('type') ?: (request()->route('category')?->type ?? ''));
@endphp

<aside class="admin-sidebar" id="adminSidebar" aria-label="منوی مدیریت">
    <div class="admin-brand">
        <div class="admin-brand-mark">ا</div>
        <div>
            <strong>گرگان اصناف</strong>
            <span>پنل مدیریت یکپارچه</span>
        </div>
        <button class="admin-sidebar-close" type="button" data-admin-sidebar-close aria-label="بستن منوی مدیریت">×</button>
    </div>

    <div class="admin-sidebar-caption">بخش‌های مدیریتی</div>
    <nav class="admin-sidebar-nav" aria-label="دسترسی به بخش‌های سامانه">
        @foreach ($adminMenuGroups as $item)
            @php
                $menuIcon = $item['icon'] ?? 'file';
                $matchPatterns = (array) ($item['match'] ?? ($item['route'] ?? []));
                $isActive = collect($matchPatterns)->contains(fn ($pattern) => request()->routeIs($pattern));
                $children = collect($item['children'] ?? []);
                $isActive = $isActive || $children->contains(function ($child) use ($activeCategoryType) {
                    $matches = collect((array) ($child['match'] ?? $child['route']))->contains(fn ($pattern) => request()->routeIs($pattern));
                    return $matches && (! isset($child['active_type']) || $activeCategoryType === $child['active_type']);
                });
                $badge = ($item['badge'] ?? null) === 'unread' ? (int) ($unreadMessagesCount ?? 0) : (int) ($item['badge'] ?? 0);
            @endphp
            @if ($children->isEmpty())
                <a class="admin-nav-link {{ $isActive ? 'is-active' : '' }}" href="{{ route($item['route'], $item['params'] ?? []) }}" @if($isActive) aria-current="page" @endif>
                    <span class="admin-nav-icon">@include('admin.components.icon', ['name' => $menuIcon])</span>
                    <span>{{ $item['title'] }}</span>
                    @if ($badge > 0)<span class="badge bg-danger ms-auto">{{ $badge }}</span>@endif
                </a>
            @else
                <details class="admin-nav-dropdown" {{ $isActive ? 'open' : '' }}>
                    <summary class="admin-nav-link {{ $isActive ? 'is-active' : '' }}">
                        <span class="admin-nav-icon">@include('admin.components.icon', ['name' => $menuIcon])</span>
                        <span>{{ $item['title'] }}</span>
                        @if ($badge > 0)<span class="badge bg-danger ms-auto">{{ $badge }}</span>@endif
                        <span class="admin-nav-caret">@include('admin.components.icon', ['name' => 'chevron'])</span>
                    </summary>
                    <div class="admin-nav-submenu">
                        @foreach ($children as $child)
                            @php
                                $childActive = collect((array) ($child['match'] ?? $child['route']))->contains(fn ($pattern) => request()->routeIs($pattern)) && (! isset($child['active_type']) || $activeCategoryType === $child['active_type']);
                                $childBadge = ($child['badge'] ?? null) === 'unread' ? (int) ($unreadMessagesCount ?? 0) : (int) ($child['badge'] ?? 0);
                            @endphp
                            <a class="admin-nav-sublink {{ $childActive ? 'is-active' : '' }}" href="{{ route($child['route'], $child['params'] ?? []) }}" @if($childActive) aria-current="page" @endif>
                                <span>{{ $child['title'] }}</span>
                                @if ($childBadge > 0)<span class="badge bg-danger ms-auto">{{ $childBadge }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                </details>
            @endif
        @endforeach
    </nav>
    <div class="admin-sidebar-bottom">
        <span class="admin-sidebar-bottom__caption">اتاق اصناف مرکز استان گلستان</span>
        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer">
            @include('admin.components.icon', ['name' => 'external'])
            <span>نمایش وب‌سایت</span>
        </a>
    </div>
</aside>
