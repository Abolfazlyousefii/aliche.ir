<?php $__env->startSection('title', 'داشبورد مدیریت'); ?>

<?php $__env->startSection('content'); ?>
<section class="admin-welcome-card">
    <div>
        <p class="admin-eyebrow">نمای کلی امروز</p>
        <h2>به پنل مدیریت اتاق اصناف مرکز استان گلستان خوش آمدید</h2>
        <p>از این بخش می‌توانید وضعیت محتوای سایت، پیام‌ها، شکایات و فعالیت‌های مهم را به‌صورت خلاصه مشاهده کنید.</p>
    </div>
    <div class="admin-date-card">
        <span>امروز</span>
        <strong><?php echo e(jalali_text_date(now('Asia/Tehran'))); ?></strong>
    </div>
</section>

<section class="admin-stats-grid" aria-label="آمار کلی پنل مدیریت">
    <article class="admin-stat-card stat-warning">
        <div class="admin-stat-icon">✅</div>
        <div>
            <span>محتواهای در انتظار تایید</span>
            <strong><?php echo e(number_format($pendingApprovalsCount)); ?></strong>
        </div>
    </article>

    <article class="admin-stat-card stat-danger">
        <div class="admin-stat-icon">📨</div>
        <div>
            <span>شکایت‌های جدید</span>
            <strong><?php echo e(number_format($openComplaintsCount)); ?></strong>
        </div>
    </article>

    <article class="admin-stat-card stat-primary">
        <div class="admin-stat-icon">🏢</div>
        <div>
            <span>تعداد اتحادیه‌ها</span>
            <strong><?php echo e(number_format($unionsCount)); ?></strong>
        </div>
    </article>

    <article class="admin-stat-card stat-success">
        <div class="admin-stat-icon">🤝</div>
        <div>
            <span>تعداد اعضا</span>
            <strong><?php echo e(number_format($membersCount)); ?></strong>
        </div>
    </article>

    <article class="admin-stat-card stat-info">
        <div class="admin-stat-icon">☎️</div>
        <div>
            <span>پیام‌های تماس خوانده‌نشده</span>
            <strong><?php echo e(number_format($unreadContactMessagesCount)); ?></strong>
        </div>
    </article>

    <article class="admin-stat-card stat-purple">
        <div class="admin-stat-icon">💬</div>
        <div>
            <span>پیامک‌های ارسال‌شده</span>
            <strong><?php echo e(number_format($sentSmsRecipientCount)); ?></strong>
        </div>
    </article>
</section>

<section class="admin-dashboard-grid">
    <div class="admin-panel-card">
        <div class="admin-panel-header">
            <h3>کارهای پیشنهادی امروز</h3>
            <span>اولویت‌دار</span>
        </div>
        <ul class="admin-task-list">
            <?php $__currentLoopData = $dashboardTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><span></span><?php echo e($task); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>


    <div class="admin-panel-card">
        <div class="admin-panel-header">
            <h3>محتواهای در انتظار تایید</h3>
            <a href="<?php echo e(route('admin.pending_approvals.index')); ?>" class="btn btn-sm btn-outline-primary">مشاهده همه</a>
        </div>
        <?php if($pendingApprovals->isNotEmpty()): ?>
            <div class="admin-status-list">
                <?php $__currentLoopData = $pendingApprovals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <span><?php echo e($item['label']); ?> - <?php echo e($item['title']); ?></span>
                        <?php if($item['show_url']): ?>
                            <a href="<?php echo e($item['show_url']); ?>" class="btn btn-sm btn-light">مشاهده</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">در حال حاضر محتوایی برای تایید وجود ندارد.</p>
        <?php endif; ?>
    </div>



    <div class="admin-panel-card">
        <div class="admin-panel-header">
            <h3>اطلاعیه‌های خصوصی</h3>
            <span>ویژه کاربران پنل</span>
        </div>
        <?php if($privateAnnouncements->isNotEmpty()): ?>
            <div class="admin-status-list">
                <?php $__currentLoopData = $privateAnnouncements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <span><?php echo e($announcement->title); ?> <?php if($announcement->union): ?> - <?php echo e($announcement->union->display_title); ?> <?php endif; ?></span>
                        <small><?php echo e(jalali_datetime($announcement->published_at) ?: jalali_datetime($announcement->starts_at)); ?></small>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">اطلاعیه خصوصی جدیدی برای شما ثبت نشده است.</p>
        <?php endif; ?>
    </div>

    <div class="admin-panel-card">
        <div class="admin-panel-header">
            <h3>آخرین وضعیت سامانه</h3>
            <span>به‌روزرسانی روزانه</span>
        </div>
        <div class="admin-status-list">
            <div><span>وضعیت سایت</span><strong class="<?php echo e($systemStatus['site'] === 'فعال' ? 'text-success' : 'text-warning'); ?>"><?php echo e($systemStatus['site']); ?></strong></div>
            <div><span>وضعیت پایگاه داده</span><strong class="<?php echo e($systemStatus['database'] === 'متصل' ? 'text-success' : 'text-danger'); ?>"><?php echo e($systemStatus['database']); ?></strong></div>
            <div><span>وضعیت پیامک</span><strong class="<?php echo e($systemStatus['sms'] === 'آخرین ارسال موفق' ? 'text-success' : 'text-warning'); ?>"><?php echo e($systemStatus['sms']); ?></strong></div>
            <div><span>آخرین گزارش پیامک</span><strong><?php echo e($systemStatus['latest_sms'] ? jalali_datetime($systemStatus['latest_sms']) : 'ثبت نشده'); ?></strong></div>
            <div><span>محتوای منتشرشده این ماه</span><strong><?php echo e(number_format($systemStatus['published_this_month'])); ?> مورد</strong></div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>