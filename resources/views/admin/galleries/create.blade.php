@extends('admin.layouts.app')

@section('title', 'ایجاد گالری تصاویر')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">گالری تصاویر</p><h2>ایجاد گالری جدید</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.galleries.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.galleries.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="title">عنوان</label><input class="form-control" id="title" name="title" value="{{ old('title') }}" required></div>
        <div class="col-md-6"><label class="form-label" for="slug">نامک</label><input class="form-control" id="slug" name="slug" value="{{ old('slug') }}" dir="ltr"><small class="text-muted">اگر خالی بماند از عنوان ساخته می‌شود.</small></div>
        <div class="col-md-4"><label class="form-label" for="union_id">اتحادیه</label><select class="form-control" id="union_id" name="union_id"><option value="">گالری عمومی</option>@foreach ($unions as $union)<option value="{{ $union->id }}" @selected((string) old('union_id') === (string) $union->id)>{{ $union->display_title }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="category_id">دسته‌بندی</label><select class="form-control" id="category_id" name="category_id"><option value="">بدون دسته‌بندی</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)old('category_id')===(string)$category->id)>{{ $category->title }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="display_location">محل نمایش</label><select class="form-control" id="display_location" name="display_location" required>@foreach ($displayLocationLabels as $value => $label)<option value="{{ $value }}" @selected(old('display_location', 'both') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="status">وضعیت</label><select class="form-control" id="status" name="status" required>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', 'draft') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="published_at">تاریخ انتشار</label><input class="form-control" id="published_at" name="published_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('published_at')) }}"></div>
        <input type="hidden" name="sort_order" value="{{ old('sort_order', 0) }}">
        <div class="col-md-4"><label class="form-label" for="is_active">فعال</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected(old('is_active', '1') === '1')>فعال</option><option value="0" @selected(old('is_active') === '0')>غیرفعال</option></select></div>
        <div class="col-12"><label class="form-label" for="description">توضیحات</label><textarea class="form-control js-rich-editor" id="description" name="description" rows="4">{{ old('description') }}</textarea></div>
        <div class="col-12"><label class="form-label" for="rejected_reason">دلیل رد</label><textarea class="form-control" id="rejected_reason" name="rejected_reason" rows="2">{{ old('rejected_reason') }}</textarea></div>
        <div class="col-md-6"><label class="form-label" for="cover_image">تصویر کاور</label><input class="form-control" id="cover_image" name="cover_image" type="file" accept="image/*" data-skip-media-picker></div>
        <div class="col-md-6"><label class="form-label" for="images">تصاویر گالری</label><input class="form-control" id="images" name="images[]" type="file" accept="image/*" multiple data-skip-media-picker><small class="text-muted">امکان انتخاب چند تصویر وجود دارد. حداکثر حجم هر تصویر ۴ مگابایت است.</small></div>

        <div class="col-12">
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
    </div>
    <div class="mt-3 d-flex gap-2"><button class="admin-primary-btn" type="submit">ذخیره گالری</button><a class="admin-secondary-btn" href="{{ route('admin.galleries.index') }}">انصراف</a></div>
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
