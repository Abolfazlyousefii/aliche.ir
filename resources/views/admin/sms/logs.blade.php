@extends('admin.layouts.app')

@section('title', 'تاریخچه پیامک‌ها')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">پیامک اطلاع‌رسانی</p><h2>تاریخچه پیامک‌ها</h2></div>
    @if (request()->user()->hasPermission('sms.send'))<a class="admin-primary-btn" href="{{ route('admin.sms.create') }}">ارسال پیامک جدید</a>@endif
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="{{ route('admin.sms.logs') }}" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="متن پیام یا گیرنده...">
        <select class="form-control" name="send_type" aria-label="فیلتر نوع ارسال">
            <option value="">همه نوع‌ها</option>
            @foreach ($sendTypeLabels as $type => $label)
                <option value="{{ $type }}" @selected($sendType === $type)>{{ $label }}</option>
            @endforeach
        </select>
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
        @if ($search !== '' || $sendType !== '' || $status !== '' || $unionId)<a class="admin-secondary-btn" href="{{ route('admin.sms.logs') }}">حذف فیلتر</a>@endif
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead><tr><th>متن پیام</th><th>نوع ارسال</th><th>اتحادیه</th><th>گیرندگان</th><th>وضعیت</th><th>ارسال‌کننده</th><th>زمان ثبت</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ Str::limit($log->message, 70) }}</td>
                        <td>{{ $log->send_type_label }}</td>
                        <td>{{ $log->union?->display_title ?: 'چند اتحادیه / عمومی' }}</td>
                        <td>{{ $log->recipient_count }}</td>
                        <td><span class="admin-status-badge status-{{ $log->status }}">{{ $log->status_label }}</span></td>
                        <td>{{ $log->sender?->name ?: '—' }}</td>
                        <td>{{ jalali_datetime($log->created_at) ?: '—' }}</td>
                        <td><a href="{{ route('admin.sms.show', $log) }}">جزئیات</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">پیامکی با معیارهای انتخاب‌شده یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $logs])
</div>
@endsection
