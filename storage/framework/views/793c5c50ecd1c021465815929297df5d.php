<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>عنوان پیام</th>
                <th>فرستنده</th>
                <th>گیرنده</th>
                <th>اولویت</th>
                <th>وضعیت</th>
                <th>تاریخ ارسال</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="<?php echo \Illuminate\Support\Arr::toCssClasses(['table-warning' => $message->recipient_id === auth()->id() && ! $message->read_at]); ?>">
                    <td>
                        <strong><?php echo e($message->subject); ?></strong>
                        <small class="d-block text-muted"><?php echo e($message->typeLabel()); ?></small>
                    </td>
                    <td><?php echo e($message->sender?->name ?? 'سیستم'); ?></td>
                    <td><?php echo e($message->recipient?->name ?? 'حذف‌شده'); ?></td>
                    <td><span class="badge bg-<?php echo e($message->priority === 'urgent' ? 'danger' : ($message->priority === 'important' ? 'warning text-dark' : 'secondary')); ?>"><?php echo e($message->priorityLabel()); ?></span></td>
                    <td>
                        <?php if($message->read_at): ?>
                            <span class="badge bg-success">خوانده‌شده</span>
                        <?php else: ?>
                            <span class="badge bg-danger">خوانده‌نشده</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($message->sent_at ? jdate($message->sent_at)->format('Y/m/d H:i') : jdate($message->created_at)->format('Y/m/d H:i')); ?></td>
                    <td>
                        <div class="admin-actions">
                            <a class="admin-secondary-btn" href="<?php echo e(route('admin.messages.show', $message)); ?>">مشاهده</a>
                            <?php if(request()->user()->hasPermission('messages.delete')): ?>
                                <form method="POST" action="<?php echo e(route('admin.messages.destroy', $message)); ?>" data-admin-delete-form data-delete-message="پیام «<?php echo e($message->subject); ?>» حذف شود؟">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="admin-danger-btn" type="submit">حذف</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="text-center text-muted">پیامی برای نمایش وجود ندارد.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php echo $__env->make('admin.partials.pagination', ['paginator' => $messages], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/messages/_table.blade.php ENDPATH**/ ?>