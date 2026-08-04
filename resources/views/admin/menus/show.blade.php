@extends('admin.layouts.app')

@section('title', 'آیتم‌های منو')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">{{ $menu->location }}</p>
        <h2>{{ $menu->title }}</h2>
    </div>
    <div class="admin-actions">
        <a class="admin-primary-btn" href="{{ route('admin.menus.items.create', $menu) }}">افزودن آیتم</a>
        <a href="{{ route('admin.menus.edit', $menu) }}">ویرایش منو</a>
        <a href="{{ route('admin.menus.index') }}">بازگشت</a>
    </div>
</div>

<div class="admin-panel-card">
    <div class="admin-panel-header">
        <h3>ساختار منو</h3>
        <span>برای جابه‌جایی drag & drop کنید</span>
    </div>

    <div class="admin-menu-sort" data-menu-sort data-sort-url="{{ route('admin.menus.items.sort', $menu) }}" data-csrf="{{ csrf_token() }}">
        @if ($items->isNotEmpty())
            <ol class="admin-menu-tree" data-menu-list>
                @foreach ($items as $item)
                    @include('admin.menus.items._tree-item', ['item' => $item, 'menu' => $menu])
                @endforeach
            </ol>
            <div class="admin-form-actions">
                <button class="admin-primary-btn" type="button" data-menu-save-sort>ذخیره ترتیب</button>
                <span class="text-muted" data-menu-sort-message></span>
            </div>
        @else
            <p class="text-muted mb-0">هنوز آیتمی برای این منو ثبت نشده است.</p>
        @endif
    </div>
</div>
@endsection
