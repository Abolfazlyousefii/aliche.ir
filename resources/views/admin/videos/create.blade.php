@extends('admin.layouts.app')

@section('title', 'ایجاد ویدیو')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">ویدیوها</p><h2>ایجاد ویدیو جدید</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.videos.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.videos.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="title">عنوان</label><input class="form-control" id="title" name="title" value="{{ old('title') }}" required></div>
        <div class="col-md-6"><label class="form-label" for="slug">نامک</label><input class="form-control" id="slug" name="slug" value="{{ old('slug') }}" dir="ltr"><small class="text-muted">اگر خالی بماند از عنوان ساخته می‌شود.</small></div>
        <div class="col-md-4"><label class="form-label" for="video_type">نوع ویدیو</label><select class="form-control" id="video_type" name="video_type" required>@foreach ($typeLabels as $value => $label)<option value="{{ $value }}" @selected(old('video_type', 'upload') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="union_id">اتحادیه</label><select class="form-control" id="union_id" name="union_id"><option value="">ویدیوی عمومی</option>@foreach ($unions as $union)<option value="{{ $union->id }}" @selected((string) old('union_id') === (string) $union->id)>{{ $union->display_title }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="category_id">دسته‌بندی</label><select class="form-control" id="category_id" name="category_id"><option value="">بدون دسته‌بندی</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)old('category_id')===(string)$category->id)>{{ $category->title }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="status">وضعیت</label><select class="form-control" id="status" name="status" required>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', 'draft') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="published_at">تاریخ انتشار</label><input class="form-control" id="published_at" name="published_at" type="text" data-jalali-datepicker value="{{ jalali_input_datetime(old('published_at')) }}"></div>
        <div class="col-md-4"><label class="form-label" for="sort_order">ترتیب نمایش</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', 0) }}"></div>
        <div class="col-md-4"><label class="form-label" for="is_active">فعال</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected(old('is_active', '1') === '1')>فعال</option><option value="0" @selected(old('is_active') === '0')>غیرفعال</option></select></div>
        <div class="col-md-4"><label class="form-label" for="cover_image">تصویر کاور</label><input class="form-control" id="cover_image" name="cover_image" type="file" accept="image/*"></div>
        <div class="col-md-4 video-upload-field"><label class="form-label" for="video_file">فایل ویدیو</label><input class="form-control" id="video_file" name="video_file" type="file" accept="video/*"><small class="text-muted">حداکثر حجم ۱۰۰ مگابایت.</small></div>
        <div class="col-md-4 video-aparat-field"><label class="form-label" for="aparat_url">لینک آپارات</label><input class="form-control" id="aparat_url" name="aparat_url" value="{{ old('aparat_url') }}" dir="ltr" placeholder="https://www.aparat.com/v/..."></div>
        <div class="col-12"><label class="form-label" for="description">توضیحات</label><textarea class="form-control js-rich-editor" id="description" name="description" rows="4">{{ old('description') }}</textarea></div>
        <div class="col-12"><label class="form-label" for="rejected_reason">دلیل رد</label><textarea class="form-control" id="rejected_reason" name="rejected_reason" rows="2">{{ old('rejected_reason') }}</textarea></div>
    </div>
    <div class="mt-3 d-flex gap-2"><button class="admin-primary-btn" type="submit">ذخیره ویدیو</button><a class="admin-secondary-btn" href="{{ route('admin.videos.index') }}">انصراف</a></div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const type = document.getElementById('video_type');
    const uploadFields = document.querySelectorAll('.video-upload-field');
    const aparatFields = document.querySelectorAll('.video-aparat-field');
    const toggleFields = () => {
        uploadFields.forEach(item => item.style.display = type.value === 'upload' ? '' : 'none');
        aparatFields.forEach(item => item.style.display = type.value === 'aparat' ? '' : 'none');
    };
    type.addEventListener('change', toggleFields);
    toggleFields();
})();
</script>
@endpush
