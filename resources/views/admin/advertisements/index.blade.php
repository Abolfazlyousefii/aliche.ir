@extends('admin.layouts.app')

@section('title', 'مدیریت تبلیغات')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">تبلیغات</p><h2>مدیریت تبلیغات</h2></div>
    <div class="admin-actions">
        <a class="admin-secondary-btn" href="{{ route('admin.advertisement_positions.index') }}">جایگاه‌ها</a>
        @if (request()->user()->hasPermission('advertisements.create'))
            <a class="admin-primary-btn" href="{{ route('admin.advertisements.create') }}">ایجاد تبلیغ جدید</a>
        @endif
    </div>
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="{{ route('admin.advertisements.index') }}" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="عنوان یا لینک...">
        <select class="form-control" name="position_id" aria-label="فیلتر جایگاه">
            <option value="">همه جایگاه‌ها</option>
            @foreach ($positions as $position)
                <option value="{{ $position->id }}" @selected((string) $positionId === (string) $position->id)>{{ $position->title }}</option>
            @endforeach
        </select>
        <select class="form-control" name="status" aria-label="فیلتر وضعیت">
            <option value="">همه وضعیت‌ها</option>
            <option value="displayable" @selected($status === 'displayable')>در حال نمایش</option>
            <option value="active" @selected($status === 'active')>فعال</option>
            <option value="inactive" @selected($status === 'inactive')>غیرفعال</option>
            <option value="scheduled" @selected($status === 'scheduled')>زمان‌بندی شده</option>
            <option value="expired" @selected($status === 'expired')>منقضی شده</option>
        </select>
        <button class="admin-primary-btn" type="submit">اعمال فیلتر</button>
        <a class="admin-secondary-btn" href="{{ route('admin.advertisements.index') }}">حذف فیلتر</a>
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>تصویر</th><th>عنوان</th><th>جایگاه</th><th>وضعیت</th><th>بازه نمایش</th><th>آمار</th><th>ترتیب</th><th>عملیات</th></tr></thead>
            <tbody>
            @forelse ($advertisements as $advertisement)
                <tr>
                    <td><img src="{{ $advertisement->image_url }}" alt="{{ $advertisement->title }}" style="width:96px;height:54px;object-fit:cover;border-radius:12px"></td>
                    <td><strong>{{ $advertisement->title }}</strong><br><small dir="ltr">{{ ($advertisement->link ? Str::limit($advertisement->link, 45) : 'بدون لینک') }}</small></td>
                    <td>{{ $advertisement->position?->title ?: '—' }}<br><small dir="ltr">{{ $advertisement->position?->key }}</small></td>
                    <td><span class="admin-badge">{{ $advertisement->status_label }}</span></td>
                    <td><small>شروع: {{ jalali_datetime($advertisement->starts_at) }}</small><br><small>پایان: {{ jalali_datetime($advertisement->expires_at) ?: 'نامحدود' }}</small></td>
                    <td><small>نمایش: {{ $advertisement->views_count }}</small><br><small>کلیک: {{ $advertisement->clicks_count }}</small></td>
                    <td>{{ $advertisement->sort_order }}</td>
                    <td>
                        <div class="admin-actions">
                            <a class="admin-secondary-btn" href="{{ route('admin.advertisements.show', $advertisement) }}">نمایش</a>
                            @if (request()->user()->hasPermission('advertisements.edit'))<a class="admin-secondary-btn" href="{{ route('admin.advertisements.edit', $advertisement) }}">ویرایش</a>@endif
                            @if (request()->user()->hasPermission('advertisements.delete'))
                                <form action="{{ route('admin.advertisements.destroy', $advertisement) }}" method="POST">@csrf @method('DELETE')<button class="admin-danger-btn" type="submit">حذف</button></form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">تبلیغی ثبت نشده است.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $advertisements])
</div>
@endsection
