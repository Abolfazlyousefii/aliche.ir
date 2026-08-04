@extends('admin.layouts.app')

@section('title', 'جزئیات اطلاعیه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">جزئیات اطلاعیه</p><h2>{{ $announcement->title }}</h2></div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-secondary-btn" href="{{ route('admin.announcements.index') }}">بازگشت</a>
        <a class="admin-primary-btn" href="{{ route('admin.announcements.edit', $announcement) }}">ویرایش</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-panel-card">
            @if ($announcement->featured_image)
                <img class="img-fluid rounded mb-3" src="{{ image_url($announcement->featured_image) }}" alt="{{ $announcement->title }}">
            @endif
            <p class="text-muted">{{ plain_text($announcement->excerpt) }}</p>
            <div class="admin-rich-content">{!! $announcement->body !!}</div>
            @if ($announcement->attachment)
                <a class="admin-primary-btn mt-3" href="{{ image_url($announcement->attachment, '') }}" target="_blank">دانلود پیوست</a>
            @endif
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-panel-card">
            <dl class="row mb-0">
                <dt class="col-5">وضعیت</dt><dd class="col-7"><span class="admin-status-badge status-{{ $announcement->status }}">{{ $announcement->status_label }}</span></dd>
                <dt class="col-5">نوع نمایش</dt><dd class="col-7">{{ $announcement->visibility_label }}</dd>
                <dt class="col-5">دسته‌بندی</dt><dd class="col-7">{{ $announcement->category?->title ?: '—' }}</dd>
                <dt class="col-5">اتحادیه</dt><dd class="col-7">{{ $announcement->union?->name ?: 'عمومی' }}</dd>
                <dt class="col-5">مهم</dt><dd class="col-7">{{ $announcement->is_important ? 'بله' : 'خیر' }}</dd>
                <dt class="col-5">صفحه اصلی</dt><dd class="col-7">{{ $announcement->show_on_home ? 'بله' : 'خیر' }}</dd>
                <dt class="col-5">فعال</dt><dd class="col-7">{{ $announcement->is_active ? 'بله' : 'خیر' }}</dd>
                <dt class="col-5">شروع</dt><dd class="col-7">{{ jalali_datetime($announcement->starts_at) ?: '—' }}</dd>
                <dt class="col-5">انقضا</dt><dd class="col-7">{{ jalali_datetime($announcement->expires_at) ?: 'بدون انقضا' }}</dd>
                <dt class="col-5">نویسنده</dt><dd class="col-7">{{ $announcement->author?->name ?: '—' }}</dd>
                <dt class="col-5">تاییدکننده</dt><dd class="col-7">{{ $announcement->approver?->name ?: '—' }}</dd>
                <dt class="col-5">انتشار</dt><dd class="col-7">{{ jalali_datetime($announcement->published_at) ?: '—' }}</dd>
            </dl>
        </div>
        @if ($announcement->rejected_reason)
            <div class="admin-panel-card mt-3"><strong>دلیل رد:</strong><p class="mb-0 mt-2">{{ $announcement->rejected_reason }}</p></div>
        @endif
        <div class="admin-panel-card mt-3">
            <h3 class="h6">اقدام مدیریتی</h3>
            <div class="d-flex gap-2 flex-wrap mb-3">
                @if ($announcement->status !== 'published' && auth()->user()?->hasPermission('announcements.publish'))
                    <form action="{{ route('admin.announcements.publish', $announcement) }}" method="POST" data-single-submit>@csrf @method('PATCH')<button class="admin-primary-btn" type="submit" data-loading-text="در حال تایید و انتشار...">تایید و انتشار محتوا</button></form>
                @endif
            </div>
            <form action="{{ route('admin.announcements.reject', $announcement) }}" method="POST">
                @csrf
                @method('PATCH')
                <label class="form-label" for="rejected_reason">دلیل رد اطلاعیه</label>
                <textarea class="form-control mb-2" id="rejected_reason" name="rejected_reason" rows="3" required>{{ old('rejected_reason') }}</textarea>
                <button class="admin-secondary-btn" type="submit">رد اطلاعیه</button>
            </form>
        </div>
    </div>
</div>
@endsection
