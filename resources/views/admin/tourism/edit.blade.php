@extends('admin.layouts.app')

@section('title', 'ویرایش مکان گردشگری')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">گردشگری</p><h2>ویرایش {{ $place->title }}</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.tourism.show', $place) }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.tourism.update', $place) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="title">عنوان</label><input class="form-control" id="title" name="title" value="{{ old('title', $place->title) }}" required></div>
        <div class="col-md-6"><label class="form-label" for="slug">نامک</label><input class="form-control" id="slug" name="slug" value="{{ old('slug', $place->slug) }}" dir="ltr"></div>
        <div class="col-md-4"><label class="form-label" for="category_id">دسته‌بندی</label><select class="form-control" id="category_id" name="category_id"><option value="">بدون دسته‌بندی</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $place->category_id) === (string) $category->id)>{{ $category->title }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="type">نوع گردشگری</label><select class="form-control" id="tourism_type" name="tourism_type" required>@foreach ($typeLabels as $value => $label)<option value="{{ $value }}" @selected(old('tourism_type', $place?->tourism_type ?? 'nature') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="badge">برچسب کارت</label><input class="form-control" id="badge" name="badge" value="{{ old('badge', $place->badge) }}"></div>
        <div class="col-md-6">
            <label class="form-label" for="image">تصویر کارت</label>
            <input class="form-control" id="image" name="image" type="file" accept="image/*" data-skip-media-picker>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="image_media_id">انتخاب تصویر کارت از کتابخانه</button>
            <select class="d-none" name="image_media_id" id="image_media_id" aria-hidden="true" tabindex="-1">
                <option value="">بدون تغییر / انتخاب تصویر از کتابخانه</option>
                @foreach($mediaItems as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('image_media_id', $currentCardMediaId) === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <div class="mt-2" data-image-preview="image" id="image_media_preview">
                @if($place->image)<img src="{{ $place->home_image_url }}" alt="{{ $place->title }}" class="img-fluid rounded" style="max-height:140px;object-fit:cover">@endif
            </div>
        </div>
        <div class="col-md-6"><label class="form-label" for="location">موقعیت کارت</label><input class="form-control" id="location" name="location" value="{{ old('location', $place->location) }}"></div>
        <div class="col-md-4"><label class="form-label" for="status">وضعیت</label><select class="form-control" id="status" name="status" required>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', $place->status) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="published_at">تاریخ انتشار</label><input class="form-control" id="published_at" name="published_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('published_at', $place->published_at)) }}"></div>
        <div class="col-md-4"><label class="form-label" for="sort_order">ترتیب نمایش</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $place->sort_order) }}"></div>
        <div class="col-md-4"><label class="form-label" for="is_active">فعال</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', (int) $place->is_active) === '1')>فعال</option><option value="0" @selected((string) old('is_active', (int) $place->is_active) === '0')>غیرفعال</option></select></div>
        <div class="col-md-4">
            <label class="form-label" for="featured_image">تصویر شاخص</label>
            <input class="form-control" id="featured_image" name="featured_image" type="file" accept="image/*" data-skip-media-picker>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="featured_image_media_id">انتخاب تصویر شاخص از کتابخانه</button>
            <select class="d-none" name="featured_image_media_id" id="featured_image_media_id" aria-hidden="true" tabindex="-1">
                <option value="">بدون تغییر / انتخاب تصویر از کتابخانه</option>
                @foreach($mediaItems as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('featured_image_media_id', $currentFeaturedMediaId) === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <div class="mt-2" id="featured_image_media_preview">
                @if($place->featured_image)<img src="{{ $place->featured_image_url }}" alt="{{ $place->title }}" class="img-fluid rounded" style="max-height:140px;object-fit:cover">@endif
            </div>
        </div>
        <div class="col-md-6"><label class="form-label" for="phone">تلفن</label><input class="form-control" id="phone" name="phone" value="{{ old('phone', $place->phone) }}"></div>
        <div class="col-md-6"><label class="form-label" for="working_hours">ساعت بازدید</label><input class="form-control" id="working_hours" name="working_hours" value="{{ old('working_hours', $place->working_hours) }}"></div>
        <div class="col-md-4"><label class="form-label" for="visit_price">هزینه بازدید</label><input class="form-control" id="visit_price" name="visit_price" value="{{ old('visit_price', $place->visit_price) }}"></div>
        <div class="col-md-4"><label class="form-label" for="latitude">عرض جغرافیایی</label><input class="form-control" id="latitude" name="latitude" value="{{ old('latitude', $place->latitude) }}" dir="ltr"></div>
        <div class="col-md-4"><label class="form-label" for="longitude">طول جغرافیایی</label><input class="form-control" id="longitude" name="longitude" value="{{ old('longitude', $place->longitude) }}" dir="ltr"></div>
        <div class="col-12"><label class="form-label" for="map_url">لینک نقشه</label><input class="form-control" id="map_url" name="map_url" value="{{ old('map_url', $place->map_url) }}" dir="ltr"></div>
        <div class="col-12"><label class="form-label" for="address">آدرس</label><textarea class="form-control" id="address" name="address" rows="2">{{ old('address', $place->address) }}</textarea></div>
        <div class="col-12"><label class="form-label" for="short_description">توضیح کوتاه</label><textarea class="form-control js-rich-editor" id="short_description" name="short_description" rows="3">{{ old('short_description', $place->short_description) }}</textarea></div>
        <div class="col-12"><label class="form-label" for="description">توضیحات کامل</label><textarea class="form-control js-rich-editor" id="description" name="description" rows="6">{{ old('description', $place->description) }}</textarea></div>
        <div class="col-12">
            <label class="form-label" for="gallery_images">افزودن تصاویر جدید به گالری</label>
            <input class="form-control" id="gallery_images" name="gallery_images[]" type="file" accept="image/*" multiple data-skip-media-picker>
            <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                <button class="admin-secondary-btn" type="button" data-media-select-target="gallery_images_media_ids" data-media-select-multiple="true">انتخاب چند تصویر از کتابخانه</button>
                <small class="text-muted">تصاویر انتخاب‌شده به گالری فعلی اضافه می‌شوند.</small>
            </div>
            <select class="d-none" id="gallery_images_media_ids" name="gallery_images_media_ids[]" multiple aria-hidden="true">
                @foreach($mediaItems as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected(in_array((string) $media->id, array_map('strval', old('gallery_images_media_ids', [])), true))>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <div class="row g-2 mt-2" id="gallery_images_media_preview"></div>
        </div>
        @if (! empty($place->gallery))
            <div class="col-12">
                <h4>تصاویر فعلی گالری</h4>
                <div class="row g-3">
                    @foreach (collect($place->gallery)->sortBy('sort_order') as $index => $image)
                        <div class="col-md-4">
                            <div class="admin-panel-card h-100">
                                <img src="{{ \App\Support\PublicFileUrl::make($image['path'] ?? '', '') }}" alt="{{ $image['caption'] ?? $place->title }}" style="width:100%;height:140px;object-fit:cover;border-radius:12px">
                                <input type="hidden" name="existing_gallery[{{ $index }}][path]" value="{{ $image['path'] ?? '' }}">
                                <label class="form-label mt-2">کپشن</label><input class="form-control" name="existing_gallery[{{ $index }}][caption]" value="{{ $image['caption'] ?? '' }}">
                                <label class="form-label mt-2">ترتیب</label><input class="form-control" name="existing_gallery[{{ $index }}][sort_order]" type="number" value="{{ $image['sort_order'] ?? 0 }}">
                                <label class="mt-2 d-flex gap-2 align-items-center"><input type="checkbox" name="existing_gallery[{{ $index }}][delete]" value="1"> حذف تصویر</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        <div class="col-12"><label class="form-label" for="rejected_reason">دلیل رد</label><textarea class="form-control" id="rejected_reason" name="rejected_reason" rows="2">{{ old('rejected_reason', $place->rejected_reason) }}</textarea></div>
    </div>
    <div class="mt-3 d-flex gap-2"><button class="admin-primary-btn" type="submit">ذخیره تغییرات</button><a class="admin-secondary-btn" href="{{ route('admin.tourism.show', $place) }}">انصراف</a></div>
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

    const renderSingleMedia = (selectId, previewId, fallbackUrl) => {
        const select = document.getElementById(selectId);
        const preview = document.getElementById(previewId);
        const option = select?.selectedOptions?.[0];
        const url = option?.dataset?.url || fallbackUrl || '';
        if (!preview) return;
        preview.replaceChildren();
        if (!url) return;

        const img = document.createElement('img');
        img.src = url;
        img.alt = 'پیش‌نمایش تصویر گردشگری';
        img.className = 'img-fluid rounded';
        img.style.cssText = 'max-height:140px;object-fit:cover';
        preview.appendChild(img);
    };

    const cardSelect = document.getElementById('image_media_id');
    cardSelect?.addEventListener('change', () => renderSingleMedia('image_media_id', 'image_media_preview', @json($place->home_image_url)));
    renderSingleMedia('image_media_id', 'image_media_preview', @json($place->home_image_url));

    const featuredSelect = document.getElementById('featured_image_media_id');
    featuredSelect?.addEventListener('change', () => renderSingleMedia('featured_image_media_id', 'featured_image_media_preview', @json($place->featured_image_url)));
    renderSingleMedia('featured_image_media_id', 'featured_image_media_preview', @json($place->featured_image_url));

    const gallerySelect = document.getElementById('gallery_images_media_ids');
    const galleryPreview = document.getElementById('gallery_images_media_preview');
    const renderGalleryMedia = () => {
        if (!galleryPreview) return;
        galleryPreview.replaceChildren();
        Array.from(gallerySelect?.selectedOptions || []).forEach((option) => {
            if (!option.dataset.url) return;
            const tile = document.createElement('div');
            tile.className = 'col-6 col-md-3';
            const img = document.createElement('img');
            img.src = option.dataset.url;
            img.alt = option.text;
            img.className = 'img-fluid rounded';
            img.style.cssText = 'height:90px;width:100%;object-fit:cover';
            tile.appendChild(img);
            galleryPreview.appendChild(tile);
        });
    };
    gallerySelect?.addEventListener('change', renderGalleryMedia);
    renderGalleryMedia();
});
</script>
@endpush
@endsection
