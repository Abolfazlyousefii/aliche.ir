<div class="admin-panel-card">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="title">عنوان</label>
            <input class="form-control" id="title" name="title" value="{{ old('title', $unionType?->title) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="slug">نامک</label>
            <input class="form-control" id="slug" name="slug" value="{{ old('slug', $unionType?->slug) }}" dir="ltr">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="icon">آیکون نوع اتحادیه</label>
            @php
                $selectedIcon = old('icon', $unionType?->resolved_icon);
            @endphp
            <select class="form-control" id="icon" name="icon">
                <option value="">انتخاب خودکار بر اساس نامک</option>
                @foreach(($iconOptions ?? \App\Models\UnionType::iconOptions()) as $iconKey => $iconLabel)
                    <option value="{{ $iconKey }}" @selected($selectedIcon === $iconKey)>{{ $iconLabel }}</option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1">آیکون انتخاب‌شده در تب‌های صفحه اصلی نمایش داده می‌شود.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="image">تصویر</label>
            <input class="form-control" id="image" name="image" type="file" accept="image/*">
            <div class="mt-2" data-image-preview="image">
                @if ($unionType?->image)
                    <img src="{{ $unionType->image_url }}" alt="تصویر فعلی" class="img-fluid rounded" style="max-height:120px;object-fit:cover">
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="sort_order">ترتیب نمایش</label>
            <input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $unionType?->sort_order ?? 0) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="is_active">وضعیت</label>
            <select class="form-control" id="is_active" name="is_active">
                <option value="1" @selected((string) old('is_active', (int) ($unionType?->is_active ?? true)) === '1')>فعال</option>
                <option value="0" @selected((string) old('is_active', (int) ($unionType?->is_active ?? true)) === '0')>غیرفعال</option>
            </select>
        </div>
    </div>
</div>

<div class="mt-3 d-flex gap-2">
    <button class="admin-primary-btn" type="submit">ذخیره نوع اتحادیه</button>
    <a class="admin-secondary-btn" href="{{ route('admin.union-types.index') }}">انصراف</a>
</div>

@push('scripts')
<script>
document.querySelectorAll('input[type="file"][accept^="image/"]').forEach((input) => {
    const preview = document.querySelector(`[data-image-preview="${input.id}"]`);
    if (!preview) return;

    input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file) return;

        const url = URL.createObjectURL(file);
        preview.innerHTML = `<img src="${url}" alt="پیش‌نمایش تصویر انتخاب‌شده" class="img-fluid rounded" style="max-height:120px;object-fit:cover">`;
        preview.querySelector('img')?.addEventListener('load', () => URL.revokeObjectURL(url), { once: true });
    });
});
</script>
@endpush
