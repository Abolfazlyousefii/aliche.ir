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
            <input class="form-control" id="image" name="image" type="file" accept="image/*">
            @if ($place->image)<small><a href="{{ Storage::url($place->image) }}" target="_blank">مشاهده تصویر کارت فعلی</a></small>@endif
            <div class="mt-2" data-image-preview="image">@if ($place->image)<img src="{{ Storage::url($place->image) }}" alt="{{ $place->title }}" class="img-fluid rounded" style="max-height:140px;object-fit:cover">@endif</div>
        </div>
        <div class="col-md-6"><label class="form-label" for="location">موقعیت کارت</label><input class="form-control" id="location" name="location" value="{{ old('location', $place->location) }}"></div>
        <div class="col-md-4"><label class="form-label" for="status">وضعیت</label><select class="form-control" id="status" name="status" required>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', $place->status) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="published_at">تاریخ انتشار</label><input class="form-control" id="published_at" name="published_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('published_at', $place->published_at)) }}"></div>
        <div class="col-md-4"><label class="form-label" for="sort_order">ترتیب نمایش</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $place->sort_order) }}"></div>
        <div class="col-md-4"><label class="form-label" for="is_active">فعال</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', (int) $place->is_active) === '1')>فعال</option><option value="0" @selected((string) old('is_active', (int) $place->is_active) === '0')>غیرفعال</option></select></div>
        <div class="col-md-4"><label class="form-label" for="featured_image">تصویر شاخص جدید</label><input class="form-control" id="featured_image" name="featured_image" type="file" accept="image/*">@if ($place->featured_image)<small><a href="{{ Storage::url($place->featured_image) }}" target="_blank">مشاهده تصویر فعلی</a></small>@endif</div>
        <div class="col-md-6"><label class="form-label" for="phone">تلفن</label><input class="form-control" id="phone" name="phone" value="{{ old('phone', $place->phone) }}"></div>
        <div class="col-md-6"><label class="form-label" for="working_hours">ساعت بازدید</label><input class="form-control" id="working_hours" name="working_hours" value="{{ old('working_hours', $place->working_hours) }}"></div>
        <div class="col-md-4"><label class="form-label" for="visit_price">هزینه بازدید</label><input class="form-control" id="visit_price" name="visit_price" value="{{ old('visit_price', $place->visit_price) }}"></div>
        <div class="col-md-4"><label class="form-label" for="latitude">عرض جغرافیایی</label><input class="form-control" id="latitude" name="latitude" value="{{ old('latitude', $place->latitude) }}" dir="ltr"></div>
        <div class="col-md-4"><label class="form-label" for="longitude">طول جغرافیایی</label><input class="form-control" id="longitude" name="longitude" value="{{ old('longitude', $place->longitude) }}" dir="ltr"></div>
        <div class="col-12"><label class="form-label" for="map_url">لینک نقشه</label><input class="form-control" id="map_url" name="map_url" value="{{ old('map_url', $place->map_url) }}" dir="ltr"></div>
        <div class="col-12"><label class="form-label" for="address">آدرس</label><textarea class="form-control" id="address" name="address" rows="2">{{ old('address', $place->address) }}</textarea></div>
        <div class="col-12"><label class="form-label" for="short_description">توضیح کوتاه</label><textarea class="form-control js-rich-editor" id="short_description" name="short_description" rows="3">{{ old('short_description', $place->short_description) }}</textarea></div>
        <div class="col-12"><label class="form-label" for="description">توضیحات کامل</label><textarea class="form-control js-rich-editor" id="description" name="description" rows="6">{{ old('description', $place->description) }}</textarea></div>
        <div class="col-12"><label class="form-label" for="gallery_images">افزودن تصاویر جدید به گالری</label><input class="form-control" id="gallery_images" name="gallery_images[]" type="file" accept="image/*" multiple></div>
        @if (! empty($place->gallery))
            <div class="col-12">
                <h4>تصاویر فعلی گالری</h4>
                <div class="row g-3">
                    @foreach (collect($place->gallery)->sortBy('sort_order') as $index => $image)
                        <div class="col-md-4">
                            <div class="admin-panel-card h-100">
                                <img src="{{ Storage::url($image['path'] ?? '') }}" alt="{{ $image['caption'] ?? $place->title }}" style="width:100%;height:140px;object-fit:cover;border-radius:12px">
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
</script>
@endpush
@endsection
