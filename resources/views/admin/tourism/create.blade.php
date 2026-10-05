@extends('admin.layouts.app')

@section('title', 'ایجاد مکان گردشگری')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">گردشگری</p><h2>ایجاد مکان گردشگری جدید</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.tourism.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.tourism.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="title">عنوان</label><input class="form-control" id="title" name="title" value="{{ old('title') }}" required></div>
        <div class="col-md-6"><label class="form-label" for="slug">نامک</label><input class="form-control" id="slug" name="slug" value="{{ old('slug') }}" dir="ltr"><small class="text-muted">اگر خالی بماند از عنوان ساخته می‌شود.</small></div>
        <div class="col-md-4"><label class="form-label" for="category_id">دسته‌بندی</label><select class="form-control" id="category_id" name="category_id"><option value="">بدون دسته‌بندی</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->title }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="type">نوع گردشگری</label><select class="form-control" id="tourism_type" name="tourism_type" required>@foreach ($typeLabels as $value => $label)<option value="{{ $value }}" @selected(old('tourism_type', $place?->tourism_type ?? 'nature') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="badge">برچسب کارت</label><input class="form-control" id="badge" name="badge" value="{{ old('badge') }}"></div>
        <div class="col-md-6">
            <label class="form-label" for="image">تصویر کارت</label>
            <input class="form-control" id="image" name="image" type="file" accept="image/*" data-skip-media-picker>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="image_media_id">انتخاب تصویر کارت از کتابخانه</button>
            <select class="d-none" name="image_media_id" id="image_media_id" aria-hidden="true" tabindex="-1">
                <option value="">انتخاب تصویر از کتابخانه</option>
                @foreach($mediaItems as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('image_media_id') === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <div class="mt-2" data-image-preview="image" id="image_media_preview"></div>
        </div>
        <div class="col-md-6"><label class="form-label" for="location">موقعیت کارت</label><input class="form-control" id="location" name="location" value="{{ old('location') }}"></div>
        <div class="col-md-4"><label class="form-label" for="status">وضعیت</label><select class="form-control" id="status" name="status" required>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', 'draft') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="published_at">تاریخ انتشار</label><input class="form-control" id="published_at" name="published_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('published_at')) }}"></div>
        <div class="col-md-4"><label class="form-label" for="sort_order">ترتیب نمایش</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', 0) }}"></div>
        <div class="col-md-4"><label class="form-label" for="is_active">فعال</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected(old('is_active', '1') === '1')>فعال</option><option value="0" @selected(old('is_active') === '0')>غیرفعال</option></select></div>
        <div class="col-md-4">
            <label class="form-label" for="featured_image">تصویر شاخص</label>
            <input class="form-control" id="featured_image" name="featured_image" type="file" accept="image/*" data-skip-media-picker>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="featured_image_media_id">انتخاب تصویر شاخص از کتابخانه</button>
            <select class="d-none" name="featured_image_media_id" id="featured_image_media_id" aria-hidden="true" tabindex="-1">
                <option value="">انتخاب تصویر از کتابخانه</option>
                @foreach($mediaItems as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('featured_image_media_id') === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <div class="mt-2" id="featured_image_media_preview"></div>
        </div>
        <div class="col-md-6"><label class="form-label" for="phone">تلفن</label><input class="form-control" id="phone" name="phone" value="{{ old('phone') }}"></div>
        <div class="col-md-6"><label class="form-label" for="working_hours">ساعت بازدید</label><input class="form-control" id="working_hours" name="working_hours" value="{{ old('working_hours') }}"></div>
        <div class="col-md-4"><label class="form-label" for="visit_price">هزینه بازدید</label><input class="form-control" id="visit_price" name="visit_price" value="{{ old('visit_price') }}"></div>
        <div class="col-md-4"><label class="form-label" for="latitude">عرض جغرافیایی</label><input class="form-control" id="latitude" name="latitude" value="{{ old('latitude') }}" dir="ltr"></div>
        <div class="col-md-4"><label class="form-label" for="longitude">طول جغرافیایی</label><input class="form-control" id="longitude" name="longitude" value="{{ old('longitude') }}" dir="ltr"></div>
        <div class="col-12"><label class="form-label" for="map_url">لینک نقشه</label><input class="form-control" id="map_url" name="map_url" value="{{ old('map_url') }}" dir="ltr" placeholder="https://..."></div>
        <div class="col-12"><label class="form-label" for="address">آدرس</label><textarea class="form-control" id="address" name="address" rows="2">{{ old('address') }}</textarea></div>
        <div class="col-12"><label class="form-label" for="short_description">توضیح کوتاه</label><textarea class="form-control js-rich-editor" id="short_description" name="short_description" rows="3">{{ old('short_description') }}</textarea></div>
        <div class="col-12"><label class="form-label" for="description">توضیحات کامل</label><textarea class="form-control js-rich-editor" id="description" name="description" rows="6">{{ old('description') }}</textarea></div>
        <div class="col-12">
            <label class="form-label" for="gallery_images">گالری تصاویر</label>
            <input class="form-control" id="gallery_images" name="gallery_images[]" type="file" accept="image/*" multiple data-skip-media-picker>
            <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                <button class="admin-secondary-btn" type="button" data-media-select-target="gallery_images_media_ids" data-media-select-multiple="true">انتخاب چند تصویر از کتابخانه</button>
                <small class="text-muted">می‌توانید هم‌زمان چند تصویر موجود در کتابخانه را به گالری اضافه کنید.</small>
            </div>
            <select class="d-none" id="gallery_images_media_ids" name="gallery_images_media_ids[]" multiple aria-hidden="true">
                @foreach($mediaItems as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected(in_array((string) $media->id, array_map('strval', old('gallery_images_media_ids', [])), true))>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <div class="row g-2 mt-2" id="gallery_images_media_preview"></div>
        </div>
        <div class="col-12"><label class="form-label" for="rejected_reason">دلیل رد</label><textarea class="form-control" id="rejected_reason" name="rejected_reason" rows="2">{{ old('rejected_reason') }}</textarea></div>
    </div>
    <div class="mt-3 d-flex gap-2"><button class="admin-primary-btn" type="submit">ذخیره مکان</button><a class="admin-secondary-btn" href="{{ route('admin.tourism.index') }}">انصراف</a></div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="file"][accept^="image/"]').forEach((input) => {
        const preview = document.querySelector(`[data-image-preview="${input.id}"]`);
        if (!preview) return;

        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) return;

            const url = URL.createObjectURL(file);
            preview.innerHTML = `<img src="${url}" alt="پیش‌نمایش تصویر انتخاب‌شده" class="img-fluid rounded" style="max-height:140px;object-fit:cover">`;
            preview.querySelector('img')?.addEventListener('load', () => URL.revokeObjectURL(url), { once: true });
        });
    });

    const renderSingleMedia = (selectId, previewId, alt) => {
        const select = document.getElementById(selectId);
        const preview = document.getElementById(previewId);
        const option = select?.selectedOptions?.[0];
        const url = option?.dataset?.url || '';
        if (!preview) return;
        preview.innerHTML = url ? `<img src="${url}" alt="${alt}" class="img-fluid rounded" style="max-height:140px;object-fit:cover">` : '';
    };

    ['image_media_id', 'featured_image_media_id'].forEach((id) => {
        const previewId = id === 'image_media_id' ? 'image_media_preview' : 'featured_image_media_preview';
        const select = document.getElementById(id);
        select?.addEventListener('change', () => renderSingleMedia(id, previewId, 'پیش‌نمایش تصویر انتخاب‌شده از کتابخانه'));
        renderSingleMedia(id, previewId, 'پیش‌نمایش تصویر انتخاب‌شده از کتابخانه');
    });

    const gallerySelect = document.getElementById('gallery_images_media_ids');
    const galleryPreview = document.getElementById('gallery_images_media_preview');
    const renderGalleryMedia = () => {
        if (!galleryPreview) return;
        galleryPreview.innerHTML = '';
        Array.from(gallerySelect?.selectedOptions || []).forEach((option) => {
            if (!option.dataset.url) return;
            galleryPreview.insertAdjacentHTML('beforeend', `<div class="col-6 col-md-3"><img src="${option.dataset.url}" alt="${option.text}" class="img-fluid rounded" style="height:90px;width:100%;object-fit:cover"></div>`);
        });
    };
    gallerySelect?.addEventListener('change', renderGalleryMedia);
    renderGalleryMedia();
});
</script>
@endpush
@endsection
