@extends('admin.layouts.app')

@section('title', 'مدیریت اتحادیه‌ها')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">مدیریت اتحادیه‌ها</p><h2>اتحادیه‌های صنفی</h2></div>
    @if(request()->user()->hasPermission('unions.create'))
        <a class="admin-primary-btn" href="{{ route('admin.unions.create') }}">ایجاد اتحادیه جدید</a>
    @endif
</div>

@include('admin.partials.list-filters', [
    'listRoute' => 'admin.unions.index',
    'listTitle' => 'فیلتر اتحادیه‌ها',
    'listDescription' => 'جستجو بر اساس نام، اطلاعات مدیر و راه‌های تماس',
    'listSearch' => $search,
    'listPlaceholder' => 'نام اتحادیه، رئیس، تلفن یا ایمیل...',
    'listPaginator' => $unions,
    'listFilters' => [
            ['name' => 'status', 'label' => 'وضعیت', 'value' => $status, 'options' => ['active' => 'فعال', 'inactive' => 'غیرفعال'], 'empty' => 'همه وضعیت‌ها'],
    ],
])

<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر اتحادیه‌ها">
        <table class="table admin-table align-middle admin-list-table" data-admin-list-table>
            <thead><tr><th>عنوان</th><th>لوگو</th><th>مدیر</th><th>شماره تماس</th><th>وضعیت</th><th>ترتیب نمایش</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse ($unions as $union)
                    <tr>
                        <td><strong>{{ $union->display_title }}</strong><br><code>{{ $union->slug }}</code></td>
                        <td>
                            @if ($union->logo)
                                <img src="{{ route('media.public', ['path' => $union->logo]) }}" alt="{{ $union->display_title }}" style="width:48px;height:48px;object-fit:contain">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $union->manager_name ?: '—' }}</td>
                        <td>{{ $union->phone ?: $union->mobile ?: '—' }}</td>
                        <td><span class="admin-status-badge {{ $union->is_active ? 'is-active' : 'is-inactive' }}">{{ $union->is_active ? 'فعال' : 'غیرفعال' }}</span></td>
                        <td>{{ $union->sort_order }}</td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.unions.show', $union) }}">مشاهده</a>
                                @if(request()->user()->hasPermission('unions.edit'))<a href="{{ route('admin.unions.edit', $union) }}">ویرایش</a>@endif
                                @if(request()->user()->hasPermission('unions.delete'))<form action="{{ route('admin.unions.destroy', $union) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>@endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">اتحادیه‌ای یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $unions])
</div>
@endsection
