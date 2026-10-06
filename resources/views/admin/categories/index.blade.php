@extends('admin.layouts.app')
@section('title', 'مدیریت دسته‌بندی‌ها')
@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">دسته‌بندی‌ها</p><h2>مدیریت دسته‌بندی‌های محتوایی</h2></div>
    @if(! empty($createTypes) && ($type === '' || in_array($type, $createTypes, true)))
        <a class="admin-primary-btn" href="{{ route('admin.categories.create', ['type' => $type !== '' ? $type : $createTypes[0]]) }}">ایجاد دسته‌بندی</a>
    @endif
</div>
@include('admin.partials.list-filters', [
    'listRoute' => 'admin.categories.index',
    'listTitle' => 'جستجوی دسته‌بندی‌ها',
    'listDescription' => 'فقط دسته‌بندی‌های قابل دسترس شما نمایش داده می‌شوند.',
    'listSearch' => $search,
    'listPlaceholder' => 'عنوان یا نامک دسته‌بندی...',
    'listPaginator' => $categories,
    'listFilters' => [
        ['name' => 'type', 'label' => 'نوع محتوا', 'value' => $type, 'options' => $types, 'empty' => 'همه انواع مجاز'],
    ],
])
<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فهرست دسته‌بندی‌ها">
        <table class="admin-table admin-list-table" data-admin-list-table>
            <thead><tr><th>آیکون</th><th>عنوان</th><th>نوع</th><th>ترتیب</th><th>وضعیت</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td style="font-size:1.4rem">{{ $category->icon ?: '—' }}</td>
                        <td><strong>{{ $category->title }}</strong><br><small dir="ltr">{{ $category->slug }}</small></td>
                        <td>{{ $types[$category->type] ?? $category->type }}</td>
                        <td>{{ fa_number($category->sort_order) }}</td>
                        <td><span class="admin-status-badge {{ $category->is_active ? 'is-active' : 'is-inactive' }}">{{ $category->is_active ? 'فعال' : 'غیرفعال' }}</span></td>
                        <td><div class="admin-actions">
                            @if(\App\Support\AdminCategoryAccess::can(request()->user(), $category->type, 'edit'))
                                <a href="{{ route('admin.categories.edit', $category) }}">ویرایش</a>
                            @endif
                            @if(\App\Support\AdminCategoryAccess::can(request()->user(), $category->type, 'delete'))
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">@csrf @method('DELETE')<button type="submit">حذف</button></form>
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">دسته‌بندی‌ای مطابق این جستجو وجود ندارد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $categories])
</div>
@endsection
