<?php
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
?>

<div class="admin-panel-card">
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label" for="title">عنوان اتحادیه</label>
            <input class="form-control" id="title" name="title" value="<?php echo e(old('title', $union?->display_title)); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="slug">اسلاگ</label>
            <input class="form-control" id="slug" name="slug" value="<?php echo e(old('slug', $union?->slug)); ?>" dir="ltr"><small class="text-muted">اگر خالی بماند از عنوان ساخته می‌شود و می‌توانید آن را دستی تغییر دهید.</small>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="logo">لوگو</label>
            <input class="form-control" id="logo" name="logo" type="file" accept="image/*">
            <div class="mt-2" data-image-preview="logo"><?php if($union?->logo): ?><img src="<?php echo e($union->logo_url); ?>" alt="لوگوی فعلی" class="img-fluid rounded" style="max-height:120px;object-fit:contain"><?php endif; ?></div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="cover_image">تصویر کاور</label>
            <input class="form-control" id="cover_image" name="cover_image" type="file" accept="image/*">
            <div class="mt-2" data-image-preview="cover_image"><?php if($union?->cover_image): ?><img src="<?php echo e($union->cover_image_url); ?>" alt="کاور فعلی" class="img-fluid rounded" style="max-height:140px;object-fit:cover"><?php endif; ?></div>
        </div>
        <div class="col-12">
            <label class="form-label" for="short_description">توضیح کوتاه</label>
            <textarea class="form-control" id="short_description" name="short_description" rows="3"><?php echo e(old('short_description', plain_text($union?->short_description))); ?></textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="description">توضیحات کامل</label>
            <textarea class="form-control js-rich-editor" id="description" name="description" rows="6"><?php echo e(old('description', $union?->description)); ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="address">آدرس</label>
            <textarea class="form-control" id="address" name="address" rows="3"><?php echo e(old('address', $union?->address)); ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="working_hours">ساعات کاری</label>
            <textarea class="form-control" id="working_hours" name="working_hours" rows="3"><?php echo e(old('working_hours', $union?->working_hours)); ?></textarea>
        </div>
        <div class="col-md-3"><label class="form-label" for="phone">تلفن</label><input class="form-control" id="phone" name="phone" value="<?php echo e(old('phone', $union?->phone)); ?>"></div>
        <div class="col-md-3"><label class="form-label" for="mobile">موبایل</label><input class="form-control" id="mobile" name="mobile" value="<?php echo e(old('mobile', $union?->mobile)); ?>"></div>
        <div class="col-md-3"><label class="form-label" for="email">ایمیل</label><input class="form-control" id="email" name="email" type="email" value="<?php echo e(old('email', $union?->email)); ?>"></div>
        <div class="col-md-3"><label class="form-label" for="website">وب‌سایت</label><input class="form-control" id="website" name="website" type="url" value="<?php echo e(old('website', $union?->website)); ?>" dir="ltr"></div>
        <div class="col-md-4">
            <label class="form-label" for="manager_name">نام مدیر</label>
            <input class="form-control" id="manager_name" name="manager_name" value="<?php echo e(old('manager_name', $union?->manager_name)); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="union_type_id">نوع اتحادیه</label>
            <select class="form-control" id="union_type_id" name="union_type_id">
                <option value="">انتخاب نوع</option>
                <?php $__currentLoopData = ($unionTypes ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unionType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($unionType->id); ?>" <?php if((string) old('union_type_id', $union?->union_type_id) === (string) $unionType->id): echo 'selected'; endif; ?>><?php echo e($unionType->icon); ?> <?php echo e($unionType->title); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input type="hidden" name="union_type" value="<?php echo e(old('union_type', $union?->union_type)); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="category_id">دسته‌بندی اتحادیه</label>
            <select class="form-control" id="category_id" name="category_id">
                <option value="">بدون دسته‌بندی</option>
                <?php $__currentLoopData = ($categories ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category->id); ?>" <?php if((string) old('category_id', $union?->category_id) === (string) $category->id): echo 'selected'; endif; ?>><?php echo e($category->title); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="manager_image">تصویر مدیر</label>
            <input class="form-control" id="manager_image" name="manager_image" type="file" accept="image/*">
            <div class="mt-2" data-image-preview="manager_image"><?php if($union?->manager_image): ?><img src="<?php echo e($union->manager_image_url); ?>" alt="تصویر فعلی مدیر" class="img-fluid rounded" style="max-height:120px;object-fit:cover"><?php endif; ?></div>
        </div>
        <div class="col-12"><h3 class="h6 mt-2">شبکه‌های اجتماعی</h3></div>
        <?php $__currentLoopData = ['instagram' => 'اینستاگرام', 'telegram' => 'تلگرام', 'whatsapp' => 'واتساپ', 'eitaa' => 'ایتا', 'bale' => 'بله', 'rubika' => 'روبیکا', 'website' => 'وب‌سایت']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-3">
                <label class="form-label" for="social_<?php echo e($key); ?>"><?php echo e($label); ?></label>
                <input class="form-control" id="social_<?php echo e($key); ?>" name="social_links[<?php echo e($key); ?>]" value="<?php echo e($socialLinks[$key] ?? ''); ?>" dir="ltr" type="url">
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div class="col-12"><h3 class="h6 mt-2">امکانات اتحادیه</h3></div>
        <?php $__currentLoopData = $featureFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-3">
                <label class="form-label" for="<?php echo e($field); ?>"><?php echo e($label); ?></label>
                <select class="form-control" id="<?php echo e($field); ?>" name="<?php echo e($field); ?>">
                    <option value="1" <?php if((string) old($field, (int) ($union?->{$field} ?? in_array($field, ['news_enabled', 'announcements_enabled', 'complaint_enabled'], true))) === '1'): echo 'selected'; endif; ?>>فعال</option>
                    <option value="0" <?php if((string) old($field, (int) ($union?->{$field} ?? in_array($field, ['news_enabled', 'announcements_enabled', 'complaint_enabled'], true))) === '0'): echo 'selected'; endif; ?>>غیرفعال</option>
                </select>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="col-12"><h3 class="h6 mt-2">خبرهای اتحادیه</h3></div>
        <div class="col-md-4">
            <label class="form-label" for="news_mode">حالت نمایش خبر</label>
            <select class="form-control" id="news_mode" name="news_mode">
                <?php $__currentLoopData = \App\Models\GuildUnion::newsModeLabels(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($mode); ?>" <?php if(old('news_mode', $union?->news_mode ?? 'auto') === $mode): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label" for="selected_posts">خبرهای انتخابی در حالت دستی</label>
            <select class="form-control js-select2" id="selected_posts" name="selected_posts[]" multiple size="6">
                <?php $__currentLoopData = ($selectablePosts ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($post->id); ?>" <?php if(in_array((string) $post->id, $selectedPostIds, true)): echo 'selected'; endif; ?>><?php echo e($post->title); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <small class="text-muted">این فیلد برای Select2 آماده شده است و در نبود کتابخانه، انتخاب چندگانه مرورگر را نمایش می‌دهد.</small>
        </div>
        <div class="col-12"><h3 class="h6 mt-2">دکمه‌های رئیس اتحادیه</h3></div>
        <div class="col-12 union-dynamic-section" data-section="president-buttons" data-next-index="<?php echo e(count($presidentButtons)); ?>">
            <div data-rows>
                <?php $__currentLoopData = $presidentButtons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $button): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="border rounded p-3 mb-2" data-row><div class="row g-2 align-items-end">
                        <div class="col-md-3"><label class="form-label">عنوان</label><input class="form-control" name="president_buttons[<?php echo e($index); ?>][title]" value="<?php echo e($button['title'] ?? ''); ?>"></div>
                        <div class="col-md-3"><label class="form-label">لینک</label><input class="form-control" name="president_buttons[<?php echo e($index); ?>][url]" value="<?php echo e($button['url'] ?? ''); ?>" dir="ltr"></div>
                        <div class="col-md-2"><label class="form-label">آیکون</label><input class="form-control" name="president_buttons[<?php echo e($index); ?>][icon]" value="<?php echo e($button['icon'] ?? ''); ?>"></div>
                        <div class="col-md-2"><label class="form-label">باز شدن</label><select class="form-control" name="president_buttons[<?php echo e($index); ?>][target]"><option value="_self" <?php if(($button['target'] ?? '_self') === '_self'): echo 'selected'; endif; ?>>همان صفحه</option><option value="_blank" <?php if(($button['target'] ?? '_self') === '_blank'): echo 'selected'; endif; ?>>صفحه جدید</option></select></div>
                        <div class="col-md-2"><label class="form-check"><input class="form-check-input" type="checkbox" name="president_buttons[<?php echo e($index); ?>][is_active]" value="1" <?php if($button['is_active'] ?? true): echo 'checked'; endif; ?>> فعال</label></div>
                    </div></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <button class="btn btn-outline-primary" type="button" data-add-row>افزودن دکمه رئیس</button>
        </div>
        <div class="col-12"><h3 class="h6 mt-2">نرخنامه اتحادیه</h3></div>
        <div class="col-md-4">
            <label class="form-label" for="price_list_mode">حالت نمایش نرخنامه</label>
            <select class="form-control" id="price_list_mode" name="price_list_mode" required>
                <?php $__currentLoopData = \App\Models\GuildUnion::priceListModeLabels(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($mode); ?>" <?php if(old('price_list_mode', $union?->price_list_mode ?? 'table') === $mode): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label" for="price_list_image">عکس نرخنامه</label>
            <input class="form-control" id="price_list_image" name="price_list_image" type="file" accept="image/*">
            <div class="mt-2" data-image-preview="price_list_image"><?php if($union?->price_list_image): ?><img src="<?php echo e($union->price_list_image_url); ?>" alt="عکس نرخنامه فعلی" class="img-fluid rounded" style="max-height:140px;object-fit:cover"><?php endif; ?></div>
        </div>
        <div class="col-12"><h3 class="h6 mt-2">تنظیمات صفحه اتحادیه</h3></div>
        <?php $__currentLoopData = \App\Models\GuildUnion::sectionLabels(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php ($checked = array_key_exists($key, $settings) ? (bool) $settings[$key] : (bool) ($settingDefaults[$key] ?? true)); ?>
            <div class="col-md-3">
                <label class="form-check d-flex align-items-center gap-2" for="settings_<?php echo e($key); ?>">
                    <input class="form-check-input" id="settings_<?php echo e($key); ?>" name="settings[<?php echo e($key); ?>]" type="checkbox" value="1" <?php if($checked): echo 'checked'; endif; ?>>
                    <span><?php echo e($label); ?></span>
                </label>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div class="col-12">
            <?php echo $__env->make('admin.unions._page_sections_form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="is_active">وضعیت</label>
            <select class="form-control" id="is_active" name="is_active">
                <option value="1" <?php if((string) old('is_active', (int) ($union?->is_active ?? true)) === '1'): echo 'selected'; endif; ?>>فعال</option>
                <option value="0" <?php if((string) old('is_active', (int) ($union?->is_active ?? true)) === '0'): echo 'selected'; endif; ?>>غیرفعال</option>
            </select>
        </div>
        <div class="col-md-4"><label class="form-label" for="sort_order">ترتیب نمایش</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="<?php echo e(old('sort_order', $union?->sort_order ?? 0)); ?>"></div>
        <div class="col-md-4"><label class="form-label" for="meta_title">عنوان متا</label><input class="form-control" id="meta_title" name="meta_title" value="<?php echo e(old('meta_title', $union?->meta_title)); ?>"></div>
        <div class="col-md-6"><label class="form-label" for="meta_description">توضیحات متا</label><input class="form-control" id="meta_description" name="meta_description" value="<?php echo e(old('meta_description', $union?->meta_description)); ?>"></div>
        <div class="col-md-6"><label class="form-label" for="meta_keywords">کلیدواژه‌های متا</label><input class="form-control" id="meta_keywords" name="meta_keywords" value="<?php echo e(old('meta_keywords', $union?->meta_keywords)); ?>"></div>
    </div>
</div>

<div class="mt-3 d-flex gap-2">
    <button class="admin-primary-btn" type="submit">ذخیره اتحادیه</button>
    <a class="admin-secondary-btn" href="<?php echo e(route('admin.unions.index')); ?>">انصراف</a>
</div>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/unions/_form.blade.php ENDPATH**/ ?>