@extends('admin.layouts.app')

@section('title', 'مدیریت منوها')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">مدیریت منوها</p>
        <h2>منوهای سایت</h2>
    </div>
    <a class="admin-primary-btn" href="{{ route('admin.menus.create') }}">ایجاد منو جدید</a>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>محل نمایش</th>
                    <th>تعداد آیتم‌ها</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($menus as $menu)
                    <tr>
                        <td><strong>{{ $menu->title }}</strong></td>
                        <td><code>{{ $menu->location }}</code></td>
                        <td>{{ $menu->items_count }}</td>
                        <td><span class="admin-status-badge {{ $menu->is_active ? 'is-active' : 'is-inactive' }}">{{ $menu->is_active ? 'فعال' : 'غیرفعال' }}</span></td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.menus.show', $menu) }}">مدیریت آیتم‌ها</a>
                                <a href="{{ route('admin.menus.edit', $menu) }}">ویرایش</a>
                                <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">هنوز منویی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.partials.pagination', ['paginator' => $menus])
</div>
@endsection
