@extends('admin.layouts.app')

@section('title', 'ایجاد جایگاه تبلیغاتی')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">تبلیغات</p><h2>ایجاد جایگاه تبلیغاتی</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.advertisement_positions.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.advertisement_positions.store') }}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="title">عنوان</label><input class="form-control" id="title" name="title" value="{{ old('title') }}" required></div>
        <div class="col-md-6"><label class="form-label" for="key">کلید جایگاه</label><input class="form-control" id="key" name="key" value="{{ old('key') }}" dir="ltr" placeholder="home_top"><small class="text-muted">برای دریافت در Blade مثل home_top استفاده می‌شود.</small></div>
        <div class="col-md-4"><label class="form-label" for="width">عرض پیشنهادی</label><input class="form-control" id="width" name="width" type="number" min="1" value="{{ old('width') }}"></div>
        <div class="col-md-4"><label class="form-label" for="height">ارتفاع پیشنهادی</label><input class="form-control" id="height" name="height" type="number" min="1" value="{{ old('height') }}"></div>
        <div class="col-md-4"><label class="form-label" for="is_active">فعال</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected(old('is_active', '1') === '1')>فعال</option><option value="0" @selected(old('is_active') === '0')>غیرفعال</option></select></div>
        <div class="col-12"><label class="form-label" for="description">توضیحات</label><textarea class="form-control js-rich-editor" id="description" name="description" rows="4">{{ old('description') }}</textarea></div>
    </div>
    <div class="mt-3 d-flex gap-2"><button class="admin-primary-btn" type="submit">ذخیره جایگاه</button><a class="admin-secondary-btn" href="{{ route('admin.advertisement_positions.index') }}">انصراف</a></div>
</form>
@endsection
