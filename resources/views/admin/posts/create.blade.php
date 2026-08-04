@extends('admin.layouts.app')

@section('title', 'ایجاد خبر')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">مدیریت اخبار</p><h2>ایجاد خبر جدید</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.posts.index') }}">بازگشت به اخبار</a>
</div>

<form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.posts._form')
</form>
@endsection
