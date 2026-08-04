@php
    $selectedFeaturedMediaId = old('featured_image_media_id');
    $currentFeaturedUrl = $page?->featured_image ? image_url($page->featured_image) : null;
@endphp

<div class="row g-3">
    <div class="col-md-8"><label class="form-label" for="title">عنوان</label><input class="form-control" id="title" name="title" value="{{ old('title', $page?->title) }}" required></div>
    <div class="col-md-4"><label class="form-label" for="slug">اسلاگ</label><input class="form-control" dir="ltr" id="slug" name="slug" value="{{ old('slug', $page?->slug) }}"><small class="text-muted">اگر خالی بماند از عنوان ساخته می‌شود.</small></div>
    <div class="col-12"><label class="form-label" for="excerpt">خلاصه</label><textarea class="form-control js-rich-editor" id="excerpt" name="excerpt" rows="2">{{ old('excerpt', $page?->excerpt) }}</textarea></div>
    <div class="col-12"><label class="form-label" for="body">محتوای صفحه</label><textarea class="form-control js-rich-editor" id="body" name="body" rows="12">{{ old('body', $page?->body) }}</textarea></div>
    <div class="col-md-4">
        <label class="form-label" for="featured_image">تصویر شاخص</label>
        <input class="form-control" id="featured_image" name="featured_image" type="file" accept="image/*" data-skip-media-picker>
        <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="featured_image_media_id">انتخاب از کتابخانه با پیش‌نمایش</button>
        <select class="form-control mt-2" name="featured_image_media_id" id="featured_image_media_id">
            <option value="">انتخاب از کتابخانه رسانه</option>
            @foreach ($mediaItems as $media)
                <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) $selectedFeaturedMediaId === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
            @endforeach
        </select>
        <small class="text-muted d-block mt-2">در صورت انتخاب فایل جدید، فایل آپلودشده جایگزین انتخاب کتابخانه می‌شود.</small>
        <div class="mt-2" id="pageFeaturedPreview">
            @if($currentFeaturedUrl)
                <img src="{{ $currentFeaturedUrl }}" alt="تصویر شاخص" class="img-fluid rounded" style="max-height:160px;object-fit:cover">
                <small class="d-block mt-1"><a href="{{ $currentFeaturedUrl }}" target="_blank" rel="noopener">مشاهده تصویر فعلی</a></small>
            @endif
        </div>
    </div>
    <div class="col-md-4"><label class="form-label" for="template">قالب</label><select class="form-control" id="template" name="template">@foreach($templates as $template)<option value="{{ $template }}" @selected(old('template', $page?->template ?? 'default') === $template)>{{ $templateLabels[$template] ?? $template }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label" for="status">وضعیت</label><select class="form-control" id="status" name="status">@foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status', $page?->status ?? 'draft') === $status)>{{ $statusLabels[$status] ?? $status }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label" for="published_at">تاریخ انتشار</label><input class="form-control" dir="ltr" id="published_at" name="published_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('published_at', $page?->published_at)) }}"></div>
    <div class="col-md-4"><label class="form-label" for="sort_order">ترتیب</label><input class="form-control" dir="ltr" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $page?->sort_order ?? 0) }}"></div>
    <div class="col-md-4"><label class="form-label" for="is_active">فعال/غیرفعال</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', $page?->is_active ?? 1) === '1')>فعال</option><option value="0" @selected((string) old('is_active', $page?->is_active ?? 1) === '0')>غیرفعال</option></select></div>
    <div class="col-md-6"><label class="form-label" for="meta_title">عنوان متا</label><input class="form-control" id="meta_title" name="meta_title" value="{{ old('meta_title', $page?->meta_title) }}"></div>
    <div class="col-md-6"><label class="form-label" for="meta_keywords">کلیدواژه‌های متا</label><input class="form-control" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $page?->meta_keywords) }}"></div>
    <div class="col-12"><label class="form-label" for="meta_description">توضیحات متا</label><textarea class="form-control" id="meta_description" name="meta_description" rows="2">{{ old('meta_description', $page?->meta_description) }}</textarea></div>
    <div class="col-12"><label class="form-label" for="rejected_reason">دلیل رد</label><textarea class="form-control" id="rejected_reason" name="rejected_reason" rows="2">{{ old('rejected_reason', $page?->rejected_reason) }}</textarea></div>
</div>
<div class="admin-form-actions"><button class="admin-primary-btn" type="submit">ذخیره صفحه</button><a class="admin-secondary-btn" href="{{ route('admin.pages.index') }}">انصراف</a></div>

@push('scripts')
<script>
const pageFeaturedSelect = document.getElementById('featured_image_media_id');
const pageFeaturedPreview = document.getElementById('pageFeaturedPreview');
pageFeaturedSelect?.addEventListener('change', () => {
    const url = pageFeaturedSelect.selectedOptions[0]?.dataset.url;
    pageFeaturedPreview.innerHTML = url ? `<img src="${url}" alt="تصویر شاخص" class="img-fluid rounded" style="max-height:160px;object-fit:cover">` : '';
});
</script>
@endpush
