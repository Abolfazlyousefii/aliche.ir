@extends('admin.layouts.app')

@section('title', 'ایجاد کاربر')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">کاربر جدید</p>
        <h2>ایجاد کاربر جدید</h2>
    </div>
    <a class="admin-secondary-btn" href="{{ route('admin.users.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.users.store') }}" method="POST">
    @csrf
    @include('admin.users._form', ['user' => null])
</form>
@endsection
