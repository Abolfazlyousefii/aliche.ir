<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<?php
    $settings = app(\App\Services\SettingService::class);
    $siteTitle = $settings->get('site.site_title', 'اتاق اصناف مرکز استان گلستان');
    $favicon = $settings->get('site.site_favicon') ? image_url($settings->get('site.site_favicon'), '') : null;
?>
<title><?php echo $__env->yieldContent('title', $siteTitle); ?></title>
<meta content="<?php echo $__env->yieldContent('meta_description', 'اتاق اصناف مرکز استان گلستان'); ?>" name="description"/>
<?php if($favicon): ?><link rel="icon" href="<?php echo e($favicon); ?>"><?php endif; ?>
<?php if (! empty(trim($__env->yieldContent('canonical')))): ?><link rel="canonical" href="<?php echo $__env->yieldContent('canonical'); ?>"/><?php endif; ?>
<?php echo $__env->make('frontend.partials.styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
<?php echo $__env->make('frontend.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('frontend.partials.market-ticker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->yieldContent('content'); ?>
<?php echo $__env->make('frontend.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->yieldContent('after_footer'); ?>
<?php echo $__env->make('frontend.partials.scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH E:\laragon\www\aliche.ir\resources\views/frontend/layouts/app.blade.php ENDPATH**/ ?>