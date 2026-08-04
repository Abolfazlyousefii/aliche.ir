<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="title">عنوان منو</label>
        <input class="form-control" id="title" name="title" value="<?php echo e(old('title', $menu?->title)); ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="location">محل نمایش</label>
        <select class="form-control" id="location" name="location" required>
            <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($location); ?>" <?php if(old('location', $menu?->location) === $location): echo 'selected'; endif; ?>><?php echo e($location); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="is_active">وضعیت</label>
        <select class="form-control" id="is_active" name="is_active" required>
            <option value="1" <?php if((string) old('is_active', $menu?->is_active ?? 1) === '1'): echo 'selected'; endif; ?>>فعال</option>
            <option value="0" <?php if((string) old('is_active', $menu?->is_active ?? 1) === '0'): echo 'selected'; endif; ?>>غیرفعال</option>
        </select>
    </div>
</div>

<div class="admin-form-actions">
    <button class="admin-primary-btn" type="submit">ذخیره منو</button>
    <a class="admin-secondary-btn" href="<?php echo e(route('admin.menus.index')); ?>">انصراف</a>
</div>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/menus/_form.blade.php ENDPATH**/ ?>