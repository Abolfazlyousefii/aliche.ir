<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="title">عنوان منو</label>
        <input class="form-control" id="title" name="title" value="{{ old('title', $menu?->title) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="location">محل نمایش</label>
        <select class="form-control" id="location" name="location" required>
            @foreach ($locations as $location)
                <option value="{{ $location }}" @selected(old('location', $menu?->location) === $location)>{{ $location }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="is_active">وضعیت</label>
        <select class="form-control" id="is_active" name="is_active" required>
            <option value="1" @selected((string) old('is_active', $menu?->is_active ?? 1) === '1')>فعال</option>
            <option value="0" @selected((string) old('is_active', $menu?->is_active ?? 1) === '0')>غیرفعال</option>
        </select>
    </div>
</div>

<div class="admin-form-actions">
    <button class="admin-primary-btn" type="submit">ذخیره منو</button>
    <a class="admin-secondary-btn" href="{{ route('admin.menus.index') }}">انصراف</a>
</div>
