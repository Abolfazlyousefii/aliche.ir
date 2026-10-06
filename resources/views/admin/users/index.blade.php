@extends('admin.layouts.app')

@section('title', 'مدیریت کاربران')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">مدیریت کاربران</p>
        <h2>کاربران پنل مدیریت</h2>
    </div>
    @if(request()->user()->hasPermission('users.create'))
        <a class="admin-primary-btn" href="{{ route('admin.users.create') }}">ایجاد کاربر جدید</a>
    @endif
</div>

@include('admin.partials.list-filters', [
    'listRoute' => 'admin.users.index',
    'listTitle' => 'فیلتر کاربران پنل',
    'listDescription' => 'جستجو و مدیریت دسترسی حساب‌های مدیریتی',
    'listSearch' => $search,
    'listPlaceholder' => 'نام، ایمیل یا شماره تماس...',
    'listPaginator' => $users,
    'listFilters' => [
            ['name' => 'status', 'label' => 'وضعیت حساب', 'value' => $status, 'options' => ['active' => 'فعال', 'inactive' => 'غیرفعال'], 'empty' => 'همه وضعیت‌ها'],
    ],
])

<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر کاربران پنل">
        <table class="table admin-table align-middle admin-list-table" data-admin-list-table>
            <thead>
                <tr>
                    <th>نام</th>
                    <th>ایمیل</th>
                    <th>شماره تماس</th>
                    <th>نقش‌ها</th>
                    <th>اتحادیه مربوطه</th>
                    <th>وضعیت</th>
                    <th>تاریخ ایجاد</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->mobile ?: '—' }}</td>
                        <td>
                            <div class="admin-badge-list">
                                @forelse ($user->roles as $role)
                                    <span>{{ $role->label }}</span>
                                @empty
                                    <em>بدون نقش</em>
                                @endforelse
                            </div>
                        </td>
                        <td>{{ $user->union?->name ?: '—' }}</td>
                        <td>
                            <span class="admin-status-badge {{ $user->is_active ? 'is-active' : 'is-inactive' }}">
                                {{ $user->is_active ? 'فعال' : 'غیرفعال' }}
                            </span>
                        </td>
                        <td>{{ jalali_date($user->created_at) }}</td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.users.show', $user) }}">مشاهده</a>
                                @if(request()->user()->hasPermission('users.edit'))<a href="{{ route('admin.users.edit', $user) }}">ویرایش</a>@endif
                                @if(request()->user()->hasPermission('users.delete'))<form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">حذف</button>
                                </form>@endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">کاربری با این مشخصات پیدا نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.partials.pagination', ['paginator' => $users])
</div>
@endsection
