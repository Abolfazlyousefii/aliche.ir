/* Resilient sequential uploads shared by Media Manager and the media-library page. */
(function (root) {
    'use strict';

    const imageExtensions = /\.(jpe?g|png|webp|gif|avif)$/i;
    const defaultMaxKB = 5120;
    const receipts = new WeakMap();

    const messageFrom = (payload) => {
        const errors = payload && payload.errors;
        if (errors && typeof errors === 'object') {
            const first = Object.values(errors).flat().find((part) => typeof part === 'string');
            if (first) return first;
        }
        return (payload && payload.message) || 'آپلود انجام نشد. لطفاً دوباره تلاش کنید.';
    };

    const uploadToken = (file) => {
        if (receipts.has(file)) return receipts.get(file);
        const id = root.crypto && typeof root.crypto.randomUUID === 'function'
            ? root.crypto.randomUUID()
            : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (part) => {
                const value = Math.floor(Math.random() * 16);
                return (part === 'x' ? value : (value & 3) | 8).toString(16);
            });
        receipts.set(file, id);
        return id;
    };

    const sendOneFile = async (file, options) => {
        const body = new FormData();
        body.append('files[]', file);
        body.append('upload_token', uploadToken(file));
        let response;
        try {
            response = await fetch(options.url, {
                method: 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': options.csrfToken || '' },
                body,
            });
        } catch (error) {
            const failure = new Error('ارتباط با سرور قطع شد. بررسی و تلاش مجدد انجام می‌شود.');
            failure.retryable = true;
            throw failure;
        }
        const payload = await response.json().catch(() => null);
        if (!response.ok) {
            const failure = new Error(response.status === 419
                ? 'نشست ورود منقضی شده است؛ صفحه را دوباره باز کنید.'
                : (payload && (payload.message || payload.errors))
                    ? messageFrom(payload)
                    : 'خطای HTTP ' + response.status + ' هنگام بارگذاری تصویر.');
            failure.retryable = [408, 429, 500, 502, 503, 504].includes(response.status);
            failure.status = response.status;
            throw failure;
        }
        if (!payload || !Array.isArray(payload.items) || payload.items.length !== 1 || !payload.items[0].id) {
            // A successful HTTP response can still have an invalid body; do not
            // blindly retry an upload whose server-side outcome is uncertain.
            throw new Error('پاسخ آپلود معتبر نبود؛ قبل از تلاش مجدد کتابخانه را بررسی کنید.');
        }
        return payload.items[0];
    };

    const wait = (ms) => new Promise((resolve) => {
        if (typeof setTimeout === 'function') setTimeout(resolve, ms);
        else resolve();
    });

    const uploadFiles = async (files, options = {}) => {
        const list = Array.from(files || []);
        const failures = [];
        const uiWarnings = [];
        let uploaded = 0;
        const maxBytes = Number(options.maxKilobytes || defaultMaxKB) * 1024;
        const send = options.sendOne || sendOneFile;
        const retryLimit = options.retryLimit === undefined ? 2 : Math.max(0, Number(options.retryLimit));

        for (const [index, file] of list.entries()) {
            try {
                if (!file || !imageExtensions.test(file.name || '')) {
                    throw new Error('فرمت مجاز: JPG، PNG، WebP، GIF یا AVIF.');
                }
                if (file.size > maxBytes) {
                    throw new Error('حجم هر تصویر نباید بیشتر از ۵ مگابایت باشد.');
                }
                if (!file.size) {
                    throw new Error('فایل خالی قابل آپلود نیست.');
                }

                let item;
                for (let attempt = 0; attempt <= retryLimit; attempt++) {
                    try {
                        item = await send(file, options);
                        break;
                    } catch (error) {
                        if (!error.retryable || attempt >= retryLimit) throw error;
                        options.onRetry?.({ file, attempt: attempt + 1, reason: error.message });
                        await (options.delay || wait)(Math.min(800 * (attempt + 1), 2500));
                    }
                }
                // The server has confirmed storage. UI/callback errors may not
                // turn this into a failed upload or trigger a duplicate retry.
                uploaded++;
                try {
                    options.onUploaded?.(item, file);
                } catch (error) {
                    uiWarnings.push({ file, message: error?.message || 'خطا در نمایش تصویر' });
                    options.onUiError?.(error, item, file);
                }
            } catch (error) {
                failures.push({ file, message: error?.message || 'خطا در آپلود', status: error?.status || null });
            }
            options.onProgress?.({
                finished: index + 1, total: list.length, uploaded, failed: failures.length,
            });
        }
        return { uploaded, failures, uiWarnings };
    };

    const renderFailures = (node, failures) => {
        if (!node) return;
        node.replaceChildren();
        failures.slice(0, 30).forEach(({ file, message }) => {
            const row = document.createElement('li');
            row.textContent = file.name + ': ' + message;
            node.appendChild(row);
        });
        if (failures.length > 30) {
            const extra = document.createElement('li');
            extra.textContent = 'و ' + (failures.length - 30) + ' فایل ناموفق دیگر';
            node.appendChild(extra);
        }
    };

    const createQueue = ({ input, status, progress, errors, retry, button, onUploaded, onAllSucceeded, onFinished }) => {
        let busy = false;
        let failures = [];
        let successTotal = 0;
        const run = async (files) => {
            if (busy || !files.length) return null;
            busy = true;
            if (button) button.disabled = true;
            if (input) input.disabled = true;
            if (retry) retry.hidden = true;
            renderFailures(errors, []);
            if (progress) { progress.hidden = false; progress.max = files.length; progress.value = 0; }
            if (status) status.textContent = 'شروع آپلود ' + files.length.toLocaleString('fa-IR') + ' تصویر…';
            let result;
            try {
                result = await uploadFiles(files, {
                    url: root.adminMediaUploadUrl,
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
                    maxKilobytes: Number(root.adminMediaMaxUploadKB || defaultMaxKB),
                    onUploaded,
                    onProgress: ({ finished, total, uploaded, failed }) => {
                        if (progress) progress.value = finished;
                        if (status) status.textContent = finished.toLocaleString('fa-IR') + ' از ' +
                            total.toLocaleString('fa-IR') + ' بررسی شد؛ ' + uploaded.toLocaleString('fa-IR') +
                            ' موفق، ' + failed.toLocaleString('fa-IR') + ' ناموفق';
                    },
                });
                successTotal += result.uploaded;
                failures = result.failures;
                renderFailures(errors, failures);
                if (retry) retry.hidden = !failures.length;
                if (status) status.textContent = failures.length
                    ? successTotal.toLocaleString('fa-IR') + ' تصویر ذخیره شد؛ ' + failures.length.toLocaleString('fa-IR') + ' ناموفق. فقط فایل‌های ناموفق را دوباره امتحان کنید.'
                    : successTotal.toLocaleString('fa-IR') + ' تصویر با موفقیت ذخیره شد.';
                onFinished?.(result);
                if (!failures.length) onAllSucceeded?.();
                return result;
            } finally {
                busy = false;
                if (input) input.disabled = false;
                if (button) button.disabled = false;
            }
        };
        retry?.addEventListener('click', () => {
            const files = failures.map(({ file }) => file);
            failures = [];
            void run(files);
        });
        return { run, isBusy: () => busy, failures: () => failures };
    };

    const setupLegacyGalleryForm = () => {
        document.querySelectorAll('[data-gallery-bulk-upload]').forEach((input) => {
            const form = input.closest('form');
            const select = form?.querySelector('#gallery_media_ids');
            const status = form?.querySelector('[data-gallery-bulk-status]');
            if (!select || !status || !root.adminMediaUploadUrl) return;
            const queue = createQueue({
                input, status,
                progress: form.querySelector('[data-gallery-bulk-progress]'),
                errors: form.querySelector('[data-gallery-bulk-errors]'),
                retry: form.querySelector('[data-gallery-bulk-retry]'),
                onUploaded: (item) => {
                    if (!item?.id || !item?.url) return;
                    const id = String(item.id);
                    let option = Array.from(select.options).find((entry) => entry.value === id);
                    if (!option) {
                        option = new Option(item.title || item.original_name || 'تصویر', id);
                        option.dataset.url = item.url;
                        select.add(option);
                    }
                    option.selected = true;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                },
            });
            input.addEventListener('change', () => {
                const files = Array.from(input.files || []);
                input.value = '';
                void queue.run(files);
            });
            form.addEventListener('submit', (event) => {
                if (!queue.isBusy()) return;
                event.preventDefault();
                event.stopImmediatePropagation();
                status.textContent = 'ابتدا منتظر پایان آپلود تصاویر بمانید.';
            }, true);
        });
    };

    const setupLibraryPage = () => {
        document.querySelectorAll('form[data-media-dropzone]').forEach((form) => {
            const input = form.querySelector('input[type="file"]');
            const status = form.querySelector('[data-media-file-count]');
            if (!input || !status || !root.adminMediaUploadUrl) return;
            const queue = createQueue({
                input, status,
                progress: form.querySelector('[data-media-bulk-progress]'),
                errors: form.querySelector('[data-media-bulk-errors]'),
                retry: form.querySelector('[data-media-bulk-retry]'),
                button: form.querySelector('button[type="submit"]'),
                onAllSucceeded: () => root.location.reload(),
            });
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                event.stopImmediatePropagation();
                const files = Array.from(input.files || []);
                input.value = '';
                void queue.run(files);
            }, true);
        });
    };

    root.adminBatchMediaUpload = { uploadFiles, createQueue };
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = { uploadFiles, createQueue, messageFrom };
    }
    if (typeof document !== 'undefined' && document.addEventListener) {
        document.addEventListener('DOMContentLoaded', () => {
            setupLegacyGalleryForm();
            setupLibraryPage();
        });
    }
})(typeof window !== 'undefined' ? window : globalThis);
