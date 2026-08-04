@extends('admin.layouts.app')

@section('title', 'ویرایش اطلاعیه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">مدیریت اطلاعیه‌ها</p><h2>ویرایش اطلاعیه: {{ $announcement->title }}</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.announcements.show', $announcement) }}">مشاهده اطلاعیه</a>
</div>

<form action="{{ route('admin.announcements.update', $announcement) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.announcements._form')
</form>
@endsection
