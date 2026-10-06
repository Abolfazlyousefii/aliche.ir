@extends('admin.layouts.app')
@section('title', 'جلسات کمیسیون')
@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">کمیسیون‌ها</p><h2>جلسات {{ $commission->title }}</h2></div>
    <div class="admin-actions">
        <a class="admin-secondary-btn" href="{{ route('admin.commissions.show', $commission) }}">بازگشت به کمیسیون</a>
        @if(request()->user()->hasPermission('commissions.create'))
            <a class="admin-primary-btn" href="{{ route('admin.commissions.sessions.create', $commission) }}">ایجاد جلسه</a>
        @endif
    </div>
</div>
@include('admin.partials.list-filters', [
    'listRoute' => 'admin.commissions.sessions.index',
    'listRouteParams' => [$commission],
    'listTitle' => 'جستجوی جلسات کمیسیون',
    'listSearch' => $search,
    'listPlaceholder' => 'عنوان جلسه...',
    'listPaginator' => $sessions,
    'listFilters' => [
        ['name' => 'status', 'label' => 'وضعیت جلسه', 'value' => $status, 'options' => \App\Models\CommissionSession::statusLabels()],
    ],
])
<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="جلسات کمیسیون">
        <table class="admin-table admin-list-table" data-admin-list-table>
            <thead><tr><th>عنوان</th><th>تاریخ جلسه</th><th>وضعیت</th><th>انتشار</th><th>فعال</th><th>ترتیب</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td><strong>{{ $session->title }}</strong></td>
                        <td>{{ jalali_datetime($session->session_date) ?: '—' }}</td>
                        <td><span class="admin-status-badge status-{{ $session->status }}">{{ $session->status_label }}</span></td>
                        <td>{{ jalali_datetime($session->published_at) ?: '—' }}</td>
                        <td>{{ $session->is_active ? 'فعال' : 'غیرفعال' }}</td>
                        <td>{{ fa_number($session->sort_order) }}</td>
                        <td><div class="admin-actions">
                            <a href="{{ route('admin.commissions.sessions.show', [$commission, $session]) }}">نمایش</a>
                            @if(request()->user()->hasPermission('commissions.edit'))
                                <a href="{{ route('admin.commissions.sessions.edit', [$commission, $session]) }}">ویرایش</a>
                            @endif
                            @if(request()->user()->hasPermission('commissions.delete'))
                                <form method="POST" action="{{ route('admin.commissions.sessions.destroy', [$commission, $session]) }}">@csrf @method('DELETE')<button type="submit">حذف</button></form>
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">جلسه‌ای مطابق جستجو پیدا نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $sessions])
</div>
@endsection
