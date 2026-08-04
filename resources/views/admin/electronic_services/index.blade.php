@extends('admin.layouts.app')

@section('title', 'مدیریت خدمات الکترونیک')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">خدمات الکترونیک</p><h2>مدیریت خدمات الکترونیک صنفی</h2></div>
    @if (request()->user()->hasPermission('electronic_services.create'))
        <a class="admin-primary-btn" href="{{ route('admin.electronic_services.create') }}">ایجاد خدمت جدید</a>
    @endif
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="{{ route('admin.electronic_services.index') }}" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="عنوان، نامک، توضیح یا لینک...">
        <select class="form-control" name="category_id" aria-label="فیلتر دسته‌بندی">
            <option value="">همه دسته‌بندی‌ها</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->title }}</option>
            @endforeach
        </select>
        <select class="form-control" name="status" aria-label="فیلتر وضعیت">
            <option value="">همه وضعیت‌ها</option>
            @foreach ($statusLabels as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="admin-primary-btn" type="submit">اعمال فیلتر</button>
        <a class="admin-secondary-btn" href="{{ route('admin.electronic_services.index') }}">حذف فیلتر</a>
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>تصویر/آیکن</th><th>عنوان</th><th>دسته‌بندی</th><th>لینک</th><th>وضعیت</th><th>ترتیب</th><th>عملیات</th></tr></thead>
            <tbody>
            @forelse ($services as $service)
                <tr>
                    <td>@if ($service->image)<img src="{{ Storage::url($service->image) }}" alt="{{ $service->title }}" style="width:72px;height:52px;object-fit:cover;border-radius:12px">@else <span style="font-size:2rem">{{ $service->icon ?: '⚡' }}</span> @endif</td>
                    <td><strong>{{ $service->title }}</strong><br><small dir="ltr">{{ $service->slug }}</small></td>
                    <td>{{ $service->category?->title ?: '—' }}</td>
                    <td><small dir="ltr">{{ $service->link ? Str::limit($service->link, 45) : $service->link_type_label }}</small></td>
                    <td>{{ $service->status_label }} / {{ $service->is_active ? 'فعال' : 'غیرفعال' }}</td>
                    <td>{{ $service->sort_order }}</td>
                    <td><div class="admin-actions"><a class="admin-secondary-btn" href="{{ route('admin.electronic_services.show', $service) }}">نمایش</a>@if (request()->user()->hasPermission('electronic_services.edit'))<a class="admin-secondary-btn" href="{{ route('admin.electronic_services.edit', $service) }}">ویرایش</a>@endif @if (request()->user()->hasPermission('electronic_services.delete'))<form action="{{ route('admin.electronic_services.destroy', $service) }}" method="POST">@csrf @method('DELETE')<button class="admin-danger-btn" type="submit">حذف</button></form>@endif</div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">خدمت الکترونیکی ثبت نشده است.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $services])
</div>
@endsection
