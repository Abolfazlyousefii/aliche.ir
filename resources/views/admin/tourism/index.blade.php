@extends('admin.layouts.app')

@section('title', 'مدیریت گردشگری')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">گردشگری</p><h2>مدیریت مکان‌های گردشگری</h2></div>
    @if (request()->user()->hasPermission('tourism.create'))
        <a class="admin-primary-btn" href="{{ route('admin.tourism.create') }}">ایجاد مکان جدید</a>
    @endif
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="{{ route('admin.tourism.index') }}" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="عنوان، نامک، توضیح یا آدرس...">
        <select class="form-control" name="status" aria-label="فیلتر وضعیت">
            <option value="">همه وضعیت‌ها</option>
            @foreach ($statusLabels as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select class="form-control" name="category_id" aria-label="فیلتر دسته‌بندی">
            <option value="">همه دسته‌بندی‌ها</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->title }}</option>
            @endforeach
        </select>
        <button class="admin-primary-btn" type="submit">اعمال فیلتر</button>
        <a class="admin-secondary-btn" href="{{ route('admin.tourism.index') }}">حذف فیلتر</a>
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>تصویر</th><th>عنوان</th><th>دسته‌بندی</th><th>وضعیت</th><th>فعال</th><th>انتشار</th><th>ترتیب</th><th>عملیات</th></tr></thead>
            <tbody>
            @forelse ($places as $place)
                <tr>
                    <td><img src="{{ $place->featured_image ? Storage::url($place->featured_image) : asset('assets/img/asnaf-gorgan-default.jpg') }}" alt="{{ $place->title }}" style="width:72px;height:52px;object-fit:cover;border-radius:12px"></td>
                    <td><strong>{{ $place->title }}</strong><br><small dir="ltr">{{ $place->slug }}</small></td>
                    <td>{{ $place->category?->title ?: '—' }}</td>
                    <td><span class="admin-badge">{{ $place->status_label }}</span></td>
                    <td>{{ $place->is_active ? 'فعال' : 'غیرفعال' }}</td>
                    <td>{{ jalali_datetime($place->published_at) ?: '—' }}</td>
                    <td>{{ $place->sort_order }}</td>
                    <td>
                        <div class="admin-actions">
                            <a class="admin-secondary-btn" href="{{ route('admin.tourism.show', $place) }}">نمایش</a>
                            @if (request()->user()->hasPermission('tourism.edit'))<a class="admin-secondary-btn" href="{{ route('admin.tourism.edit', $place) }}">ویرایش</a>@endif
                            @if (request()->user()->hasPermission('tourism.delete'))
                                <form action="{{ route('admin.tourism.destroy', $place) }}" method="POST">@csrf @method('DELETE')<button class="admin-danger-btn" type="submit">حذف</button></form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">مکان گردشگری ثبت نشده است.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $places])
</div>
@endsection
