@extends('admin.layouts.app')

@section('title', 'Migration تصویر اعضای هیئت‌مدیره')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">نگهداری سیستم</p>
        <h2>Migration تصویر اعضای هیئت‌مدیره</h2>
    </div>
    <a class="admin-secondary-btn" href="{{ route('admin.dashboard') }}">بازگشت به داشبورد</a>
</div>

<div class="admin-panel-card">
    <div class="d-flex flex-column gap-3">
        <div>
            <strong>جدول union_members:</strong>
            <span>{{ $tableExists ? 'موجود است' : 'پیدا نشد' }}</span>
        </div>

        <div>
            <strong>ستون image:</strong>
            @if($imageColumnExists)
                <span class="admin-status-badge status-active">ایجاد شده</span>
            @else
                <span class="admin-status-badge status-inactive">ایجاد نشده</span>
            @endif
        </div>

        <p class="mb-0 text-muted">
            این عملیات فقط migration مربوط به تصویر اعضای اتحادیه را اجرا می‌کند و اطلاعات فعلی اعضا را حذف یا بازنویسی نمی‌کند.
        </p>

        @if($tableExists && ! $imageColumnExists)
            <form action="{{ route('admin.maintenance.union-member-image.run') }}" method="POST" data-single-submit>
                @csrf
                <button class="admin-primary-btn" type="submit" data-loading-text="در حال اجرا...">
                    اجرای Migration تصویر اعضا
                </button>
            </form>
        @elseif($imageColumnExists)
            <div class="alert alert-success mb-0">این migration از نظر ساختار دیتابیس اعمال شده است.</div>
        @else
            <div class="alert alert-danger mb-0">به دلیل نبود جدول union_members، عملیات قابل اجرا نیست.</div>
        @endif
    </div>
</div>
@endsection
