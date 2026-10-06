<header class="admin-header">
    <div class="admin-header-start">
        <button class="admin-menu-toggle" type="button" aria-label="باز کردن منوی مدیریت" aria-controls="adminSidebar" aria-expanded="false" data-admin-sidebar-toggle>
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div>
            <p class="admin-eyebrow">سامانه مدیریت محتوا</p>
            <h1>@yield('title', 'داشبورد مدیریت')</h1>
        </div>
    </div>

    <div class="admin-header-end">
        @php($adminQuickLinks = \App\Support\AdminNavigation::searchableLinks(request()->user()))
        <button class="admin-search-mobile-toggle" type="button" data-admin-search-toggle aria-label="جستجوی بخش‌های پنل" aria-controls="adminQuickNavSearch" aria-expanded="false">
            @include('admin.components.icon', ['name' => 'search'])
        </button>
        <div class="admin-search admin-quick-search" id="adminQuickNavSearch" data-admin-quick-search role="search">
            @include('admin.components.icon', ['name' => 'search'])
            <input type="search" placeholder="جستجوی بخش‌های پنل..." aria-label="جستجوی بخش‌های قابل دسترس" aria-controls="adminQuickNavResults" aria-expanded="false" data-admin-quick-input autocomplete="off" spellcheck="false">
            <div class="admin-quick-results" id="adminQuickNavResults" data-admin-quick-results hidden>
                <p class="admin-quick-results__heading">بخش‌های قابل دسترس</p>
                @foreach($adminQuickLinks as $quickLink)
                    <a href="{{ route($quickLink['route'], $quickLink['params']) }}" data-admin-quick-link data-search-text="{{ $quickLink['group'].' '.$quickLink['title'] }}">
                        <span>{{ $quickLink['title'] }}</span>
                        <small>{{ $quickLink['group'] }}</small>
                    </a>
                @endforeach
                <p class="admin-quick-results__empty" data-admin-quick-empty hidden>بخشی با این عبارت پیدا نشد.</p>
            </div>
        </div>
        <a class="admin-header-action" href="{{ route('admin.messages.inbox') }}" aria-label="پیام‌ها">
            @include('admin.components.icon', ['name' => 'mail'])
            <span class="admin-header-action-label">پیام‌ها</span>
            @if (($unreadMessagesCount ?? 0) > 0)
                <span class="badge bg-danger">{{ fa_number($unreadMessagesCount) }}</span>
            @endif
        </a>
        <a class="admin-view-site" href="{{ route('home') }}" target="_blank" rel="noopener">@include('admin.components.icon', ['name' => 'external'])<span>مشاهده سایت</span></a>
        <div class="admin-user-card">
            <div class="admin-avatar">{{ mb_substr(auth()->user()?->name ?? 'م', 0, 1) }}</div>
            <div>
                <strong>{{ auth()->user()?->name ?? 'مدیر سامانه' }}</strong>
                <span>خوش آمدید</span>
            </div>
        </div>
        <form class="admin-logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="admin-secondary-btn admin-logout-btn" type="submit">@include('admin.components.icon', ['name' => 'logout'])<span>خروج</span></button>
        </form>
    </div>
</header>
