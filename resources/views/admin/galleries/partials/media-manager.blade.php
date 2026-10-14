{{-- Media Manager V2: a single media picker for gallery images and cover.
     The native inputs remain available when JavaScript is disabled. --}}
@php
    $oldGalleryIds = collect(old('gallery_media_ids', []))->map(fn ($id) => (string) $id)->all();
@endphp
<div class="col-md-6">
    <label class="form-label" for="cover_image">تصویر کاور</label>
    <div class="mm2-cover-control">
        <button class="admin-secondary-btn" type="button" data-media-select-target="cover_image_media_id">انتخاب کاور از کتابخانه</button>
        <input class="form-control mm2-native mt-2" id="cover_image" name="cover_image" type="file" accept="image/*" data-skip-media-picker>
    </div>
    <select class="d-none" name="cover_image_media_id" id="cover_image_media_id" aria-hidden="true" tabindex="-1">
        <option value="">بدون کاور جداگانه</option>
        @foreach ($mediaItems as $media)
            <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('cover_image_media_id', $currentCoverMediaId ?? '') === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
        @endforeach
    </select>
    <small class="text-muted d-block mt-1">در صورت نداشتن کاور جداگانه، اولین تصویر گالری به‌عنوان کاور استفاده می‌شود.</small>
    <div id="coverMediaPreview" class="mt-2">
        @if($gallery?->cover_image)
            <img src="{{ $gallery->cover_image_url }}" alt="{{ $gallery->title }}" class="mm2-cover-image">
        @endif
    </div>
</div>
<div class="col-md-6">
    <label class="form-label">تصاویر گالری</label>
    <div class="mm2-gallery-actions">
        <button class="admin-primary-btn" type="button" data-media-select-target="gallery_media_ids" data-media-select-multiple="true">مدیریت و انتخاب تصاویر گالری</button>
        <span data-mm2-gallery-count class="text-muted">تصاویر گالری</span>
    </div>
    <input class="form-control mm2-native mt-2" id="images" name="images[]" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.avif,image/*" multiple data-skip-media-picker>
    <small class="text-muted d-block mt-2">از همین پنجره تا ۶۰ تصویر یا بیشتر را به نوبت بارگذاری کنید، عکس‌های قبلی را انتخاب کنید و ترتیب تصاویر را تنظیم کنید.</small>
    <select class="d-none" id="gallery_media_ids" name="gallery_media_ids[]" multiple aria-hidden="true" tabindex="-1">
        @foreach($mediaItems as $media)
            <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected(in_array((string) $media->id, $oldGalleryIds, true))>{{ $media->title ?: $media->original_name }}</option>
        @endforeach
    </select>
</div>
<div class="col-12">
    <h3 class="h6 mb-2">تصاویر گالری و ترتیب نمایش</h3>
    <p class="text-muted small">برای جابه‌جایی، کارت‌ها را بکشید یا از فلش‌ها استفاده کنید. حذف تصویر موجود فقط پس از ذخیره تغییرات اعمال می‌شود.</p>
    <div class="mm2-workspace" data-mm2-workspace id="galleryImageWorkspace">
        @if($gallery && $gallery->images->isNotEmpty())
            @foreach($gallery->images as $image)
                <div class="mm2-workspace-card mm2-existing-card" data-mm2-item data-mm2-existing-id="{{ $image->id }}" draggable="true">
                    <img src="{{ $image->image_url }}" alt="{{ $image->caption ?: $gallery->title }}" loading="lazy">
                    <label class="form-label mt-2" for="caption_{{ $image->id }}">عنوان تصویر</label>
                    <input class="form-control form-control-sm" id="caption_{{ $image->id }}" name="existing_images[{{ $image->id }}][caption]" value="{{ old('existing_images.'.$image->id.'.caption', $image->caption) }}">
                    <input type="hidden" name="existing_images[{{ $image->id }}][sort_order]" value="{{ old('existing_images.'.$image->id.'.sort_order', $image->sort_order) }}">
                    <label class="mm2-native small mt-2"><input type="checkbox" data-mm2-delete-existing name="existing_images[{{ $image->id }}][delete]" value="1" @checked(old('existing_images.'.$image->id.'.delete') == '1')> حذف تصویر</label>
                    <div class="mm2-workspace-actions">
                        <button type="button" data-mm2-move="up" aria-label="انتقال تصویر به قبل">↑</button>
                        <button type="button" data-mm2-move="down" aria-label="انتقال تصویر به بعد">↓</button>
                        <button type="button" data-mm2-remove aria-label="حذف این تصویر از گالری">×</button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <div data-mm2-order-fields></div>
</div>
