@extends('admin.layouts.app')

@section('title', 'ویرایش منو')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">ویرایش منو</p>
        <h2>{{ $menu->title }}</h2>
    </div>
    <a class="admin-secondary-btn" href="{{ route('admin.menus.show', $menu) }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.menus.update', $menu) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.menus._form')
</form>
@endsection
