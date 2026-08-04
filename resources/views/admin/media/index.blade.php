@extends('admin.layouts.app')
@section('title','کتابخانه رسانه')
@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">Media Library</p><h2>کتابخانه رسانه</h2></div>
    <div class="admin-media-view-tabs" aria-label="حالت نمایش رسانه"><button class="is-active" type="button">شبکه‌ای</button><button type="button">فهرستی</button></div>
</div>

<div class="admin-wp-media-shell" data-wp-media-library>
    <aside class="admin-wp-upload-panel">
        <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="admin-wp-upload" data-media-dropzone>@csrf
            <input id="mediaFiles" class="admin-wp-upload-input" type="file" name="files[]" accept="image/*" multiple required>
            <label for="mediaFiles" class="admin-wp-dropzone">
                <span class="admin-wp-upload-icon">@include('admin.components.icon', ['name' => 'upload'])</span>
                <strong>پرونده‌ها را اینجا رها کنید</strong>
                <small>یا برای انتخاب چند تصویر کلیک کنید؛ مشابه بارگذاری رسانه در وردپرس.</small>
            </label>
            <div class="admin-wp-upload-meta"><span data-media-file-count>هیچ فایلی انتخاب نشده است.</span><button class="admin-primary-btn">آپلود رسانه</button></div>
        </form>
    </aside>

    <section class="admin-wp-library-panel">
        <form class="admin-wp-toolbar">
            <div><strong>کتابخانه رسانه</strong><small>انتخاب، جستجو، کپی آدرس و ویرایش اطلاعات پیوست</small></div>
            <input class="form-control" name="search" value="{{ $search }}" placeholder="جستجوی نام، عنوان یا متن جایگزین">
            <select class="form-control" name="sort"><option value="newest">جدیدترین</option><option value="oldest" @selected(request('sort')==='oldest')>قدیمی‌ترین</option></select>
            <button class="admin-secondary-btn">فیلتر</button>
        </form>

        <div class="admin-wp-media-grid">
            @forelse($media as $item)
                <article class="admin-wp-media-tile" tabindex="0" data-media-tile data-id="{{ $item->id }}">
                    <img src="{{ $item->url }}" alt="{{ $item->alt_text ?: $item->title }}">
                    <button class="admin-wp-media-check" type="button" aria-label="انتخاب رسانه">✓</button>
                    <div class="admin-wp-media-title">{{ $item->title ?: $item->original_name }}</div>
                    <template data-media-details>
                        <div class="admin-wp-attachment-preview"><img src="{{ $item->url }}" alt="{{ $item->alt_text ?: $item->title }}"></div>
                        <div class="admin-wp-attachment-fields">
                            <h3>{{ $item->title ?: $item->original_name }}</h3>
                            <p dir="ltr" class="text-muted">{{ $item->path }}</p>
                            <dl><dt>ابعاد</dt><dd>{{ $item->width ?: '—' }} × {{ $item->height ?: '—' }}</dd><dt>حجم</dt><dd>{{ number_format(($item->size ?: 0) / 1024, 1) }} KB</dd><dt>آپلودکننده</dt><dd>{{ $item->uploader?->name ?: '—' }}</dd></dl>
                            <form method="POST" action="{{ route('admin.media.update',$item) }}" class="admin-form">@csrf @method('PUT')
                                <label class="form-label">عنوان</label><input class="form-control" name="title" value="{{ $item->title }}">
                                <label class="form-label">متن جایگزین</label><input class="form-control" name="alt_text" value="{{ $item->alt_text }}">
                                <label class="form-label">کپشن</label><textarea class="form-control" name="caption" rows="2">{{ $item->caption }}</textarea>
                                <label class="form-label">توضیحات</label><textarea class="form-control" name="description" rows="3">{{ $item->description }}</textarea>
                                <div class="admin-form-actions"><button class="admin-primary-btn">ذخیره اطلاعات</button><button class="admin-secondary-btn" type="button" data-copy-url="{{ $item->url }}">کپی آدرس</button></div>
                            </form>
                            <form method="POST" action="{{ route('admin.media.destroy',$item) }}" class="mt-3">@csrf @method('DELETE')<button class="admin-danger-btn" type="submit">حذف دائمی</button></form>
                        </div>
                    </template>
                </article>
            @empty
                <p class="empty-state">رسانه‌ای یافت نشد.</p>
            @endforelse
        </div>
        @include('admin.partials.pagination',['paginator'=>$media])
    </section>
</div>
<div class="admin-wp-media-modal" data-media-modal hidden><div class="admin-wp-modal-backdrop" data-media-modal-close></div><div class="admin-wp-modal-dialog" role="dialog" aria-modal="true"><header><strong>جزئیات پیوست</strong><button type="button" data-media-modal-close>×</button></header><div class="admin-wp-modal-body" data-media-modal-body></div></div></div>
@endsection
