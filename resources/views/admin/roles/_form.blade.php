<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="label">عنوان فارسی نقش</label>
        <input class="form-control" id="label" name="label" value="{{ old('label', $role?->label) }}" placeholder="مثلاً مدیرکل" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="name">نام سیستمی</label>
        <input class="form-control" dir="ltr" id="name" name="name" value="{{ old('name', $role?->name) }}" placeholder="super-admin" required>
    </div>
    <div class="col-12">
        <label class="form-label" for="description">توضیحات</label>
        <textarea class="form-control js-rich-editor" id="description" name="description" rows="3">{{ old('description', $role?->description) }}</textarea>
    </div>
</div>

<div class="admin-permissions-box">
    <div class="admin-panel-header">
        <h3>انتخاب دسترسی‌ها</h3>
        <span>checkbox</span>
    </div>

    @foreach ($permissions as $group => $items)
        <section class="admin-permission-group">
            <h4>{{ $group }}</h4>
            <div class="admin-permission-checkboxes">
                @foreach ($items as $permission)
                    <label class="admin-check-card">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(collect(old('permissions', $selectedPermissions))->contains($permission->id))>
                        <span>{{ $permission->label }}</span>
                        <code>{{ $permission->name }}</code>
                    </label>
                @endforeach
            </div>
        </section>
    @endforeach
</div>

<div class="admin-form-actions">
    <button class="admin-primary-btn" type="submit">ذخیره نقش</button>
    <a class="admin-secondary-btn" href="{{ route('admin.roles.index') }}">انصراف</a>
</div>
