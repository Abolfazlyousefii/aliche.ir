@extends('admin.layouts.app')

@section('title', 'مدیریت اعضای اتحادیه‌ها')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">اعضای اتحادیه‌ها</p><h2>مدیریت اعضای اتحادیه‌ها</h2></div>
    @if(request()->user()->hasPermission('union_members.create'))
        <a class="admin-primary-btn" href="{{ route('admin.union_members.create') }}">ایجاد عضو جدید</a>
    @endif
</div>

@include('admin.partials.list-filters', [
    'listRoute' => 'admin.union_members.index',
    'listTitle' => 'فیلتر اعضای اتحادیه',
    'listDescription' => 'جستجوی عضو، کسب‌وکار، کد ملی و عضویت',
    'listSearch' => $search,
    'listPlaceholder' => 'نام، موبایل، کد عضویت یا کسب‌وکار...',
    'listPaginator' => $members,
    'listFilters' => [
            ['name' => 'status', 'label' => 'وضعیت عضو', 'value' => $status, 'options' => ['active' => 'فعال', 'inactive' => 'غیرفعال', 'suspended' => 'تعلیق', 'expired' => 'منقضی'], 'empty' => 'همه وضعیت‌ها'],
            ['name' => 'union_id', 'label' => 'اتحادیه', 'value' => $unionId, 'options' => $unions->mapWithKeys(fn ($union) => [(string) $union->id => $union->display_title])->all(), 'empty' => 'همه اتحادیه‌ها'],
    ],
])

<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر اعضای اتحادیه">
        <table class="table admin-table align-middle admin-list-table" data-admin-list-table>
            <thead><tr><th>نام</th><th>کد ملی</th><th>موبایل</th><th>نام کسب‌وکار</th><th>اتحادیه</th><th>وضعیت</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse ($members as $member)
                    <tr>
                        <td>
                            <strong>{{ $member->full_name }}</strong>
                            @if($member->isSeedPlaceholderProfile()) <span class="badge bg-warning text-dark">داده نمونه</span> @endif
                            <br>
                            <small>{{ $member->position ?: 'بدون سمت' }} · {{ $member->image ? 'دارای تصویر' : 'بدون تصویر' }} · {{ $member->membership_code ?: 'بدون کد عضویت' }}</small>
                        </td>
                        <td>{{ $member->national_code ?: '—' }}</td>
                        <td>{{ $member->mobile ?: '—' }}</td>
                        <td>{{ $member->business_name ?: '—' }}</td>
                        <td>{{ $member->union?->display_title ?: '—' }}</td>
                        <td><span class="admin-status-badge status-{{ $member->status }}">{{ $member->status }}</span></td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.union_members.show', $member) }}">مشاهده</a>
                                @if(request()->user()->hasPermission('union_members.edit'))<a href="{{ route('admin.union_members.edit', $member) }}">ویرایش</a>@endif
                                @if(request()->user()->hasPermission('union_members.delete'))<form action="{{ route('admin.union_members.destroy', $member) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>@endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">عضوی یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $members])
</div>
@endsection
