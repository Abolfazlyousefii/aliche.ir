@extends('admin.layouts.app')
@section('title', 'مدیریت منوها')
@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">نمایش و تنظیمات سایت</p><h2>منوهای سایت</h2></div>
    @if(request()->user()->hasPermission('menus.create'))
        <a class="admin-primary-btn" href="{{ route('admin.menus.create') }}">ایجاد منو جدید</a>
    @endif
</div>
@include('admin.partials.list-filters', [
    'listRoute' => 'admin.menus.index',
    'listTitle' => 'جستجوی منوهای سایت',
    'listSearch' => $search,
    'listPlaceholder' => 'عنوان منو یا محل نمایش...',
    'listPaginator' => $menus,
    'listFilters' => [
        ['name' => 'status', 'label' => 'وضعیت', 'value' => $status, 'options' => ['active' => 'فعال', 'inactive' => 'غیرفعال']],
    ],
])
<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="منوهای سایت">
        <table class="table admin-table align-middle admin-list-table" data-admin-list-table>
            <thead><tr><th>عنوان</th><th>محل نمایش</th><th>تعداد آیتم‌ها</th><th>وضعیت</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse($menus as $menu)
                    <tr>
                        <td><strong>{{ $menu->title }}</strong></td>
                        <td><code>{{ $menu->location }}</code></td>
                        <td>{{ fa_number($menu->items_count) }}</td>
                        <td><span class="admin-status-badge {{ $menu->is_active ? 'is-active' : 'is-inactive' }}">{{ $menu->is_active ? 'فعال' : 'غیرفعال' }}</span></td>
                        <td><div class="admin-actions">
                            <a href="{{ route('admin.menus.show', $menu) }}">مشاهده آیتم‌ها</a>
                            @if(request()->user()->hasPermission('menus.edit'))<a href="{{ route('admin.menus.edit', $menu) }}">ویرایش</a>@endif
                            @if(request()->user()->hasPermission('menus.delete'))
                                <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">منویی مطابق جستجو پیدا نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $menus])
</div>
@endsection
