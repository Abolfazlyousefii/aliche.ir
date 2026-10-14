/* Unified, accessible Media Manager V2. Uses existing Laravel media routes. */
(function (root) {
    'use strict';
    if (typeof document === 'undefined') return;
    // Prevent the two legacy media-picker implementations in admin.js from
    // attaching a second modal to the same controls.
    root.adminMediaManagerV2 = true;
    root.adminEnhancedMediaPicker = true;

    const $ = (selector, scope = document) => scope.querySelector(selector);
    const $$ = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));
    const esc = (value) => {
        const node = document.createElement('div');
        node.textContent = value == null ? '' : String(value);
        return node.innerHTML;
    };
    const state = {
        items: new Map(), selected: new Map(), active: null, page: 0, pages: 1,
        total: 0, search: '', type: 'all', sort: 'newest', loading: false,
        request: null, queryId: 0, queue: null,
    };
    let modal, grid, count, preview, apply, loadMore, uploadForm, searchTimer;
    const isUploadAllowed = !!root.adminMediaCanUpload;
    const visibleSelection = () => state.active ? Array.from(state.active.ids) : [];
    const getItem = (id) => state.selected.get(String(id)) || state.items.get(String(id));
    const close = () => {
        if (state.queue?.isBusy()) return;
        modal.hidden = true;
        document.body.classList.remove('mm2-modal-open');
        state.request?.abort();
    };

    const selectedDetails = () => {
        const ids = visibleSelection();
        count.textContent = ids.length.toLocaleString('fa-IR') + ' تصویر انتخاب‌شده';
        apply.disabled = !!state.queue?.isBusy();
        apply.textContent = state.active?.multiple ? 'افزودن تصاویر انتخاب‌شده' : 'انتخاب تصویر';
        // Render a compact selection strip; the rest remains in the grid.
        preview.replaceChildren();
        ids.slice(-40).forEach((id) => {
            const item = getItem(id);
            const el = document.createElement('div');
            el.className = 'mm2-selected-item';
            el.innerHTML = item
                ? '<img src="' + esc(item.thumbnail || item.url) + '" alt=""><span>' + esc(item.title || item.original_name || id) + '</span>'
                : '<span>تصویر شماره ' + esc(id) + '</span>';
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.textContent = '×';
            remove.setAttribute('aria-label', 'حذف از انتخاب');
            remove.addEventListener('click', () => {
                state.active?.ids.delete(String(id));
                render();
            });
            el.appendChild(remove);
            preview.appendChild(el);
        });
        if (ids.length > 40) {
            const more = document.createElement('small');
            more.textContent = (ids.length - 40).toLocaleString('fa-IR') + ' تصویر دیگر نیز انتخاب شده است.';
            preview.prepend(more);
        }
    };

    const render = () => {
        const items = $$('.mm2-grid [data-mm2-id]', modal);
        items.forEach((el) => {
            const selected = !!state.active?.ids.has(el.dataset.mm2Id);
            el.classList.toggle('is-selected', selected);
            el.setAttribute('aria-pressed', String(selected));
        });
        selectedDetails();
        $('[data-mm2-library-count]', modal).textContent =
            state.items.size.toLocaleString('fa-IR') + ' از ' + state.total.toLocaleString('fa-IR') + ' تصویر';
        loadMore.hidden = state.page >= state.pages || state.loading;
    };

    const addItems = (items) => {
        items.forEach((item) => {
            if (!item || !item.id) return;
            const id = String(item.id);
            state.items.set(id, item);
            const el = document.createElement('button');
            el.type = 'button';
            el.className = 'mm2-tile';
            el.dataset.mm2Id = id;
            el.innerHTML = '<img src="' + esc(item.thumbnail || item.url) + '" alt="' + esc(item.alt || item.title || '') +
                '" loading="lazy"><span class="mm2-tile-check">✓</span><small>' +
                esc(item.title || item.original_name || 'تصویر') + '</small>';
            const old = $('[data-mm2-id="' + id + '"]', grid);
            if (old) old.replaceWith(el);
            else grid.appendChild(el);
        });
    };

    const loadPage = async (reset = false) => {
        if (!root.adminMediaPickerUrl) return;
        if (state.loading && !reset) return;
        if (!reset && state.page >= state.pages) return;
        if (reset) {
            state.request?.abort();
            state.page = 0; state.pages = 1; state.items.clear();
            grid.replaceChildren();
        }
        const serial = ++state.queryId;
        const page = state.page + 1;
        const controller = new AbortController();
        state.request = controller;
        state.loading = true;
        $('[data-mm2-load-state]', modal).textContent = 'در حال دریافت تصاویر…';
        loadMore.disabled = true;
        try {
            const query = new URLSearchParams({
                page: String(page), per_page: '36', search: state.search,
                sort: state.sort, type: state.type,
            });
            const response = await fetch(root.adminMediaPickerUrl + '?' + query, {
                credentials: 'same-origin', headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            if (!response.ok) throw new Error(response.status === 403
                ? 'مجوز مشاهده کتابخانه رسانه وجود ندارد.'
                : 'بارگذاری تصاویر با خطای HTTP ' + response.status + ' مواجه شد.');
            const body = await response.json();
            if (serial !== state.queryId) return;
            addItems(body.items || body.data || []);
            state.page = Number(body.current_page || page);
            state.pages = Number(body.last_page || 1);
            state.total = Number(body.total || state.items.size);
            $('[data-mm2-load-state]', modal).textContent = state.page >= state.pages
                ? 'همه تصاویر نمایش داده شدند.' : 'برای دیدن تصاویر بیشتر ادامه دهید.';
        } catch (error) {
            if (error.name !== 'AbortError' && serial === state.queryId) {
                $('[data-mm2-load-state]', modal).textContent = error.message;
                loadMore.hidden = false; // manual retry remains available
            }
        } finally {
            if (serial === state.queryId) {
                state.loading = false; loadMore.disabled = false; render();
            }
        }
    };

    const setTab = (tab) => {
        $$('[data-mm2-tab]', modal).forEach((button) =>
            button.setAttribute('aria-selected', String(button.dataset.mm2Tab === tab)));
        $$('[data-mm2-pane]', modal).forEach((pane) =>
            pane.hidden = pane.dataset.mm2Pane !== tab);
    };

    const open = ({ multiple = false, selectedIds = [], onApply, selectedItems = [] }) => {
        if (!modal) return;
        const ids = selectedIds.map(String).filter(Boolean);
        state.active = { multiple, ids: new Set(multiple ? ids : ids.slice(-1)), onApply };
        selectedItems.forEach((item) => {
            if (item?.id && item?.url) state.selected.set(String(item.id), item);
        });
        state.search = ''; state.type = 'all'; state.sort = 'newest';
        $('[data-mm2-search]', modal).value = '';
        $('[data-mm2-type]', modal).value = 'all';
        $('[data-mm2-sort]', modal).value = 'newest';
        modal.hidden = false;
        document.body.classList.add('mm2-modal-open');
        setTab('library');
        selectedDetails();
        void loadPage(true);
    };

    const applyToSelect = (select, ids) => {
        // The DOM order of selected <option>s is the submitted Laravel array
        // order. Move them in selection order so 60+ images retain their order.
        Array.from(select.options).forEach((option) => { option.selected = false; });
        const chosen = select.multiple ? ids : ids.slice(-1);
        chosen.forEach((id) => {
            let option = Array.from(select.options).find((entry) => entry.value === String(id));
            const media = getItem(id);
            if (!option) {
                option = new Option(media?.title || media?.original_name || 'تصویر', String(id));
                if (media?.url) option.dataset.url = media.url;
                select.add(option);
            } else if (media?.url) {
                option.dataset.url = media.url;
            }
            option.selected = true;
            select.append(option);
        });
        select.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const pickerFor = (select) => {
        const existing = Array.from(select.selectedOptions);
        open({
            multiple: select.multiple,
            selectedIds: existing.map((option) => option.value),
            selectedItems: existing.map((option) => ({
                id: option.value, url: option.dataset.url,
                title: option.textContent, thumbnail: option.dataset.url,
            })),
            onApply: (ids) => applyToSelect(select, ids),
        });
    };

    const syncWorkspace = (form) => {
        const select = $('#gallery_media_ids', form);
        const workspace = $('[data-mm2-workspace]', form);
        if (!select || !workspace) return;
        const ids = new Set(Array.from(select.selectedOptions).map((option) => option.value));
        $$('[data-mm2-media-id]', workspace).forEach((card) => {
            if (!ids.has(card.dataset.mm2MediaId)) card.remove();
        });
        Array.from(select.selectedOptions).forEach((option) => {
            const id = option.value;
            if ($$('[data-mm2-media-id]', workspace).some((node) => node.dataset.mm2MediaId === id)) return;
            const media = getItem(id);
            const card = document.createElement('div');
            card.className = 'mm2-workspace-card';
            card.draggable = true;
            card.dataset.mm2Item = '';
            card.dataset.mm2MediaId = id;
            const url = option.dataset.url || media?.url || '';
            card.innerHTML = '<img src="' + esc(url) + '" alt="' + esc(option.textContent || 'تصویر') +
                '"><div class="mm2-workspace-caption">' + esc(option.textContent || 'تصویر') +
                '</div><div class="mm2-workspace-actions">' +
                '<button type="button" data-mm2-move="up" aria-label="انتقال به قبل">↑</button>' +
                '<button type="button" data-mm2-move="down" aria-label="انتقال به بعد">↓</button>' +
                '<button type="button" data-mm2-remove aria-label="حذف از انتخاب">×</button></div>';
            workspace.appendChild(card);
        });
        syncOrder(form);
        $('[data-mm2-gallery-count]', form).textContent =
            $$('[data-mm2-item]:not([hidden])', workspace).length.toLocaleString('fa-IR') + ' تصویر در گالری';
    };

    const syncOrder = (form) => {
        const holder = $('[data-mm2-order-fields]', form);
        const workspace = $('[data-mm2-workspace]', form);
        if (!holder || !workspace) return;
        holder.replaceChildren();
        $$('[data-mm2-item]:not([hidden])', workspace).forEach((card) => {
            const val = card.dataset.mm2MediaId ? 'media:' + card.dataset.mm2MediaId
                : 'existing:' + card.dataset.mm2ExistingId;
            if (!val || val.endsWith('undefined')) return;
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'gallery_order[]'; input.value = val;
            holder.appendChild(input);
        });
    };

    const bindGallery = (form) => {
        const select = $('#gallery_media_ids', form);
        const workspace = $('[data-mm2-workspace]', form);
        if (!select || !workspace) return;
        form.classList.add('mm2-enhanced');
        select.addEventListener('change', () => syncWorkspace(form));
        syncWorkspace(form);
        let dragged = null;
        workspace.addEventListener('dragstart', (event) => {
            dragged = event.target.closest('[data-mm2-item]');
            if (dragged) event.dataTransfer.effectAllowed = 'move';
        });
        workspace.addEventListener('dragover', (event) => {
            const target = event.target.closest('[data-mm2-item]');
            if (!dragged || !target || target === dragged) return;
            event.preventDefault();
            const rect = target.getBoundingClientRect();
            const after = event.clientY > rect.top + rect.height / 2;
            workspace.insertBefore(dragged, after ? target.nextSibling : target);
        });
        workspace.addEventListener('drop', (event) => { event.preventDefault(); syncOrder(form); });
        workspace.addEventListener('dragend', () => { dragged = null; syncOrder(form); });
        workspace.addEventListener('click', (event) => {
            const button = event.target.closest('[data-mm2-remove], [data-mm2-move]');
            const card = button?.closest('[data-mm2-item]');
            if (!card) return;
            if (button.hasAttribute('data-mm2-remove')) {
                if (card.dataset.mm2MediaId) {
                    const option = Array.from(select.options).find((item) => item.value === card.dataset.mm2MediaId);
                    if (option) option.selected = false;
                    card.remove();
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    const checkbox = $('[data-mm2-delete-existing]', card);
                    if (checkbox) checkbox.checked = true;
                    card.hidden = true;
                    syncOrder(form);
                    syncWorkspace(form);
                }
                return;
            }
            const direction = button.dataset.mm2Move;
            const others = $$('[data-mm2-item]:not([hidden])', workspace);
            const index = others.indexOf(card);
            const neighbour = direction === 'up' ? others[index - 1] : others[index + 1];
            if (neighbour) workspace.insertBefore(card, direction === 'up' ? neighbour : neighbour.nextSibling);
            syncOrder(form);
        });
        // Never submit while one or more uploads are still in progress.
        form.addEventListener('submit', (event) => {
            if (!state.queue?.isBusy()) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            alert('ابتدا منتظر پایان آپلود تصاویر بمانید.');
        }, true);
    };

    const bindCoverPreview = () => {
        const select = $('#cover_image_media_id');
        const previewNode = $('#coverMediaPreview');
        if (!select || !previewNode) return;
        select.addEventListener('change', () => {
            const option = select.selectedOptions[0];
            if (!option || !option.value || !option.dataset.url) return;
            previewNode.replaceChildren();
            const img = document.createElement('img');
            img.src = option.dataset.url; img.alt = 'تصویر کاور';
            img.className = 'mm2-cover-image';
            previewNode.appendChild(img);
        });
    };

    const initModal = () => {
        modal = document.createElement('div');
        modal.className = 'mm2-modal';
        modal.hidden = true;
        modal.innerHTML = '<div class="mm2-backdrop" data-mm2-close></div>' +
            '<section class="mm2-dialog" role="dialog" aria-modal="true" aria-labelledby="mm2Title" tabindex="-1">' +
            '<header class="mm2-header"><h2 id="mm2Title">کتابخانه رسانه</h2><button type="button" data-mm2-close aria-label="بستن">×</button></header>' +
            '<nav class="mm2-tabs" role="tablist"><button type="button" role="tab" data-mm2-tab="library" aria-selected="true">کتابخانه تصاویر</button>' +
            (isUploadAllowed ? '<button type="button" role="tab" data-mm2-tab="upload" aria-selected="false">بارگذاری تصاویر جدید</button>' : '') + '</nav>' +
            '<div class="mm2-dialog-body"><main class="mm2-main">' +
            '<div data-mm2-pane="library" class="mm2-pane">' +
            '<div class="mm2-filters"><input type="search" data-mm2-search aria-label="جستجوی تصاویر" placeholder="جستجو در نام، عنوان و توضیحات…">' +
            '<select data-mm2-type aria-label="فرمت"><option value="all">همه فرمت‌ها</option><option value="jpg">JPG</option><option value="png">PNG</option><option value="webp">WebP</option><option value="gif">GIF</option></select>' +
            '<select data-mm2-sort aria-label="مرتب‌سازی"><option value="newest">جدیدترین</option><option value="oldest">قدیمی‌ترین</option><option value="largest">حجم بیشتر</option></select></div>' +
            '<div class="mm2-grid" role="group" aria-label="فهرست تصاویر"></div>' +
            '<div class="mm2-load-row"><span data-mm2-library-count></span><span data-mm2-load-state aria-live="polite"></span>' +
            '<button type="button" data-mm2-more>تصاویر بیشتر</button></div></div>' +
            '<div data-mm2-pane="upload" class="mm2-pane" hidden>' +
            '<form data-mm2-upload class="mm2-upload-form"><input type="file" data-mm2-input multiple accept=".jpg,.jpeg,.png,.webp,.gif,.avif,image/*">' +
            '<label class="mm2-dropzone" data-mm2-drop>تصاویر را اینجا رها کنید یا برای انتخاب کلیک کنید' +
            '<small>۶۰ تصویر یا بیشتر؛ ارسال هر فایل به‌صورت مستقل؛ حداکثر ۵ مگابایت برای هر تصویر</small></label>' +
            '<div class="mm2-upload-actions"><button type="submit" class="admin-primary-btn">شروع آپلود</button>' +
            '<button type="button" class="admin-secondary-btn" data-mm2-retry hidden>تلاش مجدد فقط برای ناموفق‌ها</button></div>' +
            '<p data-mm2-status role="status" aria-live="polite">آماده بارگذاری</p>' +
            '<progress data-mm2-progress max="100" value="0" hidden></progress><ul data-mm2-errors></ul></form></div>' +
            '</main><aside class="mm2-aside"><strong>تصاویر انتخاب‌شده</strong><p data-mm2-count>۰ تصویر</p>' +
            '<div class="mm2-selected-list"></div><button class="admin-secondary-btn" type="button" data-mm2-clear>پاک کردن انتخاب‌ها</button></aside></div>' +
            '<footer class="mm2-footer"><span>تصاویر آپلودشده در کتابخانه می‌مانند؛ ثبت در گالری پس از ذخیره فرم است.</span>' +
            '<button type="button" class="admin-primary-btn" data-mm2-apply>افزودن تصاویر انتخاب‌شده</button></footer>' +
            '</section>';
        document.body.appendChild(modal);
        grid = $('.mm2-grid', modal);
        count = $('[data-mm2-count]', modal);
        preview = $('.mm2-selected-list', modal);
        apply = $('[data-mm2-apply]', modal);
        loadMore = $('[data-mm2-more]', modal);
        uploadForm = $('[data-mm2-upload]', modal);
        $$('[data-mm2-close]', modal).forEach((button) => button.addEventListener('click', close));
        modal.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
        });
        $$('[data-mm2-tab]', modal).forEach((button) => button.addEventListener('click', () => setTab(button.dataset.mm2Tab)));
        grid.addEventListener('click', (event) => {
            const card = event.target.closest('[data-mm2-id]');
            if (!card || !state.active) return;
            const id = card.dataset.mm2Id;
            if (state.active.multiple) {
                state.active.ids.has(id) ? state.active.ids.delete(id) : state.active.ids.add(id);
            } else {
                state.active.ids = new Set([id]);
            }
            if (state.active.ids.has(id)) state.selected.set(id, state.items.get(id));
            render();
        });
        apply.addEventListener('click', () => {
            if (!state.active || state.queue?.isBusy()) return;
            state.active.onApply?.(visibleSelection());
            close();
        });
        $('[data-mm2-clear]', modal).addEventListener('click', () => {
            if (state.active) state.active.ids.clear();
            render();
        });
        loadMore.addEventListener('click', () => void loadPage());
        $('[data-mm2-search]', modal).addEventListener('input', (event) => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => { state.search = event.target.value.trim(); void loadPage(true); }, 250);
        });
        $('[data-mm2-type]', modal).addEventListener('change', (event) => {
            state.type = event.target.value; void loadPage(true);
        });
        $('[data-mm2-sort]', modal).addEventListener('change', (event) => {
            state.sort = event.target.value; void loadPage(true);
        });
        const uploadInput = $('[data-mm2-input]', modal);
        const drop = $('[data-mm2-drop]', modal);
        drop.addEventListener('click', () => { if (!state.queue?.isBusy()) uploadInput.click(); });
        drop.addEventListener('dragover', (event) => { event.preventDefault(); drop.classList.add('is-dragging'); });
        drop.addEventListener('dragleave', () => drop.classList.remove('is-dragging'));
        drop.addEventListener('drop', (event) => {
            event.preventDefault(); drop.classList.remove('is-dragging');
            if (!state.queue?.isBusy()) void state.queue.run(Array.from(event.dataTransfer.files || []));
        });
        state.queue = root.adminBatchMediaUpload.createQueue({
            input: uploadInput, status: $('[data-mm2-status]', modal),
            progress: $('[data-mm2-progress]', modal),
            errors: $('[data-mm2-errors]', modal),
            retry: $('[data-mm2-retry]', modal),
            button: $('[data-mm2-upload] button[type="submit"]', modal),
            onUploaded: (item) => {
                const id = String(item.id);
                state.selected.set(id, item);
                if (state.active) {
                    if (!state.active.multiple) state.active.ids.clear();
                    state.active.ids.add(id);
                }
            },
            onFinished: (result) => {
                render();
                if (!result.failures.length) {
                    setTab('library');
                    void loadPage(true);
                }
            },
        });
        uploadForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const files = Array.from(uploadInput.files || []);
            uploadInput.value = '';
            if (files.length) void state.queue.run(files);
        });
        uploadInput.addEventListener('change', () => {
            const files = Array.from(uploadInput.files || []);
            uploadInput.value = '';
            if (files.length) void state.queue.run(files);
        });
    };

    const enhanceGenericFields = () => {
        $$('input[type="file"][accept*="image"]:not([data-skip-media-picker])').forEach((input) => {
            if (!input.name || input.closest('.mm2-modal')) return;
            const multiple = input.multiple || input.name.endsWith('[]');
            const field = input.name.replace(/\[\]$/, '');
            const name = multiple ? field + '_media_ids[]' : field + '_media_id';
            const box = document.createElement('div');
            box.className = 'mm2-generic';
            box.innerHTML = '<button type="button" class="admin-secondary-btn">انتخاب از کتابخانه</button><div class="mm2-generic-items"></div>';
            input.insertAdjacentElement('afterend', box);
            const items = $('.mm2-generic-items', box);
            let ids = [];
            $('button', box).addEventListener('click', () => open({
                multiple, selectedIds: ids,
                onApply: (next) => {
                    ids = next;
                    items.replaceChildren();
                    ids.forEach((id) => {
                        const item = getItem(id);
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden'; hidden.name = name; hidden.value = id;
                        items.appendChild(hidden);
                        if (item?.url) {
                            const img = document.createElement('img');
                            img.src = item.url; img.alt = item.title || 'تصویر'; items.appendChild(img);
                        }
                    });
                },
            }));
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (!root.adminMediaPickerUrl || !root.adminBatchMediaUpload) return;
        initModal();
        root.adminOpenMediaPicker = open;
        $$('[data-media-select-target]').forEach((button) => {
            button.addEventListener('click', () => {
                const select = document.getElementById(button.dataset.mediaSelectTarget);
                if (select) pickerFor(select);
            });
        });
        $$('[data-mm2-gallery-form]').forEach(bindGallery);
        bindCoverPreview();
        enhanceGenericFields();
    });
})(typeof window !== 'undefined' ? window : globalThis);
