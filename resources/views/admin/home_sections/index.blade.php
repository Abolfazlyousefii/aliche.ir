@extends('admin.layouts.app')

@section('title', 'مدیریت سکشن‌های صفحه اصلی')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">صفحه اصلی</p><h2>مدیریت سکشن‌های صفحه اصلی</h2></div>
</div>

<form action="{{ route('admin.home_sections.sort') }}" method="POST" class="admin-panel-card">
    @csrf
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
        <p class="text-muted mb-0">برای تغییر ترتیب، ردیف‌ها را بکشید و سپس ذخیره کنید.</p>
        @if (request()->user()->hasPermission('home_sections.edit'))
            <button class="admin-primary-btn" type="submit">ذخیره ترتیب</button>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead><tr><th>جابجایی</th><th>کلید</th><th>عنوان</th><th>توضیح کوتاه</th><th>وضعیت</th><th>ترتیب</th><th>عملیات</th></tr></thead>
            <tbody id="homeSectionsSortable">
                @foreach ($sections as $section)
                    <tr draggable="true" data-section-row>
                        <td class="text-muted" style="cursor:grab">☰<input type="hidden" name="sections[]" value="{{ $section->id }}"></td>
                        <td dir="ltr"><code>{{ $section->key }}</code><br><small>{{ $section->key_label }}</small></td>
                        <td><strong>{{ $section->title }}</strong></td>
                        <td>{{ $section->subtitle ?: '—' }}</td>
                        <td><span class="admin-status-badge status-{{ $section->is_active ? 'active' : 'inactive' }}">{{ $section->is_active ? 'فعال' : 'غیرفعال' }}</span></td>
                        <td data-sort-order>{{ $section->sort_order }}</td>
                        <td>
                            @if (request()->user()->hasPermission('home_sections.edit'))
                                <a href="{{ route('admin.home_sections.edit', $section) }}">ویرایش</a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const tbody = document.getElementById('homeSectionsSortable');
    if (!tbody) return;

    let draggedRow = null;
    const updateSortNumbers = () => {
        tbody.querySelectorAll('[data-section-row]').forEach((row, index) => {
            const sortCell = row.querySelector('[data-sort-order]');
            if (sortCell) {
                sortCell.textContent = String((index + 1) * 10);
            }
        });
    };

    tbody.addEventListener('dragstart', event => {
        draggedRow = event.target.closest('[data-section-row]');
        if (draggedRow) draggedRow.classList.add('opacity-50');
    });
    tbody.addEventListener('dragend', () => {
        if (draggedRow) draggedRow.classList.remove('opacity-50');
        draggedRow = null;
    });
    tbody.addEventListener('dragover', event => {
        event.preventDefault();
        const target = event.target.closest('[data-section-row]');
        if (!draggedRow || !target || draggedRow === target) return;
        const rect = target.getBoundingClientRect();
        const shouldInsertAfter = event.clientY > rect.top + rect.height / 2;
        tbody.insertBefore(draggedRow, shouldInsertAfter ? target.nextSibling : target);
        updateSortNumbers();
    });
    tbody.closest('form')?.addEventListener('submit', updateSortNumbers);
})();
</script>
@endpush
