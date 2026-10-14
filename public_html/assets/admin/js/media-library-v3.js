/* Dedicated admin media library: page-scoped selection and permanent deletion. */
(function (root) {
    'use strict';
    const storagePrefix = 'asnaf.media.library.selected.v3.';
    const uniqueIds = (ids) => [...new Set((ids || []).map(String).filter((id) => /^[1-9]\d*$/.test(id)))];
    const togglePageSelection = (ids, page) => {
        const next = new Set(uniqueIds(ids));
        const visible = uniqueIds(page);
        const all = visible.length > 0 && visible.every((id) => next.has(id));
        visible.forEach((id) => all ? next.delete(id) : next.add(id));
        return [...next];
    };
    const forgetDeleted = (ids, deleted) => uniqueIds(ids).filter((id) => !new Set(uniqueIds(deleted)).has(id));
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = { uniqueIds, togglePageSelection, forgetDeleted };
    }
    if (typeof document === 'undefined') return;

    document.addEventListener('DOMContentLoaded', () => {
        const page = document.querySelector('[data-media-library-v3]');
        if (!page) return;
        const storageKey = storagePrefix + page.dataset.userId;
        const one = (selector, scope = page) => scope.querySelector(selector);
        const all = (selector, scope = page) => Array.from(scope.querySelectorAll(selector));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const allowedDelete = page.dataset.canDelete === '1';
        const cards = () => all('[data-ml3-item]');
        const visibleIds = () => cards().map((card) => card.dataset.ml3Item);
        const selectedSet = new Set();
        let deleting = false;
        let pendingIds = [];
        let activeModal = null;
        const feedback = one('[data-ml3-result]');
        const count = one('[data-ml3-count]');
        const bulkButton = one('[data-ml3-delete-selected]');
        const confirmModal = one('[data-ml3-confirm-modal]');
        const detailModal = one('[data-ml3-detail-modal]');
        const details = one('[data-ml3-detail-body]');

        const save = () => {
            try {
                root.sessionStorage.setItem(storageKey, JSON.stringify([...selectedSet]));
            } catch (error) {
                // Browser storage disabled; deletion still works for this page.
            }
        };
        const read = () => {
            try {
                const ids = JSON.parse(root.sessionStorage.getItem(storageKey) || '[]');
                return uniqueIds(Array.isArray(ids) ? ids : []);
            } catch (error) { return []; }
        };
        read().forEach((id) => selectedSet.add(id));

        const render = () => {
            cards().forEach((card) => {
                const checked = selectedSet.has(card.dataset.ml3Item);
                const input = one('[data-ml3-select]', card);
                if (input) input.checked = checked;
                card.classList.toggle('is-marked', checked);
            });
            count.textContent = selectedSet.size.toLocaleString('fa-IR') + ' تصویر انتخاب‌شده';
            if (bulkButton) {
                bulkButton.disabled = !selectedSet.size || deleting;
                bulkButton.textContent = selectedSet.size
                    ? 'حذف دائمی ' + selectedSet.size.toLocaleString('fa-IR') + ' تصویر'
                    : 'حذف دائمی انتخاب‌شده‌ها';
            }
            one('[data-ml3-select-page]').disabled = deleting || !visibleIds().length;
            one('[data-ml3-clear]').disabled = deleting || !selectedSet.size;
        };
        const close = (modal) => {
            if (!modal || deleting) return;
            modal.hidden = true;
            if (modal === confirmModal) pendingIds = [];
            if (modal === detailModal) details.replaceChildren();
            if (activeModal === modal) activeModal = null;
            document.body.classList.remove('ml3-dialog-open');
        };
        const open = (modal) => {
            if (!modal || deleting) return;
            if (activeModal) close(activeModal);
            modal.hidden = false;
            activeModal = modal;
            document.body.classList.add('ml3-dialog-open');
            one('button', one('.ml3-dialog', modal))?.focus();
        };
        const describe = (message, isError = false) => {
            feedback.textContent = message;
            feedback.classList.toggle('has-errors', isError);
            feedback.hidden = false;
        };

        page.addEventListener('change', (event) => {
            const input = event.target.closest('[data-ml3-select]');
            if (!input || deleting) return;
            const card = input.closest('[data-ml3-item]');
            if (!card) return;
            input.checked ? selectedSet.add(card.dataset.ml3Item) : selectedSet.delete(card.dataset.ml3Item);
            save();
            render();
        });
        one('[data-ml3-select-page]')?.addEventListener('click', () => {
            if (deleting) return;
            const next = togglePageSelection([...selectedSet], visibleIds());
            selectedSet.clear();
            next.forEach((id) => selectedSet.add(id));
            save();
            render();
        });
        one('[data-ml3-clear]')?.addEventListener('click', () => {
            if (deleting) return;
            selectedSet.clear();
            save();
            render();
        });

        const confirmDeletion = (ids) => {
            if (!allowedDelete || deleting || !confirmModal) return;
            const candidates = uniqueIds(ids);
            if (!candidates.length) return;
            pendingIds = candidates;
            const countText = candidates.length.toLocaleString('fa-IR');
            one('[data-ml3-confirm-description]').textContent =
                'آیا می‌خواهید ' + countText + ' رسانه را برای همیشه از کتابخانه و هاست حذف کنید؟';
            const names = one('[data-ml3-confirm-names]');
            names.replaceChildren();
            candidates.slice(0, 8).forEach((id) => {
                const card = cards().find((item) => item.dataset.ml3Item === id);
                const label = one('.ml3-item-name', card || page)?.textContent?.trim() || 'رسانه شماره ' + id;
                const li = document.createElement('li');
                li.textContent = label;
                names.appendChild(li);
            });
            if (candidates.length > 8) {
                const li = document.createElement('li');
                li.textContent = 'و ' + (candidates.length - 8).toLocaleString('fa-IR') + ' رسانه دیگر';
                names.appendChild(li);
            }
            open(confirmModal);
        };

        one('[data-ml3-delete-selected]')?.addEventListener('click', () => confirmDeletion([...selectedSet]));
        page.addEventListener('click', (event) => {
            const trash = event.target.closest('[data-ml3-delete-one]');
            if (trash) {
                const card = trash.closest('[data-ml3-item]');
                if (card) confirmDeletion([card.dataset.ml3Item]);
                return;
            }
            const detailsButton = event.target.closest('[data-ml3-details]');
            if (!detailsButton) return;
            const template = one('[data-ml3-detail-template]', detailsButton.closest('[data-ml3-item]'));
            if (!template) return;
            details.replaceChildren(template.content.cloneNode(true));
            open(detailModal);
        });
        all('[data-ml3-close]').forEach((button) => button.addEventListener('click', () => close(detailModal)));
        all('[data-ml3-cancel-delete]').forEach((button) => button.addEventListener('click', () => close(confirmModal)));
        details?.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-ml3-copy]');
            if (!button) return;
            try {
                await root.navigator.clipboard.writeText(button.dataset.ml3Copy);
                button.textContent = 'آدرس کپی شد';
            } catch (error) { button.textContent = 'کپی آدرس ممکن نبود'; }
        });
        root.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && activeModal && !deleting) close(activeModal);
        });

        const sendDelete = async (ids) => {
            const singular = ids.length === 1;
            const endpoint = singular
                ? page.dataset.deleteOneUrl.replace('__MEDIA_ID__', encodeURIComponent(ids[0]))
                : page.dataset.deleteManyUrl;
            const response = await fetch(endpoint, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: singular ? undefined : JSON.stringify({ ids: ids.map(Number) }),
            });
            const json = await response.json().catch(() => null);
            if (singular && json?.status) {
                if (json.status === 'deleted') return { deleted: [String(json.id)], blocked: [], missing: [], failed: [] };
                if (json.status === 'blocked') return { deleted: [], blocked: [json], missing: [], failed: [] };
                if (json.status === 'missing') return { deleted: [], blocked: [], missing: [json], failed: [] };
            }
            if (!response.ok || !Array.isArray(json?.deleted)) {
                const error = new Error(json?.message || 'حذف با خطای HTTP ' + response.status + ' مواجه شد.');
                error.status = response.status;
                throw error;
            }
            return json;
        };

        one('[data-ml3-confirm-delete]')?.addEventListener('click', async () => {
            if (!allowedDelete || deleting || !pendingIds.length) return;
            deleting = true;
            const ids = [...pendingIds];
            const submit = one('[data-ml3-confirm-delete]');
            submit.disabled = true;
            all('[data-ml3-cancel-delete]').forEach((button) => { button.disabled = true; });
            const tally = { deleted: [], blocked: [], missing: [], failed: [] };
            let stopped = false;
            for (let n = 0; n < ids.length; n += 20) {
                const chunk = ids.slice(n, n + 20);
                describe('در حال حذف ' + Math.min(ids.length, n + chunk.length).toLocaleString('fa-IR') +
                    ' از ' + ids.length.toLocaleString('fa-IR') + ' رسانه…');
                try {
                    const result = await sendDelete(chunk);
                    ['deleted', 'blocked', 'missing', 'failed'].forEach((kind) =>
                        tally[kind].push(...(result[kind] || [])));
                    const deletedIds = (result.deleted || []).map(String);
                    deletedIds.forEach((id) => {
                        selectedSet.delete(id);
                        const card = cards().find((item) => item.dataset.ml3Item === id);
                        card?.remove();
                    });
                    // Also discard entries known to be absent from the server.
                    (result.missing || []).forEach((item) => selectedSet.delete(String(item.id)));
                } catch (error) {
                    tally.failed.push(...chunk.map((id) => ({
                        id: Number(id), message: 'نتیجه حذف نامشخص است؛ قبل از تکرار، کتابخانه را بازخوانی کنید.',
                    })));
                    if ([401, 403, 419].includes(error.status)) {
                        stopped = true;
                        break;
                    }
                }
            }
            deleting = false;
            pendingIds = [];
            submit.disabled = false;
            all('[data-ml3-cancel-delete]').forEach((button) => { button.disabled = false; });
            close(confirmModal);
            save();
            render();
            const failures = [...tally.blocked, ...tally.failed];
            const summary =
                tally.deleted.length.toLocaleString('fa-IR') + ' حذف شد؛ ' +
                tally.blocked.length.toLocaleString('fa-IR') + ' تصویر در محتوا استفاده شده و محافظت شد؛ ' +
                tally.missing.length.toLocaleString('fa-IR') + ' ناموجود؛ ' +
                tally.failed.length.toLocaleString('fa-IR') + ' خطا یا نتیجه نامشخص.';
            const explanation = failures.slice(0, 3).map((item) => item.message || '').filter(Boolean).join(' | ');
            describe(summary + (explanation ? ' ' + explanation : '') +
                (stopped ? ' عملیات به دلیل مشکل دسترسی متوقف شد.' : ''),
                !!failures.length || stopped);
        });

        const uploadForm = one('[data-ml3-upload]');
        if (uploadForm && page.dataset.canUpload === '1' && root.adminBatchMediaUpload) {
            const input = one('#ml3UploadInput');
            const status = one('[data-ml3-upload-status]');
            const progress = one('[data-ml3-upload-progress]');
            const errors = one('[data-ml3-upload-errors]');
            const retry = one('[data-ml3-upload-retry]');
            const refresh = one('[data-ml3-upload-refresh]');
            const start = one('[data-ml3-upload-start]');
            const drop = one('[data-ml3-dropzone]');
            const queue = root.adminBatchMediaUpload.createQueue({
                input, status, progress, errors, retry, button: start,
                onFinished: (result) => {
                    if (result.uploaded) refresh.hidden = false;
                },
            });
            const run = (files) => {
                if (!files.length || queue.isBusy()) return;
                status.textContent = files.length.toLocaleString('fa-IR') + ' تصویر برای آپلود آماده است…';
                void queue.run(files);
            };
            let stagedFiles = [];
            input?.addEventListener('change', () => {
                stagedFiles = Array.from(input.files || []);
                status.textContent = stagedFiles.length
                    ? stagedFiles.length.toLocaleString('fa-IR') + ' تصویر انتخاب شده؛ برای ارسال، «شروع آپلود» را بزنید.'
                    : 'فایلی انتخاب نشده است.';
            });
            uploadForm.addEventListener('submit', (event) => {
                event.preventDefault();
                const files = stagedFiles.length ? [...stagedFiles] : Array.from(input.files || []);
                stagedFiles = [];
                input.value = '';
                if (files.length) run(files);
                else if (!queue.isBusy()) status.textContent = 'ابتدا تصاویر موردنظر را انتخاب کنید.';
            });
            drop?.addEventListener('dragover', (event) => {
                event.preventDefault();
                if (!queue.isBusy()) drop.classList.add('is-dragover');
            });
            drop?.addEventListener('dragleave', () => drop.classList.remove('is-dragover'));
            drop?.addEventListener('drop', (event) => {
                event.preventDefault();
                drop.classList.remove('is-dragover');
                run(Array.from(event.dataTransfer?.files || []));
            });
        }
        const grid = one('[data-ml3-grid]');
        all('[data-admin-media-view]').forEach((button) => {
            button.addEventListener('click', () => {
                const isList = button.dataset.adminMediaView === 'list';
                grid.classList.toggle('is-list', isList);
                all('[data-admin-media-view]').forEach((other) => {
                    const selected = other === button;
                    other.setAttribute('aria-pressed', String(selected));
                    other.classList.toggle('is-active', selected);
                });
            });
        });
        render();
    });
})(typeof window !== 'undefined' ? window : globalThis);
