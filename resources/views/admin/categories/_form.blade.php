<div class="admin-panel-card">
    @php($selectedIcon = old('icon', $category?->icon))
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="title">عنوان</label><input class="form-control" id="title" name="title" value="{{ old('title', $category?->title) }}" required></div>
        <div class="col-md-6"><label class="form-label" for="slug">نامک</label><input class="form-control" id="slug" name="slug" value="{{ old('slug', $category?->slug) }}" dir="ltr"></div>
        <div class="col-md-4"><label class="form-label" for="type">نوع دسته‌بندی</label><select class="form-control" id="type" name="type" required>@foreach($types as $value => $label)<option value="{{ $value }}" @selected(old('type', $category?->type ?? request('type','news')) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4">
            <label class="form-label" for="icon">آیکون</label>
            <select class="form-control" id="icon" name="icon">
                <option value="">بدون آیکون</option>
                @foreach($icons as $icon)
                    <option value="{{ $icon }}" @selected($selectedIcon === $icon)>{{ $icon }}</option>
                @endforeach
                @if($selectedIcon && ! in_array($selectedIcon, $icons, true))
                    <option value="{{ $selectedIcon }}" selected>{{ $selectedIcon }}</option>
                @endif
            </select>
            <small class="text-muted">از فهرست آیکون‌های آماده انتخاب کنید.</small>
        </div>
        <div class="col-md-4"><label class="form-label" for="sort_order">ترتیب نمایش</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $category?->sort_order ?? 0) }}"></div>
        <div class="col-md-4"><label class="form-label" for="is_active">وضعیت</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected((string)old('is_active', (int)($category?->is_active ?? true))==='1')>فعال</option><option value="0" @selected((string)old('is_active', (int)($category?->is_active ?? true))==='0')>غیرفعال</option></select></div>
        <div class="col-12"><label class="form-label" for="description">توضیحات</label><textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $category?->description) }}</textarea></div>
    </div>
</div>
<div class="mt-3 d-flex gap-2"><button class="admin-primary-btn">ذخیره دسته‌بندی</button><a class="admin-secondary-btn" href="{{ route('admin.categories.index') }}">انصراف</a></div>
