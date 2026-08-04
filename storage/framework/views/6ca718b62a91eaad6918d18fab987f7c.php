<?php
    $sectionRows = function (string $key, string $relation) use ($union) {
        $oldRows = old("related.{$key}");
        if (is_array($oldRows)) {
            return collect($oldRows);
        }

        return $union?->{$relation} ?? collect();
    };

    $commissions = $sectionRows('commissions', 'commissions');
    $rules = $sectionRows('rules', 'rules');
    $minutes = $sectionRows('minutes', 'minutes');
    $educations = $sectionRows('educations', 'educations');
    $prices = $sectionRows('prices', 'prices');
    $valueOf = fn ($row, string $field, $default = null) => is_array($row) ? ($row[$field] ?? $default) : ($row->{$field} ?? $default);
    $dateValue = fn ($row, string $field) => ($valueOf($row, $field) instanceof \Illuminate\Support\Carbon || $valueOf($row, $field) instanceof \Carbon\CarbonInterface) ? jalali_input_date($valueOf($row, $field)) : $valueOf($row, $field);
?>

<div class="admin-panel-card mt-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h3 class="h5 mb-1">ویرایش کامل بخش‌های صفحه اتحادیه</h3>
            <p class="text-muted mb-0">کمیسیون‌ها، وظایف کمیسیون‌ها، قوانین، صورتجلسه‌ها، آموزش‌ها و نرخ‌نامه را همین‌جا مدیریت کنید.</p>
        </div>
    </div>

    <div class="accordion" id="unionPageSectionsAccordion">
        <div class="accordion-item">
            <h4 class="accordion-header" id="headingCommissions"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCommissions" aria-expanded="true" aria-controls="collapseCommissions">کمیسیون‌ها و وظایف کمیسیون‌ها</button></h4>
            <div class="accordion-collapse collapse show" id="collapseCommissions" aria-labelledby="headingCommissions" data-bs-parent="#unionPageSectionsAccordion"><div class="accordion-body">
                <div class="union-dynamic-section" data-section="commissions" data-next-index="<?php echo e($commissions->count()); ?>">
                    <div data-rows>
                        <?php $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="border rounded p-3 mb-3" data-row>
                                <input type="hidden" name="related[commissions][<?php echo e($index); ?>][id]" value="<?php echo e($valueOf($commission, 'id')); ?>">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4"><label class="form-label">عنوان کمیسیون</label><input class="form-control" name="related[commissions][<?php echo e($index); ?>][title]" value="<?php echo e($valueOf($commission, 'title')); ?>"></div>
                                    <div class="col-md-2"><label class="form-label">آیکن</label><input class="form-control" name="related[commissions][<?php echo e($index); ?>][icon]" value="<?php echo e($valueOf($commission, 'icon')); ?>"></div>
                                    <div class="col-md-2"><label class="form-label">ترتیب</label><input class="form-control" type="number" min="0" name="related[commissions][<?php echo e($index); ?>][sort_order]" value="<?php echo e($valueOf($commission, 'sort_order', 0)); ?>"></div>
                                    <div class="col-md-2"><label class="form-label">وضعیت</label><select class="form-control" name="related[commissions][<?php echo e($index); ?>][is_active]"><option value="1" <?php if((string) $valueOf($commission, 'is_active', 1) === '1'): echo 'selected'; endif; ?>>فعال</option><option value="0" <?php if((string) $valueOf($commission, 'is_active', 1) === '0'): echo 'selected'; endif; ?>>غیرفعال</option></select></div>
                                    <div class="col-md-2"><label class="form-check"><input class="form-check-input" type="checkbox" name="related[commissions][<?php echo e($index); ?>][delete]" value="1"> حذف</label></div>
                                    <div class="col-12"><label class="form-label">توضیحات کمیسیون</label><textarea class="form-control js-rich-editor" name="related[commissions][<?php echo e($index); ?>][description]" rows="2"><?php echo e($valueOf($commission, 'description')); ?></textarea></div>
                                </div>
                                <?php ($tasks = collect(is_array($commission) ? ($commission['tasks'] ?? []) : ($commission->tasks ?? []))); ?>
                                <div class="mt-3 ps-md-3 border-start union-dynamic-section" data-section="commission-tasks" data-commission-index="<?php echo e($index); ?>" data-next-index="<?php echo e($tasks->count()); ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-2"><strong>وظایف کمیسیون</strong><button class="btn btn-sm btn-outline-primary" type="button" data-add-row>افزودن وظیفه</button></div>
                                    <div data-rows>
                                        <?php $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $taskIndex => $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="row g-2 align-items-end mb-2" data-row>
                                                <input type="hidden" name="related[commissions][<?php echo e($index); ?>][tasks][<?php echo e($taskIndex); ?>][id]" value="<?php echo e($valueOf($task, 'id')); ?>">
                                                <div class="col-md-4"><input class="form-control" name="related[commissions][<?php echo e($index); ?>][tasks][<?php echo e($taskIndex); ?>][title]" placeholder="عنوان وظیفه" value="<?php echo e($valueOf($task, 'title')); ?>"></div>
                                                <div class="col-md-4"><input class="form-control" name="related[commissions][<?php echo e($index); ?>][tasks][<?php echo e($taskIndex); ?>][description]" placeholder="توضیح" value="<?php echo e($valueOf($task, 'description')); ?>"></div>
                                                <div class="col-md-2"><input class="form-control" type="number" min="0" name="related[commissions][<?php echo e($index); ?>][tasks][<?php echo e($taskIndex); ?>][sort_order]" value="<?php echo e($valueOf($task, 'sort_order', 0)); ?>"></div>
                                                <div class="col-md-1"><select class="form-control" name="related[commissions][<?php echo e($index); ?>][tasks][<?php echo e($taskIndex); ?>][is_active]"><option value="1" <?php if((string) $valueOf($task, 'is_active', 1) === '1'): echo 'selected'; endif; ?>>فعال</option><option value="0" <?php if((string) $valueOf($task, 'is_active', 1) === '0'): echo 'selected'; endif; ?>>غیرفعال</option></select></div>
                                                <div class="col-md-1"><label class="form-check"><input class="form-check-input" type="checkbox" name="related[commissions][<?php echo e($index); ?>][tasks][<?php echo e($taskIndex); ?>][delete]" value="1"> حذف</label></div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <button class="btn btn-outline-primary" type="button" data-add-row>افزودن کمیسیون</button>
                </div>
            </div></div>
        </div>

        <?php $__currentLoopData = [
            'rules' => ['title' => 'قوانین و دستورالعمل‌ها', 'rows' => $rules, 'fields' => ['title' => 'عنوان', 'description' => 'توضیحات', 'icon' => 'آیکن', 'file' => 'لینک فایل']],
            'minutes' => ['title' => 'صورتجلسه‌ها', 'rows' => $minutes, 'fields' => ['title' => 'عنوان', 'meeting_date' => 'تاریخ جلسه', 'description' => 'توضیحات', 'file' => 'لینک فایل']],
            'educations' => ['title' => 'آموزش‌ها', 'rows' => $educations, 'fields' => ['title' => 'عنوان', 'description' => 'توضیحات', 'icon' => 'آیکن', 'link' => 'لینک']],
            'prices' => ['title' => 'نرخ‌نامه', 'rows' => $prices, 'fields' => ['title' => 'عنوان', 'price' => 'قیمت', 'currency' => 'واحد', 'type' => 'نوع', 'updated_on' => 'تاریخ بروزرسانی']],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionKey => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="accordion-item">
                <h4 class="accordion-header" id="heading<?php echo e($sectionKey); ?>"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo e($sectionKey); ?>" aria-expanded="false" aria-controls="collapse<?php echo e($sectionKey); ?>"><?php echo e($section['title']); ?></button></h4>
                <div class="accordion-collapse collapse" id="collapse<?php echo e($sectionKey); ?>" aria-labelledby="heading<?php echo e($sectionKey); ?>" data-bs-parent="#unionPageSectionsAccordion"><div class="accordion-body">
                    <div class="union-dynamic-section" data-section="<?php echo e($sectionKey); ?>" data-next-index="<?php echo e($section['rows']->count()); ?>">
                        <div data-rows>
                            <?php $__currentLoopData = $section['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="border rounded p-3 mb-3" data-row>
                                    <input type="hidden" name="related[<?php echo e($sectionKey); ?>][<?php echo e($index); ?>][id]" value="<?php echo e($valueOf($row, 'id')); ?>">
                                    <div class="row g-2 align-items-end">
                                        <?php $__currentLoopData = $section['fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="col-md-<?php echo e(in_array($field, ['description'], true) ? '12' : '3'); ?>">
                                                <label class="form-label"><?php echo e($label); ?></label>
                                                <?php if($field === 'description'): ?>
                                                    <textarea class="form-control js-rich-editor" name="related[<?php echo e($sectionKey); ?>][<?php echo e($index); ?>][<?php echo e($field); ?>]" rows="2"><?php echo e($valueOf($row, $field)); ?></textarea>
                                                <?php else: ?>
                                                    <input class="form-control" <?php if(in_array($field, ['meeting_date', 'updated_on'], true)): ?> type="text" data-jalali-datepicker data-jalali-date-only dir="ltr" <?php elseif($field === 'price'): ?> type="number" step="0.01" min="0" <?php endif; ?> name="related[<?php echo e($sectionKey); ?>][<?php echo e($index); ?>][<?php echo e($field); ?>]" value="<?php echo e(in_array($field, ['meeting_date', 'updated_on'], true) ? $dateValue($row, $field) : $valueOf($row, $field)); ?>">
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-2"><label class="form-label">ترتیب</label><input class="form-control" type="number" min="0" name="related[<?php echo e($sectionKey); ?>][<?php echo e($index); ?>][sort_order]" value="<?php echo e($valueOf($row, 'sort_order', 0)); ?>"></div>
                                        <div class="col-md-2"><label class="form-label">وضعیت</label><select class="form-control" name="related[<?php echo e($sectionKey); ?>][<?php echo e($index); ?>][is_active]"><option value="1" <?php if((string) $valueOf($row, 'is_active', 1) === '1'): echo 'selected'; endif; ?>>فعال</option><option value="0" <?php if((string) $valueOf($row, 'is_active', 1) === '0'): echo 'selected'; endif; ?>>غیرفعال</option></select></div>
                                        <div class="col-md-2"><label class="form-check"><input class="form-check-input" type="checkbox" name="related[<?php echo e($sectionKey); ?>][<?php echo e($index); ?>][delete]" value="1"> حذف</label></div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <button class="btn btn-outline-primary" type="button" data-add-row>افزودن <?php echo e($section['title']); ?></button>
                    </div>
                </div></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('232659fa-2e50-4ca5-84e7-79a457cfda09')): $__env->markAsRenderedOnce('232659fa-2e50-4ca5-84e7-79a457cfda09'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('click', function (event) {
                const button = event.target.closest('[data-add-row]');
                if (!button) return;
                const section = button.closest('.union-dynamic-section');
                if (!section) return;
                const rows = section.querySelector('[data-rows]');
                const key = section.dataset.section;
                const index = Number(section.dataset.nextIndex || 0);
                section.dataset.nextIndex = index + 1;
                rows.insertAdjacentHTML('beforeend', templateFor(key, index, section.dataset.commissionIndex));
                if (window.initializeJalaliDatepickers) window.initializeJalaliDatepickers(rows.lastElementChild || rows);
            });

            function templateFor(key, index, commissionIndex) {
                if (key === 'president-buttons') {
                    return `<div class="border rounded p-3 mb-2" data-row><div class="row g-2 align-items-end"><div class="col-md-3"><label class="form-label">عنوان</label><input class="form-control" name="president_buttons[${index}][title]"></div><div class="col-md-3"><label class="form-label">لینک</label><input class="form-control" name="president_buttons[${index}][url]" dir="ltr"></div><div class="col-md-2"><label class="form-label">آیکون</label><input class="form-control" name="president_buttons[${index}][icon]" placeholder="📞"></div><div class="col-md-2"><label class="form-label">باز شدن</label><select class="form-control" name="president_buttons[${index}][target]"><option value="_self">همان صفحه</option><option value="_blank">صفحه جدید</option></select></div><div class="col-md-2"><label class="form-check"><input class="form-check-input" type="checkbox" name="president_buttons[${index}][is_active]" value="1" checked> فعال</label></div></div></div>`;
                }
                if (key === 'commissions') {
                    return `<div class="border rounded p-3 mb-3" data-row><div class="row g-2 align-items-end"><div class="col-md-4"><label class="form-label">عنوان کمیسیون</label><input class="form-control" name="related[commissions][${index}][title]"></div><div class="col-md-2"><label class="form-label">آیکن</label><input class="form-control" name="related[commissions][${index}][icon]"></div><div class="col-md-2"><label class="form-label">ترتیب</label><input class="form-control" type="number" min="0" name="related[commissions][${index}][sort_order]" value="0"></div><div class="col-md-2"><label class="form-label">وضعیت</label><select class="form-control" name="related[commissions][${index}][is_active]"><option value="1">فعال</option><option value="0">غیرفعال</option></select></div><div class="col-12"><label class="form-label">توضیحات کمیسیون</label><textarea class="form-control" name="related[commissions][${index}][description]" rows="2"></textarea></div></div><div class="mt-3 ps-md-3 border-start union-dynamic-section" data-section="commission-tasks" data-commission-index="${index}" data-next-index="0"><div class="d-flex justify-content-between align-items-center mb-2"><strong>وظایف کمیسیون</strong><button class="btn btn-sm btn-outline-primary" type="button" data-add-row>افزودن وظیفه</button></div><div data-rows></div></div></div>`;
                }
                if (key === 'commission-tasks') {
                    return `<div class="row g-2 align-items-end mb-2" data-row><div class="col-md-4"><input class="form-control" name="related[commissions][${commissionIndex}][tasks][${index}][title]" placeholder="عنوان وظیفه"></div><div class="col-md-4"><input class="form-control" name="related[commissions][${commissionIndex}][tasks][${index}][description]" placeholder="توضیح"></div><div class="col-md-2"><input class="form-control" type="number" min="0" name="related[commissions][${commissionIndex}][tasks][${index}][sort_order]" value="0"></div><div class="col-md-2"><select class="form-control" name="related[commissions][${commissionIndex}][tasks][${index}][is_active]"><option value="1">فعال</option><option value="0">غیرفعال</option></select></div></div>`;
                }
                const fields = {
                    rules: [['title', 'عنوان'], ['description', 'توضیحات'], ['icon', 'آیکن'], ['file', 'لینک فایل']],
                    minutes: [['title', 'عنوان'], ['meeting_date', 'تاریخ جلسه', 'jalali-date'], ['description', 'توضیحات'], ['file', 'لینک فایل']],
                    educations: [['title', 'عنوان'], ['description', 'توضیحات'], ['icon', 'آیکن'], ['link', 'لینک']],
                    prices: [['title', 'عنوان'], ['price', 'قیمت', 'number'], ['currency', 'واحد'], ['type', 'نوع'], ['updated_on', 'تاریخ بروزرسانی', 'jalali-date']],
                }[key] || [];
                const controls = fields.map(([field, label, type]) => `<div class="col-md-${field === 'description' ? '12' : '3'}"><label class="form-label">${label}</label>${field === 'description' ? `<textarea class="form-control" name="related[${key}][${index}][${field}]" rows="2"></textarea>` : `<input class="form-control" type="${type === 'number' ? 'number' : 'text'}" ${type === 'jalali-date' ? 'data-jalali-datepicker data-jalali-date-only dir=\"ltr\"' : ''} name="related[${key}][${index}][${field}]">`}</div>`).join('');
                return `<div class="border rounded p-3 mb-3" data-row><div class="row g-2 align-items-end">${controls}<div class="col-md-2"><label class="form-label">ترتیب</label><input class="form-control" type="number" min="0" name="related[${key}][${index}][sort_order]" value="0"></div><div class="col-md-2"><label class="form-label">وضعیت</label><select class="form-control" name="related[${key}][${index}][is_active]"><option value="1">فعال</option><option value="0">غیرفعال</option></select></div></div></div>`;
            }
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /home/aliche/domains/aliche.ir/resources/views/admin/unions/_page_sections_form.blade.php ENDPATH**/ ?>