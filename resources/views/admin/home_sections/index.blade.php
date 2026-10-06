@extends('admin.layouts.app')

@section('title', 'مدیریت سکشن‌های صفحه اصلی')

@section('content')
@php
    $canSortHome = request()->user()->hasPermission('home_sections.edit');
@endphp
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">صفحه اصلی</p><h2>مدیریت سکشن‌های صفحه اصلی</h2></div>
</div>

<form action="{{ route('admin.home_sections.sort') }}" method="POST" class="admin-panel-card" data-home-sorting-enabled="{{ $canSortHome ? 'true' : 'false' }}">
    @csrf
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
        <p class="text-muted mb-0">
            @if($canSortHome)
                ترتیب ردیف‌ها را با کشیدن یا دکمه‌های «بالاتر» و «پایین‌تر» تغییر دهید و سپس ذخیره کنید.
            @else
                ترتیب فعلی سکشن‌ها نمایش داده می‌شود. دسترسی ویرایش برای این حساب فعال نیست.
            @endif
        </p>
        @if (request()->user()->hasPermission('home_sections.edit'))
            <button class="admin-primary-btn" type="submit">ذخیره ترتیب</button>
        @endif
    </div>

    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="ترتیب بخش‌های صفحه اصلی">
        <table class="table admin-table align-middle admin-list-table" data-admin-list-table data-admin-list-sortable="true">
            <thead><tr><th>جابجایی</th><th>کلید</th><th>عنوان</th><th>توضیح کوتاه</th><th>وضعیت</th><th>ترتیب</th><th>عملیات</th></tr></thead>
            <tbody id="homeSectionsSortable">
                @foreach ($sections as $section)
                    <tr data-section-row @if($canSortHome) draggable="true" @endif>
                        <td class="text-muted">
                            @if($canSortHome)
                                <div class="admin-home-sort-controls">
                                    <span aria-hidden="true" title="کشیدن ردیف">☰</span>
                                    <button type="button" data-home-move="up" aria-label="انتقال {{ $section->title }} به بالاتر" title="بالاتر">↑</button>
                                    <button type="button" data-home-move="down" aria-label="انتقال {{ $section->title }} به پایین‌تر" title="پایین‌تر">↓</button>
                                    <input type="hidden" name="sections[]" value="{{ $section->id }}">
                                </div>
                            @else
                                <span aria-label="ترتیب فعلی">—</span>
                            @endif
                        </td>
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
    @if($canSortHome)
        <p class="visually-hidden" id="adminHomeSortFeedback" role="status" aria-live="polite"></p>
    @endif
</form>
@endsection

@push('scripts')
<script>
(() => {
    const tbody = document.getElementById('homeSectionsSortable');
    if (!tbody || tbody.closest('form')?.dataset.homeSortingEnabled !== 'true') return;

    const feedback = document.getElementById('adminHomeSortFeedback');
    const updateButtons = () => {
        const rows = Array.from(tbody.querySelectorAll('[data-section-row]'));
        rows.forEach((row, index) => {
            const up = row.querySelector('[data-home-move="up"]');
            const down = row.querySelector('[data-home-move="down"]');
            if (up) up.disabled = index === 0;
            if (down) down.disabled = index === rows.length - 1;
        });
    };

    let draggedRow = null;
    const updateSortNumbers = () => {
        tbody.querySelectorAll('[data-section-row]').forEach((row, index) => {
            const sortCell = row.querySelector('[data-sort-order]');
            if (sortCell) {
                sortCell.textContent = String((index + 1) * 10);
            }
        });
        updateButtons();
    };

    tbody.addEventListener('click', event => {
        const button = event.target.closest('[data-home-move]');
        if (!button || button.disabled) return;

        const row = button.closest('[data-section-row]');
        if (!row) return;
        const direction = button.dataset.homeMove;
        const neighbor = direction === 'up' ? row.previousElementSibling : row.nextElementSibling;
        if (!neighbor || !neighbor.matches('[data-section-row]')) return;

        if (direction === 'up') tbody.insertBefore(row, neighbor);
        else tbody.insertBefore(neighbor, row);

        updateSortNumbers();
        button.focus();
        if (feedback) {
            const title = row.querySelector('td:nth-child(3)')?.textContent?.trim() || 'سکشن';
            feedback.textContent = 'موقعیت «' + title + '» تغییر کرد. برای ثبت نهایی ذخیره ترتیب را بزنید.';
        }
    });

    updateButtons();

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
