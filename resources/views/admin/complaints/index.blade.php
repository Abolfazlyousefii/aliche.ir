@extends('admin.layouts.app')

@section('title', 'مدیریت شکایات')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">شکایات</p><h2>مدیریت شکایات ثبت‌شده</h2></div>
</div>

@include('admin.partials.list-filters', [
    'listRoute' => 'admin.complaints.index',
    'listTitle' => 'فیلتر شکایت‌ها',
    'listDescription' => 'جستجوی پرونده‌ها بر اساس کد رهگیری یا مشخصات شاکی',
    'listSearch' => $search,
    'listPlaceholder' => 'کد رهگیری، نام، تلفن یا موضوع...',
    'listPaginator' => $complaints,
    'listFilters' => [
            ['name' => 'status', 'label' => 'وضعیت پرونده', 'value' => $status, 'options' => $statusLabels, 'empty' => 'همه وضعیت‌ها'],
            ['name' => 'union_id', 'label' => 'اتحادیه', 'value' => $unionId, 'options' => $unions->mapWithKeys(fn ($union) => [(string) $union->id => $union->display_title])->all(), 'empty' => 'همه اتحادیه‌ها'],
    ],
])

<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر شکایت‌ها">
        <table class="table admin-table align-middle admin-list-table" data-admin-list-table>
            <thead><tr><th>کد رهگیری</th><th>شاکی</th><th>موبایل</th><th>موضوع</th><th>اتحادیه</th><th>وضعیت</th><th>تاریخ ثبت</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse ($complaints as $complaint)
                    <tr>
                        <td><strong dir="ltr">{{ $complaint->tracking_code }}</strong></td>
                        <td>{{ $complaint->full_name }}</td>
                        <td>{{ $complaint->mobile }}</td>
                        <td>{{ Str::limit($complaint->subject, 45) }}</td>
                        <td>{{ $complaint->union?->display_title ?: '—' }}</td>
                        <td><span class="admin-status-badge status-{{ $complaint->status }}">{{ $complaint->status_label }}</span></td>
                        <td>{{ jalali_datetime($complaint->created_at) ?: '—' }}</td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.complaints.show', $complaint) }}">مشاهده</a>
                                @if (request()->user()->hasPermission('complaints.edit'))
                                    <a href="{{ route('admin.complaints.edit', $complaint) }}">ویرایش</a>
                                @endif
                                @if (request()->user()->hasPermission('complaints.delete'))
                                    <form action="{{ route('admin.complaints.destroy', $complaint) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">شکایتی یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $complaints])
</div>
@endsection
