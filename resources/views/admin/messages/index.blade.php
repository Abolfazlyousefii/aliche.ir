@extends('admin.layouts.app')

@section('title', 'پیام‌های داخلی')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">پیام‌های داخلی</p>
        <h2>{{ request()->user()->hasPermission('messages.manage') ? 'مدیریت همه پیام‌ها' : 'صندوق ورودی من' }}</h2>
    </div>
    <div class="admin-actions">
        <a class="admin-secondary-btn" href="{{ route('admin.messages.inbox') }}">صندوق ورودی</a>
        <a class="admin-secondary-btn" href="{{ route('admin.messages.sent') }}">ارسال‌شده‌ها</a>
        @if (request()->user()->hasPermission('messages.send'))
            <a class="admin-primary-btn" href="{{ route('admin.messages.create') }}">ارسال پیام جدید</a>
        @endif
    </div>
</div>

<div class="admin-panel-card mb-3">
    <form class="row g-3" method="GET">
        <div class="col-md-5"><input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="جستجو در عنوان یا متن پیام"></div>
        <div class="col-md-3">
            <select class="form-control" name="priority">
                <option value="">همه اولویت‌ها</option>
                @foreach (['low' => 'کم', 'normal' => 'عادی', 'important' => 'مهم', 'urgent' => 'فوری'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-control" name="status">
                <option value="">همه وضعیت‌ها</option>
                <option value="unread" @selected(request('status') === 'unread')>خوانده‌نشده</option>
                <option value="read" @selected(request('status') === 'read')>خوانده‌شده</option>
            </select>
        </div>
        <div class="col-md-1"><button class="admin-primary-btn w-100" type="submit">فیلتر</button></div>
    </form>
</div>

<div class="admin-panel-card">
    @include('admin.messages._table', ['messages' => $messages])
</div>
@endsection
