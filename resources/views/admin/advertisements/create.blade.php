@extends('admin.layouts.app')

@section('title', 'ایجاد تبلیغ')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">تبلیغات</p><h2>ایجاد تبلیغ جدید</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.advertisements.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.advertisements.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="title">عنوان</label><input class="form-control" id="title" name="title" value="{{ old('title') }}" required></div>
        <div class="col-md-6"><label class="form-label" for="position_id">جایگاه</label><select class="form-control" id="position_id" name="position_id" required><option value="">انتخاب جایگاه</option>@foreach ($positions as $position)<option value="{{ $position->id }}" @selected((string) old('position_id') === (string) $position->id)>{{ $position->title }} ({{ $position->key }})</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label" for="image">تصویر تبلیغ</label><input class="form-control" id="image" name="image" type="file" accept="image/*" required></div>
        <div class="col-md-6"><label class="form-label" for="link">لینک</label><input class="form-control" id="link" name="link" value="{{ old('link') }}" dir="ltr" placeholder="https://..."></div>
        <div class="col-md-4"><label class="form-label" for="target">نحوه باز شدن لینک</label><select class="form-control" id="target" name="target" required>@foreach ($targetLabels as $value => $label)<option value="{{ $value }}" @selected(old('target', '_self') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="starts_at">زمان شروع</label><input class="form-control" id="starts_at" name="starts_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('starts_at', now())) }}" required></div>
        <div class="col-md-4"><label class="form-label" for="expires_at">زمان پایان</label><input class="form-control" id="expires_at" name="expires_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('expires_at')) }}"></div>
        <div class="col-md-3"><label class="form-label" for="sort_order">ترتیب نمایش</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', 0) }}"></div>
        <div class="col-md-3"><label class="form-label" for="views_count">تعداد نمایش</label><input class="form-control" id="views_count" name="views_count" type="number" min="0" value="{{ old('views_count', 0) }}"></div>
        <div class="col-md-3"><label class="form-label" for="clicks_count">تعداد کلیک</label><input class="form-control" id="clicks_count" name="clicks_count" type="number" min="0" value="{{ old('clicks_count', 0) }}"></div>
        <div class="col-md-3"><label class="form-label" for="is_active">فعال</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected(old('is_active', '1') === '1')>فعال</option><option value="0" @selected(old('is_active') === '0')>غیرفعال</option></select></div>
    </div>
    <div class="mt-3 d-flex gap-2"><button class="admin-primary-btn" type="submit">ذخیره تبلیغ</button><a class="admin-secondary-btn" href="{{ route('admin.advertisements.index') }}">انصراف</a></div>
</form>
@endsection
