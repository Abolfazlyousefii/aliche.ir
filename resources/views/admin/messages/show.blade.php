@extends('admin.layouts.app')

@section('title', $message->subject)

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">پیام‌های داخلی</p>
        <h2>{{ $message->subject }}</h2>
    </div>
    <div class="admin-actions">
        <a class="admin-secondary-btn" href="{{ route('admin.messages.inbox') }}">صندوق ورودی</a>
        <a class="admin-secondary-btn" href="{{ route('admin.messages.sent') }}">ارسال‌شده‌ها</a>
        @if (request()->user()->hasPermission('messages.delete'))
            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" data-admin-delete-form data-delete-message="پیام «{{ $message->subject }}» حذف شود؟">
                @csrf
                @method('DELETE')
                <button class="admin-danger-btn" type="submit">حذف</button>
            </form>
        @endif
    </div>
</div>

<div class="admin-panel-card mb-3">
    <div class="row g-3">
        <div class="col-md-4"><strong>فرستنده:</strong> {{ $message->sender?->name ?? 'سیستم' }}</div>
        <div class="col-md-4"><strong>گیرنده:</strong> {{ $message->recipient?->name ?? 'حذف‌شده' }}</div>
        <div class="col-md-4"><strong>تاریخ ارسال:</strong> {{ $message->sent_at ? jdate($message->sent_at)->format('Y/m/d H:i') : jdate($message->created_at)->format('Y/m/d H:i') }}</div>
        <div class="col-md-4"><strong>اولویت:</strong> <span class="badge bg-{{ $message->priority === 'urgent' ? 'danger' : ($message->priority === 'important' ? 'warning text-dark' : 'secondary') }}">{{ $message->priorityLabel() }}</span></div>
        <div class="col-md-4"><strong>نوع:</strong> {{ $message->typeLabel() }}</div>
        <div class="col-md-4"><strong>وضعیت خواندن:</strong> {{ $message->read_at ? 'خوانده‌شده در '.jdate($message->read_at)->format('Y/m/d H:i') : 'خوانده‌نشده' }}</div>
    </div>
    <hr>
    <article class="admin-rich-content">
        {!! $message->body !!}
    </article>
</div>

@if ($message->parent)
    <div class="admin-panel-card mb-3">
        <p class="admin-eyebrow">پیام اصلی مرتبط</p>
        <a href="{{ route('admin.messages.show', $message->parent) }}">{{ $message->parent->subject }}</a>
    </div>
@endif

<div class="admin-panel-card mb-3">
    <h3 class="h5 mb-3">پاسخ‌ها</h3>
    @forelse ($message->replies as $reply)
        <div class="border rounded p-3 mb-2">
            <div class="d-flex justify-content-between gap-2 flex-wrap mb-2">
                <strong>{{ $reply->sender?->name ?? 'سیستم' }} به {{ $reply->recipient?->name ?? 'حذف‌شده' }}</strong>
                <small class="text-muted">{{ $reply->sent_at ? jdate($reply->sent_at)->format('Y/m/d H:i') : jdate($reply->created_at)->format('Y/m/d H:i') }}</small>
            </div>
            <div class="admin-rich-content">{!! $reply->body !!}</div>
        </div>
    @empty
        <p class="text-muted mb-0">هنوز پاسخی ثبت نشده است.</p>
    @endforelse
</div>

@if ($message->allow_reply && request()->user()->hasPermission('messages.reply'))
    <form class="admin-panel-card" method="POST" action="{{ route('admin.messages.reply', $message) }}">
        @csrf
        <h3 class="h5 mb-3">ثبت پاسخ</h3>
        <textarea class="form-control js-rich-editor" name="body" rows="6" required>{{ old('body') }}</textarea>
        @error('body')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        <div class="mt-3"><button class="admin-primary-btn" type="submit">ارسال پاسخ</button></div>
    </form>
@endif
@endsection
