@extends('admin.layouts.app')

@section('title', 'ایجاد دسترسی')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">دسترسی جدید</p>
        <h2>ایجاد دسترسی جدید</h2>
    </div>
    <a class="admin-secondary-btn" href="{{ route('admin.permissions.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.permissions.store') }}" method="POST">
    @csrf
    @include('admin.permissions._form', ['permission' => null])
</form>
@endsection
