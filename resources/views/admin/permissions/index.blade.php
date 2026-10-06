@extends('admin.layouts.app')

@section('title', 'دسترسی‌ها')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">مدیریت سطح دسترسی</p>
        <h2>دسترسی‌های سامانه</h2>
    </div>
    @if(request()->user()->hasPermission('permissions.create'))<a class="admin-primary-btn" href="{{ route('admin.permissions.create') }}">ایجاد دسترسی جدید</a>@endif
</div>

@include('admin.partials.list-filters', [
    'listRoute' => 'admin.permissions.index',
    'listTitle' => 'فیلتر سطح دسترسی',
    'listDescription' => 'پیداکردن مجوزها بر اساس عنوان، گروه و نام سیستمی',
    'listSearch' => $search,
    'listPlaceholder' => 'عنوان یا نام سطح دسترسی...',
    'listPaginator' => $permissions,
    'listFilters' => [

    ],
])

<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر سطح دسترسی">
        <table class="table admin-table align-middle admin-list-table" data-admin-list-table>
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>نام دسترسی</th>
                    <th>گروه</th>
                    <th>نقش‌های متصل</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($permissions as $permission)
                    <tr>
                        <td><strong>{{ $permission->label }}</strong></td>
                        <td><code>{{ $permission->name }}</code></td>
                        <td>{{ $permission->group }}</td>
                        <td>{{ $permission->roles_count }}</td>
                        <td>
                            <div class="admin-actions">
                                @if(request()->user()->hasPermission('permissions.edit'))<a href="{{ route('admin.permissions.edit', $permission) }}">ویرایش</a>@endif
                                @if(request()->user()->hasPermission('permissions.delete'))<form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">حذف</button>
                                </form>@endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">هنوز دسترسی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.partials.pagination', ['paginator' => $permissions])
</div>
@endsection
