<li class="admin-menu-tree-item" data-menu-item-id="<?php echo e($item->id); ?>" draggable="true">
    <div class="admin-menu-tree-row">
        <span class="admin-drag-handle" title="جابجایی">↕</span>
        <div class="admin-menu-item-info">
            <strong><?php echo e($item->icon); ?> <?php echo e($item->title); ?></strong>
            <small><code><?php echo e($item->type); ?></code> — <?php echo e($item->resolved_url); ?></small>
        </div>
        <span class="admin-status-badge <?php echo e($item->is_active ? 'is-active' : 'is-inactive'); ?>"><?php echo e($item->is_active ? 'فعال' : 'غیرفعال'); ?></span>
        <div class="admin-actions">
            <a href="<?php echo e(route('admin.menus.items.edit', [$menu, $item])); ?>">ویرایش</a>
            <form action="<?php echo e(route('admin.menus.items.toggle', [$menu, $item])); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <button type="submit"><?php echo e($item->is_active ? 'غیرفعال' : 'فعال'); ?></button>
            </form>
            <form action="<?php echo e(route('admin.menus.items.destroy', [$menu, $item])); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit">حذف</button>
            </form>
        </div>
    </div>

    <ol class="admin-menu-tree" data-menu-list>
        <?php $__currentLoopData = $item->adminChildren; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo $__env->make('admin.menus.items._tree-item', ['item' => $child, 'menu' => $menu], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</li>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/menus/items/_tree-item.blade.php ENDPATH**/ ?>