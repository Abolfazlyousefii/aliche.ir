@php
    $selectedStatus = old('status', $announcement?->status ?? 'draft');
    $selectedVisibility = old('visibility', $announcement?->visibility ?? 'public');
@endphp

<div class="admin-panel-card">
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label" for="title">عنوان اطلاعیه</label>
            <input class="form-control" id="title" name="title" value="{{ old('title', $announcement?->title) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="slug">اسلاگ</label>
            <input class="form-control" id="slug" name="slug" value="{{ old('slug', $announcement?->slug) }}" dir="ltr"><small class="text-muted">اگر خالی بماند از عنوان ساخته می‌شود.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="category_id">دسته‌بندی</label>
            <select class="form-control" id="category_id" name="category_id">
                <option value="">بدون دسته‌بندی</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $announcement?->category_id) === (string) $category->id)>{{ $category->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="union_id">اتحادیه مرتبط</label>
            <select class="form-control" id="union_id" name="union_id">
                <option value="">اطلاعیه عمومی / بدون اتحادیه</option>
                @foreach ($unions as $union)
                    <option value="{{ $union->id }}" @selected((string) old('union_id', $announcement?->union_id) === (string) $union->id)>{{ $union->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="status">وضعیت</label>
            <select class="form-control" id="status" name="status" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $statusLabels[$status] ?? $status }}</option>
                @endforeach
            </select>
            <small class="text-muted">انتخاب وضعیت‌های تایید/انتشار فقط برای مدیر دارای دسترسی مجاز است.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="visibility">نوع نمایش</label>
            <select class="form-control" id="visibility" name="visibility" required>
                @foreach ($visibilityLabels as $value => $label)
                    <option value="{{ $value }}" @selected($selectedVisibility === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <small class="text-muted">خصوصی فقط در پنل کاربران نمایش داده می‌شود.</small>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="starts_at">شروع نمایش</label>
            <input class="form-control" id="starts_at" name="starts_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('starts_at', $announcement?->starts_at)) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="expires_at">تاریخ انقضا</label>
            <input class="form-control" id="expires_at" name="expires_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('expires_at', $announcement?->expires_at)) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="published_at">تاریخ انتشار</label>
            <input class="form-control" id="published_at" name="published_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('published_at', $announcement?->published_at)) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="sort_order">ترتیب نمایش</label>
            <input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $announcement?->sort_order ?? 0) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="is_important">اطلاعیه مهم</label>
            <select class="form-control" id="is_important" name="is_important">
                <option value="1" @selected((string) old('is_important', (int) ($announcement?->is_important ?? false)) === '1')>بله</option>
                <option value="0" @selected((string) old('is_important', (int) ($announcement?->is_important ?? false)) === '0')>خیر</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="show_on_home">نمایش در صفحه اصلی</label>
            <select class="form-control" id="show_on_home" name="show_on_home">
                <option value="1" @selected((string) old('show_on_home', (int) ($announcement?->show_on_home ?? false)) === '1')>بله</option>
                <option value="0" @selected((string) old('show_on_home', (int) ($announcement?->show_on_home ?? false)) === '0')>خیر</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="is_active">فعال</label>
            <select class="form-control" id="is_active" name="is_active">
                <option value="1" @selected((string) old('is_active', (int) ($announcement?->is_active ?? true)) === '1')>فعال</option>
                <option value="0" @selected((string) old('is_active', (int) ($announcement?->is_active ?? true)) === '0')>غیرفعال</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="excerpt">خلاصه اطلاعیه</label>
            <textarea class="form-control js-rich-editor" id="excerpt" name="excerpt" rows="3">{{ old('excerpt', $announcement?->excerpt) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="body">متن کامل اطلاعیه</label>
            <textarea class="form-control js-rich-editor" id="body" name="body" rows="12">{{ old('body', $announcement?->body) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="featured_image">تصویر شاخص</label>
            <input class="form-control" id="featured_image" name="featured_image" type="file" accept="image/*" data-skip-media-picker>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="featured_image_media_id">انتخاب از کتابخانه رسانه با پیش‌نمایش</button>
            <select class="form-control mt-2" name="featured_image_media_id" id="featured_image_media_id">
                <option value="">انتخاب از کتابخانه رسانه</option>
                @foreach ($mediaItems as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('featured_image_media_id') === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <small><a href="{{ route('admin.media.index') }}" target="_blank">مدیریت و آپلود در کتابخانه رسانه</a></small>
            <div class="mt-2" id="featuredPreview">
                @if ($announcement?->featured_image)
                    <img src="{{ image_url($announcement->featured_image) }}" alt="تصویر شاخص" class="img-fluid rounded" style="max-height:160px;object-fit:cover">
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="attachment">فایل پیوست</label>
            <input class="form-control" id="attachment" name="attachment" type="file">
            @if ($announcement?->attachment)
                <a class="d-inline-block mt-2" href="{{ image_url($announcement->attachment, '') }}" target="_blank">دانلود پیوست فعلی</a>
                <label class="d-block small mt-2"><input type="checkbox" name="remove_attachment" value="1"> حذف پیوست فعلی</label>
            @endif
        </div>
        <div class="col-12">
            <label class="form-label" for="rejected_reason">دلیل رد اطلاعیه</label>
            <textarea class="form-control" id="rejected_reason" name="rejected_reason" rows="3">{{ old('rejected_reason', $announcement?->rejected_reason) }}</textarea>
        </div>
    </div>
</div>

<div class="mt-3 d-flex gap-2">
    <button class="admin-primary-btn" type="submit">ذخیره اطلاعیه</button>
    <a class="admin-secondary-btn" href="{{ route('admin.announcements.index') }}">انصراف</a>
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
const featuredSelect = document.getElementById('featured_image_media_id');
const featuredPreview = document.getElementById('featuredPreview');
if (window.TomSelect && featuredSelect) {
    new TomSelect(featuredSelect, {create: false, allowEmptyOption: true});
}
featuredSelect?.addEventListener('change', () => {
    const url = featuredSelect.selectedOptions[0]?.dataset.url;
    featuredPreview.innerHTML = url ? `<img src="${url}" alt="تصویر شاخص" class="img-fluid rounded" style="max-height:160px;object-fit:cover">` : '';
});
</script>
@endpush
