@extends('admin.layouts.app')

@section('title', 'ویرایش نقش')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">ویرایش نقش</p>
        <h2>{{ $role->label }}</h2>
    </div>
    <a class="admin-secondary-btn" href="{{ route('admin.roles.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.roles.update', $role) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.roles._form', ['role' => $role])
</form>
@endsection
