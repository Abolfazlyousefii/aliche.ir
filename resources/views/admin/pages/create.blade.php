@extends('admin.layouts.app')

@section('title', 'ایجاد صفحه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">صفحه جدید</p><h2>ایجاد صفحه داینامیک</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.pages.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.pages.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.pages._form')
</form>
@endsection
