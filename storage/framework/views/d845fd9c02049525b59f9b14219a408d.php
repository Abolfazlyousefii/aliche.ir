<?php $__env->startSection('title', ($union->display_title ?? 'اتحادیه صنفی').' | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', $union->meta_description ?? $union->short_description ?? 'اطلاعات اتحادیه صنفی، اعضا، کمیسیون‌ها، اخبار، اطلاعیه‌ها و راه‌های تماس'); ?>

<?php
    $defaultImage = asset('assets/img/asnaf-gorgan-default.jpg');
    $assetImage = function (?string $path) use ($defaultImage) {
        return image_url($path, 'assets/img/asnaf-gorgan-default.jpg') ?: $defaultImage;
    };
    $heroBgImage = $assetImage($union->cover_image ?: $union->logo);
    $heroBgStyle = $union->cover_image
        ? "background-image:url('{$heroBgImage}')"
        : "background-image:url('{$heroBgImage}');background-size:contain;background-repeat:no-repeat;background-position:center";
    $plain = fn ($value, $limit = 140) => plain_text($value, $limit);
    $initial = fn ($value) => mb_substr(trim((string) $value) ?: 'ا', 0, 1);
    $newsMode = $union->news_mode ?? 'auto';
    $posts = $newsMode === 'manual' ? $union->selectedPosts->where('type', 'news')->values() : ($newsMode === 'disabled' ? collect() : $union->posts->where('type', 'news')->values());
    $articles = $union->posts->where('type', 'article')->values();
    $sliderPosts = $posts->take(5);
    $socialLinks = collect($union->social_links ?? [])->filter(fn ($url) => filled($url));
    $presidentButtons = collect($union->active_president_buttons);
    $unionMessages = collect($unionMessages ?? []);
    $heroStats = [
        ['label' => 'اعضای فعال', 'value' => $union->members->count()],
        ['label' => 'کمیسیون‌ها', 'value' => $union->commissions->count()],
        ['label' => 'اخبار و اطلاعیه‌ها', 'value' => $posts->count() + $union->announcements->count()],
    ];
    $navItems = collect([
        ['key' => 'show_manager', 'default' => true, 'id' => 'guild-manager', 'label' => 'رئیس اتحادیه', 'visible' => filled($union->manager_name)],
        ['key' => 'show_board_members', 'default' => true, 'id' => 'guild-board', 'label' => 'هیئت مدیره', 'visible' => $union->members->isNotEmpty()],
        ['key' => 'show_commissions', 'default' => true, 'id' => 'guild-commissions', 'label' => 'کمیسیون‌ها', 'visible' => $union->commissions->isNotEmpty()],
        ['key' => 'show_rules', 'default' => true, 'id' => 'guild-rules', 'label' => 'قوانین', 'visible' => $union->rules->isNotEmpty()],
        ['key' => 'show_news_slider', 'default' => true, 'id' => 'guild-news-slider', 'label' => 'اسلایدر خبری', 'visible' => $sliderPosts->isNotEmpty()],
        ['key' => 'show_news', 'default' => true, 'id' => 'guild-news', 'label' => 'اخبار', 'visible' => $posts->isNotEmpty()],
        ['key' => 'show_articles', 'default' => true, 'id' => 'guild-articles', 'label' => 'مقاله‌ها', 'visible' => $articles->isNotEmpty()],
        ['key' => 'show_prices', 'default' => false, 'id' => 'guild-prices', 'label' => 'نرخ‌نامه', 'visible' => $union->prices->isNotEmpty()],
        ['key' => 'show_complaint', 'default' => true, 'id' => 'guild-complaint', 'label' => 'ثبت شکایت', 'visible' => true],
        ['key' => 'show_minutes', 'default' => true, 'id' => 'guild-minutes', 'label' => 'صورتجلسه‌ها', 'visible' => $union->minutes->isNotEmpty()],
        ['key' => 'show_education', 'default' => true, 'id' => 'guild-education', 'label' => 'آموزش‌ها', 'visible' => $union->educations->isNotEmpty()],
        ['key' => 'show_announcements', 'default' => true, 'id' => 'guild-announcements', 'label' => 'اطلاعیه‌ها', 'visible' => $union->announcements->isNotEmpty()],
        ['key' => 'show_gallery', 'default' => true, 'id' => 'guild-gallery', 'label' => 'گالری', 'visible' => $union->galleries->isNotEmpty() || $union->videos->isNotEmpty()],
        ['key' => 'show_congratulation_messages', 'default' => true, 'id' => 'guild-messages', 'label' => 'پیام‌ها', 'visible' => $unionMessages->isNotEmpty()],
        ['key' => 'show_contact', 'default' => true, 'id' => 'guild-contact', 'label' => 'تماس', 'visible' => true],
    ])->filter(fn ($item) => $union->isSectionEnabled($item['key'], $item['default']) && $item['visible']);
?>

<?php $__env->startSection('content'); ?>
<main class="guild-page">
    <section class="guild-hero">
        <div class="guild-hero-bg" style="<?php echo e($heroBgStyle); ?>"></div>
        <div class="site-container guild-hero-content">
            <div class="guild-hero-logo">
                <?php if($union->logo): ?><img alt="<?php echo e($union->display_title); ?>" src="<?php echo e($assetImage($union->logo)); ?>"><?php else: ?><span><?php echo e($initial($union->display_title)); ?></span><?php endif; ?>
            </div>
            <div class="guild-hero-text">
                <span><?php echo e($union->category?->title ?: $union->union_type_label); ?></span>
                <h1><?php echo e($union->display_title); ?></h1>
                <p><?php echo e(plain_text($union->short_description) ?: $plain($union->description, 220) ?: 'اطلاعات این اتحادیه به‌زودی به‌روزرسانی می‌شود.'); ?></p>
            </div>
            <div class="guild-hero-stats">
                <?php $__currentLoopData = $heroStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><strong><?php echo e(number_format($stat['value'])); ?></strong><span><?php echo e($stat['label']); ?></span></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>

    <div class="site-container guild-layout">
        <aside class="guild-side-nav">
            <strong>راهنمای سریع</strong>
            <ul>
                <?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><a href="#<?php echo e($item['id']); ?>"><?php echo e($item['label']); ?></a></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </aside>

        <div>
            <?php if($union->isSectionEnabled('show_manager', true)): ?>
                <section class="guild-section guild-section-alt" id="guild-manager" style="padding-top:0">
                    <h3 class="guild-section-title">رئیس <?php echo e($union->display_title); ?></h3>
                    <div class="guild-head-card">
                        <div class="guild-head-avatar"><?php if($union->manager_image): ?><img alt="<?php echo e($union->manager_name); ?>" src="<?php echo e($assetImage($union->manager_image)); ?>"><?php else: ?><?php echo e($initial($union->manager_name ?: $union->display_title)); ?><?php endif; ?></div>
                        <div class="guild-head-info">
                            <strong><?php echo e($union->manager_name ?: 'نام رئیس اتحادیه ثبت نشده است'); ?></strong>
                            <span>رئیس <?php echo e($union->display_title); ?></span>
                            <p><?php echo e($union->description ? $plain($union->description, 260) : 'توضیحات رئیس اتحادیه به‌زودی منتشر می‌شود.'); ?></p>
                            <div class="guild-head-contact">
                                <?php $__empty_1 = true; $__currentLoopData = $presidentButtons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $button): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <a href="<?php echo e($button['url']); ?>" target="<?php echo e($button['target'] ?? '_self'); ?>" <?php if(($button['target'] ?? '_self') === '_blank'): ?> rel="noopener" <?php endif; ?>><?php echo e($button['icon'] ?? ''); ?> <?php echo e($button['title']); ?></a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <?php if($union->phone): ?><a href="tel:<?php echo e($union->phone); ?>">تماس با اتحادیه</a><?php endif; ?>
                                    <?php if($union->email): ?><a href="mailto:<?php echo e($union->email); ?>">ارسال ایمیل</a><?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <?php if($union->isSectionEnabled('show_board_members', true)): ?>
                <section class="guild-section guild-section-alt" id="guild-board">
                    <h3 class="guild-section-title">اعضای هیئت مدیره اتحادیه</h3>
                    <div class="guild-members-grid guild-members-slider">
                        <?php $__empty_1 = true; $__currentLoopData = $union->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="guild-member-card"><div class="member-avatar"><?php echo e($initial($member->full_name)); ?></div><strong><?php echo e($member->full_name); ?></strong><small><?php echo e($member->position ?: $member->business_name ?: 'عضو اتحادیه'); ?></small></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="guild-info-card"><h4>عضوی برای نمایش ثبت نشده است.</h4></div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if($union->isSectionEnabled('show_commissions', true)): ?>
                <section class="guild-section guild-section-alt" id="guild-commissions">
                    <h3 class="guild-section-title">کمیسیون‌های اتحادیه</h3>
                    <div class="guild-commission-list">
                        <?php $__empty_1 = true; $__currentLoopData = $union->commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="guild-commission-item"><div class="com-num"><?php echo e($loop->iteration); ?></div><div><strong><?php echo e($commission->title); ?></strong><small><?php echo e(plain_text($commission->description, 120) ?: 'شرح کمیسیون ثبت نشده است.'); ?></small>
                                <?php if($union->isSectionEnabled('show_commission_tasks', true) && $commission->tasks->isNotEmpty()): ?><ul><?php $__currentLoopData = $commission->tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($task->title); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul><?php endif; ?>
                            </div></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="guild-info-card"><h4>کمیسیونی برای این اتحادیه ثبت نشده است.</h4></div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if($union->isSectionEnabled('show_rules', true)): ?>
                <section class="guild-section guild-section-alt" id="guild-rules"><h3 class="guild-section-title">قوانین و دستورالعمل‌ها</h3><div class="guild-2col"><div class="guild-rules-list">
                    <?php $__empty_1 = true; $__currentLoopData = $union->rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="guild-rule-item"><div class="rule-icon"><?php echo e($rule->icon ?: '📋'); ?></div><div><strong><?php echo e($rule->title); ?></strong><small><?php echo e(plain_text($rule->description, 120) ?: 'توضیحات تکمیلی ثبت نشده است.'); ?></small><?php if($rule->file): ?><a href="<?php echo e($assetImage($rule->file)); ?>" target="_blank" rel="noopener">دانلود فایل</a><?php endif; ?></div></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="guild-info-card"><h4>قانونی برای نمایش ثبت نشده است.</h4></div><?php endif; ?>
                </div></div></section>
            <?php endif; ?>

            <?php if($union->isSectionEnabled('show_news_slider', true) && $sliderPosts->isNotEmpty()): ?>
                <section class="guild-section" id="guild-news-slider"><h3 class="guild-section-title">اسلایدر خبری اتحادیه</h3><div class="guild-news-slider"><?php $__currentLoopData = $sliderPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="guild-news-slide" href="<?php echo e(route('posts.show', $post->slug)); ?>"><img alt="<?php echo e($post->title); ?>" src="<?php echo e($post->featured_image_url); ?>"><strong><?php echo e($post->title); ?></strong><span><?php echo e(jalali_date($post->published_at) ?: '—'); ?></span></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div></section>
            <?php endif; ?>

            <?php if($union->isSectionEnabled('show_news', true)): ?>
                <section class="guild-section" id="guild-news"><h3 class="guild-section-title">آخرین اخبار اتحادیه</h3><div class="guild-article-list"><?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><article><a href="<?php echo e(route('posts.show', $post->slug)); ?>"><img alt="<?php echo e($post->title); ?>" src="<?php echo e($post->featured_image_url); ?>"><div><strong><?php echo e($post->title); ?></strong><p><?php echo e($post->summary); ?></p></div></a></article><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="guild-info-card"><h4>خبری برای این اتحادیه ثبت نشده است.</h4></div><?php endif; ?></div></section>
            <?php endif; ?>

            <?php if($union->isSectionEnabled('show_articles', true)): ?>
                <section class="guild-section guild-section-alt" id="guild-articles"><h3 class="guild-section-title">مقاله‌ها و مطالب آموزشی</h3><div class="guild-article-list"><?php $__empty_1 = true; $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><article><a href="<?php echo e(route('posts.show', $article->slug)); ?>"><img alt="<?php echo e($article->title); ?>" src="<?php echo e($article->featured_image_url); ?>"><div><strong><?php echo e($article->title); ?></strong><p><?php echo e($article->summary); ?></p></div></a></article><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="guild-info-card"><h4>مقاله‌ای برای این اتحادیه ثبت نشده است.</h4></div><?php endif; ?></div></section>
            <?php endif; ?>

            <?php if($union->isSectionEnabled('show_prices', false)): ?>
                <section class="guild-section guild-section-alt" id="guild-prices"><h3 class="guild-section-title">نرخ‌نامه اختصاصی اتحادیه</h3><?php if(($union->price_list_mode ?? 'table') === 'image'): ?> <?php if($union->price_list_image): ?><img src="<?php echo e($assetImage($union->price_list_image)); ?>" alt="نرخنامه <?php echo e($union->display_title); ?>" style="width:100%;border-radius:18px"><?php else: ?><div class="guild-info-card"><h4>عکس نرخنامه ثبت نشده است.</h4></div><?php endif; ?> <?php else: ?> <div class="price-table"><table><thead><tr><th>عنوان</th><th>نوع</th><th>قیمت</th><th>تاریخ بروزرسانی</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $union->prices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $price): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($price->title); ?></td><td><?php echo e($price->type ?: 'عمومی'); ?></td><td><?php echo e($price->price ? number_format((float) $price->price).' '.$price->currency : 'اعلام نشده'); ?></td><td><?php echo e($price->updated_on ? jalali_date($price->updated_on) : '—'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="4">نرخی برای نمایش ثبت نشده است.</td></tr><?php endif; ?></tbody></table></div><?php endif; ?></section>
            <?php endif; ?>

            <?php if($union->isSectionEnabled('show_complaint', true)): ?>
                <section class="guild-section guild-section-alt" id="guild-complaint"><h3 class="guild-section-title">ثبت شکایت صنفی</h3><div class="guild-2col"><div class="guild-info-card"><h4>نحوه ثبت شکایت</h4><p>شهروندان می‌توانند شکایات خود را در خصوص این اتحادیه به صورت آنلاین ثبت و پیگیری نمایند.</p></div><div class="guild-complaint-cta"><strong>ثبت شکایت آنلاین</strong><a class="tab-pill active" href="<?php echo e(route('complaints.create', ['union' => $union->id])); ?>">ثبت شکایت جدید</a><a class="tab-pill" href="<?php echo e(route('complaints.track')); ?>">پیگیری شکایت قبلی</a></div></div></section>
            <?php endif; ?>

            <?php if($union->isSectionEnabled('show_congratulation_messages', true) && $unionMessages->isNotEmpty()): ?>
                <section class="guild-section guild-section-alt" id="guild-messages"><h3 class="guild-section-title">پیام‌های تبریک و تسلیت</h3><div class="guild-announce-list"><?php $__currentLoopData = $unionMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e(route('congratulation_messages.show', $message->slug)); ?>"><strong><?php echo e($message->title); ?></strong><span><?php echo e($message->summary ?: $plain($message->body)); ?></span></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div></section>
            <?php endif; ?>

            <?php if($union->isSectionEnabled('show_minutes', true)): ?>
                <section class="guild-section" id="guild-minutes"><h3 class="guild-section-title">صورتجلسه‌ها</h3><div class="guild-minutes-list"><?php $__empty_1 = true; $__currentLoopData = $union->minutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $minute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="guild-info-card"><h4><?php echo e($minute->title); ?></h4><p><?php echo e(plain_text($minute->description, 140) ?: 'شرح صورتجلسه ثبت نشده است.'); ?></p><span><?php echo e($minute->meeting_date ? jalali_date($minute->meeting_date) : 'بدون تاریخ'); ?></span><?php if($minute->file): ?><a href="<?php echo e($assetImage($minute->file)); ?>" target="_blank" rel="noopener">دانلود صورتجلسه</a><?php endif; ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="guild-info-card"><h4>صورتجلسه‌ای برای نمایش ثبت نشده است.</h4></div><?php endif; ?></div></section>
            <?php endif; ?>

            <?php if($union->isSectionEnabled('show_education', true)): ?>
                <section class="guild-section guild-section-alt" id="guild-education"><h3 class="guild-section-title">آموزش‌های اتحادیه</h3><?php $__empty_1 = true; $__currentLoopData = $union->educations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $education): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><a class="guild-edu-item" href="<?php echo e($education->link ?: route('guilds.show', $union->slug)); ?>"><span><?php echo e($education->icon ?: '📚'); ?></span><div><strong><?php echo e($education->title); ?></strong><p><?php echo e(plain_text($education->description, 140) ?: 'توضیحات آموزشی ثبت نشده است.'); ?></p></div></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="guild-info-card"><h4>آموزشی برای این اتحادیه ثبت نشده است.</h4></div><?php endif; ?></section>
            <?php endif; ?>

            <?php if($union->isSectionEnabled('show_announcements', true)): ?>
                <section class="guild-section" id="guild-announcements"><h3 class="guild-section-title">اطلاعیه‌ها و بخشنامه‌ها</h3><div class="guild-announce-list"><?php $__empty_1 = true; $__currentLoopData = $union->announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><a href="<?php echo e(route('announcements.show', $announcement->slug)); ?>"><strong><?php echo e($announcement->title); ?></strong><span><?php echo e(plain_text($announcement->excerpt ?: $announcement->body, 140)); ?></span></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="guild-info-card"><h4>اطلاعیه‌ای برای این اتحادیه ثبت نشده است.</h4></div><?php endif; ?></div></section>
            <?php endif; ?>

            <?php if($union->isSectionEnabled('show_gallery', true) || $union->isSectionEnabled('show_videos', true)): ?>
                <section class="guild-section guild-section-alt" id="guild-gallery"><h3 class="guild-section-title">گالری تصاویر و ویدیوها</h3><div class="guild-gallery-grid"><?php if($union->isSectionEnabled('show_gallery', true)): ?><?php $__currentLoopData = $union->galleries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gallery): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="guild-gallery-item" href="<?php echo e(route('galleries.show', $gallery->slug)); ?>"><img alt="<?php echo e($gallery->title); ?>" src="<?php echo e($gallery->cover_image_url); ?>"><span><?php echo e($gallery->title); ?></span></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php endif; ?> <?php if($union->isSectionEnabled('show_videos', true)): ?><?php $__currentLoopData = $union->videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="guild-gallery-item video" href="<?php echo e(route('videos.show', $video->slug)); ?>"><img alt="<?php echo e($video->title); ?>" src="<?php echo e($assetImage($video->cover_image)); ?>"><span><?php echo e($video->title); ?></span></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php endif; ?> <?php if(($union->isSectionEnabled('show_gallery', true) && $union->galleries->isEmpty()) && ($union->isSectionEnabled('show_videos', true) && $union->videos->isEmpty())): ?><div class="guild-info-card"><h4>گالری یا ویدیویی برای این اتحادیه ثبت نشده است.</h4></div><?php endif; ?></div></section>
            <?php endif; ?>


            <?php if($union->isSectionEnabled('show_contact', true)): ?>
                <section class="guild-section" id="guild-contact"><h3 class="guild-section-title">تماس با اتحادیه و شبکه‌های اجتماعی</h3><div class="guild-contact-grid"><div class="guild-contact-card"><div class="contact-icon">📍</div><strong>آدرس</strong><span><?php echo e($union->address ?: 'آدرس ثبت نشده است.'); ?></span></div><div class="guild-contact-card"><div class="contact-icon">📞</div><strong>تلفن</strong><span><?php echo e($union->phone ?: $union->mobile ?: 'شماره تماس ثبت نشده است.'); ?></span></div><div class="guild-contact-card"><div class="contact-icon">✉️</div><strong>ایمیل</strong><span><?php echo e($union->email ?: 'ایمیل ثبت نشده است.'); ?></span></div><div class="guild-contact-card"><div class="contact-icon">🕘</div><strong>ساعات کاری</strong><span><?php echo e($union->working_hours ?: 'ساعات کاری ثبت نشده است.'); ?></span></div></div>
                    <?php if($union->isSectionEnabled('show_social_links', true) && $socialLinks->isNotEmpty()): ?><div class="guild-social"><?php $__currentLoopData = $socialLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if($url): ?><a href="<?php echo e($url); ?>" target="_blank" rel="noopener" aria-label="<?php echo e($name); ?>"><?php switch($name):case ('instagram'): ?> 📷 <?php break; ?> <?php case ('telegram'): ?> ✈️ <?php break; ?> <?php case ('whatsapp'): ?> 💬 <?php break; ?> <?php case ('eitaa'): ?> 📱 <?php break; ?> <?php case ('bale'): ?> 🔵 <?php break; ?> <?php case ('rubika'): ?> 🟣 <?php break; ?> <?php case ('website'): ?> 🌐 <?php break; ?> <?php default: ?> 🔗 <?php endswitch; ?></a><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/guilds/show.blade.php ENDPATH**/ ?>