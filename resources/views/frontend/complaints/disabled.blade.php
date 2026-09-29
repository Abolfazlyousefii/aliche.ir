@extends('frontend.layouts.app')

@section('title', 'ثبت شکایت غیرفعال است | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'امکان ثبت و پیگیری آنلاین شکایت در حال حاضر غیرفعال است.')

@push('styles')
<meta name="robots" content="noindex, follow">
@endpush

@section('content')
<div class="page-header">
    <div class="site-container">
        <nav class="breadcrumb-nav">
            <a href="{{ route('home') }}">خانه</a>
            <span class="breadcrumb-sep">/</span>
            <span>ثبت شکایت</span>
        </nav>
        <h1>ثبت شکایت</h1>
    </div>
</div>

<main class="archive-page">
    <div class="site-container">
        <div class="archive-header">
            <h2>این بخش موقتاً غیرفعال است</h2>
            <p>امکان ثبت و پیگیری آنلاین شکایت در حال حاضر در دسترس نیست. برای پیگیری موضوع خود می‌توانید از راه‌های ارتباطی اتاق اصناف استفاده کنید.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a class="tab-pill active" href="{{ route('contact.create') }}">تماس با ما</a>
            <a class="tab-pill" href="{{ route('home') }}">بازگشت به صفحه اصلی</a>
        </div>
    </div>
</main>
@endsection
