@extends('admin.layouts.app')

@section('title', 'مدیریت پیام تبریک و تسلیت')

@section('content')
<div class="admin-page-toolbar"><div><p class="admin-eyebrow">پیام تبریک و تسلیت</p><h2>مدیریت پیام تبریک و تسلیت مدیران اصناف</h2></div>@if(request()->user()->hasPermission('congratulation_messages.create'))<a class="admin-primary-btn" href="{{ route('admin.congratulation_messages.create') }}">ایجاد پیام جدید</a>@endif</div>
@include('admin.partials.list-filters', [
    'listRoute' => 'admin.congratulation_messages.index',
    'listTitle' => 'فیلتر پیام‌های تبریک و تسلیت',
    'listDescription' => 'جستجوی عنوان، نام مدیر، اتحادیه و وضعیت پیام',
    'listSearch' => $search,
    'listPlaceholder' => 'عنوان پیام، نام مدیر یا متن...',
    'listPaginator' => $messages,
    'listFilters' => [
            ['name' => 'union_id', 'label' => 'اتحادیه', 'value' => $unionId, 'options' => $unions->mapWithKeys(fn ($union) => [(string) $union->id => $union->display_title])->all(), 'empty' => 'همه اتحادیه‌ها'],
            ['name' => 'status', 'label' => 'وضعیت', 'value' => $status, 'options' => $statusLabels, 'empty' => 'همه وضعیت‌ها'],
    ],
])

<div class="admin-panel-card"><div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر پیام‌های تبریک و تسلیت"><table class="admin-table admin-list-table" data-admin-list-table><thead><tr><th>عنوان</th><th>نوع</th><th>مدیر</th><th>اتحادیه</th><th>نمایش</th><th>وضعیت</th><th>ترتیب</th><th>عملیات</th></tr></thead><tbody>@forelse($messages as $message)<tr><td><strong>{{ $message->title }}</strong><br><small dir="ltr">{{ $message->slug }}</small></td><td>{{ $message->message_type_label }}</td><td>{{ $message->manager_name ?: '—' }}<br><small>{{ $message->manager_position ?: '—' }}</small></td><td>{{ $message->union?->display_title ?: 'عمومی' }}</td><td><small>خانه: {{ $message->show_on_home ? 'بله' : 'خیر' }} / اتحادیه: {{ $message->show_on_union_page ? 'بله' : 'خیر' }}</small></td><td>{{ $message->status_label }} / {{ $message->is_active ? 'فعال' : 'غیرفعال' }}</td><td>{{ $message->sort_order }}</td><td><div class="admin-actions"><a class="admin-secondary-btn" href="{{ route('admin.congratulation_messages.show', $message) }}">نمایش</a>@if(request()->user()->hasPermission('congratulation_messages.edit'))<a class="admin-secondary-btn" href="{{ route('admin.congratulation_messages.edit', $message) }}">ویرایش</a>@endif @if(request()->user()->hasPermission('congratulation_messages.delete'))<form action="{{ route('admin.congratulation_messages.destroy', $message) }}" method="POST">@csrf @method('DELETE')<button class="admin-danger-btn">حذف</button></form>@endif</div></td></tr>@empty<tr><td colspan="8" class="text-center text-muted">پیام تبریک و تسلیتی ثبت نشده است.</td></tr>@endforelse</tbody></table></div>@include('admin.partials.pagination', ['paginator' => $messages])</div>
@endsection
