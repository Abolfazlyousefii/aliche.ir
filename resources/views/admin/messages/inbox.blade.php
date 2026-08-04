@extends('admin.layouts.app')

@section('title', 'صندوق ورودی پیام‌ها')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">پیام‌های داخلی</p>
        <h2>صندوق ورودی</h2>
    </div>
    <div class="admin-actions">
        <a class="admin-secondary-btn" href="{{ route('admin.messages.index') }}">همه پیام‌ها</a>
        <a class="admin-secondary-btn" href="{{ route('admin.messages.sent') }}">ارسال‌شده‌ها</a>
        @if (request()->user()->hasPermission('messages.send'))
            <a class="admin-primary-btn" href="{{ route('admin.messages.create') }}">ارسال پیام جدید</a>
        @endif
    </div>
</div>

<div class="admin-panel-card">
    @include('admin.messages._table', ['messages' => $messages])
</div>
@endsection
