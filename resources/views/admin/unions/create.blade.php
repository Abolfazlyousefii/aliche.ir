@extends('admin.layouts.app')

@section('title', 'ایجاد اتحادیه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">مدیریت اتحادیه‌ها</p><h2>ایجاد اتحادیه جدید</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.unions.index') }}">بازگشت به اتحادیه‌ها</a>
</div>

<form action="{{ route('admin.unions.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.unions._form')
</form>
@endsection
