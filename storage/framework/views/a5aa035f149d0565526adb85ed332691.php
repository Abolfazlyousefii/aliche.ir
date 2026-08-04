<?php
    $selectedStatus = old('status', $post?->status ?? 'draft');
    $selectedType = old('type', $post?->type ?? 'news');
    $selectedHomepagePosition = old('homepage_position', $post?->homepage_position ?? 'normal');
    $keywordSource = old('meta_keywords', $post?->meta_keywords ?? '');
    $metaKeywords = collect(is_array($keywordSource) ? $keywordSource : preg_split('/[,\n،]+/u', (string) $keywordSource))
        ->map(fn ($keyword) => trim((string) $keyword))
        ->filter()
        ->values();
?>

<div class="admin-panel-card">
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label" for="title">عنوان خبر</label>
            <input class="form-control" id="title" name="title" value="<?php echo e(old('title', $post?->title)); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="slug">اسلاگ</label>
            <input class="form-control" id="slug" name="slug" value="<?php echo e(old('slug', $post?->slug)); ?>" dir="rtl">
            <small class="text-muted d-block mt-1">نشانی نهایی: <?php echo e(url('/news')); ?>/<span id="slugPreview"><?php echo e(old('slug', $post?->slug) ?: 'اسلاگ-خبر'); ?></span></small>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="category_id">دسته‌بندی</label>
            <select class="form-control" id="category_id" name="category_id">
                <option value="">بدون دسته‌بندی</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category->id); ?>" <?php if((string) old('category_id', $post?->category_id) === (string) $category->id): echo 'selected'; endif; ?>><?php echo e($category->title); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <small class="text-muted">انتخاب دسته‌بندی اختیاری است.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="union_id">اتحادیه مرتبط</label>
            <select class="form-control js-union-select" id="union_id" name="union_id" data-placeholder="خبر عمومی / بدون اتحادیه">
                <option value="">خبر عمومی / بدون اتحادیه</option>
                <?php $__currentLoopData = $unions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $union): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($union->id); ?>" <?php if((string) old('union_id', $post?->union_id) === (string) $union->id): echo 'selected'; endif; ?>><?php echo e($union->display_title); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="type">نوع محتوا</label>
            <select class="form-control" id="type" name="type" required>
                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type); ?>" <?php if($selectedType === $type): echo 'selected'; endif; ?>><?php echo e($typeLabels[$type] ?? $type); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="status">وضعیت</label>
            <select class="form-control" id="status" name="status" required>
                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status); ?>" <?php if($selectedStatus === $status): echo 'selected'; endif; ?>><?php echo e($statusLabels[$status] ?? $status); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <small class="text-muted">کاربران محتواگذار فقط می‌توانند draft یا pending انتخاب کنند.</small>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="published_at">تاریخ انتشار</label>
            <input class="form-control" id="published_at" name="published_at" type="text" data-jalali-datepicker value="<?php echo e(jalali_input_datetime(old('published_at', $post?->published_at ?? now()))); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="sort_order">ترتیب نمایش</label>
            <input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="<?php echo e(old('sort_order', $post?->sort_order ?? 0)); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="homepage_position">جایگاه نمایش در صفحه اصلی</label>
            <select class="form-control" id="homepage_position" name="homepage_position">
                <?php $__currentLoopData = $homepagePositionLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if($selectedHomepagePosition === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <small class="text-muted">هر خبر فقط یکی از جایگاه‌های صفحه اصلی را می‌گیرد و در آرشیو عمومی نیز نمایش داده می‌شود.</small>
        </div>
        <div class="col-12">
            <label class="form-label" for="excerpt">خلاصه خبر</label>
            <textarea class="form-control js-rich-editor" id="excerpt" name="excerpt" rows="3"><?php echo e(old('excerpt', $post?->excerpt)); ?></textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="body">متن کامل خبر</label>
            <textarea class="form-control js-rich-editor" id="body" name="body" rows="12"><?php echo e(old('body', $post?->body)); ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="featured_image">تصویر شاخص</label>
            <input class="form-control" id="featured_image" name="featured_image" type="file" accept="image/*" data-skip-media-picker>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="featured_media_id">انتخاب از کتابخانه با پیش‌نمایش</button>
            <select class="form-control mt-2" name="featured_media_id" id="featured_media_id">
                <option value="">انتخاب از کتابخانه رسانه</option>
                <?php $__currentLoopData = $mediaItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($media->id); ?>" data-url="<?php echo e($media->url); ?>" <?php if((string) old('featured_media_id', $post?->featured_media_id) === (string) $media->id): echo 'selected'; endif; ?>><?php echo e($media->title ?: $media->original_name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <small><a href="<?php echo e(route('admin.media.index')); ?>" target="_blank">مدیریت و آپلود در کتابخانه رسانه</a></small>
            <div class="mt-2" id="featuredPreview">
                <?php if($post?->featured_image_url): ?>
                    <img src="<?php echo e($post->featured_image_url); ?>" alt="تصویر شاخص" class="img-fluid rounded" style="max-height:160px;object-fit:cover">
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="gallery_images">گالری تصاویر</label>
            <input class="form-control" id="gallery_images" name="gallery_images[]" type="file" accept="image/*" multiple data-skip-media-picker>
            <div id="galleryCaptionFields" class="mt-2">
                <input class="form-control mb-2" name="gallery_captions[]" placeholder="کپشن تصویر ۱ (اختیاری)">
            </div>
            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="gallery_media_ids" data-media-select-multiple="true">انتخاب تصاویر از کتابخانه با پیش‌نمایش</button>
            <select class="form-control mt-2" name="gallery_media_ids[]" id="gallery_media_ids" multiple size="6">
                <?php ($selectedGalleryMedia = collect(old('gallery_media_ids', $post?->mediaGallery?->pluck('id')->all() ?? []))->map(fn($id) => (string) $id)->all()); ?>
                <?php $__currentLoopData = $mediaItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($media->id); ?>" data-url="<?php echo e($media->url); ?>" <?php if(in_array((string) $media->id, $selectedGalleryMedia, true)): echo 'selected'; endif; ?>><?php echo e($media->title ?: $media->original_name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <small class="text-muted">برای هر تصویر انتخاب‌شده یک فیلد کپشن ساخته می‌شود یا از کتابخانه رسانه چند تصویر انتخاب کنید.</small>
            <div class="row g-2 mt-2" id="galleryMediaPreview"></div>
        </div>
        <?php if($post?->galleries?->isNotEmpty()): ?>
            <div class="col-12">
                <label class="form-label">تصاویر فعلی گالری</label>
                <div class="row g-3">
                    <?php $__currentLoopData = $post->galleries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gallery): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-3">
                            <div class="border rounded p-2 h-100">
                                <img src="<?php echo e(Storage::url($gallery->image)); ?>" alt="<?php echo e($gallery->caption); ?>" class="img-fluid rounded mb-2">
                                <p class="small mb-2"><?php echo e($gallery->caption ?: 'بدون کپشن'); ?></p>
                                <label class="small"><input type="checkbox" name="delete_gallery[]" value="<?php echo e($gallery->id); ?>"> حذف این تصویر</label>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="col-12">
            <label class="form-label" for="rejected_reason">دلیل رد خبر</label>
            <textarea class="form-control" id="rejected_reason" name="rejected_reason" rows="3"><?php echo e(old('rejected_reason', $post?->rejected_reason)); ?></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="meta_title">عنوان متا</label>
            <input class="form-control" id="meta_title" name="meta_title" value="<?php echo e(old('meta_title', $post?->meta_title)); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="meta_description">توضیحات متا</label>
            <input class="form-control" id="meta_description" name="meta_description" value="<?php echo e(old('meta_description', $post?->meta_description)); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="meta_keywords">کلیدواژه‌های متا</label>
            <select class="form-control" id="meta_keywords" name="meta_keywords[]" multiple size="5">
                <?php $__currentLoopData = $metaKeywords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $keyword): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($keyword); ?>" selected><?php echo e($keyword); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <small class="text-muted d-block mt-1">کلیدواژه‌ها را با Enter یا ویرگول اضافه کنید.</small>
        </div>
    </div>
</div>

<div class="mt-3 d-flex gap-2">
    <button class="admin-primary-btn" type="submit">ذخیره خبر</button>
    <a class="admin-secondary-btn" href="<?php echo e(route('admin.posts.index')); ?>">انصراف</a>
</div>

<?php $__env->startPush('scripts'); ?>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
const titleInput=document.getElementById('title'), slugInput=document.getElementById('slug'), slugPreview=document.getElementById('slugPreview'); let slugTouched=!!slugInput?.value; const cleanSlug=(value)=>value.trim().replace(/[\s_]+/g,'-').replace(/[^\u0600-\u06FF\p{L}\p{N}-]+/gu,'').replace(/-+/g,'-').replace(/^-|-$/g,'').toLowerCase(); const updateSlugPreview=()=>{ if(slugPreview) slugPreview.textContent=slugInput?.value || 'اسلاگ-خبر'; }; slugInput?.addEventListener('input',()=>{slugTouched=true; slugInput.value=cleanSlug(slugInput.value); updateSlugPreview();}); titleInput?.addEventListener('input',()=>{ if(slugTouched) return; slugInput.value=cleanSlug(titleInput.value); updateSlugPreview(); }); updateSlugPreview();
document.querySelectorAll('.js-union-select').forEach((el) => new TomSelect(el, {create: false, allowEmptyOption: true, sortField: {field: 'text', direction: 'asc'}, placeholder: el.dataset.placeholder || 'جستجوی اتحادیه'}));
new TomSelect('#featured_media_id', {create: false, allowEmptyOption: true});
new TomSelect('#gallery_media_ids', {plugins: ['remove_button'], create: false});
new TomSelect('#meta_keywords', {plugins: ['remove_button'], persist: false, create: true, createOnBlur: true, delimiter: ',', placeholder: 'مثلا: اصناف, گرگان, خبر'});
const galleryInput = document.getElementById('gallery_images');
const galleryCaptionFields = document.getElementById('galleryCaptionFields');
galleryInput?.addEventListener('change', () => {
    galleryCaptionFields.innerHTML = '';
    Array.from(galleryInput.files).forEach((file, index) => {
        const input = document.createElement('input');
        input.className = 'form-control mb-2';
        input.name = 'gallery_captions[]';
        input.placeholder = `کپشن تصویر ${index + 1}: ${file.name}`;
        galleryCaptionFields.appendChild(input);
    });
});
const featuredSelect = document.getElementById('featured_media_id');
const featuredPreview = document.getElementById('featuredPreview');
featuredSelect?.addEventListener('change', () => {
    const url = featuredSelect.selectedOptions[0]?.dataset.url;
    featuredPreview.innerHTML = url ? `<img src="${url}" alt="تصویر شاخص" class="img-fluid rounded" style="max-height:160px;object-fit:cover">` : '';
});
const gallerySelect = document.getElementById('gallery_media_ids');
const galleryPreview = document.getElementById('galleryMediaPreview');
const renderGalleryPreview = () => {
    galleryPreview.innerHTML = '';
    Array.from(gallerySelect?.selectedOptions || []).forEach((option) => {
        if (!option.dataset.url) return;
        galleryPreview.insertAdjacentHTML('beforeend', `<div class="col-3"><img src="${option.dataset.url}" alt="${option.text}" class="img-fluid rounded" style="height:90px;width:100%;object-fit:cover"></div>`);
    });
};
gallerySelect?.addEventListener('change', renderGalleryPreview);
renderGalleryPreview();
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/posts/_form.blade.php ENDPATH**/ ?>