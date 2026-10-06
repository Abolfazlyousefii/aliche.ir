@extends('admin.layouts.app')

@section('title', 'مدیریت سامانه‌ها')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">سامانه‌ها</p><h2>مدیریت سامانه‌ها</h2></div>
    @if (request()->user()->hasPermission('systems.create'))
        <a class="admin-primary-btn" href="{{ route('admin.systems.create') }}">ایجاد سامانه جدید</a>
    @endif
</div>

@include('admin.partials.list-filters', [
    'listRoute' => 'admin.systems.index',
    'listTitle' => 'فیلتر سامانه‌ها',
    'listDescription' => 'جستجوی سامانه براساس عنوان، لینک و وضعیت آن',
    'listSearch' => $search,
    'listPlaceholder' => 'عنوان سامانه، لینک یا توضیحات...',
    'listPaginator' => $systems,
    'listFilters' => [
            ['name' => 'category_id', 'label' => 'دسته‌بندی', 'value' => $categoryId, 'options' => $categories->mapWithKeys(fn ($category) => [(string) $category->id => $category->title])->all(), 'empty' => 'همه دسته‌بندی‌ها'],
            ['name' => 'status', 'label' => 'وضعیت', 'value' => $status, 'options' => $statusLabels, 'empty' => 'همه وضعیت‌ها'],
    ],
])

<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر سامانه‌ها">
        <table class="admin-table admin-list-table" data-admin-list-table>
            <thead><tr><th>تصویر/آیکن</th><th>عنوان</th><th>دسته‌بندی</th><th>لینک</th><th>وضعیت</th><th>ترتیب</th><th>عملیات</th></tr></thead>
            <tbody>
            @forelse ($systems as $system)
                <tr>
                    <td>@if ($system->image)<img src="{{ $system->image_url }}" alt="{{ $system->title }}" style="width:72px;height:52px;object-fit:cover;border-radius:12px">@else <span style="font-size:2rem">{{ $system->icon ?: '💻' }}</span> @endif</td>
                    <td><strong>{{ $system->title }}</strong><br><small dir="ltr">{{ $system->slug }}</small></td>
                    <td>{{ $system->category?->title ?: '—' }}</td>
                    <td><small dir="ltr">{{ $system->link ? Str::limit($system->link, 45) : '—' }}</small></td>
                    <td>{{ $system->status_label }} / {{ $system->is_active ? 'فعال' : 'غیرفعال' }}</td>
                    <td>{{ $system->sort_order }}</td>
                    <td>
                        <div class="admin-actions">
                            <a class="admin-secondary-btn" href="{{ route('admin.systems.show', $system) }}">نمایش</a>
                            @if (request()->user()->hasPermission('systems.edit'))<a class="admin-secondary-btn" href="{{ route('admin.systems.edit', $system) }}">ویرایش</a>@endif
                            @if (request()->user()->hasPermission('systems.delete'))
                                <form action="{{ route('admin.systems.destroy', $system) }}" method="POST">@csrf @method('DELETE')<button class="admin-danger-btn" type="submit">حذف</button></form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">سامانه‌ای ثبت نشده است.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $systems])
</div>
@endsection
