@extends('frontend.layouts.app')

@section('title', 'تماس با ما | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'ارسال پیام ارتباطی به اتاق اصناف مرکز استان گلستان')
@section('active_menu', 'contact')

@section('content')
<div class="page-header">
    <div class="site-container">
        <nav class="breadcrumb-nav">
            <a href="{{ route('home') }}">خانه</a>
            <span class="breadcrumb-sep">/</span>
            <span>تماس با ما</span>
        </nav>
        <h1>تماس با ما</h1>
    </div>
</div>

<main class="archive-page">
    <div class="site-container">
        <div class="archive-header">
            <h1>فرم ارتباط با اتاق اصناف</h1>
            <p>پرسش‌ها، پیشنهادها و پیام‌های خود را از طریق فرم زیر ارسال کنید تا توسط کارشناسان مربوطه بررسی شود.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <form class="complaint-card" action="{{ route('contact.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="full_name">نام و نام خانوادگی</label>
                            <input class="form-control" id="full_name" name="full_name" value="{{ old('full_name') }}" required maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="mobile">شماره تماس</label>
                            <input class="form-control" id="mobile" name="mobile" value="{{ old('mobile') }}" required maxlength="20" dir="ltr">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">ایمیل <span class="text-muted">(اختیاری)</span></label>
                            <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" dir="ltr">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="subject">موضوع</label>
                            <input class="form-control" id="subject" name="subject" value="{{ old('subject') }}" required maxlength="255">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="message">پیام</label>
                            <textarea class="form-control" id="message" name="message" rows="7" required minlength="10" maxlength="5000">{{ old('message') }}</textarea>
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2 flex-wrap">
                        <button class="tab-pill active" type="submit">ارسال پیام</button>
                        <a class="tab-pill" href="{{ route('home') }}">بازگشت به صفحه اصلی</a>
                    </div>
                </form>
            </div>
            <div class="col-lg-4">
                <aside class="complaint-card h-100">
                    <h2 class="h5 mb-3">اطلاعات تماس</h2>
                    <p><strong>تلفن:</strong> {{ $settings->get('site.phone', '۰۱۷۳۲۱۵۲۹۱۲') }}</p>
                    <p><strong>موبایل:</strong> {{ $settings->get('site.mobile', '—') }}</p>
                    <p><strong>ایمیل:</strong> {{ $settings->get('site.email', 'info@example.com') }}</p>
                    <p><strong>آدرس:</strong><br>{{ $settings->get('site.address', 'اتاق اصناف مرکز استان گلستان') }}</p>
                    @if ($settings->get('site.map_url'))
                        <a class="tab-pill" href="{{ $settings->get('site.map_url') }}" target="_blank" rel="noopener">مشاهده روی نقشه</a>
                    @endif
                </aside>
            </div>
        </div>
    </div>
</main>
@endsection
