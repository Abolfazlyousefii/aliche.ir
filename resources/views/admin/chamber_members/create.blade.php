@extends('admin.layouts.app')
@section('title', 'عضو جدید اتاق اصناف')
@section('content')
<div class="admin-page-toolbar"><div><p class="admin-eyebrow">اعضای اتاق اصناف</p><h2>عضو جدید</h2></div><a class="admin-secondary-btn" href="{{ route('admin.chamber_members.index') }}">بازگشت</a></div>
<form class="admin-panel-card admin-form" action="{{ route('admin.chamber_members.store') }}" method="POST" enctype="multipart/form-data">@include('admin.chamber_members._form')</form>
@endsection
