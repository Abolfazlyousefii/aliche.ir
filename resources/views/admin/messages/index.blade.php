@extends('admin.layouts.app')
@section('title', 'پیام‌های داخلی')
@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">ارتباطات و پیگیری</p><h2>{{ request()->user()->hasPermission('messages.manage') ? 'مدیریت پیام‌های داخلی' : 'پیام‌های دریافتی من' }}</h2></div>
    <div class="admin-actions">
        <a class="admin-secondary-btn" href="{{ route('admin.messages.inbox') }}">صندوق ورودی</a>
        <a class="admin-secondary-btn" href="{{ route('admin.messages.sent') }}">ارسال‌شده‌ها</a>
        @if(request()->user()->hasPermission('messages.send'))
            <a class="admin-primary-btn" href="{{ route('admin.messages.create') }}">ارسال پیام جدید</a>
        @endif
    </div>
</div>
@include('admin.partials.list-filters', [
    'listRoute' => 'admin.messages.index',
    'listTitle' => 'جستجوی پیام‌های داخلی',
    'listDescription' => 'فقط پیام‌هایی که اجازه مشاهده دارید در نتایج نمایش داده می‌شوند.',
    'listSearch' => request('search', ''),
    'listPlaceholder' => 'عنوان یا متن پیام...',
    'listPaginator' => $messages,
    'listFilters' => [
        ['name' => 'priority', 'label' => 'اولویت', 'value' => request('priority', ''), 'options' => ['low' => 'کم', 'normal' => 'عادی', 'important' => 'مهم', 'urgent' => 'فوری']],
        ['name' => 'status', 'label' => 'وضعیت خواندن', 'value' => request('status', ''), 'options' => ['unread' => 'خوانده‌نشده', 'read' => 'خوانده‌شده']],
    ],
])
<div class="admin-panel-card">
    @include('admin.messages._table', ['messages' => $messages])
</div>
@endsection
