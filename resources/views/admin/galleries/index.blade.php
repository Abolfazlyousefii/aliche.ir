@extends('admin.layouts.app')

@section('title', 'مدیریت گالری تصاویر')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">گالری تصاویر</p><h2>مدیریت گالری تصاویر</h2></div>
    @if (request()->user()->hasPermission('galleries.create'))
        <a class="admin-primary-btn" href="{{ route('admin.galleries.create') }}">ایجاد گالری جدید</a>
    @endif
</div>

<div class="admin-panel-card mb-3">
    <form class="admin-search-form" action="{{ route('admin.galleries.index') }}" method="GET">
        <label class="form-label mb-0" for="search">جستجو</label>
        <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="عنوان، نامک یا توضیحات...">
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
        @if ($search !== '' || $status !== '' || $unionId)<a class="admin-secondary-btn" href="{{ route('admin.galleries.index') }}">حذف فیلتر</a>@endif
    </form>
</div>

<div class="admin-panel-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead><tr><th>جابجایی</th><th>کاور</th><th>عنوان</th><th>نوع</th><th>محل نمایش</th><th>تصاویر</th><th>وضعیت</th><th>انتشار</th><th>عملیات</th></tr></thead>
            <tbody id="gallery-sortable">
                @forelse ($galleries as $gallery)
                    <tr draggable="true" data-id="{{ $gallery->id }}">
                        <td class="text-muted" style="cursor:move">☰</td>
                        <td><img src="{{ $gallery->cover_image_url }}" alt="{{ $gallery->title }}" style="width:72px;height:52px;object-fit:cover;border-radius:10px"></td>
                        <td><strong>{{ $gallery->title }}</strong><br><small dir="ltr">{{ $gallery->slug }}</small></td>
                        <td>{{ $gallery->union?->display_title ?: 'عمومی' }}</td>
                        <td>{{ $gallery->display_location_label }}</td>
                        <td>{{ $gallery->images_count }}</td>
                        <td><span class="admin-status-badge status-{{ $gallery->status }}">{{ $gallery->status_label }}</span><br><small>{{ $gallery->is_active ? 'فعال' : 'غیرفعال' }}</small></td>
                        <td>{{ jalali_datetime($gallery->published_at) ?: '—' }}</td>
                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.galleries.show', $gallery) }}">مشاهده</a>
                                @if (request()->user()->hasPermission('galleries.edit'))<a href="{{ route('admin.galleries.edit', $gallery) }}">ویرایش</a>@endif
                                @if (request()->user()->hasPermission('galleries.delete'))
                                    <form action="{{ route('admin.galleries.destroy', $gallery) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">گالری‌ای یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $galleries])
</div>
@endsection


@push('scripts')
<script>
const sortable = document.getElementById('gallery-sortable');
let draggedRow = null;
sortable?.addEventListener('dragstart', (event) => { draggedRow = event.target.closest('tr[data-id]'); });
sortable?.addEventListener('dragover', (event) => {
    event.preventDefault();
    const row = event.target.closest('tr[data-id]');
    if (!row || row === draggedRow) return;
    const box = row.getBoundingClientRect();
    row.parentNode.insertBefore(draggedRow, event.clientY < box.top + box.height / 2 ? row : row.nextSibling);
});
sortable?.addEventListener('drop', () => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('admin.galleries.sort') }}';
    form.innerHTML = '@csrf';
    [...sortable.querySelectorAll('tr[data-id]')].forEach((row) => {
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'items[]'; input.value = row.dataset.id; form.appendChild(input);
    });
    document.body.appendChild(form); form.submit();
});
</script>
@endpush
