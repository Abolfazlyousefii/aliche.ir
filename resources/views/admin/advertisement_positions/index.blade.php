@extends('admin.layouts.app')

@section('title', 'جایگاه‌های تبلیغاتی')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">تبلیغات</p><h2>جایگاه‌های تبلیغاتی</h2></div>
    @if (request()->user()->hasPermission('advertisements.create'))
        <a class="admin-primary-btn" href="{{ route('admin.advertisement_positions.create') }}">ایجاد جایگاه جدید</a>
    @endif
</div>

@include('admin.partials.list-filters', [
    'listRoute' => 'admin.advertisement_positions.index',
    'listTitle' => 'فیلتر جایگاه‌های تبلیغاتی',
    'listDescription' => 'جستجوی عنوان، کلید جایگاه و وضعیت فعال بودن',
    'listSearch' => $search,
    'listPlaceholder' => 'عنوان یا کلید جایگاه...',
    'listPaginator' => $positions,
    'listFilters' => [
            ['name' => 'status', 'label' => 'وضعیت', 'value' => $status, 'options' => ['active' => 'فعال', 'inactive' => 'غیرفعال'], 'empty' => 'همه وضعیت‌ها'],
    ],
])

<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر جایگاه‌های تبلیغاتی">
        <table class="admin-table admin-list-table" data-admin-list-table>
            <thead><tr><th>عنوان</th><th>کلید</th><th>ابعاد</th><th>تعداد تبلیغ</th><th>وضعیت</th><th>عملیات</th></tr></thead>
            <tbody>
            @forelse ($positions as $position)
                <tr>
                    <td><strong>{{ $position->title }}</strong><br><small>{{ Str::limit($position->description ?: '', 80) }}</small></td>
                    <td><code>{{ $position->key }}</code></td>
                    <td>{{ $position->width ?: '—' }} × {{ $position->height ?: '—' }}</td>
                    <td>{{ $position->advertisements_count }}</td>
                    <td>{{ $position->is_active ? 'فعال' : 'غیرفعال' }}</td>
                    <td>
                        <div class="admin-actions">
                            @if (request()->user()->hasPermission('advertisements.edit'))<a class="admin-secondary-btn" href="{{ route('admin.advertisement_positions.edit', $position) }}">ویرایش</a>@endif
                            @if (request()->user()->hasPermission('advertisements.delete'))
                                <form action="{{ route('admin.advertisement_positions.destroy', $position) }}" method="POST">@csrf @method('DELETE')<button class="admin-danger-btn" type="submit">حذف</button></form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">جایگاه تبلیغاتی ثبت نشده است.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $positions])
</div>
@endsection
