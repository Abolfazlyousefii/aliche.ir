@extends('frontend.layouts.app')

@section('title', 'پیگیری شکایت | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'پیگیری وضعیت شکایت ثبت‌شده با شماره موبایل و کد رهگیری')

@section('content')
<div class="page-header">
    <div class="site-container">
        <nav class="breadcrumb-nav">
            <a href="{{ route('home') }}">خانه</a>
            <span class="breadcrumb-sep">/</span>
            <span>پیگیری شکایت</span>
        </nav>
        <h1>پیگیری شکایت</h1>
    </div>
</div>

<main class="archive-page">
    <div class="site-container">
        <div class="archive-header">
            <h1>مشاهده وضعیت شکایت</h1>
            <p>کد رهگیری دریافت‌شده پس از ثبت شکایت و همان شماره موبایل ثبت‌شده را وارد کنید.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="complaint-card" action="{{ route('complaints.lookup') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="tracking_code">کد رهگیری</label>
                    <input class="form-control" id="tracking_code" name="tracking_code" value="{{ old('tracking_code') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="mobile">شماره موبایل</label>
                    <input class="form-control" id="mobile" name="mobile" value="{{ old('mobile') }}" required>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <button class="tab-pill active" type="submit">پیگیری شکایت</button>
                <a class="tab-pill" href="{{ route('complaints.create') }}">ثبت شکایت جدید</a>
            </div>
        </form>
    </div>
</main>
@endsection
