@extends('admin.layouts.app')

@section('title', 'جزئیات صفحه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">جزئیات صفحه</p><h2>{{ $page->title }}</h2></div>
    <div class="admin-actions"><a href="{{ route('admin.pages.edit', $page) }}">ویرایش</a>@if ($page->status === 'published')<a class="admin-primary-btn" href="{{ route('pages.show', $page) }}" target="_blank">مشاهده صفحه در سایت</a>@endif<a href="{{ route('admin.pages.index') }}">بازگشت</a></div>
</div>

<div class="admin-panel-card">
    <dl class="admin-detail-list">
        <div><dt>اسلاگ</dt><dd><code>{{ $page->slug }}</code></dd></div>
        <div><dt>وضعیت</dt><dd>{{ \App\Models\Page::statusLabels()[$page->status] ?? $page->status }}</dd></div>
        <div><dt>نویسنده</dt><dd>{{ $page->author?->name ?: '—' }}</dd></div>
        <div><dt>تاییدکننده</dt><dd>{{ $page->approver?->name ?: '—' }}</dd></div>
        <div><dt>تاریخ انتشار</dt><dd>{{ jalali_datetime($page->published_at) ?: '—' }}</dd></div>
        <div><dt>فعال</dt><dd>{{ $page->is_active ? 'بله' : 'خیر' }}</dd></div>
        @if ($page->rejected_reason)<div><dt>دلیل رد</dt><dd>{{ $page->rejected_reason }}</dd></div>@endif
    </dl>

    @if ($page->status === 'published')
        <div class="admin-actions mb-4"><a class="admin-primary-btn" href="{{ route('pages.show', $page) }}" target="_blank">مشاهده صفحه در سایت</a></div>
    @elseif (auth()->user()?->hasPermission('pages.approve'))
        <div class="admin-actions mb-4">
            <form action="{{ route('admin.pages.approve', $page) }}" method="POST">@csrf @method('PATCH')<button class="admin-secondary-btn" type="submit">تایید</button></form>
            <form action="{{ route('admin.pages.publish', $page) }}" method="POST">@csrf @method('PATCH')<button class="admin-primary-btn" type="submit">انتشار</button></form>
        </div>
        <form class="admin-reject-form" action="{{ route('admin.pages.reject', $page) }}" method="POST">
            @csrf @method('PATCH')
            <textarea class="form-control" name="rejected_reason" rows="2" placeholder="دلیل رد صفحه..."></textarea>
            <button class="admin-secondary-btn" type="submit">رد صفحه</button>
        </form>
    @endif

    <h3 class="admin-section-title mt-4">پیش‌نمایش محتوا</h3>
    <div class="admin-content-preview">{!! $page->body ?: '<p class="text-muted">بدون محتوا</p>' !!}</div>
</div>
@endsection
