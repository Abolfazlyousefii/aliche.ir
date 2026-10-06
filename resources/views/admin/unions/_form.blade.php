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
    $presidentIconOptions = \App\Support\UnionIcon::options();
    $socialLabels = \App\Models\GuildUnion::socialLinkLabels();
    $selectedPostIds = collect(old('selected_posts', $union?->selectedPosts?->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all();
@endphp

<div class="admin-panel-card">
    <div class="row g-3">
        <div class="col-12"><h3 class="h6 mb-0">هویت اتحادیه</h3></div>
        <div class="col-md-8">
            <label class="form-label" for="title">عنوان اتحادیه</label>
            <input class="form-control" id="title" name="title" value="{{ old('title', $union?->display_title) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="slug">اسلاگ</label>
            <input class="form-control" id="slug" name="slug" value="{{ old('slug', $union?->slug) }}" dir="ltr"><small class="text-muted">اگر خالی بماند از عنوان ساخته می‌شود و می‌توانید آن را دستی تغییر دهید.</small>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="union_type_id">نوع اتحادیه</label>
            <select class="form-control" id="union_type_id" name="union_type_id">
                <option value="">انتخاب نوع</option>
                @foreach (($unionTypes ?? collect()) as $unionType)
                    <option value="{{ $unionType->id }}" @selected((string) old('union_type_id', $union?->union_type_id) === (string) $unionType->id)>{{ $unionType->icon }} {{ $unionType->title }}</option>
                @endforeach
            </select>
            <input type="hidden" name="union_type" value="{{ old('union_type', $union?->union_type) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="cover_image">تصویر اصلی اتحادیه</label>
            <input class="form-control" id="cover_image" name="cover_image" type="file" accept="image/*" data-skip-media-picker>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="cover_image_media_id">انتخاب یا آپلود از کتابخانه</button>
            <select class="d-none" id="cover_image_media_id" name="cover_image_media_id" data-union-media-select data-preview-target="cover_image" aria-hidden="true" tabindex="-1">
                <option value="">بدون تغییر / انتخاب تصویر</option>
                @foreach(($mediaItems ?? collect()) as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('cover_image_media_id', $currentMediaIds['cover_image'] ?? null) === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1">برای کارت اتحادیه و کاور صفحه اختصاصی. Upload جدید بر انتخاب کتابخانه اولویت دارد.</small>
            <div class="mt-2" data-image-preview="cover_image">@if ($union?->cover_image)<img src="{{ $union->cover_image_url }}" alt="تصویر اصلی فعلی اتحادیه" class="img-fluid rounded" style="width:100%;max-width:360px;height:180px;object-fit:cover">@endif</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="logo">لوگوی اتحادیه (اختیاری)</label>
            <input class="form-control" id="logo" name="logo" type="file" accept="image/*" data-skip-media-picker>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="logo_media_id">انتخاب یا آپلود از کتابخانه</button>
            <select class="d-none" id="logo_media_id" name="logo_media_id" data-union-media-select data-preview-target="logo" aria-hidden="true" tabindex="-1">
                <option value="">بدون تغییر / انتخاب لوگو</option>
                @foreach(($mediaItems ?? collect()) as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('logo_media_id', $currentMediaIds['logo'] ?? null) === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1">برای نشان هویتی اتحادیه؛ تصویر اصلی جداگانه در Cover استفاده می‌شود.</small>
            <div class="mt-2" data-image-preview="logo">@if ($union?->logo)<img src="{{ $union->logo_url }}" alt="لوگوی فعلی اتحادیه" class="img-fluid rounded" style="width:120px;height:120px;object-fit:contain">@endif</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="short_description">توضیح کوتاه</label>
            <textarea class="form-control" id="short_description" name="short_description" rows="3">{{ old('short_description', plain_text($union?->short_description)) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="description">توضیحات کامل</label>
            <textarea class="form-control js-rich-editor" id="description" name="description" rows="6">{{ old('description', $union?->description) }}</textarea>
        </div>

        <div class="col-12"><h3 class="h6 mt-2 mb-0">رئیس اتحادیه</h3></div>
        <div class="col-md-4">
            <label class="form-label" for="manager_name">نام رئیس</label>
            <input class="form-control" id="manager_name" name="manager_name" value="{{ old('manager_name', $union?->manager_name) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="manager_position">سمت رئیس</label>
            <input class="form-control" id="manager_position" name="manager_position" value="{{ old('manager_position', $union?->manager_position) }}" placeholder="رئیس اتحادیه">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="manager_image">تصویر رئیس</label>
            <input class="form-control" id="manager_image" name="manager_image" type="file" accept="image/*" data-skip-media-picker>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="manager_image_media_id">انتخاب یا آپلود از کتابخانه</button>
            <select class="d-none" id="manager_image_media_id" name="manager_image_media_id" data-union-media-select data-preview-target="manager_image" aria-hidden="true" tabindex="-1">
                <option value="">بدون تغییر / انتخاب تصویر رئیس</option>
                @foreach(($mediaItems ?? collect()) as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('manager_image_media_id', $currentMediaIds['manager_image'] ?? null) === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1">ترجیحاً تصویر پرتره با نسبت نزدیک ۱:۱؛ نمایش سایت به‌صورت crop کنترل‌شده است.</small>
            <div class="mt-2" data-image-preview="manager_image">@if ($union?->manager_image)<img src="{{ $union->manager_image_url }}" alt="تصویر فعلی رئیس" class="img-fluid rounded" style="width:120px;height:120px;object-fit:cover;object-position:center 20%">@endif</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="manager_description">معرفی کوتاه رئیس</label>
            <textarea class="form-control" id="manager_description" name="manager_description" rows="4">{{ old('manager_description', $union?->manager_description) }}</textarea>
        </div>
        <div class="col-12"><h3 class="h6 mt-3 mb-0">مدیر اجرایی اتحادیه</h3>
            <small class="text-muted">این اطلاعات مستقل از رئیس اتحادیه است و در کنار ایشان در ابتدای صفحه عمومی نمایش داده می‌شود. اگر نام وارد نشود، کارت مدیر اجرایی نشان داده نمی‌شود.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="executive_name">نام مدیر اجرایی</label>
            <input class="form-control" id="executive_name" name="executive_name" maxlength="190" value="{{ old('executive_name', $union?->executive_name) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="executive_position">سمت مدیر اجرایی</label>
            <input class="form-control" id="executive_position" name="executive_position" maxlength="190" placeholder="مدیر اجرایی" value="{{ old('executive_position', $union?->executive_position) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="executive_image">تصویر مدیر اجرایی (اختیاری)</label>
            <input class="form-control" id="executive_image" name="executive_image" type="file" accept="image/*" data-skip-media-picker>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="executive_image_media_id">انتخاب تصویر مدیر اجرایی از کتابخانه</button>
            <select class="d-none" id="executive_image_media_id" name="executive_image_media_id" data-union-media-select data-preview-target="executive_image" aria-hidden="true" tabindex="-1">
                <option value="">بدون تغییر / انتخاب تصویر</option>
                @foreach(($mediaItems ?? collect()) as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('executive_image_media_id', $currentMediaIds['executive_image'] ?? null) === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <div class="mt-2" data-image-preview="executive_image">
                @if($union?->executive_image)<img src="{{ image_url($union->executive_image, '') }}" alt="تصویر فعلی مدیر اجرایی" class="img-fluid rounded" style="width:120px;height:120px;object-fit:cover">@endif
            </div>
            @if($union?->executive_image)
                <label class="form-check mt-2" for="remove_executive_image">
                    <input class="form-check-input" type="checkbox" id="remove_executive_image" name="remove_executive_image" value="1">
                    حذف تصویر مدیر اجرایی از این اتحادیه
                </label>
            @endif
        </div>

        <div class="col-12"><h4 class="h6 mt-2">دکمه‌های رئیس اتحادیه</h4></div>
        <div class="col-12 union-dynamic-section" data-section="president-buttons" data-next-index="{{ count($presidentButtons) }}">
            <div data-rows>
                @foreach ($presidentButtons as $index => $button)
                    <div class="border rounded p-3 mb-2" data-row><div class="row g-2 align-items-end">
                        <div class="col-md-3"><label class="form-label">عنوان</label><input class="form-control" name="president_buttons[{{ $index }}][title]" value="{{ $button['title'] ?? '' }}"></div>
                        <div class="col-md-3"><label class="form-label">لینک</label><input class="form-control" name="president_buttons[{{ $index }}][url]" value="{{ $button['url'] ?? '' }}" dir="ltr" placeholder="https://… یا tel:…"></div>
                        <div class="col-md-2">
                            <label class="form-label">آیکون</label>
                            <select class="form-control" name="president_buttons[{{ $index }}][icon]">
                                @foreach($presidentIconOptions as $iconKey => $iconLabel)
                                    <option value="{{ $iconKey }}" @selected(\App\Support\UnionIcon::normalize($button['icon'] ?? null) === $iconKey)>{{ $iconLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2"><label class="form-label">باز شدن</label><select class="form-control" name="president_buttons[{{ $index }}][target]"><option value="_self" @selected(($button['target'] ?? '_self') === '_self')>همان صفحه</option><option value="_blank" @selected(($button['target'] ?? '_self') === '_blank')>صفحه جدید</option></select></div>
                        <div class="col-md-1"><label class="form-check"><input class="form-check-input" type="checkbox" name="president_buttons[{{ $index }}][is_active]" value="1" @checked($button['is_active'] ?? true)> فعال</label></div>
                        <div class="col-md-1"><button class="btn btn-outline-danger w-100" type="button" data-remove-president-row aria-label="حذف دکمه">حذف</button></div>
                    </div></div>
                @endforeach
            </div>
            <button class="btn btn-outline-primary" type="button" data-add-row>افزودن دکمه رئیس</button>
        </div>

        <div class="col-12"><h3 class="h6 mt-2 mb-0">اطلاعات تماس</h3></div>
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

        <div class="col-12"><h3 class="h6 mt-2">شبکه‌های اجتماعی</h3></div>
        @foreach ($socialLabels as $key => $label)
            <div class="col-md-3">
                <label class="form-label" for="social_{{ $key }}">{{ $label }}</label>
                <input class="form-control" id="social_{{ $key }}" name="social_links[{{ $key }}]" value="{{ $socialLinks[$key] ?? '' }}" dir="ltr" type="url">
            </div>
        @endforeach
        <div class="col-12"><small class="text-muted">لینک شبکه اجتماعی باید URL کامل و معتبر باشد. فقط شبکه‌های پشتیبانی‌شده در صفحه عمومی نمایش داده می‌شوند.</small></div>
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
            <input class="form-control" id="price_list_image" name="price_list_image" type="file" accept="image/*" data-skip-media-picker>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="price_list_image_media_id">انتخاب یا آپلود از کتابخانه</button>
            <select class="d-none" id="price_list_image_media_id" name="price_list_image_media_id" data-union-media-select data-preview-target="price_list_image" aria-hidden="true" tabindex="-1">
                <option value="">بدون تغییر / انتخاب نرخنامه</option>
                @foreach(($mediaItems ?? collect()) as $media)
                    <option value="{{ $media->id }}" data-url="{{ $media->url }}" @selected((string) old('price_list_image_media_id', $currentMediaIds['price_list_image'] ?? null) === (string) $media->id)>{{ $media->title ?: $media->original_name }}</option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1">در حالت «عکس نرخنامه» نمایش داده می‌شود.</small>
            <div class="mt-2" data-image-preview="price_list_image">@if ($union?->price_list_image)<img src="{{ $union->price_list_image_url }}" alt="عکس نرخنامه فعلی" class="img-fluid rounded" style="width:100%;max-width:360px;height:180px;object-fit:contain">@endif</div>
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
const renderUnionImagePreview = (preview, url, fit, position) => {
    preview.replaceChildren();
    if (!url) return null;
    const image = document.createElement('img');
    image.src = url;
    image.alt = 'پیش‌نمایش تصویر انتخاب‌شده';
    image.className = 'img-fluid rounded';
    image.style.cssText = 'width:100%;max-width:360px;height:180px;object-fit:' + fit + ';object-position:' + position;
    preview.appendChild(image);
    return image;
};
document.querySelectorAll('input[type="file"][accept^="image/"]').forEach((input) => {
    const preview = document.querySelector(`[data-image-preview="${input.id}"]`);
    if (!preview) return;

    input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file) return;

        const url = URL.createObjectURL(file);
        const fit = input.id === 'logo' || input.id === 'price_list_image' ? 'contain' : 'cover';
        const position = input.id === 'manager_image' ? 'center 20%' : 'center';
        const image = renderUnionImagePreview(preview, url, fit, position);
        image?.addEventListener('load', () => URL.revokeObjectURL(url), { once: true });
        image?.addEventListener('error', () => URL.revokeObjectURL(url), { once: true });
    });
});

document.querySelectorAll('[data-union-media-select]').forEach((select) => {
    const previewKey = select.dataset.previewTarget;
    const preview = document.querySelector(`[data-image-preview="${previewKey}"]`);
    if (!preview) return;

    const renderSelectedMedia = () => {
        const option = select.selectedOptions?.[0];
        const url = option?.dataset?.url;
        if (!url) return;

        const fit = previewKey === 'logo' || previewKey === 'price_list_image' ? 'contain' : 'cover';
        const position = previewKey === 'manager_image' ? 'center 20%' : 'center';
        renderUnionImagePreview(preview, url, fit, position);
    };

    select.addEventListener('change', renderSelectedMedia);
});

</script>
@endpush
