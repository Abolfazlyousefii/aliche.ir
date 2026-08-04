@extends('admin.layouts.app')

@section('title', 'ویرایش دسترسی')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">ویرایش دسترسی</p>
        <h2>{{ $permission->label }}</h2>
    </div>
    <a class="admin-secondary-btn" href="{{ route('admin.permissions.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.permissions.update', $permission) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.permissions._form', ['permission' => $permission])
</form>
@endsection
