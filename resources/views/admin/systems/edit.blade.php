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
        <div class="col-md-4"><label class="form-label" for="image">تصویر جدید</label><input class="form-control" id="image" name="image" type="file" accept="image/*">@if ($system->image)<small><a href="{{ $system->image_url }}" target="_blank">مشاهده تصویر فعلی</a></small>@endif</div>
        <div class="col-md-8"><label class="form-label" for="link">لینک ورود</label><input class="form-control" id="link" name="link" value="{{ old('link', $system->link) }}" dir="ltr"></div>
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
