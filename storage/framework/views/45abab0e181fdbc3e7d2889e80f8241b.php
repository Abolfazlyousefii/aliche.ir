<?php ($hasChildren = $menuItem->children->isNotEmpty()); ?>
<li class="<?php echo e($itemClass); ?> <?php echo e($hasChildren ? 'has-top-submenu top-nav-item' : ''); ?>">
    <?php if($hasChildren && $variant !== 'compact'): ?>
        <button aria-expanded="false" class="<?php echo e($linkClass); ?> top-nav-link" type="button"><?php echo e($menuItem->icon); ?> <?php echo e($menuItem->title); ?><span class="top-submenu-caret"></span></button>
        <ul class="top-submenu">
            <?php $__currentLoopData = $menuItem->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><a href="<?php echo e($child->resolved_url); ?>" target="<?php echo e($child->target); ?>"><?php echo e($child->icon); ?> <?php echo e($child->title); ?></a></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php elseif($hasChildren): ?>
        <button class="<?php echo e($linkClass); ?>" aria-expanded="false"><?php echo e($menuItem->icon); ?> <?php echo e($menuItem->title); ?></button>
        <div class="top-submenu">
            <?php $__currentLoopData = $menuItem->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($child->resolved_url); ?>" target="<?php echo e($child->target); ?>"><?php echo e($child->icon); ?> <?php echo e($child->title); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <a class="<?php echo e($linkClass); ?><?php echo e(request()->url() === $menuItem->resolved_url ? ' active' : ''); ?>" href="<?php echo e($menuItem->resolved_url); ?>" target="<?php echo e($menuItem->target); ?>"><?php echo e($menuItem->icon); ?> <?php echo e($menuItem->title); ?></a>
    <?php endif; ?>
</li>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/frontend/partials/dynamic-menu-item.blade.php ENDPATH**/ ?>