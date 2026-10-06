@extends('admin.layouts.app')

@section('title', 'مدیریت ویدیوها')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">ویدیوها</p><h2>مدیریت ویدیوها</h2></div>
    @if (request()->user()->hasPermission('videos.create'))
        <a class="admin-primary-btn" href="{{ route('admin.videos.create') }}">ایجاد ویدیو جدید</a>
    @endif
</div>

@include('admin.partials.list-filters', [
    'listRoute' => 'admin.videos.index',
    'listTitle' => 'فیلتر ویدئوها',
    'listDescription' => 'انتخاب ویدئو براساس نوع، اتحادیه و وضعیت انتشار',
    'listSearch' => $search,
    'listPlaceholder' => 'عنوان یا توضیحات ویدئو...',
    'listPaginator' => $videos,
    'listFilters' => [
            ['name' => 'video_type', 'label' => 'نوع ویدئو', 'value' => $videoType, 'options' => $typeLabels, 'empty' => 'همه نوع‌ها'],
            ['name' => 'status', 'label' => 'وضعیت', 'value' => $status, 'options' => $statusLabels, 'empty' => 'همه وضعیت‌ها'],
            ['name' => 'union_id', 'label' => 'اتحادیه', 'value' => $unionId, 'options' => $unions->mapWithKeys(fn ($union) => [(string) $union->id => $union->display_title])->all(), 'empty' => 'عمومی و همه اتحادیه‌ها'],
    ],
])

<div class="admin-panel-card">
    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="فیلتر ویدئوها">
        <table class="table admin-table align-middle admin-list-table" data-admin-list-table>
            <thead><tr><th>کاور</th><th>عنوان</th><th>نوع ویدیو</th><th>اتحادیه</th><th>وضعیت</th><th>انتشار</th><th>ترتیب</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse ($videos as $video)
                    <tr>
                        <td><img src="{{ image_url($video->cover_image) }}" alt="{{ $video->title }}" style="width:72px;height:52px;object-fit:cover;border-radius:10px"></td>
                        <td><strong>{{ $video->title }}</strong><br><small dir="ltr">{{ $video->slug }}</small></td>
                        <td>{{ $video->type_label }}</td>
                        <td>{{ $video->union?->display_title ?: 'عمومی' }}</td>
                        <td><span class="admin-status-badge status-{{ $video->status }}">{{ $video->status_label }}</span><br><small>{{ $video->is_active ? 'فعال' : 'غیرفعال' }}</small></td>
                        <td>{{ jalali_datetime($video->published_at) ?: '—' }}</td>
                        <td>{{ $video->sort_order }}</td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.videos.show', $video) }}">مشاهده</a>
                                @if (request()->user()->hasPermission('videos.edit'))<a href="{{ route('admin.videos.edit', $video) }}">ویرایش</a>@endif
                                @if (request()->user()->hasPermission('videos.delete'))
                                    <form action="{{ route('admin.videos.destroy', $video) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">ویدیویی یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $videos])
</div>
@endsection
