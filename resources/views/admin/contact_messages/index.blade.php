@extends('admin.layouts.app')

@section('title', 'پیام‌های تماس')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">ارتباط با ما</p>
        <h2>مدیریت پیام‌های ارتباطی</h2>
    </div>
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="{{ route('admin.contact_messages.index') }}" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="نام، موبایل، ایمیل، موضوع یا متن پیام...">
        <select class="form-control" name="read_status" aria-label="فیلتر وضعیت خواندن">
            <option value="">همه پیام‌ها</option>
            <option value="unread" @selected($readStatus === 'unread')>خوانده‌نشده</option>
            <option value="read" @selected($readStatus === 'read')>خوانده‌شده</option>
        </select>
        <button class="admin-primary-btn" type="submit">اعمال فیلتر</button>
        <a class="admin-secondary-btn" href="{{ route('admin.contact_messages.index') }}">حذف فیلتر</a>
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>فرستنده</th>
                    <th>موضوع</th>
                    <th>تماس</th>
                    <th>وضعیت</th>
                    <th>تاریخ ثبت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    <tr>
                        <td><strong>{{ $message->full_name }}</strong></td>
                        <td>{{ Str::limit($message->subject, 55) }}</td>
                        <td><span dir="ltr">{{ $message->mobile }}</span><br><small dir="ltr">{{ $message->email ?: '—' }}</small></td>
                        <td>
                            @if($message->is_read)
                                <span class="badge text-bg-success">خوانده‌شده</span>
                            @else
                                <span class="badge text-bg-warning">خوانده‌نشده</span>
                            @endif
                        </td>
                        <td>{{ jalali_datetime($message->created_at) ?: '—' }}</td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.contact_messages.show', $message) }}">مشاهده</a>
                                @unless($message->is_read)
                                    <form action="{{ route('admin.contact_messages.mark_read', $message) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit">خوانده شد</button>
                                    </form>
                                @endunless
                                @if (request()->user()->hasPermission('contact_messages.delete'))
                                    <form action="{{ route('admin.contact_messages.destroy', $message) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">حذف</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">پیامی یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $messages])
</div>
@endsection
