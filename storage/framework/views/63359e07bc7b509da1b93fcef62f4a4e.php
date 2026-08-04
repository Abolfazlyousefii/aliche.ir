<?php $__env->startSection('title', $video->title.' | اتاق اصناف مرکز استان گلستان'); ?>
<?php $__env->startSection('meta_description', plain_text($video->description, 160)); ?>

<?php $__env->startSection('content'); ?>
<section class="page-header page-header-alt">
  <div class="site-container">
    <h1><?php echo e($video->title); ?></h1>
    <nav class="breadcrumb">
      <a href="<?php echo e(route('home')); ?>">خانه</a>
      <a href="<?php echo e(route('videos.index')); ?>">چندرسانه‌ای</a>
      <span><?php echo e($video->title); ?></span>
    </nav>
  </div>
</section>

<section class="site-container">
  <div class="video-single-layout">
    <div class="video-single-main">
      <div class="video-player-wrap">
        <div class="video-player">
          <?php if($video->video_type === 'upload' && $video->video_file): ?>
            <video class="video-player" controls poster="<?php echo e(image_url($video->cover_image)); ?>">
              <source src="<?php echo e(image_url($video->video_file, '')); ?>" type="video/mp4">
            </video>
          <?php elseif($video->video_type === 'aparat' && $video->aparat_embed_url): ?>
            <iframe class="video-player" src="<?php echo e($video->aparat_embed_url); ?>" title="<?php echo e($video->title); ?>" allowfullscreen loading="lazy"></iframe>
          <?php elseif($video->aparat_url): ?>
            <a href="<?php echo e($video->aparat_url); ?>" target="_blank" rel="noopener">
              <img src="<?php echo e(image_url($video->cover_image)); ?>" alt="<?php echo e($video->title); ?>" loading="lazy"/>
              <div class="video-player-overlay"></div>
              <button class="video-big-play" type="button" aria-label="مشاهده ویدیو"></button>
            </a>
          <?php else: ?>
            <img src="<?php echo e(image_url($video->cover_image)); ?>" alt="<?php echo e($video->title); ?>" loading="lazy"/>
            <div class="video-player-overlay"></div>
            <button class="video-big-play" type="button" aria-label="ویدیویی ثبت نشده است"></button>
          <?php endif; ?>
        </div>
      </div>
      <div class="video-single-body">
        <div class="video-meta">
          <span>📅 <?php echo e(jalali_date($video->published_at) ?: jalali_date($video->created_at)); ?></span>
          <span>🎞 <?php echo e($video->type_label); ?></span>
          <?php if($video->union): ?><span>🏢 <?php echo e($video->union->display_title); ?></span><?php endif; ?>
        </div>
        <h2><?php echo e($video->title); ?></h2>
        <p><?php echo e(plain_text($video->description) ?: 'توضیحی برای این ویدیو ثبت نشده است.'); ?></p>
        <div class="video-tags">
          <span class="post-tag">ویدیو</span>
          <span class="post-tag"><?php echo e($video->type_label); ?></span>
          <?php if($video->union): ?><span class="post-tag"><?php echo e($video->union->display_title); ?></span><?php endif; ?>
        </div>
      </div>
    </div>
    <aside class="video-sidebar">
      <div class="video-sidebar-card">
        <h4>ویدیوهای مرتبط</h4>
        <div class="video-related-list">
          <?php $__empty_1 = true; $__currentLoopData = $relatedVideos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedVideo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('videos.show', $relatedVideo->slug)); ?>" class="video-related-item">
              <div class="vri-thumb"><img src="<?php echo e(image_url($relatedVideo->cover_image)); ?>" alt="<?php echo e($relatedVideo->title); ?>" loading="lazy"/><span class="vri-play-icon"></span></div>
              <div class="vri-body">
                <strong><?php echo e($relatedVideo->title); ?></strong>
                <span><?php echo e($relatedVideo->type_label); ?></span>
              </div>
            </a>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p>ویدیوی مرتبطی برای نمایش وجود ندارد.</p>
          <?php endif; ?>
        </div>
      </div>
    </aside>
  </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/videos/show.blade.php ENDPATH**/ ?>