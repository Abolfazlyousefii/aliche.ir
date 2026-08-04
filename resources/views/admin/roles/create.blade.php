@extends('admin.layouts.app')

@section('title', 'ایجاد نقش')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">نقش جدید</p>
        <h2>ایجاد نقش جدید</h2>
    </div>
    <a class="admin-secondary-btn" href="{{ route('admin.roles.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.roles.store') }}" method="POST">
    @csrf
    @include('admin.roles._form', ['role' => null])
</form>
@endsection
