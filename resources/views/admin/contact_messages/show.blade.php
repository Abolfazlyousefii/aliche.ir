@extends('admin.layouts.app')

@section('title', 'جزئیات پیام تماس')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">پیام تماس</p>
        <h2>{{ $contactMessage->subject }}</h2>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-secondary-btn" href="{{ route('admin.contact_messages.index') }}">بازگشت</a>
        @if (request()->user()->hasPermission('contact_messages.delete'))
            <form action="{{ route('admin.contact_messages.destroy', $contactMessage) }}" method="POST">
                @csrf
                @method('DELETE')
                <button class="admin-danger-btn" type="submit">حذف پیام</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-panel-card">
            <h3 class="h5 mb-3">متن پیام</h3>
            <div>{!! nl2br(e($contactMessage->message)) !!}</div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-panel-card">
            <dl class="row mb-0">
                <dt class="col-5">نام</dt><dd class="col-7">{{ $contactMessage->full_name }}</dd>
                <dt class="col-5">موبایل</dt><dd class="col-7" dir="ltr">{{ $contactMessage->mobile }}</dd>
                <dt class="col-5">ایمیل</dt><dd class="col-7" dir="ltr">{{ $contactMessage->email ?: '—' }}</dd>
                <dt class="col-5">وضعیت</dt><dd class="col-7">{{ $contactMessage->is_read ? 'خوانده‌شده' : 'خوانده‌نشده' }}</dd>
                <dt class="col-5">خوانده‌شده در</dt><dd class="col-7">{{ jalali_datetime($contactMessage->read_at) ?: '—' }}</dd>
                <dt class="col-5">IP</dt><dd class="col-7" dir="ltr">{{ $contactMessage->ip_address ?: '—' }}</dd>
                <dt class="col-5">ثبت</dt><dd class="col-7">{{ jalali_datetime($contactMessage->created_at) ?: '—' }}</dd>
            </dl>
        </div>

        <div class="admin-panel-card mt-3">
            <h3 class="h6 mb-2">User Agent</h3>
            <small class="text-muted" dir="ltr">{{ $contactMessage->user_agent ?: '—' }}</small>
        </div>
    </div>
</div>
@endsection
