@extends('frontend.layouts.app')

@section('title', ($isNew ? 'کد رهگیری شکایت' : 'نتیجه پیگیری شکایت').' | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'نمایش نتیجه ثبت یا پیگیری شکایت')

@section('content')
<div class="page-header">
    <div class="site-container">
        <nav class="breadcrumb-nav">
            <a href="{{ route('home') }}">خانه</a>
            <span class="breadcrumb-sep">/</span>
            <a href="{{ route('complaints.track') }}">پیگیری شکایت</a>
            <span class="breadcrumb-sep">/</span>
            <span>نتیجه</span>
        </nav>
        <h1>{{ $isNew ? 'شکایت با موفقیت ثبت شد' : 'نتیجه پیگیری شکایت' }}</h1>
    </div>
</div>

<main class="archive-page">
    <div class="site-container">
        @if ($isNew)
            <div class="alert alert-success">شکایت شما ثبت شد. لطفاً کد رهگیری زیر را برای پیگیری‌های بعدی نگهداری کنید.</div>
        @endif

        <div class="complaint-card">
            <div class="row g-3">
                <div class="col-md-4"><strong>کد رهگیری:</strong> <span dir="ltr">{{ $complaint->tracking_code }}</span></div>
                <div class="col-md-4"><strong>وضعیت:</strong> {{ $complaint->status_label }}</div>
                <div class="col-md-4"><strong>اتحادیه:</strong> {{ $complaint->union?->display_title ?: '—' }}</div>
                <div class="col-md-6"><strong>نام:</strong> {{ $complaint->full_name }}</div>
                <div class="col-md-6"><strong>موبایل:</strong> {{ $complaint->mobile }}</div>
                <div class="col-12"><strong>موضوع:</strong> {{ $complaint->subject }}</div>
                <div class="col-12"><strong>شرح شکایت:</strong><p class="mt-2 mb-0">{{ $complaint->body }}</p></div>
            </div>
        </div>

        <div class="complaint-card mt-3">
            <h2 class="h5 mb-3">پاسخ اتحادیه / مدیریت</h2>
            @if ($complaint->admin_response)
                <p>{{ $complaint->admin_response }}</p>
                <small class="text-muted">تاریخ پاسخ: {{ jalali_datetime($complaint->answered_at) ?: '—' }}</small>
            @else
                <p class="mb-0 text-muted">هنوز پاسخی برای این شکایت ثبت نشده است.</p>
            @endif
        </div>

        <div class="mt-3 d-flex gap-2 flex-wrap">
            <a class="tab-pill active" href="{{ route('complaints.track') }}">پیگیری شکایت دیگر</a>
            <a class="tab-pill" href="{{ route('complaints.create') }}">ثبت شکایت جدید</a>
        </div>
    </div>
</main>
@endsection
