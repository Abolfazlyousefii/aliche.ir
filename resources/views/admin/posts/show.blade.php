@extends('admin.layouts.app')

@section('title', 'جزئیات خبر')

@section('content')
@php
    $cleanExcerpt = trim(strip_tags(html_entity_decode((string) $post->excerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    $decodedBody = html_entity_decode((string) $post->body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
@endphp
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">جزئیات خبر</p><h2>{{ $post->title }}</h2></div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-secondary-btn" href="{{ route('admin.posts.index') }}">بازگشت</a>
        <a class="admin-primary-btn" href="{{ route('admin.posts.edit', $post) }}">ویرایش</a>
        @if ($post->status === 'published')<a class="admin-secondary-btn" href="{{ route('posts.show', $post->slug) }}" target="_blank">مشاهده صفحه در سایت</a>@endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-panel-card">
            @if ($post->featured_image)
                <img class="img-fluid rounded mb-3" src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}">
            @endif
            @if ($cleanExcerpt !== '')
                <p class="text-muted">{{ $cleanExcerpt }}</p>
            @endif
            <div class="admin-rich-content">{!! $decodedBody ?: '<p class="text-muted">محتوایی برای این خبر ثبت نشده است.</p>' !!}</div>
        </div>
        @if ($post->galleries->isNotEmpty())
            <div class="admin-panel-card mt-3">
                <h3 class="h5 mb-3">گالری خبر</h3>
                <div class="row g-3">
                    @foreach ($post->galleries as $gallery)
                        <div class="col-md-4">
                            <img class="img-fluid rounded" src="{{ Storage::url($gallery->image) }}" alt="{{ $gallery->caption ?: $post->title }}">
                            @if ($gallery->caption)<p class="small mt-2 mb-0">{{ $gallery->caption }}</p>@endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    <div class="col-lg-4">
        <div class="admin-panel-card">
            <dl class="row mb-0">
                <dt class="col-5">وضعیت</dt><dd class="col-7"><span class="admin-status-badge status-{{ $post->status }}">{{ $post->status_label }}</span></dd>
                <dt class="col-5">نوع</dt><dd class="col-7">{{ $post->type_label }}</dd>
                <dt class="col-5">دسته‌بندی</dt><dd class="col-7">{{ $post->category?->title ?: '—' }}</dd>
                <dt class="col-5">اتحادیه</dt><dd class="col-7">{{ $post->union?->name ?: 'عمومی' }}</dd>
                <dt class="col-5">جایگاه صفحه اصلی</dt><dd class="col-7">{{ $post->homepage_position_label }}</dd>
                <dt class="col-5">بازدید</dt><dd class="col-7">{{ number_format($post->views_count) }}</dd>
                <dt class="col-5">نویسنده</dt><dd class="col-7">{{ $post->author?->name ?: '—' }}</dd>
                <dt class="col-5">تاییدکننده</dt><dd class="col-7">{{ $post->approver?->name ?: '—' }}</dd>
                <dt class="col-5">انتشار</dt><dd class="col-7">{{ jalali_datetime($post->published_at) ?: '—' }}</dd>
            </dl>
        </div>
        @if ($post->rejected_reason)
            <div class="admin-panel-card mt-3"><strong>دلیل رد:</strong><p class="mb-0 mt-2">{{ $post->rejected_reason }}</p></div>
        @endif
        <div class="admin-panel-card mt-3">
            <h3 class="h6">اقدام مدیریتی</h3>
            <div class="d-flex gap-2 flex-wrap mb-3">
                @if ($post->canBePublished() && auth()->user()?->hasPermission('posts.publish'))
                    <form action="{{ route('admin.posts.publish', $post) }}" method="POST" data-single-submit>@csrf @method('PATCH')<button class="admin-primary-btn" type="submit" data-loading-text="در حال تایید و انتشار...">تایید و انتشار محتوا</button></form>
                @endif
                @if ($post->canBeUnpublished() && auth()->user()?->hasPermission('posts.publish'))
                    <form action="{{ route('admin.posts.unpublish', $post) }}" method="POST" data-single-submit>@csrf @method('PATCH')<button class="admin-secondary-btn" type="submit" data-loading-text="در حال بازگرداندن به پیش‌نویس...">بازگرداندن به پیش‌نویس</button></form>
                @endif
                @if (! $post->canBeApproved() && ! $post->canBePublished() && ! $post->canBeUnpublished())
                    <span class="text-muted small">برای وضعیت فعلی این خبر اقدام انتشار دیگری لازم نیست.</span>
                @endif
            </div>
            @if ($post->canBeRejected() && auth()->user()?->hasPermission('posts.approve'))
                <form action="{{ route('admin.posts.reject', $post) }}" method="POST" data-single-submit>
                    @csrf
                    @method('PATCH')
                    <label class="form-label" for="rejected_reason">دلیل رد خبر</label>
                    <textarea class="form-control mb-2" id="rejected_reason" name="rejected_reason" rows="3" required>{{ old('rejected_reason') }}</textarea>
                    <button class="admin-secondary-btn" type="submit" data-loading-text="در حال ثبت رد...">رد خبر</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
