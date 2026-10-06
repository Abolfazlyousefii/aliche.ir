@extends('admin.layouts.app')

@section('title', 'مدیریت گردشگری')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">گردشگری</p><h2>مدیریت مکان‌های گردشگری</h2></div>
    @if (request()->user()->hasPermission('tourism.create'))
        <a class="admin-primary-btn" href="{{ route('admin.tourism.create') }}">ایجاد مکان جدید</a>
    @endif
</div>

@include('admin.partials.list-filters', [
    'listRoute' => 'admin.tourism.index',
    'listTitle' => 'فیلتر مکان‌های گردشگری',
    'listDescription' => 'جستجوی نام مکان و محدود کردن نتایج بر اساس نوع و وضعیت',
    'listSearch' => $search,
    'listPlaceholder' => 'نام مکان، آدرس یا توضیحات...',
    'listPaginator' => $places,
    'listFilters' => [
            ['name' => 'status', 'label' => 'وضعیت انتشار', 'value' => $status, 'options' => $statusLabels, 'empty' => 'همه وضعیت‌ها'],
            ['name' => 'category_id', 'label' => 'دسته‌بندی', 'value' => $categoryId, 'options' => $categories->mapWithKeys(fn ($category) => [(string) $category->id => $category->title])->all(), 'empty' => 'همه دسته‌ها'],
            ['name' => 'tourism_type', 'label' => 'نوع گردشگری', 'value' => $tourismType, 'options' => $typeLabels, 'empty' => 'همه انواع'],
    ],
])

<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر مکان‌های گردشگری">
        <table class="admin-table admin-list-table" data-admin-list-table>
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
