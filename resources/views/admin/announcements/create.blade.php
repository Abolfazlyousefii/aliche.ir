@extends('admin.layouts.app')

@section('title', 'ایجاد اطلاعیه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">مدیریت اطلاعیه‌ها</p><h2>ایجاد اطلاعیه جدید</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.announcements.index') }}">بازگشت به اطلاعیه‌ها</a>
</div>

<form action="{{ route('admin.announcements.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.announcements._form')
</form>
@endsection
