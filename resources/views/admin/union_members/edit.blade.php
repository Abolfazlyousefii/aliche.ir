@extends('admin.layouts.app')

@section('title', 'ویرایش عضو اتحادیه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">اعضای اتحادیه‌ها</p><h2>ویرایش عضو: {{ $member->full_name }}</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.union_members.show', $member) }}">مشاهده عضو</a>
</div>

<form action="{{ route('admin.union_members.update', $member) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.union_members._form')
</form>
@endsection
