@extends('admin.layouts.app')

@section('title', 'ایجاد عضو اتحادیه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">اعضای اتحادیه‌ها</p><h2>ایجاد عضو جدید</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.union_members.index') }}">بازگشت به اعضا</a>
</div>

<form action="{{ route('admin.union_members.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.union_members._form')
</form>
@endsection
