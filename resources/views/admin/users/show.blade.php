@extends('admin.layouts.app')

@section('title', 'جزئیات کاربر')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">جزئیات کاربر</p>
        <h2>{{ $user->name }}</h2>
    </div>
    <div class="admin-actions">
        <a href="{{ route('admin.users.edit', $user) }}">ویرایش</a>
        <a href="{{ route('admin.users.index') }}">بازگشت</a>
    </div>
</div>

<div class="admin-panel-card">
    <dl class="admin-detail-list">
        <div><dt>نام</dt><dd>{{ $user->name }}</dd></div>
        <div><dt>ایمیل</dt><dd>{{ $user->email }}</dd></div>
        <div><dt>شماره تماس</dt><dd>{{ $user->mobile ?: '—' }}</dd></div>
        <div><dt>اتحادیه مربوطه</dt><dd>{{ $user->union?->name ?: '—' }}</dd></div>
        <div><dt>وضعیت</dt><dd>{{ $user->is_active ? 'فعال' : 'غیرفعال' }}</dd></div>
        <div><dt>تاریخ ایجاد</dt><dd>{{ jalali_datetime($user->created_at) }}</dd></div>
    </dl>

    <h3 class="admin-section-title">نقش‌های کاربر</h3>
    <div class="admin-badge-list admin-badge-list-lg">
        @forelse ($user->roles as $role)
            <span>{{ $role->label }} <code>{{ $role->name }}</code></span>
        @empty
            <em>نقشی برای این کاربر ثبت نشده است.</em>
        @endforelse
    </div>
</div>
@endsection
