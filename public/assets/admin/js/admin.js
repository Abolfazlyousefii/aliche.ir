document.addEventListener('DOMContentLoaded', () => {
    const sidebarToggles = document.querySelectorAll('[data-admin-sidebar-toggle]');
    const sidebarClosers = document.querySelectorAll('[data-admin-sidebar-close]');

    const openSidebar = () => document.body.classList.add('admin-sidebar-open');
    const closeSidebar = () => document.body.classList.remove('admin-sidebar-open');

    sidebarToggles.forEach((button) => {
        button.addEventListener('click', () => {
            if (document.body.classList.contains('admin-sidebar-open')) {
                closeSidebar();
                return;
            }

            openSidebar();
        });
    });

    sidebarClosers.forEach((element) => {
        element.addEventListener('click', closeSidebar);
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const sortArea = document.querySelector('[data-menu-sort]');

    if (!sortArea) {
        return;
    }

    let draggedItem = null;

    sortArea.querySelectorAll('[data-menu-item-id]').forEach((item) => {
        item.addEventListener('dragstart', (event) => {
            draggedItem = item;
            item.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
        });

        item.addEventListener('dragend', () => {
            item.classList.remove('is-dragging');
            draggedItem = null;
        });
    });

    sortArea.querySelectorAll('[data-menu-list]').forEach((list) => {
        list.addEventListener('dragover', (event) => {
            event.preventDefault();
            const afterElement = getDragAfterElement(list, event.clientY);

            if (!draggedItem || draggedItem.contains(list)) {
                return;
            }

            if (afterElement == null) {
                list.appendChild(draggedItem);
            } else {
                list.insertBefore(draggedItem, afterElement);
            }
        });
    });

    sortArea.querySelector('[data-menu-save-sort]')?.addEventListener('click', async () => {
        const message = sortArea.querySelector('[data-menu-sort-message]');
        const rootList = sortArea.querySelector(':scope > [data-menu-list]');

        if (!rootList) {
            return;
        }

        message.textContent = 'در حال ذخیره...';

        const response = await fetch(sortArea.dataset.sortUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': sortArea.dataset.csrf,
            },
            body: JSON.stringify({ items: serializeMenuList(rootList) }),
        });

        message.textContent = response.ok ? 'ترتیب ذخیره شد.' : 'ذخیره ترتیب با خطا مواجه شد.';
    });
});

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll(':scope > [data-menu-item-id]:not(.is-dragging)')];

    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
            return { offset, element: child };
        }

        return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function serializeMenuList(list) {
    return [...list.querySelectorAll(':scope > [data-menu-item-id]')].map((item) => {
        const childList = item.querySelector(':scope > [data-menu-list]');

        return {
            id: Number(item.dataset.menuItemId),
            children: childList ? serializeMenuList(childList) : [],
        };
    });
}

function initializeJalaliDatepickers(root = document) {
    const jalaliInputs = root.querySelectorAll('[data-jalali-datepicker]:not([data-jalali-initialized])');

    jalaliInputs.forEach((element) => {
        const dateOnly = element.hasAttribute('data-jalali-date-only');
        element.setAttribute('dir', 'ltr');
        element.setAttribute('inputmode', 'numeric');
        element.setAttribute('placeholder', element.getAttribute('placeholder') || (dateOnly ? '1403/01/15' : '1403/01/15 10:30'));
        element.classList.add('jalali-date-input');
        element.setAttribute('data-jalali-initialized', 'true');
    });

    if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.persianDatepicker) {
        return;
    }

    window.jQuery(root).find('[data-jalali-datepicker]').addBack('[data-jalali-datepicker]').each(function initializeJalaliDatepicker() {
        const input = window.jQuery(this);
        const dateOnly = this.hasAttribute('data-jalali-date-only');

        input.persianDatepicker({
            autoClose: true,
            calendar: {
                persian: {
                    locale: 'fa',
                    showHint: true,
                },
            },
            format: dateOnly ? 'YYYY/MM/DD' : 'YYYY/MM/DD HH:mm',
            initialValue: false,
            observer: true,
            timePicker: {
                enabled: !dateOnly,
                meridiem: {
                    enabled: false,
                },
            },
            toolbox: {
                calendarSwitch: {
                    enabled: false,
                },
            },
        });
    });
}

window.initializeJalaliDatepickers = initializeJalaliDatepickers;
document.addEventListener('DOMContentLoaded', () => initializeJalaliDatepickers());

document.addEventListener('DOMContentLoaded', () => {
    const deleteModalElement = document.getElementById('adminDeleteModal');

    if (!deleteModalElement || !window.bootstrap) {
        return;
    }

    const deleteModal = new window.bootstrap.Modal(deleteModalElement);
    const confirmButton = deleteModalElement.querySelector('[data-admin-delete-confirm]');
    const messageElement = deleteModalElement.querySelector('[data-admin-delete-message]');
    let pendingDeleteForm = null;

    document.querySelectorAll('form').forEach((form) => {
        const methodInput = form.querySelector('input[name="_method"]');
        const isDeleteForm = methodInput && methodInput.value.toUpperCase() === 'DELETE';

        if (!isDeleteForm) {
            return;
        }

        form.setAttribute('data-admin-delete-form', 'true');

        form.addEventListener('submit', (event) => {
            if (form.dataset.adminDeleteConfirmed === 'true') {
                return;
            }

            event.preventDefault();
            pendingDeleteForm = form;

            if (messageElement) {
                const rowTitle = form.closest('tr')?.querySelector('strong')?.textContent?.trim();
                messageElement.textContent = rowTitle ? `مورد انتخاب‌شده: ${rowTitle}` : '';
            }

            deleteModal.show();
        });
    });

    confirmButton?.addEventListener('click', () => {
        if (!pendingDeleteForm) {
            return;
        }

        pendingDeleteForm.dataset.adminDeleteConfirmed = 'true';
        deleteModal.hide();
        pendingDeleteForm.requestSubmit();
    });

    deleteModalElement.addEventListener('hidden.bs.modal', () => {
        if (pendingDeleteForm?.dataset.adminDeleteConfirmed !== 'true') {
            pendingDeleteForm = null;
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const markPageReady = () => {
        document.documentElement.classList.remove('loading', 'is-loading', 'admin-loading');
        document.body.classList.remove('loading', 'is-loading', 'admin-loading');
        document.body.classList.add('admin-page-ready');
    };

    markPageReady();
    window.addEventListener('load', markPageReady);
    window.addEventListener('pageshow', markPageReady);

    document.querySelectorAll('[data-admin-pagination] a[href]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const href = link.getAttribute('href');

            if (!href || href === '#' || link.getAttribute('aria-disabled') === 'true') {
                event.preventDefault();
                markPageReady();
                return;
            }

            link.dataset.adminPaginationClicked = 'true';
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-single-submit]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.submitted === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitted = 'true';
            const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');

            if (submitter) {
                const loadingText = submitter.getAttribute('data-loading-text');
                if (loadingText && 'textContent' in submitter) {
                    submitter.textContent = loadingText;
                }
                submitter.setAttribute('aria-disabled', 'true');
                submitter.disabled = true;
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const pickerUrl = window.adminMediaPickerUrl;

    if (!pickerUrl) {
        return;
    }

    let mediaItems = [];
    let mediaLoaded = false;
    let activePicker = null;

    const escapeHtml = (value) => {
        const element = document.createElement('div');
        element.textContent = value ?? '';
        return element.innerHTML;
    };

    const ensureModal = () => {
        let modal = document.querySelector('[data-admin-media-picker-modal]');
        if (modal) return modal;

        modal = document.createElement('div');
        modal.className = 'admin-wp-media-modal';
        modal.setAttribute('data-admin-media-picker-modal', '');
        modal.hidden = true;
        modal.innerHTML = `
            <div class="admin-wp-modal-backdrop" data-admin-media-picker-close></div>
            <div class="admin-wp-modal-dialog admin-media-picker-dialog" role="dialog" aria-modal="true" aria-label="انتخاب تصویر از کتابخانه">
                <header><strong>انتخاب تصویر از کتابخانه</strong><button type="button" data-admin-media-picker-close>×</button></header>
                <div class="admin-media-picker-modal-body">
                    <div class="admin-media-picker-modal-main">
                        <form class="admin-wp-upload mb-3" data-admin-media-picker-upload>
                            <input class="admin-wp-upload-input" id="adminMediaPickerUpload" name="files[]" type="file" accept="image/*" multiple>
                            <label class="admin-wp-dropzone" for="adminMediaPickerUpload"><span class="admin-wp-upload-icon">☁️</span><strong>آپلود در همین پنجره</strong><small>تصاویر بعد از آپلود به کتابخانه اضافه می‌شوند و پیش‌نمایش دارند.</small></label>
                            <div class="admin-wp-upload-meta"><span data-admin-media-picker-upload-status>برای افزودن تصویر جدید فایل انتخاب کنید.</span><button class="admin-primary-btn" type="submit">آپلود و افزودن به کتابخانه</button></div>
                        </form>
                        <div class="admin-wp-toolbar"><input class="form-control" type="search" placeholder="جستجوی تصویر..." data-admin-media-picker-search><small data-admin-media-picker-status>در حال دریافت تصاویر...</small></div>
                        <div class="admin-wp-media-grid" data-admin-media-picker-grid></div>
                    </div>
                    <aside class="admin-media-picker-sidebar"><strong>پیش‌نمایش انتخاب</strong><div data-admin-media-picker-preview class="admin-media-picker-preview"><span>یک تصویر انتخاب کنید.</span></div><button class="admin-primary-btn w-100 mt-3" type="button" data-admin-media-picker-apply disabled>انتخاب تصویر</button></aside>
                </div>
            </div>`;
        document.body.appendChild(modal);
        modal.querySelectorAll('[data-admin-media-picker-close]').forEach((button) => button.addEventListener('click', () => { modal.hidden = true; }));
        modal.querySelector('[data-admin-media-picker-upload]')?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const form = event.currentTarget;
            const input = form.querySelector('input[type="file"]');
            const status = form.querySelector('[data-admin-media-picker-upload-status]');
            if (!window.adminMediaUploadUrl || !input?.files?.length) {
                if (status) status.textContent = 'ابتدا حداقل یک تصویر انتخاب کنید.';
                return;
            }
            const body = new FormData();
            [...input.files].forEach((file) => body.append('files[]', file));
            if (status) status.textContent = 'در حال آپلود...';
            const response = await fetch(window.adminMediaUploadUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body,
            });
            if (!response.ok) {
                if (status) status.textContent = 'آپلود ناموفق بود.';
                return;
            }
            const data = await response.json();
            const uploaded = data.items || [];
            mediaItems = [...uploaded, ...mediaItems.filter((item) => !uploaded.some((newItem) => String(newItem.id) === String(item.id)))];
            mediaLoaded = true;
            uploaded.forEach((item) => activePicker?.selectedIds.add(String(item.id)));
            input.value = '';
            if (status) status.textContent = `${uploaded.length} تصویر آپلود و به کتابخانه اضافه شد.`;
            renderModal();
        });
        modal.querySelector('[data-admin-media-picker-search]')?.addEventListener('input', () => renderModal());
        modal.querySelector('[data-admin-media-picker-grid]')?.addEventListener('click', (event) => {
            const tile = event.target.closest('[data-admin-media-picker-item]');
            if (!tile || !activePicker) return;
            const id = tile.dataset.id;
            if (activePicker.multiple) {
                activePicker.selectedIds.has(id) ? activePicker.selectedIds.delete(id) : activePicker.selectedIds.add(id);
            } else {
                activePicker.selectedIds = new Set([id]);
            }
            renderModal();
        });
        modal.querySelector('[data-admin-media-picker-apply]')?.addEventListener('click', () => {
            if (!activePicker) return;
            activePicker.onApply([...activePicker.selectedIds]);
            modal.hidden = true;
        });
        return modal;
    };

    const loadMedia = async () => {
        if (mediaLoaded) return mediaItems;

        const response = await fetch(pickerUrl, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error(response.status === 403
                ? 'برای مشاهده کتابخانه رسانه دسترسی لازم را ندارید.'
                : 'دریافت تصاویر کتابخانه ناموفق بود.');
        }

        const data = await response.json();
        mediaItems = data.items || [];
        mediaLoaded = true;
        return mediaItems;
    };

    const renderModal = () => {
        const modal = ensureModal();
        const grid = modal.querySelector('[data-admin-media-picker-grid]');
        const status = modal.querySelector('[data-admin-media-picker-status]');
        const preview = modal.querySelector('[data-admin-media-picker-preview]');
        const apply = modal.querySelector('[data-admin-media-picker-apply]');
        const search = (modal.querySelector('[data-admin-media-picker-search]')?.value || '').trim().toLowerCase();
        const items = mediaItems.filter((item) => !search || `${item.title || ''} ${item.alt || ''}`.toLowerCase().includes(search));
        grid.innerHTML = items.map((item) => `<button class="admin-wp-media-tile${activePicker?.selectedIds.has(String(item.id)) ? ' is-selected' : ''}" type="button" data-admin-media-picker-item data-id="${escapeHtml(item.id)}"><img src="${escapeHtml(item.url)}" alt="${escapeHtml(item.alt || item.title || '')}"><span class="admin-wp-media-check">✓</span><span class="admin-wp-media-title">${escapeHtml(item.title || 'تصویر')}</span></button>`).join('') || '<p class="text-muted p-3">تصویری در کتابخانه پیدا نشد.</p>';
        const chosen = mediaItems.filter((item) => activePicker?.selectedIds.has(String(item.id)));
        preview.innerHTML = chosen.length ? chosen.map((item) => `<figure><img src="${escapeHtml(item.url)}" alt="${escapeHtml(item.alt || item.title || '')}"><figcaption>${escapeHtml(item.title || 'تصویر')}</figcaption></figure>`).join('') : '<span>یک تصویر انتخاب کنید.</span>';
        status.textContent = mediaLoaded ? `${items.length} تصویر آماده انتخاب است.` : 'در حال دریافت تصاویر...';
        apply.disabled = chosen.length === 0;
        apply.textContent = activePicker?.multiple ? 'انتخاب تصاویر' : 'انتخاب تصویر';
    };

    const openPicker = async ({ multiple = false, selectedIds = [], onApply }) => {
        const modal = ensureModal();
        activePicker = { multiple, selectedIds: new Set(selectedIds.map(String).filter(Boolean)), onApply };
        modal.hidden = false;
        renderModal();

        try {
            await loadMedia();
        } catch (error) {
            const status = modal.querySelector('[data-admin-media-picker-status]');
            const grid = modal.querySelector('[data-admin-media-picker-grid]');
            if (status) status.textContent = error.message;
            if (grid) grid.innerHTML = `<p class="text-danger p-3">${escapeHtml(error.message)}</p>`;
            return;
        }

        renderModal();
    };

    document.querySelectorAll('[data-media-select-target]').forEach((button) => {
        button.addEventListener('click', () => {
            const select = document.getElementById(button.dataset.mediaSelectTarget);
            if (!select) return;
            const multiple = button.dataset.mediaSelectMultiple === 'true' || select.multiple;
            openPicker({
                multiple,
                selectedIds: [...select.selectedOptions].map((option) => option.value),
                onApply: (ids) => {
                    ids.forEach((id) => {
                        if ([...select.options].some((option) => option.value === String(id))) return;
                        const item = mediaItems.find((media) => String(media.id) === String(id));
                        if (!item) return;
                        const option = new Option(item.title || item.original_name || 'تصویر', item.id, false, false);
                        option.dataset.url = item.url;
                        select.add(option);
                        select.tomselect?.addOption({value: String(item.id), text: item.title || item.original_name || 'تصویر', url: item.url});
                    });

                    if (select.tomselect) {
                        select.tomselect.setValue(multiple ? ids : (ids[0] || ''), true);
                    } else {
                        [...select.options].forEach((option) => { option.selected = ids.includes(option.value); });
                    }
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                },
            });
        });
    });

    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]:not([data-skip-media-picker])');
    imageInputs.forEach((input) => {
        if (!input.name || input.dataset.mediaPickerReady === 'true') return;
        input.dataset.mediaPickerReady = 'true';
        const isMultiple = input.multiple || input.name.endsWith('[]');
        const baseName = input.name.replace(/\[\]$/, '');
        const hiddenName = isMultiple ? `${baseName}_media_ids[]` : `${baseName}_media_id`;
        const wrapper = document.createElement('div');
        wrapper.className = 'admin-media-picker mt-2';
        wrapper.innerHTML = `<button class="admin-secondary-btn" type="button" data-media-picker-open>انتخاب از کتابخانه با پیش‌نمایش</button><small class="text-muted d-block mt-2" data-media-picker-status>می‌توانید به‌جای آپلود فایل جدید، عکس را از کتابخانه انتخاب کنید.</small><div class="row g-2 mt-2" data-media-picker-selected></div>`;
        input.insertAdjacentElement('afterend', wrapper);
        const selected = wrapper.querySelector('[data-media-picker-selected]');
        const status = wrapper.querySelector('[data-media-picker-status]');
        const renderSelected = (ids) => {
            selected.innerHTML = ids.map((id) => {
                const item = mediaItems.find((media) => String(media.id) === String(id));
                return item ? `<div class="col-4 col-md-3" data-media-picker-selection><div class="border rounded p-1 position-relative"><input type="hidden" name="${escapeHtml(hiddenName)}" value="${escapeHtml(item.id)}"><img src="${escapeHtml(item.url)}" alt="${escapeHtml(item.title || '')}" class="img-fluid rounded" style="height:82px;width:100%;object-fit:cover"><button type="button" class="btn btn-sm btn-light position-absolute top-0 start-0" data-media-picker-remove aria-label="حذف">×</button></div></div>` : '';
            }).join('');
        };
        wrapper.querySelector('[data-media-picker-open]')?.addEventListener('click', () => openPicker({
            multiple: isMultiple,
            selectedIds: [...selected.querySelectorAll('input[type="hidden"]')].map((hidden) => hidden.value),
            onApply: (ids) => { renderSelected(ids); status.textContent = isMultiple ? 'تصاویر از کتابخانه انتخاب شدند.' : 'تصویر از کتابخانه انتخاب شد.'; },
        }));
        selected.addEventListener('click', (event) => {
            if (event.target.closest('[data-media-picker-remove]')) event.target.closest('[data-media-picker-selection]')?.remove();
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const slugify = (value) => value
        .trim()
        .replace(/[\s_\u200c]+/g, '-')
        .replace(/[^\u0600-\u06FF\p{L}\p{N}-]+/gu, '')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '')
        .toLowerCase();

    document.querySelectorAll('form').forEach((form) => {
        const titleInput = form.querySelector('#title, [name="title"]');
        const slugInput = form.querySelector('#slug, [name="slug"]');

        if (!titleInput || !slugInput || slugInput.dataset.autoSlugReady === 'true') {
            return;
        }

        slugInput.dataset.autoSlugReady = 'true';
        let slugTouched = Boolean(slugInput.value.trim());

        slugInput.addEventListener('input', () => {
            slugTouched = Boolean(slugInput.value.trim());
        });

        titleInput.addEventListener('input', () => {
            if (slugTouched) {
                return;
            }

            slugInput.value = slugify(titleInput.value);
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-media-modal]');
    const modalBody = document.querySelector('[data-media-modal-body]');
    const tiles = document.querySelectorAll('[data-media-tile]');

    if (modal && modalBody) {
        const close = () => {
            modal.hidden = true;
            modalBody.innerHTML = '';
            tiles.forEach((tile) => tile.classList.remove('is-selected'));
        };

        document.querySelectorAll('[data-media-modal-close]').forEach((button) => button.addEventListener('click', close));
        tiles.forEach((tile) => {
            const open = () => {
                const template = tile.querySelector('template[data-media-details]');
                if (!template) return;
                tiles.forEach((item) => item.classList.remove('is-selected'));
                tile.classList.add('is-selected');
                modalBody.innerHTML = template.innerHTML;
                modal.hidden = false;
            };
            tile.addEventListener('click', open);
            tile.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    open();
                }
            });
        });
        modalBody.addEventListener('click', async (event) => {
            const copyButton = event.target.closest('[data-copy-url]');
            if (!copyButton) return;
            await navigator.clipboard.writeText(copyButton.dataset.copyUrl || '');
            copyButton.textContent = 'کپی شد';
        });
        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) close();
        });
    }

    document.querySelectorAll('[data-media-dropzone]').forEach((form) => {
        const input = form.querySelector('input[type="file"]');
        const count = form.querySelector('[data-media-file-count]');
        if (!input || !count) return;
        const updateCount = () => {
            count.textContent = input.files.length ? `${input.files.length} فایل آماده آپلود است.` : 'هیچ فایلی انتخاب نشده است.';
        };
        input.addEventListener('change', updateCount);
        form.addEventListener('dragover', (event) => {
            event.preventDefault();
            form.classList.add('is-dragover');
        });
        form.addEventListener('dragleave', () => form.classList.remove('is-dragover'));
        form.addEventListener('drop', (event) => {
            event.preventDefault();
            form.classList.remove('is-dragover');
            if (event.dataTransfer?.files?.length) {
                input.files = event.dataTransfer.files;
                updateCount();
            }
        });
    });

    document.querySelectorAll('input[name="sort_order"], input[name$="[sort_order]"]').forEach((input) => {
        if (input.dataset.sortHelpReady === 'true') return;
        input.dataset.sortHelpReady = 'true';
        input.insertAdjacentHTML('afterend', '<small class="sort-order-help"><strong>راهنما:</strong> عدد کمتر زودتر و بالاتر نمایش داده می‌شود؛ برای ترتیب دقیق از ۱۰، ۲۰، ۳۰ استفاده کنید تا بین موارد جا داشته باشید.</small>');
    });
});


function setupAccessibleFormValidation(root = document) {
    const forms = Array.from(root.querySelectorAll('form')).filter((form) => !form.hasAttribute('data-native-validation'));

    if (!forms.length) return;

    const labels = {
        valueMissing: 'این فیلد الزامی است و نباید خالی بماند.',
        typeMismatch: {
            email: 'فرمت ایمیل درست نیست. لطفاً ایمیل را به شکل name@example.com وارد کنید.',
            url: 'فرمت نشانی وب درست نیست. لطفاً آدرس را با http:// یا https:// وارد کنید.',
            default: 'فرمت مقدار واردشده درست نیست.',
        },
        tooShort: (field) => `این فیلد باید حداقل ${field.getAttribute('minlength')} کاراکتر باشد.`,
        tooLong: (field) => `این فیلد نباید بیشتر از ${field.getAttribute('maxlength')} کاراکتر باشد.`,
        patternMismatch: 'مقدار واردشده با قالب مورد انتظار سازگار نیست. لطفاً نمونه یا راهنمای کنار فیلد را بررسی کنید.',
        rangeUnderflow: (field) => `مقدار این فیلد نباید کمتر از ${field.getAttribute('min')} باشد.`,
        rangeOverflow: (field) => `مقدار این فیلد نباید بیشتر از ${field.getAttribute('max')} باشد.`,
        stepMismatch: 'مقدار واردشده با گام مجاز این فیلد سازگار نیست.',
        badInput: 'مقدار واردشده قابل خواندن نیست. لطفاً آن را اصلاح کنید.',
    };

    const getFieldLabel = (field) => {
        if (field.id) {
            const label = root.querySelector(`label[for="${CSS.escape(field.id)}"]`);
            if (label) return label.textContent.replace(/\s+/g, ' ').trim();
        }
        return field.getAttribute('aria-label') || field.getAttribute('placeholder') || field.name || 'این فیلد';
    };

    const getMessage = (field) => {
        const validity = field.validity;
        if (validity.valueMissing) return labels.valueMissing;
        if (validity.typeMismatch) return labels.typeMismatch[field.type] || labels.typeMismatch.default;
        if (validity.tooShort) return labels.tooShort(field);
        if (validity.tooLong) return labels.tooLong(field);
        if (validity.patternMismatch) return field.getAttribute('title') || labels.patternMismatch;
        if (validity.rangeUnderflow) return labels.rangeUnderflow(field);
        if (validity.rangeOverflow) return labels.rangeOverflow(field);
        if (validity.stepMismatch) return labels.stepMismatch;
        if (validity.badInput) return labels.badInput;
        return field.validationMessage || 'مقدار این فیلد معتبر نیست.';
    };

    const clearFieldError = (field) => {
        field.classList.remove('is-invalid');
        field.removeAttribute('aria-invalid');
        const feedback = field.parentElement?.querySelector(`[data-client-error-for="${CSS.escape(field.name || field.id)}"]`);
        feedback?.remove();
    };

    const showFieldError = (field, message) => {
        clearFieldError(field);
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block text-danger small mt-1';
        feedback.dataset.clientErrorFor = field.name || field.id;
        feedback.textContent = message;
        field.insertAdjacentElement('afterend', feedback);
    };

    forms.forEach((form) => {
        form.noValidate = true;
        form.addEventListener('submit', (event) => {
            const invalidFields = Array.from(form.elements).filter((field) => field instanceof HTMLElement && 'validity' in field && !field.validity.valid);
            form.querySelector('[data-client-validation-summary]')?.remove();
            Array.from(form.querySelectorAll('[data-client-error-for]')).forEach((el) => el.remove());
            Array.from(form.querySelectorAll('.is-invalid[aria-invalid="true"]')).forEach((field) => clearFieldError(field));

            if (!invalidFields.length) return;

            event.preventDefault();
            const summary = document.createElement('div');
            summary.className = 'alert alert-danger';
            summary.setAttribute('role', 'alert');
            summary.setAttribute('tabindex', '-1');
            summary.dataset.clientValidationSummary = 'true';
            const list = document.createElement('ul');
            list.className = 'mb-0';
            invalidFields.forEach((field) => {
                const label = getFieldLabel(field);
                const message = getMessage(field);
                showFieldError(field, message);
                const item = document.createElement('li');
                item.textContent = `${label}: ${message}`;
                list.appendChild(item);
            });
            summary.appendChild(list);
            form.prepend(summary);
            summary.focus({ preventScroll: true });
            summary.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        form.addEventListener('input', (event) => {
            const field = event.target;
            if (field instanceof HTMLElement && field.matches('.is-invalid') && 'validity' in field && field.validity.valid) clearFieldError(field);
        });
    });
}


document.addEventListener('DOMContentLoaded', () => setupAccessibleFormValidation());

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-icon-picker]').forEach((picker) => {
        const input = picker.querySelector('[data-icon-picker-input]');
        const buttons = Array.from(picker.querySelectorAll('[data-icon-value]'));
        if (!input) return;

        const sync = () => {
            buttons.forEach((button) => button.classList.toggle('is-selected', button.dataset.iconValue === input.value.trim()));
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                input.value = button.dataset.iconValue || '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                sync();
            });
        });

        input.addEventListener('input', sync);
        sync();
    });
});
