<?php
    $savedLinkMode = $item?->route_name ? 'internal' : ($item?->type === 'custom' || ! $item ? 'manual' : 'content');
    $linkMode = old('link_mode', $savedLinkMode);
?>

<div class="row g-3" data-menu-item-form>
    <div class="col-md-6">
        <label class="form-label" for="title">عنوان</label>
        <input class="form-control" id="title" name="title" value="<?php echo e(old('title', $item?->title)); ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="target">باز شدن لینک</label>
        <select class="form-control" id="target" name="target">
            <option value="_self" <?php if(old('target', $item?->target ?? '_self') === '_self'): echo 'selected'; endif; ?>>همین پنجره</option>
            <option value="_blank" <?php if(old('target', $item?->target) === '_blank'): echo 'selected'; endif; ?>>تب جدید</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="link_mode">روش لینک‌دهی</label>
        <select class="form-control" id="link_mode" name="link_mode" required data-link-mode>
            <option value="manual" <?php if($linkMode === 'manual'): echo 'selected'; endif; ?>>وارد کردن لینک</option>
            <option value="internal" <?php if($linkMode === 'internal'): echo 'selected'; endif; ?>>انتخاب صفحه داخلی</option>
            <option value="content" <?php if($linkMode === 'content'): echo 'selected'; endif; ?>>اتصال به محتوا (پیشرفته)</option>
        </select>
    </div>

    <div class="col-12" data-link-fields="manual">
        <label class="form-label" for="url">آدرس لینک</label>
        <input class="form-control" dir="ltr" id="url" name="url" value="<?php echo e(old('url', $item?->url)); ?>" placeholder="https://example.com یا /pages/about">
        <small class="text-muted">با انتخاب این روش، همین آدرس جایگزین لینک قبلی می‌شود.</small>
    </div>
    <div class="col-12" data-link-fields="internal">
        <label class="form-label" for="route_name">صفحه داخلی</label>
        <select class="form-control" id="route_name" name="route_name">
            <option value="">یک صفحه را انتخاب کنید</option>
            <?php $__currentLoopData = $internalRoutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $routeName => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($routeName); ?>" <?php if(old('route_name', $item?->route_name) === $routeName): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <div class="col-12" data-link-fields="content">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="type">نوع محتوا</label>
                <select class="form-control" id="type" name="type" required>
                    <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type); ?>" <?php if(old('type', $item?->type ?? 'custom') === $type): echo 'selected'; endif; ?>><?php echo e($type); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-4"><label class="form-label" for="reference_type">نوع مرجع</label><input class="form-control" dir="ltr" id="reference_type" name="reference_type" value="<?php echo e(old('reference_type', $item?->reference_type)); ?>" placeholder="Page, Post, Union..."></div>
            <div class="col-md-4"><label class="form-label" for="reference_id">شناسه مرجع</label><input class="form-control" dir="ltr" id="reference_id" name="reference_id" type="number" min="1" value="<?php echo e(old('reference_id', $item?->reference_id)); ?>"></div>
        </div>
    </div>

    <div class="col-md-4"><label class="form-label" for="parent_id">انتخاب والد</label><select class="form-control" id="parent_id" name="parent_id"><option value="">بدون والد</option><?php $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($parent->id); ?>" <?php if((string) old('parent_id', $item?->parent_id) === (string) $parent->id): echo 'selected'; endif; ?>><?php echo e($parent->title); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
    <div class="col-md-4"><label class="form-label" for="icon">آیکون</label><input class="form-control" id="icon" name="icon" value="<?php echo e(old('icon', $item?->icon)); ?>" placeholder="مثلاً 📰 یا کلاس آیکون"></div>
    <div class="col-md-2"><label class="form-label" for="sort_order">ترتیب</label><input class="form-control" dir="ltr" id="sort_order" name="sort_order" type="number" min="0" value="<?php echo e(old('sort_order', $item?->sort_order ?? 0)); ?>"></div>
    <div class="col-md-2"><label class="form-label" for="is_active">وضعیت</label><select class="form-control" id="is_active" name="is_active"><option value="1" <?php if((string) old('is_active', $item?->is_active ?? 1) === '1'): echo 'selected'; endif; ?>>فعال</option><option value="0" <?php if((string) old('is_active', $item?->is_active ?? 1) === '0'): echo 'selected'; endif; ?>>غیرفعال</option></select></div>
</div>
<div class="admin-form-actions"><button class="admin-primary-btn" type="submit">ذخیره تغییرات</button><a class="admin-secondary-btn" href="<?php echo e(route('admin.menus.show', $menu)); ?>">انصراف</a></div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-menu-item-form]');
    const mode = form?.querySelector('[data-link-mode]');
    if (!form || !mode) return;

    const updateFields = () => {
        form.querySelectorAll('[data-link-fields]').forEach((section) => {
            const active = section.dataset.linkFields === mode.value;
            section.hidden = !active;
            section.querySelectorAll('input, select').forEach((field) => field.disabled = !active);
        });
    };

    mode.addEventListener('change', updateFields);
    updateFields();
});
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/menus/items/_form.blade.php ENDPATH**/ ?>