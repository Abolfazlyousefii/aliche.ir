<header class="admin-header">
    <div class="admin-header-start">
        <button class="admin-menu-toggle" type="button" aria-label="باز کردن منوی مدیریت" data-admin-sidebar-toggle>
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div>
            <p class="admin-eyebrow">سامانه مدیریت محتوا</p>
            <h1><?php echo $__env->yieldContent('title', 'داشبورد مدیریت'); ?></h1>
        </div>
    </div>

    <div class="admin-header-end">
        <div class="admin-search" role="search">
            <span>🔎</span>
            <input type="search" placeholder="جستجو در پنل..." aria-label="جستجو در پنل مدیریت">
        </div>
        <a class="admin-view-site" href="<?php echo e(route('admin.messages.inbox')); ?>">
            پیام‌ها
            <?php if(($unreadMessagesCount ?? 0) > 0): ?>
                <span class="badge bg-danger"><?php echo e(fa_number($unreadMessagesCount)); ?></span>
            <?php endif; ?>
        </a>
        <a class="admin-view-site" href="<?php echo e(route('home')); ?>" target="_blank" rel="noopener">مشاهده سایت</a>
        <div class="admin-user-card">
            <div class="admin-avatar"><?php echo e(mb_substr(auth()->user()?->name ?? 'م', 0, 1)); ?></div>
            <div>
                <strong><?php echo e(auth()->user()?->name ?? 'مدیر سامانه'); ?></strong>
                <span>خوش آمدید</span>
            </div>
        </div>
        <form class="admin-logout-form" action="<?php echo e(route('logout')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button class="admin-secondary-btn" type="submit">خروج</button>
        </form>
    </div>
</header>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/partials/header.blade.php ENDPATH**/ ?>