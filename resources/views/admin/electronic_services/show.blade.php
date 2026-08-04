@extends('admin.layouts.app')

@section('title', 'جزئیات خدمت الکترونیک')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">خدمات الکترونیک</p><h2>{{ $service->title }}</h2></div>
    <div class="admin-actions"><a class="admin-secondary-btn" href="{{ route('admin.electronic_services.index') }}">بازگشت</a>@if (request()->user()->hasPermission('electronic_services.edit'))<a class="admin-primary-btn" href="{{ route('admin.electronic_services.edit', $service) }}">ویرایش</a>@endif</div>
</div>

<div class="admin-panel-card">
    <div class="row g-4">
        <div class="col-md-4">
            @if ($service->image)<img src="{{ Storage::url($service->image) }}" alt="{{ $service->title }}" style="width:100%;height:260px;object-fit:cover;border-radius:18px">@else<div style="height:260px;display:flex;align-items:center;justify-content:center;border-radius:18px;background:#f7f7f7;font-size:5rem">{{ $service->icon ?: '⚡' }}</div>@endif
        </div>
        <div class="col-md-8">
            <div class="row g-3">
                <div class="col-md-6"><strong>نامک:</strong><p dir="ltr">{{ $service->slug }}</p></div>
                <div class="col-md-6"><strong>دسته‌بندی:</strong><p>{{ $service->category?->title ?: '—' }}</p></div>
                <div class="col-md-6"><strong>نوع لینک:</strong><p>{{ $service->link_type_label }}</p></div>
                <div class="col-md-6"><strong>وضعیت:</strong><p>{{ $service->status_label }}</p></div>
                <div class="col-md-6"><strong>انتشار:</strong><p>{{ jalali_datetime($service->published_at) ?: '—' }}</p></div>
                <div class="col-md-6"><strong>فعال:</strong><p>{{ $service->is_active ? 'بله' : 'خیر' }}</p></div>
                <div class="col-md-6"><strong>Target:</strong><p dir="ltr">{{ $service->target }} ({{ $service->target_label }})</p></div>
                <div class="col-md-6"><strong>ترتیب:</strong><p>{{ $service->sort_order }}</p></div>
                <div class="col-12"><strong>لینک:</strong><p dir="ltr">@if ($service->link)<a href="{{ $service->link }}" target="{{ $service->target }}">{{ $service->link }}</a>@else — @endif</p></div>
                <div class="col-12"><strong>دلیل رد:</strong><p>{{ $service->rejected_reason ?: '—' }}</p></div>
            </div>
        </div>
    </div>
</div>

<div class="admin-panel-card mt-3">
    <h3>توضیحات</h3>
    <p><strong>توضیح کوتاه:</strong> {{ plain_text($service->short_description) ?: '—' }}</p>
    <div class="admin-rich-content">{!! rich_text($service->body, '—') !!}</div>
</div>
@endsection
