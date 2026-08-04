@extends('admin.layouts.app')

@section('title', 'مدیریت اطلاعیه‌ها')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">CMS اطلاعیه‌ها</p><h2>مدیریت اطلاعیه‌ها</h2></div>
    <a class="admin-primary-btn" href="{{ route('admin.announcements.create') }}">ایجاد اطلاعیه جدید</a>
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="{{ route('admin.announcements.index') }}" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="عنوان، اسلاگ یا خلاصه...">
        <select class="form-control" name="status" aria-label="فیلتر وضعیت">
            <option value="">همه وضعیت‌ها</option>
            @foreach (\App\Models\Announcement::STATUSES as $itemStatus)
                <option value="{{ $itemStatus }}" @selected($status === $itemStatus)>{{ $itemStatus }}</option>
            @endforeach
        </select>
        <select class="form-control" name="union_id" aria-label="فیلتر اتحادیه">
            <option value="">همه اتحادیه‌ها</option>
            @foreach ($unions as $union)
                <option value="{{ $union->id }}" @selected((string) $unionId === (string) $union->id)>{{ $union->name }}</option>
            @endforeach
        </select>
        <button class="admin-primary-btn" type="submit">اعمال</button>
        @if ($search !== '' || $status !== '' || $unionId)<a class="admin-secondary-btn" href="{{ route('admin.announcements.index') }}">حذف فیلتر</a>@endif
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
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
                                <a href="{{ route('admin.announcements.edit', $announcement) }}">ویرایش</a>
                                <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>
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
