@extends('admin.layouts.app')

@section('title', 'مدیریت اعضای اتحادیه‌ها')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">اعضای اتحادیه‌ها</p><h2>مدیریت اعضای اتحادیه‌ها</h2></div>
    <a class="admin-primary-btn" href="{{ route('admin.union_members.create') }}">ایجاد عضو جدید</a>
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="{{ route('admin.union_members.index') }}" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="نام، کد ملی، موبایل، کد عضویت یا کسب‌وکار...">
        <select class="form-control" name="status" aria-label="فیلتر وضعیت">
            <option value="">همه وضعیت‌ها</option>
            @foreach (\App\Models\UnionMember::STATUSES as $itemStatus)
                <option value="{{ $itemStatus }}" @selected($status === $itemStatus)>{{ $itemStatus }}</option>
            @endforeach
        </select>
        <select class="form-control" name="union_id" aria-label="فیلتر اتحادیه">
            <option value="">همه اتحادیه‌ها</option>
            @foreach ($unions as $union)
                <option value="{{ $union->id }}" @selected((string) $unionId === (string) $union->id)>{{ $union->display_title }}</option>
            @endforeach
        </select>
        <button class="admin-primary-btn" type="submit">اعمال</button>
        @if ($search !== '' || $status !== '' || $unionId)<a class="admin-secondary-btn" href="{{ route('admin.union_members.index') }}">حذف فیلتر</a>@endif
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead><tr><th>نام</th><th>کد ملی</th><th>موبایل</th><th>نام کسب‌وکار</th><th>اتحادیه</th><th>وضعیت</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse ($members as $member)
                    <tr>
                        <td><strong>{{ $member->full_name }}</strong><br><small>{{ $member->membership_code ?: 'بدون کد عضویت' }}</small></td>
                        <td>{{ $member->national_code ?: '—' }}</td>
                        <td>{{ $member->mobile ?: '—' }}</td>
                        <td>{{ $member->business_name ?: '—' }}</td>
                        <td>{{ $member->union?->display_title ?: '—' }}</td>
                        <td><span class="admin-status-badge status-{{ $member->status }}">{{ $member->status }}</span></td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.union_members.show', $member) }}">مشاهده</a>
                                <a href="{{ route('admin.union_members.edit', $member) }}">ویرایش</a>
                                <form action="{{ route('admin.union_members.destroy', $member) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">عضوی یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $members])
</div>
@endsection
