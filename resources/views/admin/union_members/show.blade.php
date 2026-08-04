@extends('admin.layouts.app')

@section('title', 'جزئیات عضو اتحادیه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">جزئیات عضو اتحادیه</p><h2>{{ $member->full_name }}</h2></div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-secondary-btn" href="{{ route('admin.union_members.index') }}">بازگشت</a>
        <a class="admin-primary-btn" href="{{ route('admin.union_members.edit', $member) }}">ویرایش</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-panel-card">
            <h3 class="h5 mb-3">اطلاعات کسب‌وکار</h3>
            <dl class="row mb-0">
                <dt class="col-md-4">نام کسب‌وکار</dt><dd class="col-md-8">{{ $member->business_name ?: '—' }}</dd>
                <dt class="col-md-4">شماره پروانه کسب</dt><dd class="col-md-8">{{ $member->business_license_number ?: '—' }}</dd>
                <dt class="col-md-4">آدرس</dt><dd class="col-md-8">{{ $member->address ?: '—' }}</dd>
                <dt class="col-md-4">توضیحات</dt><dd class="col-md-8">{{ $member->description ?: '—' }}</dd>
            </dl>
        </div>
        @if ($member->attachments)
            <div class="admin-panel-card mt-3">
                <h3 class="h5 mb-3">پیوست‌ها</h3>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($member->attachments as $attachment)
                        <a class="admin-secondary-btn" href="{{ Storage::url($attachment['path']) }}" target="_blank">{{ $attachment['name'] ?? basename($attachment['path']) }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    <div class="col-lg-4">
        <div class="admin-panel-card">
            <dl class="row mb-0">
                <dt class="col-5">اتحادیه</dt><dd class="col-7">{{ $member->union?->display_title ?: '—' }}</dd>
                <dt class="col-5">کد ملی</dt><dd class="col-7">{{ $member->national_code ?: '—' }}</dd>
                <dt class="col-5">موبایل</dt><dd class="col-7">{{ $member->mobile ?: '—' }}</dd>
                <dt class="col-5">تلفن</dt><dd class="col-7">{{ $member->phone ?: '—' }}</dd>
                <dt class="col-5">کد عضویت</dt><dd class="col-7">{{ $member->membership_code ?: '—' }}</dd>
                <dt class="col-5">وضعیت</dt><dd class="col-7"><span class="admin-status-badge status-{{ $member->status }}">{{ $member->status }}</span></dd>
                <dt class="col-5">فعال</dt><dd class="col-7">{{ $member->is_active ? 'بله' : 'خیر' }}</dd>
                <dt class="col-5">ثبت</dt><dd class="col-7">{{ jalali_datetime($member->created_at) ?: '—' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
