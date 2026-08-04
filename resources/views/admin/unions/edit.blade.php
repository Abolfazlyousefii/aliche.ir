@extends('admin.layouts.app')

@section('title', 'ویرایش اتحادیه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">مدیریت اتحادیه‌ها</p><h2>ویرایش اتحادیه: {{ $union->display_title }}</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.unions.show', $union) }}">مشاهده اتحادیه</a>
</div>

<form action="{{ route('admin.unions.update', $union) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.unions._form')
</form>
@endsection
