@extends('admin.layouts.app')

@section('title', 'مدیریت صفحات')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">CMS صفحات</p><h2>صفحات سایت</h2></div>
    @if(request()->user()->hasPermission('pages.create'))
        <a class="admin-primary-btn" href="{{ route('admin.pages.create') }}">ایجاد صفحه جدید</a>
    @endif
</div>

@include('admin.partials.list-filters', [
    'listRoute' => 'admin.pages.index',
    'listTitle' => 'فیلتر صفحات',
    'listDescription' => 'جستجوی صفحات محتوایی سایت و وضعیت انتشار آن‌ها',
    'listSearch' => $search,
    'listPlaceholder' => 'عنوان صفحه یا نامک...',
    'listPaginator' => $pages,
    'listFilters' => [
            ['name' => 'status', 'label' => 'وضعیت', 'value' => $status, 'options' => \App\Models\Page::statusLabels(), 'empty' => 'همه وضعیت‌ها'],
    ],
])

<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر صفحات">
        <table class="table admin-table align-middle admin-list-table" data-admin-list-table>
            <thead><tr><th>عنوان</th><th>اسلاگ</th><th>وضعیت</th><th>نویسنده</th><th>تاریخ انتشار</th><th>فعال/غیرفعال</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse ($pages as $page)
                    <tr>
                        <td><strong>{{ $page->title }}</strong></td>
                        <td><code>{{ $page->slug }}</code></td>
                        <td><span class="admin-status-badge status-{{ $page->status }}">{{ \App\Models\Page::statusLabels()[$page->status] ?? $page->status }}</span></td>
                        <td>{{ $page->author?->name ?: '—' }}</td>
                        <td>{{ jalali_datetime($page->published_at) ?: '—' }}</td>
                        <td><span class="admin-status-badge {{ $page->is_active ? 'is-active' : 'is-inactive' }}">{{ $page->is_active ? 'فعال' : 'غیرفعال' }}</span></td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.pages.show', $page) }}">مشاهده</a>
                                @if(request()->user()->hasPermission('pages.edit'))<a href="{{ route('admin.pages.edit', $page) }}">ویرایش</a>@endif
                                @if(request()->user()->hasPermission('pages.delete'))<form action="{{ route('admin.pages.destroy', $page) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>@endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">صفحه‌ای یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $pages])
</div>
@endsection
