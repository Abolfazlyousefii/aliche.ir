@extends('admin.layouts.app')

@section('title', 'جزئیات نقش')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">جزئیات نقش</p>
        <h2>{{ $role->label }}</h2>
    </div>
    <div class="admin-actions">
        <a href="{{ route('admin.roles.edit', $role) }}">ویرایش</a>
        <a href="{{ route('admin.roles.index') }}">بازگشت</a>
    </div>
</div>

<div class="admin-panel-card">
    <dl class="admin-detail-list">
        <div><dt>نام سیستمی</dt><dd><code>{{ $role->name }}</code></dd></div>
        <div><dt>توضیحات</dt><dd>{{ plain_text($role->description) ?: '—' }}</dd></div>
        <div><dt>تعداد کاربران</dt><dd>{{ $role->users_count }}</dd></div>
    </dl>

    <h3 class="admin-section-title">دسترسی‌های این نقش</h3>
    <div class="admin-permission-summary">
        @forelse ($role->permissions->groupBy('group') as $group => $items)
            <div class="admin-permission-group-card">
                <strong>{{ $group }}</strong>
                <ul>
                    @foreach ($items as $permission)
                        <li>{{ $permission->label }} <code>{{ $permission->name }}</code></li>
                    @endforeach
                </ul>
            </div>
        @empty
            <p class="text-muted mb-0">هیچ دسترسی برای این نقش انتخاب نشده است.</p>
        @endforelse
    </div>
</div>
@endsection
