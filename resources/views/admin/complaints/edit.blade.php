@extends('admin.layouts.app')

@section('title', 'ویرایش شکایت')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">ویرایش شکایت</p><h2>{{ $complaint->subject }}</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.complaints.show', $complaint) }}">بازگشت به جزئیات</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.complaints.update', $complaint) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">کد رهگیری</label>
            <input class="form-control" value="{{ $complaint->tracking_code }}" disabled dir="ltr">
        </div>
        <div class="col-md-4">
            <label class="form-label">اتحادیه</label>
            <input class="form-control" value="{{ $complaint->union?->display_title ?: '—' }}" disabled>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="status">وضعیت</label>
            <select class="form-control" id="status" name="status" required>
                @foreach ($statusLabels as $itemStatus => $label)
                    <option value="{{ $itemStatus }}" @selected(old('status', $complaint->status) === $itemStatus)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">شاکی</label>
            <input class="form-control" value="{{ $complaint->full_name }} - {{ $complaint->mobile }}" disabled>
        </div>
        <div class="col-md-6">
            <label class="form-label">موضوع</label>
            <input class="form-control" value="{{ $complaint->subject }}" disabled>
        </div>
        <div class="col-12">
            <label class="form-label">شرح شکایت</label>
            <textarea class="form-control" rows="5" disabled>{{ $complaint->body }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="internal_note">یادداشت داخلی</label>
            <textarea class="form-control" id="internal_note" name="internal_note" rows="5">{{ old('internal_note', $complaint->internal_note) }}</textarea>
            <small class="text-muted">این یادداشت فقط در پنل مدیریت نمایش داده می‌شود.</small>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button class="admin-primary-btn" type="submit">ذخیره تغییرات</button>
        <a class="admin-secondary-btn" href="{{ route('admin.complaints.show', $complaint) }}">انصراف</a>
    </div>
</form>
@endsection
