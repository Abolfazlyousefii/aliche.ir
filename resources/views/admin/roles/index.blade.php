@extends('admin.layouts.app')

@section('title', 'نقش‌ها و دسترسی‌ها')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">مدیریت نقش‌ها</p>
        <h2>نقش‌های سامانه</h2>
    </div>
    <div class="admin-actions">
        @if(request()->user()->hasPermission('permissions.view'))<a href="{{ route('admin.permissions.index') }}">مدیریت دسترسی‌ها</a>@endif
        @if(request()->user()->hasPermission('roles.create'))<a class="admin-primary-btn" href="{{ route('admin.roles.create') }}">ایجاد نقش جدید</a>@endif
    </div>
</div>

@include('admin.partials.list-filters', [
    'listRoute' => 'admin.roles.index',
    'listTitle' => 'فیلتر نقش‌های کاربری',
    'listDescription' => 'جستجوی عنوان، نام سیستمی و توضیحات نقش',
    'listSearch' => $search,
    'listPlaceholder' => 'عنوان یا نام نقش...',
    'listPaginator' => $roles,
    'listFilters' => [

    ],
])

<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر نقش‌های کاربری">
        <table class="table admin-table align-middle admin-list-table" data-admin-list-table>
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>نام سیستمی</th>
                    <th>تعداد دسترسی‌ها</th>
                    <th>تعداد کاربران</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr>
                        <td><strong>{{ $role->label }}</strong></td>
                        <td><code>{{ $role->name }}</code></td>
                        <td>{{ $role->permissions_count }}</td>
                        <td>{{ $role->users_count }}</td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.roles.show', $role) }}">مشاهده</a>
                                @if(request()->user()->hasPermission('roles.edit'))<a href="{{ route('admin.roles.edit', $role) }}">ویرایش</a>@endif
                                @if(request()->user()->hasPermission('roles.delete'))<form action="{{ route('admin.roles.destroy', $role) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">حذف</button>
                                </form>@endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">هنوز نقشی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.partials.pagination', ['paginator' => $roles])
</div>
@endsection
