@extends('admin.layouts.app')

@section('title', 'ویرایش کاربر')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">ویرایش کاربر</p>
        <h2>{{ $user->name }}</h2>
    </div>
    <a class="admin-secondary-btn" href="{{ route('admin.users.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.users.update', $user) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.users._form', ['user' => $user])
</form>
@endsection
