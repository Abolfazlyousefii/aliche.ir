@extends('admin.layouts.app')

@section('title', 'مدیریت شکایات')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">شکایات</p><h2>مدیریت شکایات ثبت‌شده</h2></div>
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="{{ route('admin.complaints.index') }}" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="کد رهگیری، نام، موبایل یا موضوع...">
        <select class="form-control" name="status" aria-label="فیلتر وضعیت">
            <option value="">همه وضعیت‌ها</option>
            @foreach ($statusLabels as $itemStatus => $label)
                <option value="{{ $itemStatus }}" @selected($status === $itemStatus)>{{ $label }}</option>
            @endforeach
        </select>
        <select class="form-control" name="union_id" aria-label="فیلتر اتحادیه">
            <option value="">همه اتحادیه‌ها</option>
            @foreach ($unions as $union)
                <option value="{{ $union->id }}" @selected((string) $unionId === (string) $union->id)>{{ $union->display_title }}</option>
            @endforeach
        </select>
        <button class="admin-primary-btn" type="submit">اعمال</button>
        @if ($search !== '' || $status !== '' || $unionId)<a class="admin-secondary-btn" href="{{ route('admin.complaints.index') }}">حذف فیلتر</a>@endif
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead><tr><th>کد رهگیری</th><th>شاکی</th><th>موبایل</th><th>موضوع</th><th>اتحادیه</th><th>وضعیت</th><th>تاریخ ثبت</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse ($complaints as $complaint)
                    <tr>
                        <td><strong dir="ltr">{{ $complaint->tracking_code }}</strong></td>
                        <td>{{ $complaint->full_name }}</td>
                        <td>{{ $complaint->mobile }}</td>
                        <td>{{ Str::limit($complaint->subject, 45) }}</td>
                        <td>{{ $complaint->union?->display_title ?: '—' }}</td>
                        <td><span class="admin-status-badge status-{{ $complaint->status }}">{{ $complaint->status_label }}</span></td>
                        <td>{{ jalali_datetime($complaint->created_at) ?: '—' }}</td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.complaints.show', $complaint) }}">مشاهده</a>
                                @if (request()->user()->hasPermission('complaints.edit'))
                                    <a href="{{ route('admin.complaints.edit', $complaint) }}">ویرایش</a>
                                @endif
                                @if (request()->user()->hasPermission('complaints.delete'))
                                    <form action="{{ route('admin.complaints.destroy', $complaint) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">شکایتی یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $complaints])
</div>
@endsection
