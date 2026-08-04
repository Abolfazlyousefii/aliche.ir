@extends('admin.layouts.app')
@section('title', 'ویرایش عضو اتاق اصناف')
@section('content')
<div class="admin-page-toolbar"><div><p class="admin-eyebrow">اعضای اتاق اصناف</p><h2>{{ $member->full_name }}</h2></div><a class="admin-secondary-btn" href="{{ route('admin.chamber_members.index') }}">بازگشت</a></div>
<form class="admin-panel-card admin-form" action="{{ route('admin.chamber_members.update', $member) }}" method="POST" enctype="multipart/form-data">@method('PUT') @include('admin.chamber_members._form')</form>
@endsection
