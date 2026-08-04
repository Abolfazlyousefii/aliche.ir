<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">نام</label>
        <input class="form-control" id="name" name="name" value="{{ old('name', $user?->name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">ایمیل</label>
        <input class="form-control" dir="ltr" id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="mobile">شماره تماس</label>
        <input class="form-control" dir="ltr" id="mobile" name="mobile" type="tel" value="{{ old('mobile', $user?->mobile) }}" autocomplete="tel" inputmode="tel" placeholder="09110000000" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="union_id">اتحادیه مرتبط، اختیاری</label>
        <select class="form-control" id="union_id" name="union_id">
            <option value="">بدون اتحادیه</option>
            @foreach ($unions as $union)
                <option value="{{ $union->id }}" @selected((string) old('union_id', $user?->union_id) === (string) $union->id)>{{ $union->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="password">رمز عبور</label>
        <input class="form-control" dir="ltr" id="password" name="password" type="password" {{ $user ? '' : 'required' }}>
        @if ($user)
            <small class="text-muted">اگر خالی بماند، رمز عبور قبلی تغییر نمی‌کند.</small>
        @endif
    </div>
    <div class="col-md-6">
        <label class="form-label" for="password_confirmation">تکرار رمز عبور</label>
        <input class="form-control" dir="ltr" id="password_confirmation" name="password_confirmation" type="password" {{ $user ? '' : 'required' }}>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="is_active">وضعیت فعال/غیرفعال</label>
        <select class="form-control" id="is_active" name="is_active" required>
            <option value="1" @selected((string) old('is_active', $user?->is_active ?? 1) === '1')>فعال</option>
            <option value="0" @selected((string) old('is_active', $user?->is_active ?? 1) === '0')>غیرفعال</option>
        </select>
    </div>
</div>

<div class="admin-permissions-box">
    <div class="admin-panel-header">
        <h3>نقش‌های کاربر</h3>
        <span>انتخاب چندگانه</span>
    </div>
    <div class="admin-permission-checkboxes">
        @foreach ($roles as $role)
            <label class="admin-check-card">
                <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(collect(old('roles', $selectedRoles))->contains($role->id))>
                <span>{{ $role->label }}</span>
                <code>{{ $role->name }}</code>
            </label>
        @endforeach
    </div>
</div>

<div class="admin-form-actions">
    <button class="admin-primary-btn" type="submit">ذخیره کاربر</button>
    <a class="admin-secondary-btn" href="{{ route('admin.users.index') }}">انصراف</a>
</div>
