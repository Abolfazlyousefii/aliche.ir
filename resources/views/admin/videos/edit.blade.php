@extends('admin.layouts.app')

@section('title', 'ویرایش ویدیو')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">ویدیوها</p><h2>{{ $video->title }}</h2></div>
    <div class="d-flex gap-2 flex-wrap"><a class="admin-secondary-btn" href="{{ route('admin.videos.show', $video) }}">مشاهده</a><a class="admin-secondary-btn" href="{{ route('admin.videos.index') }}">بازگشت</a></div>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.videos.update', $video) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="title">عنوان</label><input class="form-control" id="title" name="title" value="{{ old('title', $video->title) }}" required></div>
        <div class="col-md-6"><label class="form-label" for="slug">نامک</label><input class="form-control" id="slug" name="slug" value="{{ old('slug', $video->slug) }}" dir="ltr"></div>
        <div class="col-md-4"><label class="form-label" for="video_type">نوع ویدیو</label><select class="form-control" id="video_type" name="video_type" required>@foreach ($typeLabels as $value => $label)<option value="{{ $value }}" @selected(old('video_type', $video->video_type) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="union_id">اتحادیه</label><select class="form-control" id="union_id" name="union_id"><option value="">ویدیوی عمومی</option>@foreach ($unions as $union)<option value="{{ $union->id }}" @selected((string) old('union_id', $video->union_id) === (string) $union->id)>{{ $union->display_title }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="category_id">دسته‌بندی</label><select class="form-control" id="category_id" name="category_id"><option value="">بدون دسته‌بندی</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)old('category_id', $video->category_id)===(string)$category->id)>{{ $category->title }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="status">وضعیت</label><select class="form-control" id="status" name="status" required>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', $video->status) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="published_at">تاریخ انتشار</label><input class="form-control" id="published_at" name="published_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('published_at', $video->published_at)) }}"></div>
        <div class="col-md-4"><label class="form-label" for="sort_order">ترتیب نمایش</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $video->sort_order) }}"></div>
        <div class="col-md-4"><label class="form-label" for="is_active">فعال</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', (int) $video->is_active) === '1')>فعال</option><option value="0" @selected((string) old('is_active', (int) $video->is_active) === '0')>غیرفعال</option></select></div>
        <div class="col-md-4">
            <label class="form-label" for="cover_image">تصویر کاور</label>
            <input class="form-control" id="cover_image" name="cover_image" type="file" accept="image/*" data-skip-media-picker>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="cover_image_media_id">انتخاب یا آپلود کاور از کتابخانه</button>
            <select class="d-none" name="cover_image_media_id" id="cover_image_media_id" aria-hidden="true" tabindex="-1">
                <option value="">بدون تغییر / انتخاب کاور از کتابخانه</option>
                @foreach(($mediaItems ?? collect()) as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('cover_image_media_id', $currentCoverMediaId) === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <div id="cover_image_media_preview" class="mt-2">
                @if($video->cover_image)<img class="rounded" src="{{ image_url($video->cover_image) }}" alt="{{ $video->title }}" style="width:100%;max-width:260px;height:150px;object-fit:cover">@endif
            </div>
        </div>
        <div class="col-md-4 video-upload-field"><label class="form-label" for="video_file">فایل ویدیوی جدید</label><input class="form-control" id="video_file" name="video_file" type="file" accept="video/*">@if($video->video_file)<a class="d-inline-block mt-2" href="{{ image_url($video->video_file, '') }}" target="_blank">مشاهده فایل فعلی</a>@endif<small class="text-muted d-block mt-1">برای پخش مستقیم در مرورگر، MP4 یا WebM پیشنهاد می‌شود.</small></div>
        <div class="col-md-4 video-aparat-field"><label class="form-label" for="aparat_url">لینک آپارات</label><input class="form-control" id="aparat_url" name="aparat_url" value="{{ old('aparat_url', $video->aparat_url) }}" dir="ltr"></div>
        <div class="col-12"><label class="form-label" for="description">توضیحات</label><textarea class="form-control js-rich-editor" id="description" name="description" rows="4">{{ old('description', $video->description) }}</textarea></div>
        <div class="col-12"><label class="form-label" for="rejected_reason">دلیل رد</label><textarea class="form-control" id="rejected_reason" name="rejected_reason" rows="2">{{ old('rejected_reason', $video->rejected_reason) }}</textarea></div>
    </div>
    <div class="mt-3 d-flex gap-2"><button class="admin-primary-btn" type="submit">ذخیره تغییرات</button><a class="admin-secondary-btn" href="{{ route('admin.videos.show', $video) }}">انصراف</a></div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const type = document.getElementById('video_type');
    const uploadFields = document.querySelectorAll('.video-upload-field');
    const aparatFields = document.querySelectorAll('.video-aparat-field');
    const toggleFields = () => {
        uploadFields.forEach(item => item.style.display = type.value === 'upload' ? '' : 'none');
        aparatFields.forEach(item => item.style.display = type.value === 'aparat' ? '' : 'none');
    };
    type.addEventListener('change', toggleFields);
    toggleFields();

    const coverSelect = document.getElementById('cover_image_media_id');
    const coverPreview = document.getElementById('cover_image_media_preview');
    const renderCover = () => {
        const option = coverSelect?.selectedOptions?.[0];
        const url = option?.dataset?.url;
        if (url && coverPreview) {
            coverPreview.innerHTML = '<img class="rounded" src="' + url + '" alt="پیش‌نمایش کاور" style="width:100%;max-width:260px;height:150px;object-fit:cover">';
        }
    };
    coverSelect?.addEventListener('change', renderCover);
    renderCover();
})();
</script>
@endpush
