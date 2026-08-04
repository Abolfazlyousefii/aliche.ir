@extends('admin.layouts.app')

@section('title', 'ویرایش پیام تبریک و تسلیت')

@section('content')
<div class="admin-page-toolbar"><div><p class="admin-eyebrow">پیام تبریک و تسلیت</p><h2>ویرایش {{ $message->title }}</h2></div><a class="admin-secondary-btn" href="{{ route('admin.congratulation_messages.index') }}">بازگشت</a></div>
<form class="admin-panel-card admin-form" action="{{ route('admin.congratulation_messages.update', $message) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
@php($selectedType = old('message_type', $message->message_type ?? 'congratulation'))
<div class="row g-3">
<div class="col-md-6"><label class="form-label">عنوان</label><input class="form-control" name="title" value="{{ old('title', $message->title ?? '') }}" required></div>
<div class="col-md-6"><label class="form-label">نامک</label><input class="form-control" name="slug" value="{{ old('slug', $message->slug ?? '') }}" dir="ltr"><small class="text-muted">اگر خالی بماند از عنوان ساخته می‌شود.</small></div>
<div class="col-md-4"><label class="form-label">نوع پیام</label><select class="form-control" name="message_type" required>@foreach($messageTypeLabels as $value=>$label)<option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">اتحادیه</label><select class="form-control" name="union_id"><option value="">پیام عمومی</option>@foreach($unions as $union)<option value="{{ $union->id }}" @selected((string)old('union_id',$message->union_id ?? null)===(string)$union->id)>{{ $union->display_title }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">گیرنده پیامک</label><select class="form-control" name="recipient_type" id="recipient_type"><option value="">بدون ارسال پیامک</option><option value="member" @selected(old('recipient_type', $message->recipient_type ?? null)==='member')>عضو اتحادیه</option><option value="user" @selected(old('recipient_type', $message->recipient_type ?? null)==='user')>کاربر پنل</option><option value="union_manager" @selected(old('recipient_type', $message->recipient_type ?? null)==='union_manager')>رئیس اتحادیه</option></select></div>
<div class="col-md-12"><label class="form-label">انتخاب شخص</label><select class="form-control" name="recipient_id" id="recipient_id"><option value="">انتخاب کنید</option><optgroup label="اعضا" data-type="member">@foreach($members as $member)<option value="{{ $member->id }}" data-type="member" @selected(old('recipient_type', $message->recipient_type ?? null)==='member' && (string)old('recipient_id', $message->recipient_id ?? null)===(string)$member->id)>{{ $member->full_name }} - {{ $member->union?->display_title }} - {{ $member->mobile }}</option>@endforeach</optgroup><optgroup label="کاربران" data-type="user">@foreach($users as $user)<option value="{{ $user->id }}" data-type="user" @selected(old('recipient_type', $message->recipient_type ?? null)==='user' && (string)old('recipient_id', $message->recipient_id ?? null)===(string)$user->id)>{{ $user->name }} - {{ $user->mobile }}</option>@endforeach</optgroup><optgroup label="رؤسای اتحادیه" data-type="union_manager">@foreach($unions as $union)@if($union->mobile)<option value="{{ $union->id }}" data-type="union_manager" @selected(old('recipient_type', $message->recipient_type ?? null)==='union_manager' && (string)old('recipient_id', $message->recipient_id ?? null)===(string)$union->id)>{{ $union->manager_name ?: $union->display_title }} - {{ $union->mobile }}</option>@endif@endforeach</optgroup></select><small class="text-muted">با انتخاب گیرنده، پیامک به موبایل ثبت‌شده ارسال و در گزارش پیامک‌ها ذخیره می‌شود.</small></div>
<div class="col-md-4"><label class="form-label">نام مدیر</label><input class="form-control" name="manager_name" value="{{ old('manager_name',$message->manager_name ?? '') }}"></div>
<div class="col-md-4"><label class="form-label">سمت مدیر</label><input class="form-control" name="manager_position" value="{{ old('manager_position',$message->manager_position ?? '') }}"></div>
<div class="col-md-4"><label class="form-label">تصویر مدیر</label><input class="form-control" type="file" name="manager_image" accept="image/*">@if(($message->manager_image ?? null))<small><a href="{{ Storage::url($message->manager_image) }}" target="_blank">مشاهده فعلی</a></small>@endif</div>
<div class="col-md-4"><label class="form-label">نمایش در صفحه اصلی</label><select class="form-control" name="show_on_home"><option value="1" @selected((string)old('show_on_home',(int)($message->show_on_home ?? true))==='1')>بله</option><option value="0" @selected((string)old('show_on_home',(int)($message->show_on_home ?? true))==='0')>خیر</option></select></div>
<div class="col-md-4"><label class="form-label">نمایش در صفحه اتحادیه</label><select class="form-control" name="show_on_union_page"><option value="1" @selected((string)old('show_on_union_page',(int)($message->show_on_union_page ?? true))==='1')>بله</option><option value="0" @selected((string)old('show_on_union_page',(int)($message->show_on_union_page ?? true))==='0')>خیر</option></select></div>
<div class="col-md-4"><label class="form-label">وضعیت</label><select class="form-control" name="status">@foreach($statusLabels as $value=>$label)<option value="{{ $value }}" @selected(old('status',$message->status ?? 'draft')===$value)>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">تاریخ انتشار</label><input class="form-control" type="text" data-jalali-datepicker name="published_at" value="{{ jalali_input_datetime(old('published_at', $message->published_at ?? null)) }}"></div>
<div class="col-md-4"><label class="form-label">دلیل رد</label><input class="form-control" name="rejected_reason" value="{{ old('rejected_reason',$message->rejected_reason ?? '') }}"></div>
<div class="col-md-4"><label class="form-label">ترتیب نمایش</label><input class="form-control" type="number" name="sort_order" value="{{ old('sort_order',$message->sort_order ?? 0) }}" min="0"></div>
<div class="col-md-4"><label class="form-label">فعال</label><select class="form-control" name="is_active"><option value="1" @selected((string)old('is_active',(int)($message->is_active ?? true))==='1')>فعال</option><option value="0" @selected((string)old('is_active',(int)($message->is_active ?? true))==='0')>غیرفعال</option></select></div>
<div class="col-12"><label class="form-label">متن پیام</label><textarea class="form-control js-rich-editor" name="body" rows="12">{{ old('body',$message->body ?? '') }}</textarea></div>
</div>
@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
document.querySelectorAll('.js-rich-editor').forEach((el) => ClassicEditor.create(el, {language: 'fa'}).catch(console.error));
const typeSelect = document.getElementById('recipient_type');
const recipientSelect = document.getElementById('recipient_id');
function filterRecipients(){ const type=typeSelect?.value; recipientSelect?.querySelectorAll('option[data-type]').forEach(o=>o.hidden = type && o.dataset.type !== type); recipientSelect?.querySelectorAll('optgroup').forEach(g=>g.hidden = type && g.dataset.type !== type); }
typeSelect?.addEventListener('change', filterRecipients); filterRecipients();
</script>
@endpush

<div class="mt-3 d-flex gap-2"><button class="admin-primary-btn">ذخیره پیام</button><a class="admin-secondary-btn" href="{{ route('admin.congratulation_messages.index') }}">انصراف</a></div>
</form>
@endsection
