@extends('admin.layouts.app')

@section('title', 'جزئیات مکان گردشگری')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">گردشگری</p><h2>{{ $place->title }}</h2></div>
    <div class="admin-actions">
        <a class="admin-secondary-btn" href="{{ route('admin.tourism.index') }}">بازگشت</a>
        @if (request()->user()->hasPermission('tourism.edit'))<a class="admin-primary-btn" href="{{ route('admin.tourism.edit', $place) }}">ویرایش</a>@endif
    </div>
</div>

<div class="admin-panel-card">
    <div class="row g-4">
        <div class="col-md-4"><img src="{{ $place->featured_image ? Storage::url($place->featured_image) : asset('assets/img/asnaf-gorgan-default.jpg') }}" alt="{{ $place->title }}" style="width:100%;height:260px;object-fit:cover;border-radius:18px"></div>
        <div class="col-md-8">
            <div class="row g-3">
                <div class="col-md-6"><strong>نامک:</strong><p dir="ltr">{{ $place->slug }}</p></div>
                <div class="col-md-6"><strong>دسته‌بندی:</strong><p>{{ $place->category?->title ?: '—' }}</p></div>
                <div class="col-md-6"><strong>وضعیت:</strong><p>{{ $place->status_label }}</p></div>
                <div class="col-md-6"><strong>فعال:</strong><p>{{ $place->is_active ? 'بله' : 'خیر' }}</p></div>
                <div class="col-md-6"><strong>تاریخ انتشار:</strong><p>{{ jalali_datetime($place->published_at) ?: '—' }}</p></div>
                <div class="col-md-6"><strong>ترتیب نمایش:</strong><p>{{ $place->sort_order }}</p></div>
                <div class="col-md-6"><strong>ایجادکننده:</strong><p>{{ $place->creator?->name ?: '—' }}</p></div>
                <div class="col-md-6"><strong>تأییدکننده:</strong><p>{{ $place->approver?->name ?: '—' }}</p></div>
            </div>
        </div>
    </div>
</div>

<div class="admin-panel-card mt-3">
    <h3>اطلاعات بازدید</h3>
    <div class="row g-3">
        <div class="col-md-6"><strong>آدرس:</strong><p>{{ $place->address ?: '—' }}</p></div>
        <div class="col-md-6"><strong>لینک نقشه:</strong><p dir="ltr">@if ($place->map_url)<a href="{{ $place->map_url }}" target="_blank">{{ $place->map_url }}</a>@else — @endif</p></div>
        <div class="col-md-6"><strong>مختصات:</strong><p dir="ltr">{{ $place->latitude ?: '—' }} / {{ $place->longitude ?: '—' }}</p></div>
        <div class="col-md-6"><strong>تلفن:</strong><p>{{ $place->phone ?: '—' }}</p></div>
        <div class="col-md-6"><strong>ساعت بازدید:</strong><p>{{ $place->working_hours ?: '—' }}</p></div>
        <div class="col-md-6"><strong>هزینه بازدید:</strong><p>{{ $place->visit_price ?: '—' }}</p></div>
    </div>
</div>

<div class="admin-panel-card mt-3">
    <h3>توضیحات</h3>
    <p><strong>توضیح کوتاه:</strong> {{ plain_text($place->short_description) ?: '—' }}</p>
    <div>{!! rich_text($place->description, '—') !!}</div>
    @if ($place->rejected_reason)<hr><p><strong>دلیل رد:</strong> {{ $place->rejected_reason }}</p>@endif
</div>

@if (! empty($place->gallery))
    <div class="admin-panel-card mt-3">
        <h3>گالری تصاویر</h3>
        <div class="row g-3">
            @foreach (collect($place->gallery)->sortBy('sort_order') as $image)
                <div class="col-md-3"><img src="{{ Storage::url($image['path'] ?? '') }}" alt="{{ $image['caption'] ?? $place->title }}" style="width:100%;height:150px;object-fit:cover;border-radius:12px"><small>{{ $image['caption'] ?? '' }}</small></div>
            @endforeach
        </div>
    </div>
@endif
@endsection
