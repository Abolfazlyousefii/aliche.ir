@php
    $featureFields = [
        'news_enabled' => 'اخبار',
        'announcements_enabled' => 'اطلاعیه‌ها',
        'gallery_enabled' => 'گالری',
        'videos_enabled' => 'ویدیوها',
        'members_enabled' => 'اعضا',
        'services_enabled' => 'خدمات',
        'complaint_enabled' => 'فرم شکایت',
        'congratulations_enabled' => 'پیام تبریک مدیر',
    ];
    $socialLinks = old('social_links', $union?->social_links ?? []);
    $settings = old('settings', $union?->settings ?? []);
    $settingDefaults = \App\Models\GuildUnion::sectionDefaults();
    $presidentButtons = old('president_buttons', $union?->president_buttons ?? []);
    $selectedPostIds = collect(old('selected_posts', $union?->selectedPosts?->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all();
@endphp

<div class="admin-panel-card">
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label" for="title">عنوان اتحادیه</label>
            <input class="form-control" id="title" name="title" value="{{ old('title', $union?->display_title) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="slug">اسلاگ</label>
            <input class="form-control" id="slug" name="slug" value="{{ old('slug', $union?->slug) }}" dir="ltr"><small class="text-muted">اگر خالی بماند از عنوان ساخته می‌شود و می‌توانید آن را دستی تغییر دهید.</small>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="logo">لوگو</label>
            <input class="form-control" id="logo" name="logo" type="file" accept="image/*">
            <div class="mt-2" data-image-preview="logo">@if ($union?->logo)<img src="{{ $union->logo_url }}" alt="لوگوی فعلی" class="img-fluid rounded" style="max-height:120px;object-fit:contain">@endif</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="cover_image">تصویر کاور</label>
            <input class="form-control" id="cover_image" name="cover_image" type="file" accept="image/*">
            <div class="mt-2" data-image-preview="cover_image">@if ($union?->cover_image)<img src="{{ $union->cover_image_url }}" alt="کاور فعلی" class="img-fluid rounded" style="max-height:140px;object-fit:cover">@endif</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="short_description">توضیح کوتاه</label>
            <textarea class="form-control" id="short_description" name="short_description" rows="3">{{ old('short_description', plain_text($union?->short_description)) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="description">توضیحات کامل</label>
            <textarea class="form-control js-rich-editor" id="description" name="description" rows="6">{{ old('description', $union?->description) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="address">آدرس</label>
            <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $union?->address) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="working_hours">ساعات کاری</label>
            <textarea class="form-control" id="working_hours" name="working_hours" rows="3">{{ old('working_hours', $union?->working_hours) }}</textarea>
        </div>
        <div class="col-md-3"><label class="form-label" for="phone">تلفن</label><input class="form-control" id="phone" name="phone" value="{{ old('phone', $union?->phone) }}"></div>
        <div class="col-md-3"><label class="form-label" for="mobile">موبایل</label><input class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $union?->mobile) }}"></div>
        <div class="col-md-3"><label class="form-label" for="email">ایمیل</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email', $union?->email) }}"></div>
        <div class="col-md-3"><label class="form-label" for="website">وب‌سایت</label><input class="form-control" id="website" name="website" type="url" value="{{ old('website', $union?->website) }}" dir="ltr"></div>
        <div class="col-md-4">
            <label class="form-label" for="manager_name">نام مدیر</label>
            <input class="form-control" id="manager_name" name="manager_name" value="{{ old('manager_name', $union?->manager_name) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="union_type_id">نوع اتحادیه</label>
            <select class="form-control" id="union_type_id" name="union_type_id">
                <option value="">انتخاب نوع</option>
                @foreach (($unionTypes ?? collect()) as $unionType)
                    <option value="{{ $unionType->id }}" @selected((string) old('union_type_id', $union?->union_type_id) === (string) $unionType->id)>{{ $unionType->icon }} {{ $unionType->title }}</option>
                @endforeach
            </select>
            <input type="hidden" name="union_type" value="{{ old('union_type', $union?->union_type) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="category_id">دسته‌بندی اتحادیه</label>
            <select class="form-control" id="category_id" name="category_id">
                <option value="">بدون دسته‌بندی</option>
                @foreach (($categories ?? collect()) as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $union?->category_id) === (string) $category->id)>{{ $category->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="manager_image">تصویر مدیر</label>
            <input class="form-control" id="manager_image" name="manager_image" type="file" accept="image/*">
            <div class="mt-2" data-image-preview="manager_image">@if ($union?->manager_image)<img src="{{ $union->manager_image_url }}" alt="تصویر فعلی مدیر" class="img-fluid rounded" style="max-height:120px;object-fit:cover">@endif</div>
        </div>
        <div class="col-12"><h3 class="h6 mt-2">شبکه‌های اجتماعی</h3></div>
        @foreach (['instagram' => 'اینستاگرام', 'telegram' => 'تلگرام', 'whatsapp' => 'واتساپ', 'eitaa' => 'ایتا', 'bale' => 'بله', 'rubika' => 'روبیکا', 'website' => 'وب‌سایت'] as $key => $label)
            <div class="col-md-3">
                <label class="form-label" for="social_{{ $key }}">{{ $label }}</label>
                <input class="form-control" id="social_{{ $key }}" name="social_links[{{ $key }}]" value="{{ $socialLinks[$key] ?? '' }}" dir="ltr" type="url">
            </div>
        @endforeach
        <div class="col-12"><h3 class="h6 mt-2">امکانات اتحادیه</h3></div>
        @foreach ($featureFields as $field => $label)
            <div class="col-md-3">
                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                <select class="form-control" id="{{ $field }}" name="{{ $field }}">
                    <option value="1" @selected((string) old($field, (int) ($union?->{$field} ?? in_array($field, ['news_enabled', 'announcements_enabled', 'complaint_enabled'], true))) === '1')>فعال</option>
                    <option value="0" @selected((string) old($field, (int) ($union?->{$field} ?? in_array($field, ['news_enabled', 'announcements_enabled', 'complaint_enabled'], true))) === '0')>غیرفعال</option>
                </select>
            </div>
        @endforeach

        <div class="col-12"><h3 class="h6 mt-2">خبرهای اتحادیه</h3></div>
        <div class="col-md-4">
            <label class="form-label" for="news_mode">حالت نمایش خبر</label>
            <select class="form-control" id="news_mode" name="news_mode">
                @foreach (\App\Models\GuildUnion::newsModeLabels() as $mode => $label)
                    <option value="{{ $mode }}" @selected(old('news_mode', $union?->news_mode ?? 'auto') === $mode)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label" for="selected_posts">خبرهای انتخابی در حالت دستی</label>
            <select class="form-control js-select2" id="selected_posts" name="selected_posts[]" multiple size="6">
                @foreach (($selectablePosts ?? collect()) as $post)
                    <option value="{{ $post->id }}" @selected(in_array((string) $post->id, $selectedPostIds, true))>{{ $post->title }}</option>
                @endforeach
            </select>
            <small class="text-muted">این فیلد برای Select2 آماده شده است و در نبود کتابخانه، انتخاب چندگانه مرورگر را نمایش می‌دهد.</small>
        </div>
        <div class="col-12"><h3 class="h6 mt-2">دکمه‌های رئیس اتحادیه</h3></div>
        <div class="col-12 union-dynamic-section" data-section="president-buttons" data-next-index="{{ count($presidentButtons) }}">
            <div data-rows>
                @foreach ($presidentButtons as $index => $button)
                    <div class="border rounded p-3 mb-2" data-row><div class="row g-2 align-items-end">
                        <div class="col-md-3"><label class="form-label">عنوان</label><input class="form-control" name="president_buttons[{{ $index }}][title]" value="{{ $button['title'] ?? '' }}"></div>
                        <div class="col-md-3"><label class="form-label">لینک</label><input class="form-control" name="president_buttons[{{ $index }}][url]" value="{{ $button['url'] ?? '' }}" dir="ltr"></div>
                        <div class="col-md-2"><label class="form-label">آیکون</label><input class="form-control" name="president_buttons[{{ $index }}][icon]" value="{{ $button['icon'] ?? '' }}"></div>
                        <div class="col-md-2"><label class="form-label">باز شدن</label><select class="form-control" name="president_buttons[{{ $index }}][target]"><option value="_self" @selected(($button['target'] ?? '_self') === '_self')>همان صفحه</option><option value="_blank" @selected(($button['target'] ?? '_self') === '_blank')>صفحه جدید</option></select></div>
                        <div class="col-md-2"><label class="form-check"><input class="form-check-input" type="checkbox" name="president_buttons[{{ $index }}][is_active]" value="1" @checked($button['is_active'] ?? true)> فعال</label></div>
                    </div></div>
                @endforeach
            </div>
            <button class="btn btn-outline-primary" type="button" data-add-row>افزودن دکمه رئیس</button>
        </div>
        <div class="col-12"><h3 class="h6 mt-2">نرخنامه اتحادیه</h3></div>
        <div class="col-md-4">
            <label class="form-label" for="price_list_mode">حالت نمایش نرخنامه</label>
            <select class="form-control" id="price_list_mode" name="price_list_mode" required>
                @foreach (\App\Models\GuildUnion::priceListModeLabels() as $mode => $label)
                    <option value="{{ $mode }}" @selected(old('price_list_mode', $union?->price_list_mode ?? 'table') === $mode)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label" for="price_list_image">عکس نرخنامه</label>
            <input class="form-control" id="price_list_image" name="price_list_image" type="file" accept="image/*">
            <div class="mt-2" data-image-preview="price_list_image">@if ($union?->price_list_image)<img src="{{ $union->price_list_image_url }}" alt="عکس نرخنامه فعلی" class="img-fluid rounded" style="max-height:140px;object-fit:cover">@endif</div>
        </div>
        <div class="col-12"><h3 class="h6 mt-2">تنظیمات صفحه اتحادیه</h3></div>
        @foreach (\App\Models\GuildUnion::sectionLabels() as $key => $label)
            @php($checked = array_key_exists($key, $settings) ? (bool) $settings[$key] : (bool) ($settingDefaults[$key] ?? true))
            <div class="col-md-3">
                <label class="form-check d-flex align-items-center gap-2" for="settings_{{ $key }}">
                    <input class="form-check-input" id="settings_{{ $key }}" name="settings[{{ $key }}]" type="checkbox" value="1" @checked($checked)>
                    <span>{{ $label }}</span>
                </label>
            </div>
        @endforeach
        <div class="col-12">
            @include('admin.unions._page_sections_form')
        </div>
        <div class="col-md-4">
            <label class="form-label" for="is_active">وضعیت</label>
            <select class="form-control" id="is_active" name="is_active">
                <option value="1" @selected((string) old('is_active', (int) ($union?->is_active ?? true)) === '1')>فعال</option>
                <option value="0" @selected((string) old('is_active', (int) ($union?->is_active ?? true)) === '0')>غیرفعال</option>
            </select>
        </div>
        <div class="col-md-4"><label class="form-label" for="sort_order">ترتیب نمایش</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $union?->sort_order ?? 0) }}"></div>
        <div class="col-md-4"><label class="form-label" for="meta_title">عنوان متا</label><input class="form-control" id="meta_title" name="meta_title" value="{{ old('meta_title', $union?->meta_title) }}"></div>
        <div class="col-md-6"><label class="form-label" for="meta_description">توضیحات متا</label><input class="form-control" id="meta_description" name="meta_description" value="{{ old('meta_description', $union?->meta_description) }}"></div>
        <div class="col-md-6"><label class="form-label" for="meta_keywords">کلیدواژه‌های متا</label><input class="form-control" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $union?->meta_keywords) }}"></div>
    </div>
</div>

<div class="mt-3 d-flex gap-2">
    <button class="admin-primary-btn" type="submit">ذخیره اتحادیه</button>
    <a class="admin-secondary-btn" href="{{ route('admin.unions.index') }}">انصراف</a>
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
        preview.innerHTML = `<img src="${url}" alt="پیش‌نمایش تصویر انتخاب‌شده" class="img-fluid rounded" style="max-height:140px;object-fit:contain">`;
        preview.querySelector('img')?.addEventListener('load', () => URL.revokeObjectURL(url), { once: true });
    });
});
</script>
@endpush
