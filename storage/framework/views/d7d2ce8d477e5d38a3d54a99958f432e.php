<?php $__env->startSection('title', 'ویرایش تبلیغ'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">تبلیغات</p><h2>ویرایش <?php echo e($advertisement->title); ?></h2></div>
    <a class="admin-secondary-btn" href="<?php echo e(route('admin.advertisements.show', $advertisement)); ?>">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="<?php echo e(route('admin.advertisements.update', $advertisement)); ?>" method="POST" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="title">عنوان</label><input class="form-control" id="title" name="title" value="<?php echo e(old('title', $advertisement->title)); ?>" required></div>
        <div class="col-md-6"><label class="form-label" for="position_id">جایگاه</label><select class="form-control" id="position_id" name="position_id" required><option value="">انتخاب جایگاه</option><?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($position->id); ?>" <?php if((string) old('position_id', $advertisement->position_id) === (string) $position->id): echo 'selected'; endif; ?>><?php echo e($position->title); ?> (<?php echo e($position->key); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div class="col-md-6"><label class="form-label" for="image">تصویر تبلیغ جدید</label><input class="form-control" id="image" name="image" type="file" accept="image/*"><?php if($advertisement->image): ?><small><a href="<?php echo e($advertisement->image_url); ?>" target="_blank">مشاهده تصویر فعلی</a></small><?php endif; ?></div>
        <div class="col-md-6"><label class="form-label" for="link">لینک</label><input class="form-control" id="link" name="link" value="<?php echo e(old('link', $advertisement->link)); ?>" dir="ltr"></div>
        <div class="col-md-4"><label class="form-label" for="target">نحوه باز شدن لینک</label><select class="form-control" id="target" name="target" required><?php $__currentLoopData = $targetLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(old('target', $advertisement->target) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div class="col-md-4"><label class="form-label" for="starts_at">زمان شروع</label><input class="form-control" id="starts_at" name="starts_at" type="text" data-jalali-datepicker value="<?php echo e(jalali_input_datetime(old('starts_at', $advertisement->starts_at))); ?>" required></div>
        <div class="col-md-4"><label class="form-label" for="expires_at">زمان پایان</label><input class="form-control" id="expires_at" name="expires_at" type="text" data-jalali-datepicker value="<?php echo e(jalali_input_datetime(old('expires_at', $advertisement->expires_at))); ?>"></div>
        <div class="col-md-3"><label class="form-label" for="sort_order">ترتیب نمایش</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" value="<?php echo e(old('sort_order', $advertisement->sort_order)); ?>"></div>
        <div class="col-md-3"><label class="form-label" for="views_count">تعداد نمایش</label><input class="form-control" id="views_count" name="views_count" type="number" min="0" value="<?php echo e(old('views_count', $advertisement->views_count)); ?>"></div>
        <div class="col-md-3"><label class="form-label" for="clicks_count">تعداد کلیک</label><input class="form-control" id="clicks_count" name="clicks_count" type="number" min="0" value="<?php echo e(old('clicks_count', $advertisement->clicks_count)); ?>"></div>
        <div class="col-md-3"><label class="form-label" for="is_active">فعال</label><select class="form-control" id="is_active" name="is_active"><option value="1" <?php if((string) old('is_active', (int) $advertisement->is_active) === '1'): echo 'selected'; endif; ?>>فعال</option><option value="0" <?php if((string) old('is_active', (int) $advertisement->is_active) === '0'): echo 'selected'; endif; ?>>غیرفعال</option></select></div>
    </div>
    <div class="mt-3 d-flex gap-2"><button class="admin-primary-btn" type="submit">ذخیره تغییرات</button><a class="admin-secondary-btn" href="<?php echo e(route('admin.advertisements.show', $advertisement)); ?>">انصراف</a></div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/advertisements/edit.blade.php ENDPATH**/ ?>