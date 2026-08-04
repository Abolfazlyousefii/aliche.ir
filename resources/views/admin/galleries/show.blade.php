@extends('admin.layouts.app')

@section('title', 'جزئیات گالری تصاویر')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">جزئیات گالری</p><h2>{{ $gallery->title }}</h2></div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-secondary-btn" href="{{ route('admin.galleries.index') }}">بازگشت</a>
        @if (request()->user()->hasPermission('galleries.edit'))<a class="admin-primary-btn" href="{{ route('admin.galleries.edit', $gallery) }}">ویرایش</a>@endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-panel-card">
            <h3 class="h5 mb-3">توضیحات</h3>
            <div class="gallery-description mb-0">{{ plain_text($gallery->description) ?: '—' }}</div>
        </div>
        <div class="admin-panel-card mt-3">
            <h3 class="h5 mb-3">تصاویر گالری</h3>
            <div class="row g-3">
                @forelse ($gallery->images as $image)
                    <div class="col-md-4"><div class="border rounded p-2 h-100"><a href="{{ $image->image_url }}" target="_blank"><img src="{{ $image->image_url }}" alt="{{ $image->caption ?: $gallery->title }}" style="width:100%;height:160px;object-fit:cover;border-radius:10px"></a><p class="small mt-2 mb-1">{{ plain_text($image->caption) ?: 'بدون کپشن' }}</p><small class="text-muted">ترتیب: {{ $image->sort_order }}</small></div></div>
                @empty
                    <p class="text-muted mb-0">تصویری برای این گالری ثبت نشده است.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-panel-card">
            @if ($gallery->cover_image)<img src="{{ $gallery->cover_image_url }}" alt="{{ $gallery->title }}" class="img-fluid rounded mb-3">@endif
            <dl class="row mb-0">
                <dt class="col-5">نامک</dt><dd class="col-7" dir="ltr">{{ $gallery->slug }}</dd>
                <dt class="col-5">نوع</dt><dd class="col-7">{{ $gallery->union?->display_title ?: 'عمومی' }}</dd>
                <dt class="col-5">وضعیت</dt><dd class="col-7"><span class="admin-status-badge status-{{ $gallery->status }}">{{ $gallery->status_label }}</span></dd>
                <dt class="col-5">فعال</dt><dd class="col-7">{{ $gallery->is_active ? 'بله' : 'خیر' }}</dd>
                <dt class="col-5">انتشار</dt><dd class="col-7">{{ jalali_datetime($gallery->published_at) ?: '—' }}</dd>
                <dt class="col-5">ترتیب</dt><dd class="col-7">{{ $gallery->sort_order }}</dd>
                <dt class="col-5">ایجادکننده</dt><dd class="col-7">{{ $gallery->creator?->name ?: '—' }}</dd>
                <dt class="col-5">تاییدکننده</dt><dd class="col-7">{{ $gallery->approver?->name ?: '—' }}</dd>
            </dl>
        </div>
        @if ($gallery->rejected_reason)
            <div class="admin-panel-card mt-3"><strong>دلیل رد:</strong><p class="mb-0 mt-2">{{ $gallery->rejected_reason }}</p></div>
        @endif
    </div>
</div>
@endsection
