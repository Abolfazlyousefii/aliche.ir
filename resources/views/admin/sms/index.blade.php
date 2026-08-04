@extends('admin.layouts.app')

@section('title', 'مدیریت پیامک اطلاع‌رسانی')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">پیامک اطلاع‌رسانی</p><h2>مدیریت پیامک‌ها</h2></div>
    <div class="d-flex gap-2 flex-wrap">
        @if (request()->user()->hasPermission('sms.send'))
            <a class="admin-primary-btn" href="{{ route('admin.sms.create') }}">ارسال پیامک</a>
        @endif
        @if (request()->user()->hasPermission('sms.logs'))
            <a class="admin-secondary-btn" href="{{ route('admin.sms.logs') }}">تاریخچه ارسال</a>
        @endif
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="admin-panel-card"><span class="text-muted">کل پیامک‌ها</span><h3 class="mt-2 mb-0">{{ $totalLogs }}</h3></div></div>
    <div class="col-md-3"><div class="admin-panel-card"><span class="text-muted">ارسال‌شده</span><h3 class="mt-2 mb-0">{{ $sentLogs }}</h3></div></div>
    <div class="col-md-3"><div class="admin-panel-card"><span class="text-muted">در انتظار</span><h3 class="mt-2 mb-0">{{ $pendingLogs }}</h3></div></div>
    <div class="col-md-3"><div class="admin-panel-card"><span class="text-muted">ناموفق/ناقص</span><h3 class="mt-2 mb-0">{{ $failedLogs }}</h3></div></div>
</div>

<div class="admin-panel-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h5 mb-0">آخرین پیامک‌ها</h3>
        @if (request()->user()->hasPermission('sms.logs'))<a href="{{ route('admin.sms.logs') }}">مشاهده همه</a>@endif
    </div>
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead><tr><th>متن</th><th>نوع ارسال</th><th>تعداد گیرندگان</th><th>وضعیت</th><th>ارسال‌کننده</th><th>زمان</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse ($recentLogs as $log)
                    <tr>
                        <td>{{ Str::limit($log->message, 60) }}</td>
                        <td>{{ $log->send_type_label }}</td>
                        <td>{{ $log->recipient_count }}</td>
                        <td><span class="admin-status-badge status-{{ $log->status }}">{{ $log->status_label }}</span></td>
                        <td>{{ $log->sender?->name ?: '—' }}</td>
                        <td>{{ jalali_datetime($log->created_at) ?: '—' }}</td>
                        <td>@if (request()->user()->hasPermission('sms.logs'))<a href="{{ route('admin.sms.show', $log) }}">جزئیات</a>@else — @endif</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">هنوز پیامکی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
