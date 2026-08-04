<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="label">عنوان فارسی دسترسی</label>
        <input class="form-control" id="label" name="label" value="{{ old('label', $permission?->label) }}" placeholder="مثلاً مشاهده اخبار" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="name">نام دسترسی</label>
        <input class="form-control" dir="ltr" id="name" name="name" value="{{ old('name', $permission?->name) }}" placeholder="posts.view" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="group">گروه</label>
        <input class="form-control" dir="ltr" id="group" name="group" value="{{ old('group', $permission?->group) }}" placeholder="posts" required>
    </div>
    <div class="col-12">
        <label class="form-label" for="description">توضیحات</label>
        <textarea class="form-control js-rich-editor" id="description" name="description" rows="3">{{ old('description', $permission?->description) }}</textarea>
    </div>
</div>

<div class="admin-form-actions">
    <button class="admin-primary-btn" type="submit">ذخیره دسترسی</button>
    <a class="admin-secondary-btn" href="{{ route('admin.permissions.index') }}">انصراف</a>
</div>
