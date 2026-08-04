@extends('admin.layouts.app')

@section('title', 'ویرایش گالری تصاویر')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">گالری تصاویر</p><h2>{{ $gallery->title }}</h2></div>
    <div class="d-flex gap-2 flex-wrap"><a class="admin-secondary-btn" href="{{ route('admin.galleries.show', $gallery) }}">مشاهده</a><a class="admin-secondary-btn" href="{{ route('admin.galleries.index') }}">بازگشت</a></div>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.galleries.update', $gallery) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="title">عنوان</label><input class="form-control" id="title" name="title" value="{{ old('title', $gallery->title) }}" required></div>
        <div class="col-md-6"><label class="form-label" for="slug">نامک</label><input class="form-control" id="slug" name="slug" value="{{ old('slug', $gallery->slug) }}" dir="ltr"></div>
        <div class="col-md-4"><label class="form-label" for="union_id">اتحادیه</label><select class="form-control" id="union_id" name="union_id"><option value="">گالری عمومی</option>@foreach ($unions as $union)<option value="{{ $union->id }}" @selected((string) old('union_id', $gallery->union_id) === (string) $union->id)>{{ $union->display_title }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="category_id">دسته‌بندی</label><select class="form-control" id="category_id" name="category_id"><option value="">بدون دسته‌بندی</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)old('category_id', $gallery->category_id)===(string)$category->id)>{{ $category->title }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="display_location">محل نمایش</label><select class="form-control" id="display_location" name="display_location" required>@foreach ($displayLocationLabels as $value => $label)<option value="{{ $value }}" @selected(old('display_location', $gallery->display_location ?? 'both') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="status">وضعیت</label><select class="form-control" id="status" name="status" required>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', $gallery->status) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="published_at">تاریخ انتشار</label><input class="form-control" id="published_at" name="published_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('published_at', $gallery->published_at)) }}"></div>
        <input type="hidden" name="sort_order" value="{{ old('sort_order', $gallery->sort_order) }}">
        <div class="col-md-4"><label class="form-label" for="is_active">فعال</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', (int) $gallery->is_active) === '1')>فعال</option><option value="0" @selected((string) old('is_active', (int) $gallery->is_active) === '0')>غیرفعال</option></select></div>
        <div class="col-12"><label class="form-label" for="description">توضیحات</label><textarea class="form-control js-rich-editor" id="description" name="description" rows="4">{{ old('description', $gallery->description) }}</textarea></div>
        <div class="col-12"><label class="form-label" for="rejected_reason">دلیل رد</label><textarea class="form-control" id="rejected_reason" name="rejected_reason" rows="2">{{ old('rejected_reason', $gallery->rejected_reason) }}</textarea></div>
        <div class="col-md-6"><label class="form-label" for="cover_image">تصویر کاور جدید</label><input class="form-control" id="cover_image" name="cover_image" type="file" accept="image/*" data-skip-media-picker>@if($gallery->cover_image)<a class="d-inline-block mt-2" href="{{ $gallery->cover_image_url }}" target="_blank">مشاهده کاور فعلی</a>@endif</div>
        <div class="col-md-6"><label class="form-label" for="images">افزودن تصاویر جدید</label><input class="form-control" id="images" name="images[]" type="file" accept="image/*" multiple data-skip-media-picker></div>
    </div>

    <div class="mt-4">
        <label class="form-label" for="gallery_media_ids">انتخاب از کتابخانه تصاویر</label>
        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
            <button class="admin-secondary-btn" type="button" data-media-select-target="gallery_media_ids" data-media-select-multiple="true">باز کردن کتابخانه و انتخاب با پیش‌نمایش</button>
            <small class="text-muted">پنجره کتابخانه را باز کنید، تصویرها را به‌صورت بندانگشتی ببینید و چند مورد را انتخاب کنید.</small>
        </div>
        <select class="form-control d-none" id="gallery_media_ids" name="gallery_media_ids[]" multiple size="8" aria-hidden="true">
            @foreach ($mediaItems as $media)
                <option value="{{ $media->id }}" data-url="{{ $media->url }}">{{ $media->title ?: $media->original_name }}</option>
            @endforeach
        </select>
        <small class="text-muted">برای افزودن تصاویر موجود در کتابخانه، یک یا چند تصویر را انتخاب کنید.</small>
        <div class="row g-2 mt-2" id="galleryMediaPreview"></div>
    </div>

    @if ($gallery->images->isNotEmpty())
        <div class="mt-4">
            <h3 class="h5 mb-3">مرتب‌سازی و ویرایش تصاویر</h3>
            <div class="row g-3">
                @foreach ($gallery->images as $image)
                    <div class="col-md-4">
                        <div class="border rounded p-2 h-100">
                            <img src="{{ $image->image_url }}" alt="{{ $image->caption ?: $gallery->title }}" style="width:100%;height:160px;object-fit:cover;border-radius:10px">
                            <label class="form-label mt-2" for="caption_{{ $image->id }}">کپشن</label>
                            <input class="form-control" id="caption_{{ $image->id }}" name="existing_images[{{ $image->id }}][caption]" value="{{ old('existing_images.'.$image->id.'.caption', $image->caption) }}">
                            <input type="hidden" name="existing_images[{{ $image->id }}][sort_order]" value="{{ old('existing_images.'.$image->id.'.sort_order', $image->sort_order) }}">
                            <label class="d-block small mt-2"><input type="checkbox" name="existing_images[{{ $image->id }}][delete]" value="1"> حذف این تصویر</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-3 d-flex gap-2"><button class="admin-primary-btn" type="submit">ذخیره تغییرات</button><a class="admin-secondary-btn" href="{{ route('admin.galleries.show', $gallery) }}">انصراف</a></div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const gallerySelect = document.getElementById('gallery_media_ids');
    const galleryPreview = document.getElementById('galleryMediaPreview');
    if (!gallerySelect || !galleryPreview) return;
    const renderPreview = () => {
        galleryPreview.innerHTML = '';
        Array.from(gallerySelect.selectedOptions || []).forEach((option) => {
            galleryPreview.insertAdjacentHTML('beforeend', `<div class="col-6 col-md-3"><img src="${option.dataset.url}" alt="${option.text}" class="img-fluid rounded" style="height:90px;width:100%;object-fit:cover"></div>`);
        });
    };
    gallerySelect.addEventListener('change', renderPreview);
    renderPreview();
});
</script>
@endpush
