@csrf
<div class="row g-3">
    <div class="col-md-6"><label class="form-label" for="first_name">نام</label><input class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $member->first_name) }}" required></div>
    <div class="col-md-6"><label class="form-label" for="last_name">نام خانوادگی</label><input class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $member->last_name) }}" required></div>
    <div class="col-md-6"><label class="form-label" for="position">سمت</label><input class="form-control" id="position" name="position" value="{{ old('position', $member->position) }}" required></div>
    <div class="col-md-3"><label class="form-label" for="sort_order">ترتیب نمایش</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $member->sort_order ?? 0) }}"></div>
    <div class="col-md-3"><label class="form-label" for="is_active">وضعیت</label><select class="form-control" id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', (int) $member->is_active) === '1')>فعال</option><option value="0" @selected((string) old('is_active', (int) $member->is_active) === '0')>غیرفعال</option></select></div>
    <div class="col-md-6">
        <label class="form-label" for="photo">عکس</label>
        <input class="form-control" id="photo" name="photo" type="file" accept="image/*" data-skip-media-picker>
        <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="photo_media_id">انتخاب یا آپلود از کتابخانه با پیش‌نمایش</button>
        <select class="d-none" name="photo_media_id" id="photo_media_id" aria-hidden="true" tabindex="-1">
            <option value="">انتخاب عکس از کتابخانه تصاویر</option>
            @foreach(($mediaItems ?? collect()) as $media)
                <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('photo_media_id') === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
            @endforeach
        </select>
        <small class="text-muted d-block mt-1">می‌توانید عکس را همین‌جا آپلود کنید تا در کتابخانه ذخیره شود، یا از تصاویر موجود انتخاب کنید. فایل آپلودی جدید جایگزین انتخاب کتابخانه می‌شود.</small>
        <div id="photo_media_preview" class="mt-2">
            @if($member->photo)<img class="rounded" src="{{ $member->photo_url }}" alt="{{ $member->full_name }}" style="width:110px;height:110px;object-fit:cover">@endif
        </div>
    </div>
</div>
<div class="mt-3 d-flex gap-2"><button class="admin-primary-btn" type="submit">ذخیره</button><a class="admin-secondary-btn" href="{{ route('admin.chamber_members.index') }}">انصراف</a></div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('photo_media_id');
    const preview = document.getElementById('photo_media_preview');
    const render = () => {
        const option = select?.selectedOptions?.[0];
        const url = option?.dataset?.url;
        if (url && preview) {
            preview.innerHTML = `<img class="rounded" src="${url}" alt="عکس انتخاب‌شده" style="width:110px;height:110px;object-fit:cover">`;
        }
    };
    select?.addEventListener('change', render);
    render();
});
</script>
@endpush
