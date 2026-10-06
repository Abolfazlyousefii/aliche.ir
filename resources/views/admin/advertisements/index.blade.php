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

@include('admin.partials.list-filters', [
    'listRoute' => 'admin.advertisements.index',
    'listTitle' => 'فیلتر تبلیغات',
    'listDescription' => 'یافتن تبلیغ براساس عنوان، جایگاه و زمان نمایش',
    'listSearch' => $search,
    'listPlaceholder' => 'عنوان تبلیغ یا لینک...',
    'listPaginator' => $advertisements,
    'listFilters' => [
            ['name' => 'position_id', 'label' => 'جایگاه نمایش', 'value' => $positionId, 'options' => $positions->mapWithKeys(fn ($position) => [(string) $position->id => $position->title])->all(), 'empty' => 'همه جایگاه‌ها'],
            ['name' => 'status', 'label' => 'وضعیت تبلیغ', 'value' => $status, 'options' => ['displayable' => 'در حال نمایش', 'active' => 'فعال', 'inactive' => 'غیرفعال', 'scheduled' => 'زمان‌بندی‌شده', 'expired' => 'منقضی‌شده'], 'empty' => 'همه وضعیت‌ها'],
    ],
])

<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر تبلیغات">
        <table class="admin-table admin-list-table" data-admin-list-table>
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
