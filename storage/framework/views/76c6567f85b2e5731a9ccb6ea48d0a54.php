<article class="archive-card guild-card">
    <a href="<?php echo e(route('guilds.show', $union->slug)); ?>">
        <img alt="<?php echo e($union->display_title); ?>" class="archive-card-img" src="<?php echo e($assetImage($union->cover_image ?: $union->logo)); ?>">
        <div class="archive-card-body">
            <span class="card-cat"><?php echo e($union->category?->title ?: $union->union_type_label); ?></span>
            <h2><?php echo e($union->display_title); ?></h2>
            <p><?php echo e(plain_text($union->short_description ?: $union->description, 150)); ?></p>
            <?php if($union->manager_name): ?><span class="card-date">مدیر: <?php echo e($union->manager_name); ?></span><?php endif; ?>
            <?php if($union->phone): ?><span class="card-date"><?php echo e(fa_number($union->phone)); ?></span><?php endif; ?>
        </div>
    </a>
</article>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/guilds/partials/card.blade.php ENDPATH**/ ?>