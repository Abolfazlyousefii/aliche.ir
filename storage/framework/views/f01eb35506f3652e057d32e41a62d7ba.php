<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'پنل مدیریت'); ?> | اتاق اصناف مرکز استان گلستان</title>
    <link href="https://cdn.jsdelivr.net" rel="preconnect">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css" rel="stylesheet">
    <link href="<?php echo e(asset('assets/admin/css/admin.css')); ?>?v=<?php echo e(filemtime(public_path('assets/admin/css/admin.css'))); ?>" rel="stylesheet">
</head>
<body>
    <div class="admin-shell">
        <?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="admin-main">
            <?php echo $__env->make('admin.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <main class="admin-content">
                <?php echo $__env->make('admin.partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->yieldContent('content'); ?>
            </main>

            <?php echo $__env->make('admin.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>

    <div class="admin-backdrop" data-admin-sidebar-close></div>

    <div class="modal fade" id="adminDeleteModal" tabindex="-1" aria-labelledby="adminDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content admin-confirm-modal">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="adminDeleteModalLabel">تایید عملیات حذف</h2>
                    <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">این عملیات قابل بازگشت نیست. آیا از حذف این مورد مطمئن هستید؟</p>
                    <small class="text-muted" data-admin-delete-message></small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-secondary-btn" data-bs-dismiss="modal">انصراف</button>
                    <button type="button" class="admin-danger-btn" data-admin-delete-confirm>بله، حذف شود</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>window.adminRichTextUploadUrl = <?php echo json_encode(route('admin.rich_text.upload'), 15, 512) ?>; window.adminMediaPickerUrl = <?php echo json_encode(route('admin.media.picker'), 15, 512) ?>; window.adminMediaUploadUrl = <?php echo json_encode(route('admin.media.store'), 15, 512) ?>;</script>
    <script src="<?php echo e(asset('assets/admin/js/admin.js')); ?>?v=<?php echo e(filemtime(public_path('assets/admin/js/admin.js'))); ?>"></script>
    <script src="<?php echo e(asset('assets/admin/js/rich-editor.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/layouts/app.blade.php ENDPATH**/ ?>