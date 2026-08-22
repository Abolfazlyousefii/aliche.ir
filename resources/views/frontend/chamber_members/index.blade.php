@extends('frontend.layouts.app')
@section('title', 'اعضای اتاق اصناف')
@section('content')
<section class="page-hero"><div class="container"><p class="section-eyebrow">اتاق اصناف</p><h1>اعضای اتاق اصناف</h1><p>معرفی اعضا به‌صورت داینامیک از پنل مدیریت.</p></div></section>
<section class="section"><div class="container"><div class="row g-4">@forelse($members as $member)<div class="col-md-6 col-lg-4"><article class="content-card h-100 text-center"><img src="{{ $member->photo_url }}" alt="{{ $member->full_name }}" class="mx-auto mb-3" width="150" height="150" loading="lazy" decoding="async" style="object-fit:cover;border-radius:28px"><h2 class="h5 mb-2">{{ $member->full_name }}</h2><p class="text-primary fw-bold mb-2">{{ $member->position }}</p>@if($member->bio)<p class="text-muted mb-0">{{ $member->bio }}</p>@endif</article></div>@empty<div class="col-12"><div class="content-card text-center text-muted">هنوز عضوی ثبت نشده است.</div></div>@endforelse</div></div></section>
@endsection
