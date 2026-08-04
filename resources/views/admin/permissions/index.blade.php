@extends('admin.layouts.app')

@section('title', 'دسترسی‌ها')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">مدیریت سطح دسترسی</p>
        <h2>دسترسی‌های سامانه</h2>
    </div>
    <a class="admin-primary-btn" href="{{ route('admin.permissions.create') }}">ایجاد دسترسی جدید</a>
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="{{ route('admin.permissions.index') }}" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="عنوان، نام دسترسی یا گروه...">
        <button class="admin-primary-btn" type="submit">جستجو</button>
        @if ($search !== '')
            <a class="admin-secondary-btn" href="{{ route('admin.permissions.index') }}">حذف فیلتر</a>
        @endif
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
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
                                <a href="{{ route('admin.permissions.edit', $permission) }}">ویرایش</a>
                                <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">حذف</button>
                                </form>
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
