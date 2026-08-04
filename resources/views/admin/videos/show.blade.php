@extends('admin.layouts.app')

@section('title', 'جزئیات ویدیو')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">جزئیات ویدیو</p><h2>{{ $video->title }}</h2></div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-secondary-btn" href="{{ route('admin.videos.index') }}">بازگشت</a>
        @if (request()->user()->hasPermission('videos.edit'))<a class="admin-primary-btn" href="{{ route('admin.videos.edit', $video) }}">ویرایش</a>@endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-panel-card">
            <h3 class="h5 mb-3">پخش ویدیو</h3>
            @if ($video->video_type === 'upload' && $video->video_file)
                <video controls style="width:100%;max-height:460px;border-radius:14px;background:#000" poster="{{ image_url($video->cover_image, '') }}">
                    <source src="{{ image_url($video->video_file, '') }}">
                    مرورگر شما از نمایش ویدیو پشتیبانی نمی‌کند.
                </video>
            @elseif ($video->video_type === 'aparat' && $video->aparat_url)
                <div class="ratio ratio-16x9"><iframe src="{{ $video->aparat_embed_url }}" allowfullscreen></iframe></div>
                <a class="d-inline-block mt-2" href="{{ $video->aparat_url }}" target="_blank">مشاهده در آپارات</a>
            @else
                <p class="text-muted mb-0">منبع ویدیو ثبت نشده است.</p>
            @endif
        </div>
        <div class="admin-panel-card mt-3"><h3 class="h5 mb-3">توضیحات</h3><p class="mb-0">{{ plain_text($video->description) ?: '—' }}</p></div>
    </div>
    <div class="col-lg-4">
        <div class="admin-panel-card">
            @if ($video->cover_image)<img src="{{ image_url($video->cover_image) }}" alt="{{ $video->title }}" class="img-fluid rounded mb-3">@endif
            <dl class="row mb-0">
                <dt class="col-5">نامک</dt><dd class="col-7" dir="ltr">{{ $video->slug }}</dd>
                <dt class="col-5">نوع</dt><dd class="col-7">{{ $video->type_label }}</dd>
                <dt class="col-5">اتحادیه</dt><dd class="col-7">{{ $video->union?->display_title ?: 'عمومی' }}</dd>
                <dt class="col-5">وضعیت</dt><dd class="col-7"><span class="admin-status-badge status-{{ $video->status }}">{{ $video->status_label }}</span></dd>
                <dt class="col-5">فعال</dt><dd class="col-7">{{ $video->is_active ? 'بله' : 'خیر' }}</dd>
                <dt class="col-5">انتشار</dt><dd class="col-7">{{ jalali_datetime($video->published_at) ?: '—' }}</dd>
                <dt class="col-5">ترتیب</dt><dd class="col-7">{{ $video->sort_order }}</dd>
                <dt class="col-5">ایجادکننده</dt><dd class="col-7">{{ $video->creator?->name ?: '—' }}</dd>
                <dt class="col-5">تاییدکننده</dt><dd class="col-7">{{ $video->approver?->name ?: '—' }}</dd>
            </dl>
        </div>
        @if ($video->rejected_reason)<div class="admin-panel-card mt-3"><strong>دلیل رد:</strong><p class="mb-0 mt-2">{{ $video->rejected_reason }}</p></div>@endif
    </div>
</div>
@endsection
