@extends('admin.layouts.app')

@section('title', 'افزودن آیتم منو')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">{{ $menu->title }}</p>
        <h2>افزودن آیتم منو</h2>
    </div>
    <a class="admin-secondary-btn" href="{{ route('admin.menus.show', $menu) }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.menus.items.store', $menu) }}" method="POST">
    @csrf
    @include('admin.menus.items._form')
</form>
@endsection
