@php
    $savedLinkMode = $item?->route_name ? 'internal' : ($item?->type === 'custom' || ! $item ? 'manual' : 'content');
    $linkMode = old('link_mode', $savedLinkMode);
@endphp

<div class="row g-3" data-menu-item-form>
    <div class="col-md-6">
        <label class="form-label" for="title">عنوان</label>
        <input class="form-control" id="title" name="title" value="{{ old('title', $item?->title) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="target">باز شدن لینک</label>
        <select class="form-control" id="target" name="target">
            <option value="_self" @selected(old('target', $item?->target ?? '_self') === '_self')>همین پنجره</option>
            <option value="_blank" @selected(old('target', $item?->target) === '_blank')>تب جدید</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="link_mode">روش لینک‌دهی</label>
        <select class="form-control" id="link_mode" name="link_mode" required data-link-mode>
            <option value="manual" @selected($linkMode === 'manual')>وارد کردن لینک</option>
            <option value="internal" @selected($linkMode === 'internal')>انتخاب صفحه داخلی</option>
            <option value="content" @selected($linkMode === 'content')>اتصال به محتوا (پیشرفته)</option>
        </select>
    </div>

    <div class="col-12" data-link-fields="manual">
        <label class="form-label" for="url">آدرس لینک</label>
        <input class="form-control" dir="ltr" id="url" name="url" value="{{ old('url', $item?->url) }}" placeholder="https://example.com یا /pages/about">
        <small class="text-muted">با انتخاب این روش، همین آدرس جایگزین لینک قبلی می‌شود.</small>
    </div>
    <div class="col-12" data-link-fields="internal">
        <label class="form-label" for="route_name">صفحه داخلی</label>
        <select class="form-control" id="route_name" name="route_name">
            <option value="">یک صفحه را انتخاب کنید</option>
            @foreach ($internalRoutes as $routeName => $label)
                <option value="{{ $routeName }}" @selected(old('route_name', $item?->route_name) === $routeName)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-12" data-link-fields="content">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="type">نوع محتوا</label>
                <select class="form-control" id="type" name="type" required>
                    @foreach ($types as $type)
                        <option value="{{ $type }}" @selected(old('type', $item?->type ?? 'custom') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label" for="reference_type">نوع مرجع</label><input class="form-control" dir="ltr" id="reference_type" name="reference_type" value="{{ old('reference_type', $item?->reference_type) }}" placeholder="Page, Post, Union..."></div>
            <div class="col-md-4"><label class="form-label" for="reference_id">شناسه مرجع</label><input class="form-control" dir="ltr" id="reference_id" name="reference_id" type="number" min="1" value="{{ old('reference_id', $item?->reference_id) }}"></div>
        </div>
    </div>

    <div class="col-md-4"><label class="form-label" for="parent_id">انتخاب والد</label><select class="form-control" id="parent_id" name="parent_id"><option value="">بدون والد</option>@foreach ($parents as $parent)<option value="{{ $parent->id }}" @selected((string) old('parent_id', $item?->parent_id) === (string) $parent->id)>{{ $parent->title }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label" for="icon">آیکون</label><input class="form-control" id="icon" name="icon" value="{{ old('icon', $item?->icon) }}" placeholder="مثلاً 📰 یا کلاس آیکون"></div>
    <div class="col-md-2"><label class="form-label" for="sort_order">ترتیب</label><input class="form-control" dir="ltr" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $item?->sort_order ?? 0) }}"></div>
    <div class="col-md-2"><label class="form-label" for="is_active">وضعیت</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', $item?->is_active ?? 1) === '1')>فعال</option><option value="0" @selected((string) old('is_active', $item?->is_active ?? 1) === '0')>غیرفعال</option></select></div>
</div>
<div class="admin-form-actions"><button class="admin-primary-btn" type="submit">ذخیره تغییرات</button><a class="admin-secondary-btn" href="{{ route('admin.menus.show', $menu) }}">انصراف</a></div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-menu-item-form]');
    const mode = form?.querySelector('[data-link-mode]');
    if (!form || !mode) return;

    const updateFields = () => {
        form.querySelectorAll('[data-link-fields]').forEach((section) => {
            const active = section.dataset.linkFields === mode.value;
            section.hidden = !active;
            section.querySelectorAll('input, select').forEach((field) => field.disabled = !active);
        });
    };

    mode.addEventListener('change', updateFields);
    updateFields();
});
</script>
@endpush
