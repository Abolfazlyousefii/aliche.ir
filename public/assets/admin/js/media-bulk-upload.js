/* Upload many images through the existing permission-checked media endpoint.
 * A separate POST per file avoids PHP max_file_uploads and large post_max_size
 * requests. Successful files are never re-uploaded on Retry.
 */
(function (root) {
    'use strict';

    const formats = /\.(jpe?g|png|webp|gif|avif)$/i;
    const maxKilobytes = 5120;

    const messageFrom = (payload) => {
        const errors = payload?.errors;
        if (errors && typeof errors === 'object') {
            const first = Object.values(errors).flat().find((entry) => typeof entry === 'string');
            if (first) return first;
        }
        return payload?.message || 'آپلود انجام نشد. لطفاً دوباره تلاش کنید.';
    };

    const sendOneFile = async (file, options) => {
        const body = new FormData();
        body.append('files[]', file);
        const response = await fetch(options.url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': options.csrfToken || '',
            },
            body,
        });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok || !Array.isArray(payload.items) || payload.items.length !== 1) {
            throw new Error(messageFrom(payload));
        }
        return payload.items[0];
    };

    const uploadFiles = async (files, options = {}) => {
        const list = Array.from(files || []);
        const failures = [];
        let uploaded = 0;
        const maxBytes = (options.maxKilobytes || maxKilobytes) * 1024;
        const send = options.sendOne || sendOneFile;

        for (const [index, file] of list.entries()) {
            try {
                if (!file || !formats.test(file.name || '')) {
                    throw new Error('فرمت مجاز: JPG، PNG، WebP، GIF یا AVIF.');
                }
                if (file.size > maxBytes) {
                    throw new Error('حجم هر تصویر نباید بیشتر از ۵ مگابایت باشد.');
                }
                if (!file.size) {
                    throw new Error('فایل خالی قابل آپلود نیست.');
                }
                const item = await send(file, options);
                uploaded += 1;
                options.onUploaded?.(item, file);
            } catch (error) {
                failures.push({ file, message: error?.message || 'خطا در آپلود' });
            }
            options.onProgress?.({
                finished: index + 1,
                total: list.length,
                uploaded,
                failed: failures.length,
            });
        }

        return { uploaded, failures };
    };

    const renderFailures = (node, failures) => {
        if (!node) return;
        node.replaceChildren();
        failures.slice(0, 12).forEach(({ file, message }) => {
            const row = document.createElement('li');
            row.textContent = `${file.name}: ${message}`;
            node.appendChild(row);
        });
        if (failures.length > 12) {
            const extra = document.createElement('li');
            extra.textContent = `و ${failures.length - 12} فایل ناموفق دیگر`;
            node.appendChild(extra);
        }
    };

    // Reusable UI queue for the gallery form AND the main media-library page.
    // It deliberately never stores file blobs or tokens in local storage.
    const createQueue = ({ input, status, progress, errors, retry, button, onUploaded, onAllSucceeded }) => {
        let busy = false;
        let failures = [];
        let successTotal = 0;

        const run = async (files) => {
            if (busy || !files.length) return;
            busy = true;
            if (button) button.disabled = true;
            input.disabled = true;
            if (retry) retry.hidden = true;
            renderFailures(errors, []);
            if (progress) { progress.hidden = false; progress.max = files.length; progress.value = 0; }
            status.textContent = `شروع آپلود ${files.length.toLocaleString('fa-IR')} تصویر…`;

            try {
                const result = await uploadFiles(files, {
                    url: root.adminMediaUploadUrl,
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
                    maxKilobytes: Number(root.adminMediaMaxUploadKB || maxKilobytes),
                    onUploaded,
                    onProgress: ({ finished, total, uploaded, failed }) => {
                        if (progress) progress.value = finished;
                        status.textContent = `${finished.toLocaleString('fa-IR')} از ${total.toLocaleString('fa-IR')} بررسی شد؛ ${uploaded.toLocaleString('fa-IR')} موفق، ${failed.toLocaleString('fa-IR')} ناموفق`;
                    },
                });
                successTotal += result.uploaded;
                failures = result.failures;
                renderFailures(errors, failures);
                if (retry) retry.hidden = failures.length === 0;
                status.textContent = failures.length
                    ? `${successTotal.toLocaleString('fa-IR')} تصویر ذخیره شد؛ ${failures.length.toLocaleString('fa-IR')} تصویر ناموفق است. تلاش مجدد یا ذخیره با تصاویر موفق امکان‌پذیر است.`
                    : `${successTotal.toLocaleString('fa-IR')} تصویر با موفقیت در کتابخانه ذخیره شد.`;
                if (!failures.length) onAllSucceeded?.();
            } finally {
                busy = false;
                input.disabled = false;
                if (button) button.disabled = false;
            }
        };

        retry?.addEventListener('click', () => {
            const files = failures.map(({ file }) => file);
            failures = [];
            void run(files);
        });

        return { run, isBusy: () => busy };
    };

    const setupGalleryForm = () => {
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
                        select.tomselect?.addOption({ value: id, text: option.text, url: item.url });
                    }
                    option.selected = true;
                    if (select.tomselect) {
                        select.tomselect.addItem(id, true);
                    }
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                },
            });

            input.addEventListener('change', () => {
                const files = Array.from(input.files || []);
                // Prevent the entire selection from being POSTed again with
                // the final gallery form; successes are submitted as media IDs.
                input.value = '';
                void queue.run(files);
            });
            form.addEventListener('submit', (event) => {
                if (!queue.isBusy()) return;
                event.preventDefault();
                event.stopImmediatePropagation();
                status.textContent = 'ابتدا منتظر پایان آپلود تصاویر بمانید، سپس گالری را ذخیره کنید.';
                status.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
                button: form.querySelector('button[type="submit"], button:not([type])'),
                onAllSucceeded: () => {
                    // The existing library list is server-rendered. Refresh
                    // only after ALL files were saved, so interrupted uploads
                    // remain on-screen for retry rather than disappearing.
                    root.location.reload();
                },
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
            setupGalleryForm();
            setupLibraryPage();
        });
    }
})(typeof window !== 'undefined' ? window : globalThis);
