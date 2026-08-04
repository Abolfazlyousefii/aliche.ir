@php
    $adminMenuGroups = [
        [
            'title' => 'داشبورد', 'icon' => 'home', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'permission' => 'dashboard.view',
            'children' => [],
        ],
        [
            'title' => 'صفحات', 'icon' => 'file', 'route' => 'admin.pages.index', 'match' => 'admin.pages.*', 'permission' => 'pages.view',
            'children' => [
                ['title' => 'صفحه جدید', 'route' => 'admin.pages.create', 'match' => 'admin.pages.create', 'permission' => 'pages.create'],
                ['title' => 'همه صفحه‌ها', 'route' => 'admin.pages.index', 'match' => 'admin.pages.index', 'permission' => 'pages.view'],
            ],
        ],
        [
            'title' => 'اخبار', 'icon' => 'news', 'route' => 'admin.posts.index', 'match' => 'admin.posts.*', 'permission' => 'posts.view',
            'children' => [
                ['title' => 'خبر جدید', 'route' => 'admin.posts.create', 'match' => 'admin.posts.create', 'permission' => 'posts.create'],
                ['title' => 'همه خبرها', 'route' => 'admin.posts.index', 'match' => 'admin.posts.index', 'permission' => 'posts.view'],
                ['title' => 'دسته‌بندی اخبار', 'route' => 'admin.categories.index', 'params' => ['type' => 'news'], 'match' => 'admin.categories.*', 'active_type' => 'news', 'permission' => 'posts.view'],
            ],
        ],
        [
            'title' => 'رسانه‌ها', 'icon' => 'image', 'route' => 'admin.media.index', 'match' => ['admin.media.*', 'admin.galleries.*', 'admin.videos.*'],
            'children' => [
                ['title' => 'کتابخانه تصاویر', 'route' => 'admin.media.index', 'match' => 'admin.media.*', 'permission' => 'media.view'],
                ['title' => 'گالری جدید', 'permission' => 'galleries.create', 'route' => 'admin.galleries.create', 'match' => 'admin.galleries.create'],
                ['title' => 'همه گالری‌ها', 'route' => 'admin.galleries.index', 'match' => 'admin.galleries.index'],
                ['title' => 'دسته‌بندی گالری', 'permission' => 'galleries.view', 'route' => 'admin.categories.index', 'params' => ['type' => 'gallery'], 'match' => 'admin.categories.*', 'active_type' => 'gallery'],
                ['title' => 'ویدیوها', 'route' => 'admin.videos.index', 'match' => 'admin.videos.*'],
                ['title' => 'دسته‌بندی ویدیو', 'permission' => 'videos.view', 'route' => 'admin.categories.index', 'params' => ['type' => 'video'], 'match' => 'admin.categories.*', 'active_type' => 'video'],
            ],
        ],
        [
            'title' => 'اعضای اتاق اصناف', 'icon' => 'users', 'route' => 'admin.chamber_members.index', 'match' => 'admin.chamber_members.*',
            'children' => [
                ['title' => 'عضو جدید', 'permission' => 'chamber_members.create', 'route' => 'admin.chamber_members.create', 'match' => 'admin.chamber_members.create'],
                ['title' => 'همه اعضا', 'route' => 'admin.chamber_members.index', 'match' => 'admin.chamber_members.index'],
            ],
        ],
        [
            'title' => 'اتحادیه‌ها', 'icon' => 'building', 'route' => 'admin.unions.index', 'match' => ['admin.unions.*', 'admin.union_members.*', 'admin.union-types.*'],
            'children' => [
                ['title' => 'اتحادیه جدید', 'permission' => 'unions.create', 'route' => 'admin.unions.create', 'match' => 'admin.unions.create'],
                ['title' => 'همه اتحادیه‌ها', 'route' => 'admin.unions.index', 'match' => 'admin.unions.index'],
                ['title' => 'دسته‌بندی اتحادیه‌ها', 'permission' => 'unions.view', 'route' => 'admin.categories.index', 'params' => ['type' => 'union'], 'match' => 'admin.categories.*', 'active_type' => 'union'],
                ['title' => 'اعضای اتحادیه‌ها', 'route' => 'admin.union_members.index', 'match' => 'admin.union_members.*'],
                ['title' => 'نوع اتحادیه‌ها', 'route' => 'admin.union-types.index', 'match' => 'admin.union-types.*'],
            ],
        ],
        [
            'title' => 'ارتباطات', 'icon' => 'mail', 'route' => 'admin.messages.inbox', 'match' => ['admin.messages.*', 'admin.sms.*', 'admin.contact_messages.*', 'admin.complaints.*'],
            'badge' => $unreadMessagesCount ?? 0,
            'children' => [
                ['title' => 'پیام‌های داخلی', 'route' => 'admin.messages.inbox', 'match' => 'admin.messages.*', 'badge' => $unreadMessagesCount ?? 0],
                ['title' => 'ارسال پیام جدید', 'route' => 'admin.messages.create', 'match' => 'admin.messages.create', 'permission' => 'messages.send'],
                ['title' => 'پیام‌های تماس', 'route' => 'admin.contact_messages.index', 'match' => 'admin.contact_messages.*'],
                ['title' => 'شکایات', 'route' => 'admin.complaints.index', 'match' => 'admin.complaints.*'],
                ['title' => 'پیامک‌ها', 'route' => 'admin.sms.index', 'match' => 'admin.sms.*'],
            ],
        ],
        [
            'title' => 'تنظیمات و بخش‌ها', 'icon' => 'settings', 'route' => 'admin.settings.edit', 'match' => ['admin.settings.*', 'admin.menus.*', 'admin.systems.*', 'admin.electronic_services.*', 'admin.home_sections.*', 'admin.header_settings.*', 'admin.footer_settings.*', 'admin.announcements.*', 'admin.congratulation_messages.*', 'admin.tourism.*', 'admin.commissions.*', 'admin.advertisements.*', 'admin.advertisement_positions.*'],
            'children' => [
                ['title' => 'سامانه‌ها', 'permission' => 'systems.view', 'route' => 'admin.systems.index', 'match' => 'admin.systems.*'],
                ['title' => 'سامانه جدید', 'permission' => 'systems.create', 'route' => 'admin.systems.create', 'match' => 'admin.systems.create'],
                ['title' => 'اطلاعیه‌ها', 'route' => 'admin.announcements.index', 'match' => 'admin.announcements.*'],
                ['title' => 'گردشگری', 'route' => 'admin.tourism.index', 'match' => 'admin.tourism.*'],
                ['title' => 'دسته‌بندی گردشگری', 'permission' => 'tourism.view', 'route' => 'admin.categories.index', 'params' => ['type' => 'tourism'], 'match' => 'admin.categories.*', 'active_type' => 'tourism'],
                ['title' => 'کمیسیون‌ها', 'route' => 'admin.commissions.index', 'match' => 'admin.commissions.*'],
                ['title' => 'تبلیغات', 'route' => 'admin.advertisements.index', 'match' => ['admin.advertisements.*', 'admin.advertisement_positions.*']],
                ['title' => 'منوها', 'route' => 'admin.menus.index', 'match' => 'admin.menus.*'],
                ['title' => 'صفحه اصلی', 'route' => 'admin.home_sections.index', 'match' => 'admin.home_sections.*'],
                ['title' => 'دسته‌بندی خدمات', 'permission' => 'electronic_services.view', 'route' => 'admin.categories.index', 'params' => ['type' => 'service'], 'match' => 'admin.categories.*', 'active_type' => 'service'],
                ['title' => 'دسته‌بندی سامانه‌ها', 'permission' => 'systems.view', 'route' => 'admin.categories.index', 'params' => ['type' => 'system'], 'match' => 'admin.categories.*', 'active_type' => 'system'],
                ['title' => 'تنظیمات سایت', 'route' => 'admin.settings.edit', 'match' => 'admin.settings.*'],
            ],
        ],
        [
            'title' => 'کاربران', 'icon' => 'shield', 'route' => 'admin.users.index', 'match' => ['admin.users.*', 'admin.roles.*', 'admin.permissions.*'],
            'children' => [
                ['title' => 'کاربران', 'route' => 'admin.users.index', 'match' => 'admin.users.*'],
                ['title' => 'نقش‌ها و دسترسی‌ها', 'route' => 'admin.roles.index', 'match' => ['admin.roles.*', 'admin.permissions.*']],
            ],
        ],
    ];

    $adminMenuPermissionMap = [
        'admin.dashboard' => 'dashboard.view',
        'admin.pending_approvals.' => 'pending_approvals.view',
        'admin.pages.' => 'pages.view',
        'admin.posts.' => 'posts.view',
        'admin.media.' => 'media.view',
        'admin.galleries.' => 'galleries.view',
        'admin.videos.' => 'videos.view',
        'admin.chamber_members.' => 'chamber_members.view',
        'admin.unions.' => 'unions.view',
        'admin.union_members.' => 'union_members.view',
        'admin.union-types.' => 'unions.view',
        'admin.messages.' => 'messages.view',
        'admin.contact_messages.' => 'contact_messages.view',
        'admin.complaints.' => 'complaints.view',
        'admin.sms.' => 'sms.view',
        'admin.announcements.' => 'announcements.view',
        'admin.tourism.' => 'tourism.view',
        'admin.commissions.' => 'commissions.view',
        'admin.advertisements.' => 'advertisements.view',
        'admin.advertisement_positions.' => 'advertisements.view',
        'admin.menus.' => 'menus.view',
        'admin.home_sections.' => 'home_sections.view',
        'admin.systems.' => 'systems.view',
        'admin.electronic_services.' => 'electronic_services.view',
        'admin.congratulation_messages.' => 'congratulation_messages.view',
        'admin.settings.' => 'settings.view',
        'admin.header_settings.' => 'header_settings.view',
        'admin.footer_settings.' => 'footer_settings.view',
        'admin.categories.' => null,
        'admin.users.' => 'users.view',
        'admin.roles.' => 'roles.view',
        'admin.permissions.' => 'permissions.view',
    ];

    $resolveMenuPermission = function (array $menuItem) use ($adminMenuPermissionMap): ?string {
        if (array_key_exists('permission', $menuItem)) {
            return $menuItem['permission'];
        }

        $routeName = $menuItem['route'] ?? '';

        if (array_key_exists($routeName, $adminMenuPermissionMap)) {
            return $adminMenuPermissionMap[$routeName];
        }

        foreach ($adminMenuPermissionMap as $routePrefix => $permission) {
            if (str_ends_with($routePrefix, '.') && str_starts_with($routeName, $routePrefix)) {
                return $permission;
            }
        }

        return null;
    };

    $canSeeMenuItem = function (array $menuItem) use ($resolveMenuPermission): bool {
        $permission = $resolveMenuPermission($menuItem);

        return $permission === null || request()->user()->hasPermission($permission);
    };
@endphp

<aside class="admin-sidebar" id="adminSidebar" aria-label="منوی مدیریت">
    <div class="admin-brand">
        <div class="admin-brand-mark">ا</div>
        <div>
            <strong>پنل مدیریت</strong>
            <span>اتاق اصناف مرکز استان گلستان</span>
        </div>
    </div>

    <nav class="admin-sidebar-nav">
        @foreach ($adminMenuGroups as $item)
            @php
                $menuIcon = $item['icon'] ?? 'file';
                $matchPatterns = (array) ($item['match'] ?? $item['route']);
                $isActive = collect($matchPatterns)->contains(fn ($pattern) => request()->routeIs($pattern));
                $children = collect($item['children'] ?? [])->filter(fn ($child) => $canSeeMenuItem($child));
                $isActive = $isActive || $children->contains(function ($child) {
                    $matches = collect((array) ($child['match'] ?? $child['route']))->contains(fn ($pattern) => request()->routeIs($pattern));
                    return $matches && (! isset($child['active_type']) || request('type') === $child['active_type']);
                });
                $badge = (int) ($item['badge'] ?? 0);
                $canSeeParent = $canSeeMenuItem($item);
            @endphp
            @continue(! $canSeeParent && $children->isEmpty())
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
                                $childActive = collect((array) ($child['match'] ?? $child['route']))->contains(fn ($pattern) => request()->routeIs($pattern)) && (! isset($child['active_type']) || request('type') === $child['active_type']);
                                $childBadge = (int) ($child['badge'] ?? 0);
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
</aside>
