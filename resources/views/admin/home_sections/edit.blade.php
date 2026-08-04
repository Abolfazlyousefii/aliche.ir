@extends('admin.layouts.app')

@section('title', 'ویرایش سکشن صفحه اصلی')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">ویرایش سکشن</p><h2>{{ $section->title }}</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.home_sections.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.home_sections.update', $section) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">کلید سکشن</label>
            <input class="form-control" value="{{ $section->key }}" disabled dir="ltr">
            <small class="text-muted">کلید برای اتصال به partial فرانت‌اند استفاده می‌شود و قابل تغییر نیست.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="title">عنوان سکشن</label>
            <input class="form-control" id="title" name="title" value="{{ old('title', $section->title) }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="sort_order">ترتیب نمایش</label>
            <input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $section->sort_order) }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="is_active">وضعیت</label>
            <select class="form-control" id="is_active" name="is_active" required>
                <option value="1" @selected((string) old('is_active', (int) $section->is_active) === '1')>فعال</option>
                <option value="0" @selected((string) old('is_active', (int) $section->is_active) === '0')>غیرفعال</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="subtitle">توضیح کوتاه</label>
            <input class="form-control" id="subtitle" name="subtitle" value="{{ old('subtitle', $section->subtitle) }}">
        </div>
        <div class="col-12">
            <label class="form-label" for="content">محتوا / متن جایگزین</label>
            <textarea class="form-control js-rich-editor" id="content" name="content" rows="4">{{ old('content', $section->content) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="settings">تنظیمات اختصاصی JSON</label>
            <textarea class="form-control" id="settings" name="settings" rows="8" dir="ltr">{{ old('settings', $settingsJson) }}</textarea>
            <small class="text-muted">مثال تنظیمات: {"limit": 6}. اگر سکشن تنظیم اختصاصی ندارد مقدار را خالی یا {} بگذارید.</small>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2 flex-wrap">
        <button class="admin-primary-btn" type="submit">ذخیره تغییرات</button>
        <a class="admin-secondary-btn" href="{{ route('admin.home_sections.index') }}">انصراف</a>
    </div>
</form>
@endsection
