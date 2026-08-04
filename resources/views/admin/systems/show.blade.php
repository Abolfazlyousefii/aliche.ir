@extends('admin.layouts.app')

@section('title', 'جزئیات سامانه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">سامانه‌ها</p><h2>{{ $system->title }}</h2></div>
    <div class="admin-actions">
        <a class="admin-secondary-btn" href="{{ route('admin.systems.index') }}">بازگشت</a>
        @if (request()->user()->hasPermission('systems.edit'))<a class="admin-primary-btn" href="{{ route('admin.systems.edit', $system) }}">ویرایش</a>@endif
    </div>
</div>

<div class="admin-panel-card">
    <div class="row g-4">
        <div class="col-md-4">
            @if ($system->image)
                <img src="{{ $system->image_url }}" alt="{{ $system->title }}" style="width:100%;height:260px;object-fit:cover;border-radius:18px">
            @else
                <div style="height:260px;display:flex;align-items:center;justify-content:center;border-radius:18px;background:#f7f7f7;font-size:5rem">{{ $system->icon ?: '💻' }}</div>
            @endif
        </div>
        <div class="col-md-8">
            <div class="row g-3">
                <div class="col-md-6"><strong>نامک:</strong><p dir="ltr">{{ $system->slug }}</p></div>
                <div class="col-md-6"><strong>دسته‌بندی:</strong><p>{{ $system->category?->title ?: '—' }}</p></div>
                <div class="col-md-6"><strong>آیکن:</strong><p>{{ $system->icon ?: '—' }}</p></div>
                <div class="col-md-6"><strong>وضعیت:</strong><p>{{ $system->status_label }}</p></div>
                <div class="col-md-6"><strong>انتشار:</strong><p>{{ jalali_datetime($system->published_at) ?: '—' }}</p></div>
                <div class="col-md-6"><strong>فعال:</strong><p>{{ $system->is_active ? 'بله' : 'خیر' }}</p></div>
                <div class="col-md-6"><strong>Target:</strong><p dir="ltr">{{ $system->target }} ({{ $system->target_label }})</p></div>
                <div class="col-md-6"><strong>ترتیب:</strong><p>{{ $system->sort_order }}</p></div>
                <div class="col-12"><strong>لینک:</strong><p dir="ltr">@if ($system->link)<a href="{{ $system->link }}" target="_blank">{{ $system->link }}</a>@else — @endif</p></div>
            </div>
        </div>
    </div>
</div>

<div class="admin-panel-card mt-3">
    <h3>توضیحات</h3>
    <p><strong>توضیح کوتاه:</strong> {{ plain_text($system->short_description) ?: '—' }}</p>
    <div>{!! rich_text($system->description, '—') !!}</div>
</div>
@endsection
