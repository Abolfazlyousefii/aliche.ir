@extends('admin.layouts.app')
@section('title', 'مدیریت انواع اتحادیه')
@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">اتحادیه‌ها و سازمان</p><h2>انواع اتحادیه</h2></div>
    @if(request()->user()->hasPermission('union_types.create'))
        <a class="admin-primary-btn" href="{{ route('admin.union-types.create') }}">ایجاد نوع اتحادیه</a>
    @endif
</div>
@include('admin.partials.list-filters', [
    'listRoute' => 'admin.union-types.index',
    'listTitle' => 'جستجوی انواع اتحادیه',
    'listSearch' => $search,
    'listPlaceholder' => 'عنوان یا نامک نوع اتحادیه...',
    'listPaginator' => $unionTypes,
    'listFilters' => [
        ['name' => 'status', 'label' => 'وضعیت', 'value' => $status, 'options' => ['active' => 'فعال', 'inactive' => 'غیرفعال']],
    ],
])
<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="انواع اتحادیه">
        <table class="admin-table admin-list-table" data-admin-list-table>
            <thead><tr><th>تصویر</th><th>عنوان</th><th>آیکون</th><th>نامک</th><th>ترتیب</th><th>وضعیت</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse($unionTypes as $unionType)
                    <tr>
                        <td>@if($unionType->image)<img src="{{ $unionType->image_url }}" alt="{{ $unionType->title }}" style="width:56px;height:56px;object-fit:cover;border-radius:9px">@else — @endif</td>
                        <td><strong>{{ $unionType->title }}</strong></td>
                        <td><span class="union-type-admin-icon-wrap"><x-union-type-icon :icon="$unionType->resolved_icon" /><span>{{ $unionType->icon_label }}</span></span></td>
                        <td dir="ltr">{{ $unionType->slug }}</td>
                        <td>{{ fa_number($unionType->sort_order) }}</td>
                        <td><span class="admin-status-badge {{ $unionType->is_active ? 'is-active' : 'is-inactive' }}">{{ $unionType->is_active ? 'فعال' : 'غیرفعال' }}</span></td>
                        <td><div class="admin-actions">
                            @if(request()->user()->hasPermission('union_types.edit'))
                                <a href="{{ route('admin.union-types.edit', $unionType) }}">ویرایش</a>
                            @endif
                            @if(request()->user()->hasPermission('union_types.delete'))
                                <form method="POST" action="{{ route('admin.union-types.destroy', $unionType) }}">@csrf @method('DELETE')<button type="submit">حذف</button></form>
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">نوع اتحادیه‌ای مطابق جستجو یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $unionTypes])
</div>
@endsection
