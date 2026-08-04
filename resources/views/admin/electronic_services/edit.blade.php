@extends('admin.layouts.app')

@section('title', 'ویرایش خدمت الکترونیک')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">خدمات الکترونیک</p><h2>ویرایش {{ $service->title }}</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.electronic_services.show', $service) }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.electronic_services.update', $service) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="title">عنوان</label><input class="form-control" id="title" name="title" value="{{ old('title', $service->title) }}" required></div>
        <div class="col-md-6"><label class="form-label" for="slug">نامک</label><input class="form-control" id="slug" name="slug" value="{{ old('slug', $service->slug) }}" dir="ltr"></div>
        <div class="col-md-4"><label class="form-label" for="category_id">دسته‌بندی</label><select class="form-control" id="category_id" name="category_id"><option value="">بدون دسته‌بندی</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $service->category_id) === (string) $category->id)>{{ $category->title }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="icon">آیکن</label><input class="form-control" id="icon" name="icon" value="{{ old('icon', $service->icon) }}"></div>
        <div class="col-md-4"><label class="form-label" for="image">تصویر جدید</label><input class="form-control" id="image" name="image" type="file" accept="image/*">@if ($service->image)<small><a href="{{ Storage::url($service->image) }}" target="_blank">مشاهده تصویر فعلی</a></small>@endif</div>
        <div class="col-md-4"><label class="form-label" for="link_type">نوع لینک</label><select class="form-control" id="link_type" name="link_type" required>@foreach ($linkTypeLabels as $value => $label)<option value="{{ $value }}" @selected(old('link_type', $service->link_type) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-5"><label class="form-label" for="link">لینک خدمت</label><input class="form-control" id="link" name="link" value="{{ old('link', $service->link) }}" dir="ltr"></div>
        <div class="col-md-3"><label class="form-label" for="target">نحوه باز شدن</label><select class="form-control" id="target" name="target" required>@foreach ($targetLabels as $value => $label)<option value="{{ $value }}" @selected(old('target', $service->target) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="status">وضعیت</label><select class="form-control" id="status" name="status" required>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', $service->status) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="published_at">تاریخ انتشار</label><input class="form-control" id="published_at" name="published_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('published_at', $service->published_at)) }}"></div>
        <div class="col-md-4"><label class="form-label" for="rejected_reason">دلیل رد</label><input class="form-control" id="rejected_reason" name="rejected_reason" value="{{ old('rejected_reason', $service->rejected_reason) }}"></div>
        <div class="col-md-6"><label class="form-label" for="sort_order">ترتیب نمایش</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $service->sort_order) }}"></div>
        <div class="col-md-6"><label class="form-label" for="is_active">فعال</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', (int) $service->is_active) === '1')>فعال</option><option value="0" @selected((string) old('is_active', (int) $service->is_active) === '0')>غیرفعال</option></select></div>
        <div class="col-12"><label class="form-label" for="short_description">توضیح کوتاه</label><textarea class="form-control js-rich-editor" id="short_description" name="short_description" rows="3">{{ old('short_description', $service->short_description) }}</textarea></div>
        <div class="col-12"><label class="form-label" for="body">متن خدمت</label><textarea class="form-control js-rich-editor" id="body" name="body" rows="12">{{ old('body', $service->body) }}</textarea></div>
    </div>
    <div class="mt-3 d-flex gap-2"><button class="admin-primary-btn" type="submit">ذخیره تغییرات</button><a class="admin-secondary-btn" href="{{ route('admin.electronic_services.show', $service) }}">انصراف</a></div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>document.querySelectorAll('.js-rich-editor').forEach((el) => ClassicEditor.create(el, {language: 'fa'}).catch(console.error));</script>
@endpush
