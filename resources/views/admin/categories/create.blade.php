@extends('admin.layouts.app')
@section('title','ایجاد دسته‌بندی')
@section('content')
<div class="admin-page-toolbar"><div><p class="admin-eyebrow">دسته‌بندی‌ها</p><h2>ایجاد دسته‌بندی جدید</h2></div><a class="admin-secondary-btn" href="{{ route('admin.categories.index') }}">بازگشت</a></div>
<form method="POST" action="{{ route('admin.categories.store') }}">@csrf @include('admin.categories._form')</form>
@endsection
