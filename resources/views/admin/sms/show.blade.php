@extends('admin.layouts.app')

@section('title', 'جزئیات پیامک')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">جزئیات پیامک</p><h2>گزارش ارسال پیامک</h2></div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-secondary-btn" href="{{ route('admin.sms.logs') }}">بازگشت به تاریخچه</a>
        @if (request()->user()->hasPermission('sms.send'))<a class="admin-primary-btn" href="{{ route('admin.sms.create') }}">ارسال پیامک جدید</a>@endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-panel-card">
            <h3 class="h5 mb-3">متن پیامک</h3>
            <p class="mb-0">{{ $smsLog->message }}</p>
        </div>

        <div class="admin-panel-card mt-3">
            <h3 class="h5 mb-3">گیرندگان</h3>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead><tr><th>نام</th><th>موبایل</th><th>اتحادیه</th><th>کد عضویت</th></tr></thead>
                    <tbody>
                        @foreach ($smsLog->recipients ?? [] as $recipient)
                            <tr>
                                <td>{{ $recipient['full_name'] ?? '—' }}</td>
                                <td dir="ltr">{{ $recipient['mobile'] ?? '—' }}</td>
                                <td>{{ $recipient['union_title'] ?? '—' }}</td>
                                <td>{{ $recipient['membership_code'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-panel-card mt-3">
            <h3 class="h5 mb-3">پاسخ سرویس پیامک</h3>
            <pre class="mb-0" dir="ltr" style="white-space:pre-wrap">{{ json_encode($smsLog->provider_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="admin-panel-card">
            <dl class="row mb-0">
                <dt class="col-5">نوع ارسال</dt><dd class="col-7">{{ $smsLog->send_type_label }}</dd>
                <dt class="col-5">وضعیت</dt><dd class="col-7"><span class="admin-status-badge status-{{ $smsLog->status }}">{{ $smsLog->status_label }}</span></dd>
                <dt class="col-5">تعداد گیرندگان</dt><dd class="col-7">{{ $smsLog->recipient_count }}</dd>
                <dt class="col-5">اتحادیه</dt><dd class="col-7">{{ $smsLog->union?->display_title ?: 'چند اتحادیه / عمومی' }}</dd>
                <dt class="col-5">ارسال‌کننده</dt><dd class="col-7">{{ $smsLog->sender?->name ?: '—' }}</dd>
                <dt class="col-5">زمان ارسال</dt><dd class="col-7">{{ jalali_datetime($smsLog->sent_at) ?: '—' }}</dd>
                <dt class="col-5">زمان ثبت</dt><dd class="col-7">{{ jalali_datetime($smsLog->created_at) ?: '—' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
