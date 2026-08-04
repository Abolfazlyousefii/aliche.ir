@extends('admin.layouts.app')

@section('title', 'ویرایش آیتم منو')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">{{ $menu->title }}</p>
        <h2>ویرایش آیتم: {{ $item->title }}</h2>
    </div>
    <a class="admin-secondary-btn" href="{{ route('admin.menus.show', $menu) }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.menus.items.update', [$menu, $item]) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.menus.items._form')
</form>
@endsection
