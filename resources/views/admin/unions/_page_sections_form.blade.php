@php
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
@endphp

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
                <div class="union-dynamic-section" data-section="commissions" data-next-index="{{ $commissions->count() }}">
                    <div data-rows>
                        @foreach($commissions as $index => $commission)
                            <div class="border rounded p-3 mb-3" data-row>
                                <input type="hidden" name="related[commissions][{{ $index }}][id]" value="{{ $valueOf($commission, 'id') }}">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4"><label class="form-label">عنوان کمیسیون</label><input class="form-control" name="related[commissions][{{ $index }}][title]" value="{{ $valueOf($commission, 'title') }}"></div>
                                    <div class="col-md-2"><label class="form-label">آیکن</label><input class="form-control" name="related[commissions][{{ $index }}][icon]" value="{{ $valueOf($commission, 'icon') }}"></div>
                                    <div class="col-md-2"><label class="form-label">ترتیب</label><input class="form-control" type="number" min="0" name="related[commissions][{{ $index }}][sort_order]" value="{{ $valueOf($commission, 'sort_order', 0) }}"></div>
                                    <div class="col-md-2"><label class="form-label">وضعیت</label><select class="form-control" name="related[commissions][{{ $index }}][is_active]"><option value="1" @selected((string) $valueOf($commission, 'is_active', 1) === '1')>فعال</option><option value="0" @selected((string) $valueOf($commission, 'is_active', 1) === '0')>غیرفعال</option></select></div>
                                    <div class="col-md-2"><label class="form-check"><input class="form-check-input" type="checkbox" name="related[commissions][{{ $index }}][delete]" value="1"> حذف</label></div>
                                    <div class="col-12"><label class="form-label">توضیحات کمیسیون</label><textarea class="form-control js-rich-editor" name="related[commissions][{{ $index }}][description]" rows="2">{{ $valueOf($commission, 'description') }}</textarea></div>
                                </div>
                                @php($tasks = collect(is_array($commission) ? ($commission['tasks'] ?? []) : ($commission->tasks ?? [])))
                                <div class="mt-3 ps-md-3 border-start union-dynamic-section" data-section="commission-tasks" data-commission-index="{{ $index }}" data-next-index="{{ $tasks->count() }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2"><strong>وظایف کمیسیون</strong><button class="btn btn-sm btn-outline-primary" type="button" data-add-row>افزودن وظیفه</button></div>
                                    <div data-rows>
                                        @foreach($tasks as $taskIndex => $task)
                                            <div class="row g-2 align-items-end mb-2" data-row>
                                                <input type="hidden" name="related[commissions][{{ $index }}][tasks][{{ $taskIndex }}][id]" value="{{ $valueOf($task, 'id') }}">
                                                <div class="col-md-4"><input class="form-control" name="related[commissions][{{ $index }}][tasks][{{ $taskIndex }}][title]" placeholder="عنوان وظیفه" value="{{ $valueOf($task, 'title') }}"></div>
                                                <div class="col-md-4"><input class="form-control" name="related[commissions][{{ $index }}][tasks][{{ $taskIndex }}][description]" placeholder="توضیح" value="{{ $valueOf($task, 'description') }}"></div>
                                                <div class="col-md-2"><input class="form-control" type="number" min="0" name="related[commissions][{{ $index }}][tasks][{{ $taskIndex }}][sort_order]" value="{{ $valueOf($task, 'sort_order', 0) }}"></div>
                                                <div class="col-md-1"><select class="form-control" name="related[commissions][{{ $index }}][tasks][{{ $taskIndex }}][is_active]"><option value="1" @selected((string) $valueOf($task, 'is_active', 1) === '1')>فعال</option><option value="0" @selected((string) $valueOf($task, 'is_active', 1) === '0')>غیرفعال</option></select></div>
                                                <div class="col-md-1"><label class="form-check"><input class="form-check-input" type="checkbox" name="related[commissions][{{ $index }}][tasks][{{ $taskIndex }}][delete]" value="1"> حذف</label></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="btn btn-outline-primary" type="button" data-add-row>افزودن کمیسیون</button>
                </div>
            </div></div>
        </div>

        @foreach([
            'rules' => ['title' => 'قوانین و دستورالعمل‌ها', 'rows' => $rules, 'fields' => ['title' => 'عنوان', 'description' => 'توضیحات', 'icon' => 'آیکن', 'file' => 'لینک فایل']],
            'minutes' => ['title' => 'صورتجلسه‌ها', 'rows' => $minutes, 'fields' => ['title' => 'عنوان', 'meeting_date' => 'تاریخ جلسه', 'description' => 'توضیحات', 'file' => 'لینک فایل']],
            'educations' => ['title' => 'آموزش‌ها', 'rows' => $educations, 'fields' => ['title' => 'عنوان', 'description' => 'توضیحات', 'icon' => 'آیکن', 'link' => 'لینک']],
            'prices' => ['title' => 'نرخ‌نامه', 'rows' => $prices, 'fields' => ['title' => 'عنوان', 'price' => 'قیمت', 'currency' => 'واحد', 'type' => 'نوع', 'updated_on' => 'تاریخ بروزرسانی']],
        ] as $sectionKey => $section)
            <div class="accordion-item">
                <h4 class="accordion-header" id="heading{{ $sectionKey }}"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $sectionKey }}" aria-expanded="false" aria-controls="collapse{{ $sectionKey }}">{{ $section['title'] }}</button></h4>
                <div class="accordion-collapse collapse" id="collapse{{ $sectionKey }}" aria-labelledby="heading{{ $sectionKey }}" data-bs-parent="#unionPageSectionsAccordion"><div class="accordion-body">
                    <div class="union-dynamic-section" data-section="{{ $sectionKey }}" data-next-index="{{ $section['rows']->count() }}">
                        <div data-rows>
                            @foreach($section['rows'] as $index => $row)
                                <div class="border rounded p-3 mb-3" data-row>
                                    <input type="hidden" name="related[{{ $sectionKey }}][{{ $index }}][id]" value="{{ $valueOf($row, 'id') }}">
                                    <div class="row g-2 align-items-end">
                                        @foreach($section['fields'] as $field => $label)
                                            <div class="col-md-{{ in_array($field, ['description'], true) ? '12' : '3' }}">
                                                <label class="form-label">{{ $label }}</label>
                                                @if($field === 'description')
                                                    <textarea class="form-control js-rich-editor" name="related[{{ $sectionKey }}][{{ $index }}][{{ $field }}]" rows="2">{{ $valueOf($row, $field) }}</textarea>
                                                @else
                                                    <input class="form-control" @if(in_array($field, ['meeting_date', 'updated_on'], true)) type="text" data-jalali-datepicker data-jalali-date-only dir="ltr" @elseif($field === 'price') type="number" step="0.01" min="0" @endif name="related[{{ $sectionKey }}][{{ $index }}][{{ $field }}]" value="{{ in_array($field, ['meeting_date', 'updated_on'], true) ? $dateValue($row, $field) : $valueOf($row, $field) }}">
                                                @endif
                                            </div>
                                        @endforeach
                                        <div class="col-md-2"><label class="form-label">ترتیب</label><input class="form-control" type="number" min="0" name="related[{{ $sectionKey }}][{{ $index }}][sort_order]" value="{{ $valueOf($row, 'sort_order', 0) }}"></div>
                                        <div class="col-md-2"><label class="form-label">وضعیت</label><select class="form-control" name="related[{{ $sectionKey }}][{{ $index }}][is_active]"><option value="1" @selected((string) $valueOf($row, 'is_active', 1) === '1')>فعال</option><option value="0" @selected((string) $valueOf($row, 'is_active', 1) === '0')>غیرفعال</option></select></div>
                                        <div class="col-md-2"><label class="form-check"><input class="form-check-input" type="checkbox" name="related[{{ $sectionKey }}][{{ $index }}][delete]" value="1"> حذف</label></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="btn btn-outline-primary" type="button" data-add-row>افزودن {{ $section['title'] }}</button>
                    </div>
                </div></div>
            </div>
        @endforeach
    </div>
</div>

@once
    @push('scripts')
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
                    return `<div class="border rounded p-3 mb-2" data-row><div class="row g-2 align-items-end"><div class="col-md-3"><label class="form-label">عنوان</label><input class="form-control" name="president_buttons[${index}][title]"></div><div class="col-md-3"><label class="form-label">لینک</label><input class="form-control" name="president_buttons[${index}][url]" dir="ltr"></div><div class="col-md-2"><label class="form-label">آیکون</label><input class="form-control" name="president_buttons[${index}][icon]" placeholder="phone, email, document"></div><div class="col-md-2"><label class="form-label">باز شدن</label><select class="form-control" name="president_buttons[${index}][target]"><option value="_self">همان صفحه</option><option value="_blank">صفحه جدید</option></select></div><div class="col-md-2"><label class="form-check"><input class="form-check-input" type="checkbox" name="president_buttons[${index}][is_active]" value="1" checked> فعال</label></div></div></div>`;
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
    @endpush
@endonce
