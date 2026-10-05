@extends('admin.layouts.app')

@section('title', 'ویرایش سامانه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">سامانه‌ها</p><h2>ویرایش {{ $system->title }}</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.systems.show', $system) }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.systems.update', $system) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="title">عنوان</label><input class="form-control" id="title" name="title" value="{{ old('title', $system->title) }}" required></div>
        <div class="col-md-6"><label class="form-label" for="slug">نامک</label><input class="form-control" id="slug" name="slug" value="{{ old('slug', $system->slug) }}" dir="ltr"></div>
        <div class="col-md-4"><label class="form-label" for="category_id">دسته‌بندی</label><select class="form-control" id="category_id" name="category_id"><option value="">بدون دسته‌بندی</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $system->category_id) === (string) $category->id)>{{ $category->title }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="icon">آیکن آماده/دلخواه</label>@include('admin.systems.partials.icon-picker')</div>
        <div class="col-md-4">
            <label class="form-label" for="image">تصویر سامانه</label>
            <input class="form-control" id="image" name="image" type="file" accept="image/*" data-skip-media-picker>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="image_media_id">انتخاب یا آپلود از کتابخانه</button>
            <select class="d-none" id="image_media_id" name="image_media_id" aria-hidden="true" tabindex="-1">
                <option value="">بدون تغییر / انتخاب تصویر</option>
                @foreach(($mediaItems ?? collect()) as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('image_media_id', $currentMediaId) === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1">آپلود جدید بر انتخاب کتابخانه اولویت دارد.</small>
            <div id="system_image_preview" class="mt-2">@if($system->image)<img class="rounded" src="{{ $system->image_url }}" alt="{{ $system->title }}" style="width:100%;max-width:320px;height:160px;object-fit:cover">@endif</div>
        </div>
        <div class="col-md-8"><label class="form-label" for="link">لینک ورود</label><input class="form-control" id="link" name="link" value="{{ old('link', $system->link) }}" dir="ltr" placeholder="https://... یا /path"><small class="text-muted d-block mt-1">فقط لینک‌های http/https یا مسیر داخلی سایت پذیرفته می‌شوند. لینک نامعتبر در سایت عمومی نمایش داده نمی‌شود.</small></div>
        <div class="col-md-4"><label class="form-label" for="target">نحوه باز شدن</label><select class="form-control" id="target" name="target" required>@foreach ($targetLabels as $value => $label)<option value="{{ $value }}" @selected(old('target', $system->target) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="status">وضعیت</label><select class="form-control" id="status" name="status" required>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', $system->status) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="published_at">تاریخ انتشار</label><input class="form-control" id="published_at" name="published_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('published_at', $system->published_at)) }}"></div>
        <div class="col-md-6"><label class="form-label" for="sort_order">ترتیب نمایش</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $system->sort_order) }}"></div>
        <div class="col-md-6"><label class="form-label" for="is_active">فعال</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', (int) $system->is_active) === '1')>فعال</option><option value="0" @selected((string) old('is_active', (int) $system->is_active) === '0')>غیرفعال</option></select></div>
        <div class="col-12"><label class="form-label" for="short_description">توضیح کوتاه</label><textarea class="form-control js-rich-editor" id="short_description" name="short_description" rows="3">{{ old('short_description', $system->short_description) }}</textarea></div>
        <div class="col-12"><label class="form-label" for="description">توضیحات کامل</label><textarea class="form-control js-rich-editor" id="description" name="description" rows="6">{{ old('description', $system->description) }}</textarea></div>
    </div>
    <div class="mt-3 d-flex gap-2"><button class="admin-primary-btn" type="submit">ذخیره تغییرات</button><a class="admin-secondary-btn" href="{{ route('admin.systems.show', $system) }}">انصراف</a></div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('image_media_id');
    const input = document.getElementById('image');
    const preview = document.getElementById('system_image_preview');

    function renderUrl(url) {
        if (!preview) return;
        preview.innerHTML = url
            ? '<img class="rounded" src="' + url + '" alt="پیش‌نمایش تصویر سامانه" style="width:100%;max-width:320px;height:160px;object-fit:cover">'
            : '';
    }

    select?.addEventListener('change', function () {
        renderUrl(select.selectedOptions?.[0]?.dataset?.url || '');
    });

    input?.addEventListener('change', function () {
        const file = input.files?.[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        renderUrl(url);
        preview?.querySelector('img')?.addEventListener('load', () => URL.revokeObjectURL(url), { once: true });
    });

    renderUrl(select?.selectedOptions?.[0]?.dataset?.url || '');
});
</script>
@endpush
