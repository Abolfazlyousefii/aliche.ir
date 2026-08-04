@extends('admin.layouts.app')

@section('title', 'مدیریت اتحادیه‌ها')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">مدیریت اتحادیه‌ها</p><h2>اتحادیه‌های صنفی</h2></div>
    <a class="admin-primary-btn" href="{{ route('admin.unions.create') }}">ایجاد اتحادیه جدید</a>
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="{{ route('admin.unions.index') }}" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="عنوان، مدیر، تلفن یا ایمیل...">
        <select class="form-control" name="status" aria-label="فیلتر وضعیت">
            <option value="">همه وضعیت‌ها</option>
            <option value="active" @selected($status === 'active')>فعال</option>
            <option value="inactive" @selected($status === 'inactive')>غیرفعال</option>
        </select>
        <button class="admin-primary-btn" type="submit">اعمال فیلتر</button>
        @if ($search !== '' || $status !== '')<a class="admin-secondary-btn" href="{{ route('admin.unions.index') }}">حذف فیلتر</a>@endif
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
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
                                <a href="{{ route('admin.unions.edit', $union) }}">ویرایش</a>
                                <form action="{{ route('admin.unions.destroy', $union) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>
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
