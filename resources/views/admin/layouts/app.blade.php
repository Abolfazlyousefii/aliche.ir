<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل مدیریت') | اتاق اصناف مرکز استان گلستان</title>
    <link href="https://cdn.jsdelivr.net" rel="preconnect">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css" rel="stylesheet">
    <link href="{{ asset('assets/admin/css/admin.css') }}?v={{ filemtime(public_path('assets/admin/css/admin.css')) }}" rel="stylesheet">
    @php
        $adminNavigationCss = base_path('public/assets/admin/css/navigation.css');
    @endphp
    <link href="{{ asset('assets/admin/css/navigation.css') }}?v={{ is_file($adminNavigationCss) ? filemtime($adminNavigationCss) : '1' }}" rel="stylesheet">

    @if(request()->routeIs('admin.unions.*', 'admin.union_members.*'))
        @php
            $unionAdminStylesPath = public_path('assets/admin/css/union-admin.css');
            $unionAdminStylesVersion = is_file($unionAdminStylesPath) ? filemtime($unionAdminStylesPath) : '1';
        @endphp
        <link href="{{ asset('assets/admin/css/union-admin.css') }}?v={{ $unionAdminStylesVersion }}" rel="stylesheet">
    @endif
    @php
        $adminUiV2Css = base_path('public/assets/admin/css/admin-ui-v2.css');
    @endphp
    <link href="{{ asset('assets/admin/css/admin-ui-v2.css') }}?v={{ is_file($adminUiV2Css) ? filemtime($adminUiV2Css) : '1' }}" rel="stylesheet">
    @if(request()->routeIs(
        'admin.unions.index',
        'admin.union_members.index',
        'admin.complaints.index',
        'admin.tourism.index',
        'admin.electronic_services.index',
        'admin.announcements.index',
        'admin.pages.index',
        'admin.users.index'
    ))
        @php
            $adminListCss = base_path('public/assets/admin/css/list-workspace.css');
        @endphp
        <link href="{{ asset('assets/admin/css/list-workspace.css') }}?v={{ is_file($adminListCss) ? filemtime($adminListCss) : '1' }}" rel="stylesheet">
    @endif
    @if(request()->routeIs('admin.unions.create', 'admin.unions.edit', 'admin.posts.create', 'admin.posts.edit'))
        @php
            $formWorkspaceCss = base_path('public/assets/admin/css/form-workspace.css');
        @endphp
        <link href="{{ asset('assets/admin/css/form-workspace.css') }}?v={{ is_file($formWorkspaceCss) ? filemtime($formWorkspaceCss) : '1' }}" rel="stylesheet">
    @endif
</head>
<body class="admin-phase2">
    <a class="admin-skip-link" href="#adminMainContent">رفتن به محتوای اصلی</a>
    <div class="admin-shell">
        @include('admin.partials.sidebar')

        <div class="admin-main">
            @include('admin.partials.header')

            <main class="admin-content" id="adminMainContent" tabindex="-1">
                @include('admin.partials.alerts')
                @yield('content')
            </main>

            @include('admin.partials.footer')
        </div>
    </div>

    <div class="admin-backdrop" data-admin-sidebar-close></div>

    <div class="modal fade" id="adminDeleteModal" tabindex="-1" aria-labelledby="adminDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content admin-confirm-modal">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="adminDeleteModalLabel">تایید عملیات حذف</h2>
                    <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">این عملیات قابل بازگشت نیست. آیا از حذف این مورد مطمئن هستید؟</p>
                    <small class="text-muted" data-admin-delete-message></small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-secondary-btn" data-bs-dismiss="modal">انصراف</button>
                    <button type="button" class="admin-danger-btn" data-admin-delete-confirm>بله، حذف شود</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>window.adminRichTextUploadUrl = @json(route('admin.rich_text.upload')); window.adminMediaPickerUrl = @json(route('admin.media.picker')); window.adminMediaUploadUrl = @json(route('admin.media.store'));</script>
    <script src="{{ asset('assets/admin/js/admin.js') }}?v={{ filemtime(public_path('assets/admin/js/admin.js')) }}"></script>
    @php
        $adminNavigationJs = base_path('public/assets/admin/js/navigation.js');
    @endphp
    <script src="{{ asset('assets/admin/js/navigation.js') }}?v={{ is_file($adminNavigationJs) ? filemtime($adminNavigationJs) : '1' }}"></script>
    <script src="{{ asset('assets/admin/js/rich-editor.js') }}"></script>
    @if(request()->routeIs(
        'admin.unions.index',
        'admin.union_members.index',
        'admin.complaints.index',
        'admin.tourism.index',
        'admin.electronic_services.index',
        'admin.announcements.index',
        'admin.pages.index',
        'admin.users.index'
    ))
        @php
            $adminListJs = base_path('public/assets/admin/js/list-workspace.js');
        @endphp
        <script src="{{ asset('assets/admin/js/list-workspace.js') }}?v={{ is_file($adminListJs) ? filemtime($adminListJs) : '1' }}"></script>
    @endif
    @if(request()->routeIs('admin.unions.create', 'admin.unions.edit', 'admin.posts.create', 'admin.posts.edit'))
        @php
            $formWorkspaceJs = base_path('public/assets/admin/js/form-workspace.js');
        @endphp
        <script src="{{ asset('assets/admin/js/form-workspace.js') }}?v={{ is_file($formWorkspaceJs) ? filemtime($formWorkspaceJs) : '1' }}"></script>
    @endif
    @stack('scripts')
</body>
</html>
