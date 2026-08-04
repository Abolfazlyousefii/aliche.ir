@extends('admin.layouts.app')

@section('title', 'ایجاد منو')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">منوی جدید</p>
        <h2>ایجاد منو جدید</h2>
    </div>
    <a class="admin-secondary-btn" href="{{ route('admin.menus.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.menus.store') }}" method="POST">
    @csrf
    @include('admin.menus._form')
</form>
@endsection
