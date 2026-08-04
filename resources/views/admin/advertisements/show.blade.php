@extends('admin.layouts.app')

@section('title', 'جزئیات تبلیغ')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">تبلیغات</p><h2>{{ $advertisement->title }}</h2></div>
    <div class="admin-actions">
        <a class="admin-secondary-btn" href="{{ route('admin.advertisements.index') }}">بازگشت</a>
        @if (request()->user()->hasPermission('advertisements.edit'))<a class="admin-primary-btn" href="{{ route('admin.advertisements.edit', $advertisement) }}">ویرایش</a>@endif
    </div>
</div>

<div class="admin-panel-card">
    <div class="row g-4">
        <div class="col-md-5"><img src="{{ $advertisement->image_url }}" alt="{{ $advertisement->title }}" style="width:100%;max-height:320px;object-fit:contain;border-radius:18px;background:#f7f7f7"></div>
        <div class="col-md-7">
            <div class="row g-3">
                <div class="col-md-6"><strong>جایگاه:</strong><p>{{ $advertisement->position?->title ?: '—' }}</p></div>
                <div class="col-md-6"><strong>کلید جایگاه:</strong><p dir="ltr">{{ $advertisement->position?->key ?: '—' }}</p></div>
                <div class="col-md-6"><strong>وضعیت:</strong><p>{{ $advertisement->status_label }}</p></div>
                <div class="col-md-6"><strong>فعال:</strong><p>{{ $advertisement->is_active ? 'بله' : 'خیر' }}</p></div>
                <div class="col-md-6"><strong>شروع:</strong><p>{{ jalali_datetime($advertisement->starts_at) }}</p></div>
                <div class="col-md-6"><strong>پایان:</strong><p>{{ jalali_datetime($advertisement->expires_at) ?: 'نامحدود' }}</p></div>
                <div class="col-md-6"><strong>نمایش:</strong><p>{{ $advertisement->views_count }}</p></div>
                <div class="col-md-6"><strong>کلیک:</strong><p>{{ $advertisement->clicks_count }}</p></div>
                <div class="col-md-6"><strong>ترتیب:</strong><p>{{ $advertisement->sort_order }}</p></div>
                <div class="col-md-6"><strong>Target:</strong><p dir="ltr">{{ $advertisement->target }} ({{ $advertisement->target_label }})</p></div>
                <div class="col-12"><strong>لینک:</strong><p dir="ltr">@if ($advertisement->link)<a href="{{ $advertisement->link }}" target="_blank">{{ $advertisement->link }}</a>@else — @endif</p></div>
            </div>
        </div>
    </div>
</div>
@endsection
