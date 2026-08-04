@extends('admin.layouts.app')

@section('title', 'مدیریت صفحات')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">CMS صفحات</p><h2>صفحات سایت</h2></div>
    <a class="admin-primary-btn" href="{{ route('admin.pages.create') }}">ایجاد صفحه جدید</a>
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="{{ route('admin.pages.index') }}" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="عنوان یا اسلاگ...">
        <select class="form-control" name="status" aria-label="فیلتر وضعیت">
            <option value="">همه وضعیت‌ها</option>
            @foreach (\App\Models\Page::STATUSES as $itemStatus)
                <option value="{{ $itemStatus }}" @selected($status === $itemStatus)>{{ \App\Models\Page::statusLabels()[$itemStatus] ?? $itemStatus }}</option>
            @endforeach
        </select>
        <button class="admin-primary-btn" type="submit">اعمال</button>
        @if ($search !== '' || $status !== '')<a class="admin-secondary-btn" href="{{ route('admin.pages.index') }}">حذف فیلتر</a>@endif
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
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
                                <a href="{{ route('admin.pages.edit', $page) }}">ویرایش</a>
                                <form action="{{ route('admin.pages.destroy', $page) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>
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
