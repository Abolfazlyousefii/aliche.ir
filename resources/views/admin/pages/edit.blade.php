@extends('admin.layouts.app')

@section('title', 'ویرایش صفحه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">ویرایش صفحه</p><h2>{{ $page->title }}</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.pages.show', $page) }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.pages.update', $page) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.pages._form')
</form>
@endsection
