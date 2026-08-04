@extends('admin.layouts.app')
@section('title','ویرایش دسته‌بندی')
@section('content')
<div class="admin-page-toolbar"><div><p class="admin-eyebrow">دسته‌بندی‌ها</p><h2>ویرایش {{ $category->title }}</h2></div><a class="admin-secondary-btn" href="{{ route('admin.categories.index') }}">بازگشت</a></div>
<form method="POST" action="{{ route('admin.categories.update', $category) }}">@csrf @method('PUT') @include('admin.categories._form')</form>
@endsection
