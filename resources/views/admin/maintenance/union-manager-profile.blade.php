@extends('admin.layouts.app')

@section('title', 'Migration اطلاعات رئیس اتحادیه')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">نگهداری سیستم</p>
        <h2>Migration اطلاعات رئیس اتحادیه</h2>
    </div>
    <a class="admin-secondary-btn" href="{{ route('admin.dashboard') }}">بازگشت به داشبورد</a>
</div>

<div class="admin-panel-card">
    <div class="d-flex flex-column gap-3">
        <div>
            <strong>جدول unions:</strong>
            <span>{{ $tableExists ? 'موجود است' : 'پیدا نشد' }}</span>
        </div>

        <div>
            <strong>ستون manager_position:</strong>
            @if($managerPositionExists)
                <span class="admin-status-badge status-active">ایجاد شده</span>
            @else
                <span class="admin-status-badge status-inactive">ایجاد نشده</span>
            @endif
        </div>

        <div>
            <strong>ستون manager_description:</strong>
            @if($managerDescriptionExists)
                <span class="admin-status-badge status-active">ایجاد شده</span>
            @else
                <span class="admin-status-badge status-inactive">ایجاد نشده</span>
            @endif
        </div>

        <p class="mb-0 text-muted">
            این عملیات فقط migration مربوط به سمت و معرفی رئیس اتحادیه را اجرا می‌کند و اطلاعات فعلی اتحادیه‌ها را حذف یا بازنویسی نمی‌کند.
        </p>

        @if($tableExists && (! $managerPositionExists || ! $managerDescriptionExists))
            <form action="{{ route('admin.maintenance.union-manager-profile.run') }}" method="POST" data-single-submit>
                @csrf
                <button class="admin-primary-btn" type="submit" data-loading-text="در حال اجرا...">
                    اجرای Migration اطلاعات رئیس اتحادیه
                </button>
            </form>
        @elseif($managerPositionExists && $managerDescriptionExists)
            <div class="alert alert-success mb-0">هر دو ستون موردنیاز در دیتابیس موجود هستند.</div>
        @else
            <div class="alert alert-danger mb-0">به دلیل نبود جدول unions، عملیات قابل اجرا نیست.</div>
        @endif
    </div>
</div>
@endsection
