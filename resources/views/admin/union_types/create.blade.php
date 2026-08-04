@extends('admin.layouts.app')
@section('title','ایجاد نوع اتحادیه')
@section('content')
<div class="admin-page-toolbar"><div><p class="admin-eyebrow">نوع اتحادیه‌ها</p><h2>ایجاد نوع اتحادیه</h2></div><a class="admin-secondary-btn" href="{{ route('admin.union-types.index') }}">بازگشت</a></div>
<form method="POST" action="{{ route('admin.union-types.store') }}" enctype="multipart/form-data">@csrf @include('admin.union_types._form')</form>
@endsection
