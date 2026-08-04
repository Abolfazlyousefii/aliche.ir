<?php
    $settings = app(\App\Services\SettingService::class);
    $footerItems = app(\App\Services\MenuService::class)->items('footer');
    $logo = image_url($settings->get('footer.footer_logo'), 'assets/img/asnaf-footer-mark.svg');
    $description = $settings->get('footer.footer_description', $settings->get('footer.description', 'اتاق اصناف مرکز استان گلستان به عنوان نماینده جامعه صنفی استان، پشتیبان کسب‌وکارهای صنفی، ناظر بر فعالیت اتحادیه‌های صنفی و تسهیل‌گر تعامل با دستگاه‌های اجرایی و نظارتی است.'));
    $phone = fa_number($settings->get('site.phone', '۰۱۷-۳۲۱۵۲۹۱۲'));
    $address = $settings->get('site.address', 'گرگان، خیابان مطهری جنوبی، روبروی پمپ بنزین، ساختمان اتاق اصناف');
    $email = $settings->get('site.email', 'info@asnaf-gorgan.ir');
    $copyright = $settings->get('footer.copyright_text', $settings->get('footer.copyright', 'تمام حقوق مادی و معنوی این وبسایت متعلق به اتاق اصناف مرکز استان گلستان می‌باشد'));
    $socials = collect($settings->get('footer.footer_social_links', $settings->get('site.social_links', [])));
    $columns = collect($settings->get('footer.footer_columns', []));
    $contactInfo = collect($settings->get('footer.footer_contact_info', []));
    $quickFallbacks = collect([
        ['title' => 'صفحه اصلی', 'url' => route('home')],
        ['title' => 'آرشیو اخبار', 'url' => route('posts.index')],
        ['title' => 'اتحادیه‌های صنفی', 'url' => route('guilds.index')],
        ['title' => 'سامانه خدمات صنفی', 'url' => route('systems.index')],
        ['title' => 'گالری تصاویر', 'url' => route('galleries.index')],
        ['title' => 'گردشگری', 'url' => route('tourism.index')],
    ]);
    if ($columns->isEmpty()) {
        $columns = collect([
            ['title' => 'دسترسی سریع', 'links' => $footerItems->take(8)->map(fn($item) => ['title' => $item->title, 'url' => $item->resolved_url, 'target' => $item->target])->values()->all() ?: $quickFallbacks->all()],
        ]);
    }
?>
<footer class="site-footer">
<div class="site-container">
<div class="footer-main">
<div class="footer-col footer-brand-col">
<img alt="اتاق اصناف مرکز استان گلستان" src="<?php echo e($logo); ?>"/>
<div><?php echo $description; ?></div>
</div>
<?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="footer-col">
<h4><?php echo e($column['title'] ?? 'لینک‌های مفید'); ?></h4>
<ul>
<?php $__currentLoopData = ($column['links'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<li><a href="<?php echo e($item['url'] ?? '#'); ?>" target="<?php echo e($item['target'] ?? '_self'); ?>"><?php echo e($item['icon'] ?? ''); ?> <?php echo e($item['title'] ?? ''); ?></a></li>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<div class="footer-col">
<h4>اطلاعات تماس</h4>
<?php if($contactInfo->isNotEmpty()): ?>
<?php $__currentLoopData = $contactInfo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="footer-contact-item"><span class="fc-icon"><?php echo e($contact['icon'] ?? '•'); ?></span><span><?php echo fa_number($contact['value'] ?? ''); ?></span></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
<div class="footer-contact-item"><span class="fc-icon">📍</span><span><?php echo e($address); ?></span></div>
<div class="footer-contact-item"><span class="fc-icon">📞</span><span><?php echo $phone; ?></span></div>
<div class="footer-contact-item"><span class="fc-icon">✉️</span><span><?php echo e($email); ?></span></div>
<?php endif; ?>
</div>
</div>
<div class="footer-divider"></div>
<div class="footer-orgs">
<?php $__empty_1 = true; $__currentLoopData = $footerItems->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<a href="<?php echo e($item->resolved_url); ?>" target="<?php echo e($item->target); ?>"><?php echo e($item->icon); ?> <?php echo e($item->title); ?></a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<?php $__currentLoopData = $quickFallbacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<a href="<?php echo e($item['url']); ?>"><?php echo e($item['title']); ?></a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
</div>
<div class="footer-divider"></div>
<div class="footer-bottom">
<div class="footer-social">
<?php $__currentLoopData = $socials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $social): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
    $social = is_array($social) ? $social : ['title' => $key, 'url' => $social, 'icon' => mb_substr((string) $key, 0, 1)];
?>
<?php if(filled($social['url'] ?? null)): ?>
<a href="<?php echo e($social['url']); ?>" aria-label="<?php echo e($social['title'] ?? $key); ?>" target="<?php echo e($social['target'] ?? '_blank'); ?>" rel="noopener"><?php echo e($social['icon'] ?? mb_substr((string) ($social['title'] ?? $key), 0, 1)); ?></a>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<div class="footer-copy"><?php echo e(fa_number($copyright)); ?></div>
</div>
</div>
</footer>
<?php /**PATH E:\laragon\www\aliche.ir\resources\views/frontend/partials/footer.blade.php ENDPATH**/ ?>