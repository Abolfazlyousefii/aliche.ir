@extends('admin.layouts.app')

@section('title', 'مدیریت ویدیوها')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">ویدیوها</p><h2>مدیریت ویدیوها</h2></div>
    @if (request()->user()->hasPermission('videos.create'))
        <a class="admin-primary-btn" href="{{ route('admin.videos.create') }}">ایجاد ویدیو جدید</a>
    @endif
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="{{ route('admin.videos.index') }}" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="عنوان، نامک یا توضیحات...">
        <select class="form-control" name="video_type" aria-label="فیلتر نوع ویدیو">
            <option value="">همه نوع‌ها</option>
            @foreach ($typeLabels as $type => $label)
                <option value="{{ $type }}" @selected($videoType === $type)>{{ $label }}</option>
            @endforeach
        </select>
        <select class="form-control" name="status" aria-label="فیلتر وضعیت">
            <option value="">همه وضعیت‌ها</option>
            @foreach ($statusLabels as $itemStatus => $label)
                <option value="{{ $itemStatus }}" @selected($status === $itemStatus)>{{ $label }}</option>
            @endforeach
        </select>
        <select class="form-control" name="union_id" aria-label="فیلتر اتحادیه">
            <option value="">عمومی و همه اتحادیه‌ها</option>
            @foreach ($unions as $union)
                <option value="{{ $union->id }}" @selected((string) $unionId === (string) $union->id)>{{ $union->display_title }}</option>
            @endforeach
        </select>
        <button class="admin-primary-btn" type="submit">اعمال</button>
        @if ($search !== '' || $status !== '' || $videoType !== '' || $unionId)<a class="admin-secondary-btn" href="{{ route('admin.videos.index') }}">حذف فیلتر</a>@endif
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
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
