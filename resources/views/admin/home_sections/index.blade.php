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
                ترتیب را جابه‌جا کنید و با کلید هر ردیف، نمایش آن سکشن را فعال یا غیرفعال کنید. هر دو تغییر با دکمه ذخیره ثبت می‌شوند.
            @else
                ترتیب فعلی سکشن‌ها نمایش داده می‌شود. دسترسی ویرایش برای این حساب فعال نیست.
            @endif
        </p>
        @if (request()->user()->hasPermission('home_sections.edit'))
            <button class="admin-primary-btn" type="submit">ذخیره ترتیب و وضعیت</button>
        @endif
    </div>

    <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="ترتیب بخش‌های صفحه اصلی">
        <table class="table admin-table align-middle admin-list-table" data-admin-list-table data-admin-list-sortable="true">
            <thead><tr><th>جابجایی</th><th>کلید</th><th>عنوان</th><th>توضیح کوتاه</th><th>وضعیت</th><th>ترتیب</th><th>عملیات</th></tr></thead>
            <tbody id="homeSectionsSortable">
                @foreach ($sections as $section)
                    <tr data-section-row class="{{ $section->is_active ? '' : 'home-section-disabled' }}" @if($canSortHome) draggable="true" @endif>
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
                        <td>
                            <div class="admin-home-visibility">
                                @if($canSortHome)
                                    <input type="hidden" name="statuses[{{ $section->id }}]" value="0">
                                    <label class="admin-home-switch" aria-label="نمایش {{ $section->title }} در صفحه اصلی">
                                        <input type="checkbox" role="switch" data-home-visible name="statuses[{{ $section->id }}]" value="1" @checked($section->is_active) aria-label="نمایش {{ $section->title }} در صفحه اصلی">
                                        <span class="admin-home-switch-track" aria-hidden="true"></span>
                                    </label>
                                @endif
                                <span data-home-status class="admin-status-badge status-{{ $section->is_active ? 'active' : 'inactive' }}">{{ $section->is_active ? 'فعال' : 'غیرفعال' }}</span>
                            </div>
                        </td>
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
        <p class="text-muted small mt-2 mb-0">غیرفعال‌سازی فقط سکشن را از صفحه اصلی مخفی می‌کند و اطلاعات یا ترتیب آن حذف نمی‌شود.</p>
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

    tbody.addEventListener('change', event => {
        const toggle = event.target.closest('[data-home-visible]');
        if (!toggle) return;
        const row = toggle.closest('[data-section-row]');
        const active = toggle.checked;
        const badge = row?.querySelector('[data-home-status]');
        if (badge) {
            badge.textContent = active ? 'فعال' : 'غیرفعال';
            badge.classList.toggle('status-active', active);
            badge.classList.toggle('status-inactive', !active);
        }
        row?.classList.toggle('home-section-disabled', !active);
        if (feedback) feedback.textContent = 'وضعیت سکشن تغییر کرد. برای اعمال در صفحه اصلی، «ذخیره ترتیب و وضعیت» را بزنید.';
    });

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
            feedback.textContent = 'موقعیت «' + title + '» تغییر کرد. برای ثبت نهایی «ذخیره ترتیب و وضعیت» را بزنید.';
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

@push('styles')
<style>
/* Section-order visibility controls; confined to this admin screen. */
.admin-home-visibility { display: inline-flex; align-items: center; gap: 9px; white-space: nowrap; }
.admin-home-switch { display: inline-flex; position: relative; align-items: center; cursor: pointer; margin: 0; }
.admin-home-switch input { position: absolute; width: 100%; height: 100%; opacity: 0; cursor: pointer; margin: 0; }
.admin-home-switch-track { display: block; position: relative; width: 39px; height: 23px; border-radius: 999px; background: #cbd5e1; transition: background .15s ease; pointer-events: none; }
.admin-home-switch-track::after { content: ""; position: absolute; top: 3px; right: 3px; width: 17px; height: 17px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.13); transition: transform .15s ease; }
.admin-home-switch input:checked + .admin-home-switch-track { background: #167a9e; }
.admin-home-switch input:checked + .admin-home-switch-track::after { transform: translateX(-16px); }
.admin-home-switch input:focus-visible + .admin-home-switch-track { outline: 3px solid #70b7d5; outline-offset: 3px; }
#homeSectionsSortable tr.home-section-disabled td { background-color: #f8fafc; }
#homeSectionsSortable tr.home-section-disabled td strong { color: #64748b; }
@media (prefers-reduced-motion: reduce) { .admin-home-switch-track, .admin-home-switch-track::after { transition: none; } }
</style>
@endpush
