@extends('admin.layouts.app')
@section('title', 'تنظیمات هدر')
@section('content')
@php
    $defaultButtons = [[
        'title' => 'سامانه خدمات صنفی',
        'url' => route('systems.index'),
        'icon' => '💻',
        'target' => '_self',
        'is_active' => true,
    ]];
@endphp
<div class="admin-page-toolbar"><div><p class="admin-eyebrow">تنظیمات</p><h2>تنظیمات هدر</h2></div></div>
<form class="admin-panel-card admin-form" action="{{ route('admin.header_settings.update') }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
<div class="row g-3">
<div class="col-md-6"><label class="form-label">لوگوی دسکتاپ</label><input class="form-control" type="file" name="desktop_logo" accept="image/*">@if($settings->get('header.desktop_logo'))<small><a href="{{ Storage::url($settings->get('header.desktop_logo')) }}" target="_blank">مشاهده فعلی</a></small>@endif</div>
<div class="col-md-6"><label class="form-label">لوگوی موبایل</label><input class="form-control" type="file" name="mobile_logo" accept="image/*">@if($settings->get('header.mobile_logo'))<small><a href="{{ Storage::url($settings->get('header.mobile_logo')) }}" target="_blank">مشاهده فعلی</a></small>@endif</div>
<div class="col-md-6"><label class="form-label">لوگوی هدر قدیمی (Fallback)</label><input class="form-control" type="file" name="header_logo" accept="image/*">@if($settings->get('header.header_logo'))<small><a href="{{ Storage::url($settings->get('header.header_logo')) }}" target="_blank">مشاهده فعلی</a></small>@endif</div>
<div class="col-md-6"><label class="form-label">متن بالای هدر</label><input class="form-control" name="top_text" value="{{ old('top_text', $settings->get('header.top_text', 'اتاق اصناف مرکز استان گلستان؛ پشتیبان کسب‌وکارهای صنفی')) }}"></div>
<div class="col-md-4"><label class="form-label">نمایش تاریخ بالای هدر</label><select class="form-control" name="top_date_enabled"><option value="1" @selected(old('top_date_enabled', (int)$settings->get('header.top_date_enabled', true)) == 1)>فعال</option><option value="0" @selected(old('top_date_enabled', (int)$settings->get('header.top_date_enabled', true)) == 0)>غیرفعال</option></select></div>
<div class="col-md-4"><label class="form-label">متن دکمه تماس</label><input class="form-control" name="contact_button_text" value="{{ old('contact_button_text', $settings->get('header.contact_button_text', 'تماس با اتاق')) }}"></div>
<div class="col-md-4"><label class="form-label">لینک دکمه تماس</label><input class="form-control" name="contact_button_link" value="{{ old('contact_button_link', $settings->get('header.contact_button_link', 'tel:01732152912')) }}" dir="ltr"></div>
<div class="col-12">
<label class="form-label">دکمه‌های داینامیک هدر (JSON)</label>
<textarea class="form-control" name="header_buttons" rows="8" dir="ltr">{{ old('header_buttons', json_encode($settings->get('header.header_buttons', $defaultButtons), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea>
<small class="text-muted">مثال: [{"title":"سامانه خدمات صنفی","url":"/systems","icon":"💻","target":"_self","is_active":true}]</small>
</div>
</div><div class="mt-3"><button class="admin-primary-btn">ذخیره تنظیمات هدر</button></div></form>
@endsection
