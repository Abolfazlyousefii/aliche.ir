@extends('admin.layouts.app')

@section('title', 'جزئیات اتحادیه')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">جزئیات اتحادیه</p><h2>{{ $union->display_title }}</h2></div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-secondary-btn" href="{{ route('admin.unions.index') }}">بازگشت</a>
        <a class="admin-primary-btn" href="{{ route('admin.unions.edit', $union) }}">ویرایش</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-panel-card">
            @if ($union->cover_image)
                <img class="img-fluid rounded mb-3" src="{{ route('media.public', ['path' => $union->cover_image]) }}" alt="{{ $union->display_title }}">
            @endif
            <div class="d-flex align-items-center gap-3 mb-3">
                @if ($union->logo)<img src="{{ route('media.public', ['path' => $union->logo]) }}" alt="{{ $union->display_title }}" style="width:72px;height:72px;object-fit:contain">@endif
                <div><h3 class="h5 mb-1">{{ $union->display_title }}</h3><p class="text-muted mb-0">{{ plain_text($union->short_description) }}</p></div>
            </div>
            <div class="admin-rich-content">{!! $union->description ?: '—' !!}</div>
        </div>

        <div class="admin-panel-card mt-3">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <h3 class="h6 mb-0">بخش‌های قابل ویرایش صفحه اتحادیه</h3>
                <a class="admin-secondary-btn" href="{{ route('admin.unions.edit', $union) }}">ویرایش بخش‌ها</a>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><strong>کمیسیون‌ها</strong><p class="text-muted mb-1">{{ $union->commissions->count() }} مورد</p><ul class="mb-0">@foreach($union->commissions->take(3) as $commission)<li>{{ $commission->title }} <small>({{ $commission->tasks->count() }} وظیفه)</small></li>@endforeach</ul></div>
                <div class="col-md-6"><strong>صورتجلسه‌ها</strong><p class="text-muted mb-1">{{ $union->minutes->count() }} مورد</p><ul class="mb-0">@foreach($union->minutes->take(3) as $minute)<li>{{ $minute->title }}</li>@endforeach</ul></div>
                <div class="col-md-6"><strong>قوانین و دستورالعمل‌ها</strong><p class="text-muted mb-1">{{ $union->rules->count() }} مورد</p><ul class="mb-0">@foreach($union->rules->take(3) as $rule)<li>{{ $rule->title }}</li>@endforeach</ul></div>
                <div class="col-md-6"><strong>آموزش‌ها و نرخ‌نامه</strong><p class="text-muted mb-1">{{ $union->educations->count() }} آموزش / {{ $union->prices->count() }} نرخ</p><ul class="mb-0">@foreach($union->educations->take(2) as $education)<li>{{ $education->title }}</li>@endforeach @foreach($union->prices->take(1) as $price)<li>{{ $price->title }}</li>@endforeach</ul></div>
            </div>
        </div>
        <div class="admin-panel-card mt-3">
            <h3 class="h6">امکانات فعال</h3>
            <div class="d-flex flex-wrap gap-2">
                @foreach ([
                    'news_enabled' => 'اخبار', 'announcements_enabled' => 'اطلاعیه‌ها', 'gallery_enabled' => 'گالری',
                    'videos_enabled' => 'ویدیوها', 'members_enabled' => 'اعضا', 'services_enabled' => 'خدمات',
                    'complaint_enabled' => 'فرم شکایت', 'congratulations_enabled' => 'پیام تبریک مدیر'
                ] as $field => $label)
                    <span class="admin-status-badge {{ $union->{$field} ? 'is-active' : 'is-inactive' }}">{{ $label }}: {{ $union->{$field} ? 'فعال' : 'غیرفعال' }}</span>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-panel-card">
            <dl class="row mb-0">
                <dt class="col-5">وضعیت</dt><dd class="col-7">{{ $union->is_active ? 'فعال' : 'غیرفعال' }}</dd>
                <dt class="col-5">نوع اتحادیه</dt><dd class="col-7">{{ $union->union_type_label }}</dd>
                <dt class="col-5">حالت خبر</dt><dd class="col-7">{{ \App\Models\GuildUnion::newsModeLabels()[$union->news_mode ?? 'auto'] ?? 'خودکار' }}</dd>
                <dt class="col-5">مدیر</dt><dd class="col-7">{{ $union->manager_name ?: '—' }}</dd>
                <dt class="col-5">تلفن</dt><dd class="col-7">{{ $union->phone ?: '—' }}</dd>
                <dt class="col-5">موبایل</dt><dd class="col-7">{{ $union->mobile ?: '—' }}</dd>
                <dt class="col-5">ایمیل</dt><dd class="col-7">{{ $union->email ?: '—' }}</dd>
                <dt class="col-5">وب‌سایت</dt><dd class="col-7">@if ($union->website)<a href="{{ $union->website }}" target="_blank">مشاهده</a>@else — @endif</dd>
                <dt class="col-5">ساعات کاری</dt><dd class="col-7">{{ $union->working_hours ?: '—' }}</dd>
                <dt class="col-5">ترتیب</dt><dd class="col-7">{{ $union->sort_order }}</dd>
                <dt class="col-5">اخبار</dt><dd class="col-7">{{ $union->posts_count }}</dd>
                <dt class="col-5">اطلاعیه‌ها</dt><dd class="col-7">{{ $union->announcements_count }}</dd>
                <dt class="col-5">کاربران</dt><dd class="col-7">{{ $union->users_count }}</dd>
            </dl>
        </div>
        @if ($union->manager_image)
            <div class="admin-panel-card mt-3"><img class="img-fluid rounded" src="{{ route('media.public', ['path' => $union->manager_image]) }}" alt="{{ $union->manager_name }}"></div>
        @endif
    </div>
</div>
@endsection
