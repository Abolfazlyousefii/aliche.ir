@extends('admin.layouts.app')
@section('title', 'مدیریت کمیسیون‌ها')
@section('content')
<div class="admin-page-toolbar"><div><p class="admin-eyebrow">کمیسیون‌ها</p><h2>مدیریت کمیسیون‌ها</h2></div>@if(request()->user()->hasPermission('commissions.create'))<a class="admin-primary-btn" href="{{ route('admin.commissions.create') }}">ایجاد کمیسیون</a>@endif</div>
@include('admin.partials.list-filters', [
    'listRoute' => 'admin.commissions.index',
    'listTitle' => 'فیلتر کمیسیون‌ها',
    'listDescription' => 'جستجوی کمیسیون‌ها و محدودکردن وضعیت انتشار',
    'listSearch' => $search,
    'listPlaceholder' => 'نام کمیسیون یا توضیحات...',
    'listPaginator' => $commissions,
    'listFilters' => [
            ['name' => 'status', 'label' => 'وضعیت', 'value' => $status, 'options' => $statusLabels, 'empty' => 'همه وضعیت‌ها'],
    ],
])

<div class="admin-panel-card"><div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر کمیسیون‌ها"><table class="admin-table admin-list-table" data-admin-list-table><thead><tr><th>تصویر</th><th>عنوان</th><th>وضعیت</th><th>فعال</th><th>جلسات</th><th>ترتیب</th><th>عملیات</th></tr></thead><tbody>@forelse($commissions as $commission)<tr><td>@if($commission->image)<img src="{{ $commission->image_url }}" style="width:72px;height:52px;object-fit:cover;border-radius:12px" alt="{{ $commission->title }}">@else — @endif</td><td><strong>{{ $commission->title }}</strong><br><small dir="ltr">{{ $commission->slug }}</small></td><td>{{ $commission->status_label }}</td><td>{{ $commission->is_active ? 'فعال' : 'غیرفعال' }}</td><td>{{ $commission->sessions_count }}</td><td>{{ $commission->sort_order }}</td><td><div class="admin-actions"><a class="admin-secondary-btn" href="{{ route('admin.commissions.show', $commission) }}">نمایش</a><a class="admin-secondary-btn" href="{{ route('admin.commissions.sessions.index', $commission) }}">جلسات</a>@if(request()->user()->hasPermission('commissions.edit'))<a class="admin-secondary-btn" href="{{ route('admin.commissions.edit', $commission) }}">ویرایش</a>@endif @if(request()->user()->hasPermission('commissions.delete'))<form method="POST" action="{{ route('admin.commissions.destroy', $commission) }}">@csrf @method('DELETE')<button class="admin-danger-btn">حذف</button></form>@endif</div></td></tr>@empty<tr><td colspan="7" class="text-center text-muted">کمیسیونی ثبت نشده است.</td></tr>@endforelse</tbody></table></div>@include('admin.partials.pagination', ['paginator' => $commissions])</div>
@endsection
