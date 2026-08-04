@extends('admin.layouts.app')
@section('title', 'ایجاد کمیسیون')
@section('content')
<div class="admin-page-toolbar"><div><p class="admin-eyebrow">کمیسیون‌ها</p><h2>کمیسیون جدید</h2></div><a class="admin-secondary-btn" href="{{ route('admin.commissions.index') }}">بازگشت</a></div>
<form class="admin-panel-card admin-form" method="POST" action="{{ route('admin.commissions.store') }}" enctype="multipart/form-data">@csrf
<div class="row g-3">
<div class="col-md-6"><label class="form-label">عنوان</label><input class="form-control" name="title" value="{{ old('title') }}" required></div>
<div class="col-md-6"><label class="form-label">نامک</label><input class="form-control" name="slug" value="{{ old('slug') }}" dir="ltr"></div>
<div class="col-md-4"><label class="form-label">وضعیت</label><select class="form-control" name="status">@foreach($statusLabels as $value=>$label)<option value="{{ $value }}" @selected(old('status','draft')===$value)>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">تاریخ انتشار</label><input class="form-control" type="text" data-jalali-datepicker name="published_at" value="{{ jalali_input_datetime(old('published_at')) }}"></div>
<div class="col-md-4"><label class="form-label">دلیل رد</label><input class="form-control" name="rejected_reason" value="{{ old('rejected_reason') }}"></div>
<div class="col-md-4"><label class="form-label">ترتیب</label><input class="form-control" type="number" name="sort_order" value="{{ old('sort_order',0) }}" min="0"></div>
<div class="col-md-4"><label class="form-label">فعال</label><select class="form-control" name="is_active"><option value="1" @selected(old('is_active','1')==='1')>فعال</option><option value="0" @selected(old('is_active')==='0')>غیرفعال</option></select></div>
<div class="col-md-6"><label class="form-label">تصویر</label><input class="form-control" type="file" name="image" accept="image/*"></div>
<div class="col-md-6"><label class="form-label">پیوست‌ها</label><input class="form-control" type="file" name="attachments[]" multiple></div>
<div class="col-12"><label class="form-label">اعضا (هر عضو در یک خط)</label><textarea class="form-control" name="members" rows="4">{{ old('members') }}</textarea></div>
<div class="col-12"><label class="form-label">توضیحات</label><textarea class="form-control js-rich-editor" name="description" rows="6">{{ old('description') }}</textarea></div>
<div class="col-12"><h3 class="h6 mt-2">وظایف کمیسیون</h3><p class="text-muted small">برای نمایش در صفحه اول و صفحه جزئیات کمیسیون، عنوان و توضیح هر وظیفه را وارد کنید.</p></div>
@for($i = 0; $i < 5; $i++)
<div class="col-md-4"><label class="form-label">عنوان وظیفه {{ $i + 1 }}</label><input class="form-control" name="tasks[{{ $i }}][title]" value="{{ old('tasks.'.$i.'.title') }}"></div>
<div class="col-md-5"><label class="form-label">توضیح وظیفه {{ $i + 1 }}</label><textarea class="form-control js-rich-editor" name="tasks[{{ $i }}][description]" rows="3">{{ old('tasks.'.$i.'.description') }}</textarea></div>
<div class="col-md-2"><label class="form-label">ترتیب</label><input class="form-control" type="number" min="0" name="tasks[{{ $i }}][sort_order]" value="{{ old('tasks.'.$i.'.sort_order', $i) }}"></div>
<div class="col-md-1"><label class="form-label">فعال</label><select class="form-control" name="tasks[{{ $i }}][is_active]"><option value="1" @selected(old('tasks.'.$i.'.is_active','1')==='1')>بله</option><option value="0" @selected(old('tasks.'.$i.'.is_active')==='0')>خیر</option></select></div>
@endfor
</div>
<div class="mt-3 d-flex gap-2"><button class="admin-primary-btn">ذخیره</button><a class="admin-secondary-btn" href="{{ route('admin.commissions.index') }}">انصراف</a></div></form>
@endsection
