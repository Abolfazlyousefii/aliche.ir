@php($selectedStatus = old('status', $member?->status ?? 'active'))

<div class="admin-panel-card">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="union_id">اتحادیه</label>
            <select class="form-control" id="union_id" name="union_id" required>
                <option value="">انتخاب اتحادیه</option>
                @foreach ($unions as $union)
                    <option value="{{ $union->id }}" @selected((string) old('union_id', $member?->union_id) === (string) $union->id)>{{ $union->display_title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="full_name">نام و نام خانوادگی</label>
            <input class="form-control" id="full_name" name="full_name" value="{{ old('full_name', $member?->full_name) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="national_code">کد ملی</label>
            <input class="form-control" id="national_code" name="national_code" value="{{ old('national_code', $member?->national_code) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="mobile">موبایل</label>
            <input class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $member?->mobile) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="phone">تلفن</label>
            <input class="form-control" id="phone" name="phone" value="{{ old('phone', $member?->phone) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="membership_code">کد عضویت</label>
            <input class="form-control" id="membership_code" name="membership_code" value="{{ old('membership_code', $member?->membership_code) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="business_license_number">شماره پروانه کسب</label>
            <input class="form-control" id="business_license_number" name="business_license_number" value="{{ old('business_license_number', $member?->business_license_number) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="business_name">نام کسب‌وکار</label>
            <input class="form-control" id="business_name" name="business_name" value="{{ old('business_name', $member?->business_name) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="status">وضعیت عضویت</label>
            <select class="form-control" id="status" name="status" required>
                @foreach (\App\Models\UnionMember::STATUSES as $status)
                    <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="is_active">فعال</label>
            <select class="form-control" id="is_active" name="is_active">
                <option value="1" @selected((string) old('is_active', (int) ($member?->is_active ?? true)) === '1')>فعال</option>
                <option value="0" @selected((string) old('is_active', (int) ($member?->is_active ?? true)) === '0')>غیرفعال</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="address">آدرس</label>
            <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $member?->address) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="description">توضیحات</label>
            <textarea class="form-control js-rich-editor" id="description" name="description" rows="4">{{ old('description', $member?->description) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="attachments">پیوست‌ها</label>
            <input class="form-control" id="attachments" name="attachments[]" type="file" multiple>
            <small class="text-muted">امکان انتخاب چند فایل وجود دارد. حداکثر حجم هر فایل ۱۰ مگابایت است.</small>
        </div>
        @if ($member?->attachments)
            <div class="col-12">
                <label class="form-label">پیوست‌های فعلی</label>
                <div class="row g-2">
                    @foreach ($member->attachments as $attachment)
                        <div class="col-md-4">
                            <div class="border rounded p-2 h-100">
                                <a href="{{ Storage::url($attachment['path']) }}" target="_blank">{{ $attachment['name'] ?? basename($attachment['path']) }}</a>
                                <label class="d-block small mt-2"><input type="checkbox" name="delete_attachments[]" value="{{ $attachment['path'] }}"> حذف این پیوست</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<div class="mt-3 d-flex gap-2">
    <button class="admin-primary-btn" type="submit">ذخیره عضو</button>
    <a class="admin-secondary-btn" href="{{ route('admin.union_members.index') }}">انصراف</a>
</div>
