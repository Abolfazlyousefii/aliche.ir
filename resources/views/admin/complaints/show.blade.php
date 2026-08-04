@extends('admin.layouts.app')

@section('title', 'جزئیات شکایت')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">جزئیات شکایت</p><h2>{{ $complaint->subject }}</h2></div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-secondary-btn" href="{{ route('admin.complaints.index') }}">بازگشت</a>
        @if (request()->user()->hasPermission('complaints.edit'))
            <a class="admin-primary-btn" href="{{ route('admin.complaints.edit', $complaint) }}">ویرایش وضعیت</a>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-panel-card">
            <h3 class="h5 mb-3">متن شکایت</h3>
            <p class="mb-0">{{ $complaint->body }}</p>
        </div>

        <div class="admin-panel-card mt-3">
            <h3 class="h5 mb-3">پاسخ ثبت‌شده</h3>
            @if ($complaint->admin_response)
                <p>{{ $complaint->admin_response }}</p>
                <small class="text-muted">ثبت‌کننده پاسخ: {{ $complaint->answerer?->name ?: '—' }} | {{ jalali_datetime($complaint->answered_at) ?: '—' }}</small>
            @else
                <p class="text-muted mb-0">هنوز پاسخی ثبت نشده است.</p>
            @endif
        </div>

        @if (request()->user()->hasPermission('complaints.reply'))
            <form class="admin-panel-card admin-form mt-3" action="{{ route('admin.complaints.reply', $complaint) }}" method="POST">
                @csrf
                @method('PATCH')
                <h3 class="h5 mb-3">ثبت پاسخ</h3>
                <div class="mb-3">
                    <label class="form-label" for="admin_response">پاسخ به شاکی</label>
                    <textarea class="form-control" id="admin_response" name="admin_response" rows="5" required>{{ old('admin_response', $complaint->admin_response) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="reply_status">وضعیت پس از پاسخ</label>
                    <select class="form-control" id="reply_status" name="status" required>
                        @foreach ($statusLabels as $itemStatus => $label)
                            <option value="{{ $itemStatus }}" @selected(old('status', 'answered') === $itemStatus)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="admin-primary-btn" type="submit">ثبت پاسخ</button>
            </form>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="admin-panel-card">
            <dl class="row mb-0">
                <dt class="col-5">کد رهگیری</dt><dd class="col-7" dir="ltr">{{ $complaint->tracking_code }}</dd>
                <dt class="col-5">وضعیت</dt><dd class="col-7"><span class="admin-status-badge status-{{ $complaint->status }}">{{ $complaint->status_label }}</span></dd>
                <dt class="col-5">اتحادیه</dt><dd class="col-7">{{ $complaint->union?->display_title ?: '—' }}</dd>
                <dt class="col-5">نام</dt><dd class="col-7">{{ $complaint->full_name }}</dd>
                <dt class="col-5">کد ملی</dt><dd class="col-7">{{ $complaint->national_code ?: '—' }}</dd>
                <dt class="col-5">موبایل</dt><dd class="col-7">{{ $complaint->mobile }}</dd>
                <dt class="col-5">ثبت</dt><dd class="col-7">{{ jalali_datetime($complaint->created_at) ?: '—' }}</dd>
                <dt class="col-5">آخرین تغییر</dt><dd class="col-7">{{ jalali_datetime($complaint->updated_at) ?: '—' }}</dd>
            </dl>
        </div>

        <div class="admin-panel-card mt-3">
            <h3 class="h5 mb-3">پیوست</h3>
            @if ($complaint->attachment)
                <a class="admin-secondary-btn" href="{{ route('admin.complaints.download', $complaint) }}">دانلود فایل پیوست</a>
            @else
                <p class="text-muted mb-0">پیوستی ثبت نشده است.</p>
            @endif
        </div>

        <div class="admin-panel-card mt-3">
            <h3 class="h5 mb-3">یادداشت داخلی</h3>
            <p class="mb-0">{{ $complaint->internal_note ?: '—' }}</p>
        </div>
    </div>
</div>
@endsection
