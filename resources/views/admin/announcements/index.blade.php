@extends('admin.layouts.app')

@section('title', 'مدیریت اطلاعیه‌ها')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">CMS اطلاعیه‌ها</p><h2>مدیریت اطلاعیه‌ها</h2></div>
    @if(request()->user()->hasPermission('announcements.create'))
        <a class="admin-primary-btn" href="{{ route('admin.announcements.create') }}">ایجاد اطلاعیه جدید</a>
    @endif
</div>

@include('admin.partials.list-filters', [
    'listRoute' => 'admin.announcements.index',
    'listTitle' => 'فیلتر اطلاعیه‌ها',
    'listDescription' => 'اطلاعیه‌ها را بر اساس عنوان، وضعیت یا اتحادیه محدود کنید',
    'listSearch' => $search,
    'listPlaceholder' => 'عنوان اطلاعیه یا خلاصه...',
    'listPaginator' => $announcements,
    'listFilters' => [
            ['name' => 'status', 'label' => 'وضعیت', 'value' => $status, 'options' => \App\Models\Announcement::statusLabels(), 'empty' => 'همه وضعیت‌ها'],
            ['name' => 'union_id', 'label' => 'اتحادیه', 'value' => $unionId, 'options' => $unions->mapWithKeys(fn ($union) => [(string) $union->id => $union->display_title])->all(), 'empty' => 'همه اتحادیه‌ها'],
    ],
])

<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر اطلاعیه‌ها">
        <table class="table admin-table align-middle admin-list-table" data-admin-list-table>
            <thead><tr><th>عنوان</th><th>دسته‌بندی</th><th>اتحادیه</th><th>وضعیت</th><th>نوع نمایش</th><th>شروع/انقضا</th><th>مهم</th><th>انتشار</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse ($announcements as $announcement)
                    <tr>
                        <td><strong>{{ $announcement->title }}</strong><br><code>{{ $announcement->slug }}</code></td>
                        <td>{{ $announcement->category?->title ?: '—' }}</td>
                        <td>{{ $announcement->union?->name ?: 'عمومی' }}</td>
                        <td><span class="admin-status-badge status-{{ $announcement->status }}">{{ $announcement->status_label }}</span></td>
                        <td>{{ $announcement->visibility_label }}</td>
                        <td>{{ jalali_datetime($announcement->starts_at) ?: '—' }}<br><small>{{ jalali_datetime($announcement->expires_at) ?: 'بدون انقضا' }}</small></td>
                        <td>{{ $announcement->is_important ? 'بله' : 'خیر' }}</td>
                        <td>{{ jalali_datetime($announcement->published_at) ?: '—' }}</td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.announcements.show', $announcement) }}">مشاهده</a>
                                @if(request()->user()->hasPermission('announcements.edit'))<a href="{{ route('admin.announcements.edit', $announcement) }}">ویرایش</a>@endif
                                @if(request()->user()->hasPermission('announcements.delete'))<form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>@endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">اطلاعیه‌ای یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $announcements])
</div>
@endsection
