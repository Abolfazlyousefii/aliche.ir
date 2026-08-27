(() => {
    const ready = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
            return;
        }

        callback();
    };

    ready(() => {
        const root = document.querySelector('[data-posts-index]');

        if (!root) {
            return;
        }

        const form = root.querySelector('[data-post-filter-form]');
        const results = root.querySelector('[data-post-results]');
        const statusNode = root.querySelector('[data-post-filter-status]');
        const searchInput = root.querySelector('[data-post-search-input]');
        const todayInput = root.querySelector('[data-post-today-filter]');
        const submitButton = root.querySelector('[data-post-filter-submit]');
        const clearLink = root.querySelector('[data-post-clear-filters]');
        const activeFilterCount = root.querySelector('[data-post-filter-count]');

        if (!form || !results) {
            return;
        }

        let searchTimer = null;
        let activeRequest = null;
        let pendingDeleteForm = null;

        const setBusy = (busy, message = '') => {
            results.classList.toggle('is-loading', busy);
            results.setAttribute('aria-busy', busy ? 'true' : 'false');

            if (submitButton) {
                submitButton.disabled = busy;
            }

            if (statusNode) {
                statusNode.textContent = message;
            }
        };

        const formUrl = () => {
            const url = new URL(form.action, window.location.origin);
            const data = new FormData(form);

            data.forEach((value, key) => {
                const normalized = String(value ?? '').trim();

                if (normalized !== '') {
                    url.searchParams.set(key, normalized);
                }
            });

            return url;
        };

        const setTodayState = (enabled) => {
            if (!todayInput) {
                return;
            }

            todayInput.disabled = !enabled;
            todayInput.value = enabled ? '1' : '';
        };

        const syncFormFromUrl = (urlLike) => {
            const url = new URL(urlLike, window.location.origin);

            form.querySelectorAll('input[name], select[name]').forEach((field) => {
                if (field === todayInput) {
                    return;
                }

                const value = url.searchParams.get(field.name) ?? '';

                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = url.searchParams.has(field.name);
                    return;
                }

                field.value = value;
            });

            setTodayState(url.searchParams.get('today') === '1');
            updateShortcutState(url);
            updateClearState(url);
        };

        const updateShortcutState = (urlLike) => {
            const current = new URL(urlLike, window.location.origin);
            const currentQuery = current.searchParams.toString();

            root.querySelectorAll('[data-post-filter-link]').forEach((link) => {
                const target = new URL(link.href, window.location.origin);
                link.classList.toggle('is-active', target.searchParams.toString() === currentQuery);
            });
        };

        const updateClearState = (urlLike) => {
            const url = new URL(urlLike, window.location.origin);
            const activeEntries = [...url.searchParams.entries()]
                .filter(([key, value]) => key !== 'page' && value !== '');
            const active = activeEntries.length > 0;

            if (clearLink) {
                clearLink.classList.toggle('disabled', !active);

                if (active) {
                    clearLink.removeAttribute('aria-disabled');
                    clearLink.removeAttribute('tabindex');
                } else {
                    clearLink.setAttribute('aria-disabled', 'true');
                    clearLink.setAttribute('tabindex', '-1');
                }
            }

            if (activeFilterCount) {
                activeFilterCount.hidden = !active;
                activeFilterCount.textContent = `${activeEntries.length.toLocaleString('fa-IR')} فیلتر فعال`;
            }
        };

        const enhanceAjaxDeleteForms = (scope) => {
            const modalElement = document.getElementById('adminDeleteModal');
            const confirmButton = modalElement?.querySelector('[data-admin-delete-confirm]');
            const messageElement = modalElement?.querySelector('[data-admin-delete-message]');
            const modal = modalElement && window.bootstrap
                ? window.bootstrap.Modal.getOrCreateInstance(modalElement)
                : null;

            scope.querySelectorAll('form').forEach((deleteForm) => {
                const methodInput = deleteForm.querySelector('input[name="_method"]');
                const isDelete = methodInput && methodInput.value.toUpperCase() === 'DELETE';

                if (!isDelete || deleteForm.dataset.adminDeleteForm === 'true' || deleteForm.dataset.postsDeleteEnhanced === 'true') {
                    return;
                }

                deleteForm.dataset.postsDeleteEnhanced = 'true';

                deleteForm.addEventListener('submit', (event) => {
                    if (deleteForm.dataset.postsDeleteConfirmed === 'true') {
                        return;
                    }

                    event.preventDefault();

                    if (!modalElement || !modal) {
                        if (window.confirm('از حذف این خبر مطمئن هستید؟')) {
                            deleteForm.dataset.postsDeleteConfirmed = 'true';
                            deleteForm.requestSubmit();
                        }
                        return;
                    }

                    pendingDeleteForm = deleteForm;

                    if (messageElement) {
                        const title = deleteForm.closest('tr')?.querySelector('strong')?.textContent?.trim();
                        messageElement.textContent = title ? `مورد انتخاب‌شده: ${title}` : '';
                    }

                    modal.show();
                });
            });

            if (confirmButton && confirmButton.dataset.postsAjaxDeleteBound !== 'true') {
                confirmButton.dataset.postsAjaxDeleteBound = 'true';

                confirmButton.addEventListener('click', () => {
                    if (!pendingDeleteForm) {
                        return;
                    }

                    const targetForm = pendingDeleteForm;
                    pendingDeleteForm = null;
                    targetForm.dataset.postsDeleteConfirmed = 'true';
                    modal?.hide();
                    targetForm.requestSubmit();
                });
            }

            if (modalElement && modalElement.dataset.postsAjaxDeleteBound !== 'true') {
                modalElement.dataset.postsAjaxDeleteBound = 'true';
                modalElement.addEventListener('hidden.bs.modal', () => {
                    pendingDeleteForm = null;
                });
            }
        };

        const fetchResults = async (urlLike, options = {}) => {
            const { pushHistory = true, syncForm = false } = options;
            const url = new URL(urlLike, window.location.origin);

            activeRequest?.abort();
            activeRequest = new AbortController();

            setBusy(true, 'در حال بروزرسانی نتایج...');

            try {
                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    signal: activeRequest.signal,
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const payload = await response.json();

                results.innerHTML = payload.html;

                const resolvedUrl = payload.url || url.toString();

                if (syncForm) {
                    syncFormFromUrl(resolvedUrl);
                } else {
                    updateShortcutState(resolvedUrl);
                    updateClearState(resolvedUrl);
                }

                if (pushHistory) {
                    window.history.pushState({ postsFilters: true }, '', resolvedUrl);
                }

                enhanceAjaxDeleteForms(results);

                const countText = Number(payload.total || 0).toLocaleString('fa-IR');
                setBusy(false, payload.filter_error || `${countText} خبر پیدا شد.`);

                const tableTop = root.querySelector('[data-post-results]');

                if (tableTop && window.matchMedia('(max-width: 720px)').matches) {
                    tableTop.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                setBusy(false, 'بروزرسانی نتایج با خطا مواجه شد. دوباره تلاش کنید.');
                console.error('Posts AJAX filters failed:', error);
            }
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const hasExplicitDate = [...form.querySelectorAll('[data-post-date-filter]')]
                .some((field) => String(field.value || '').trim() !== '');

            if (hasExplicitDate) {
                setTodayState(false);
            }

            fetchResults(formUrl(), { pushHistory: true });
        });

        root.querySelectorAll('[data-post-auto-filter]').forEach((field) => {
            field.addEventListener('change', () => {
                fetchResults(formUrl(), { pushHistory: true });
            });
        });

        root.querySelectorAll('[data-post-date-filter]').forEach((field) => {
            field.addEventListener('change', () => {
                setTodayState(false);
                fetchResults(formUrl(), { pushHistory: true });
            });
        });

        searchInput?.addEventListener('input', () => {
            window.clearTimeout(searchTimer);

            searchTimer = window.setTimeout(() => {
                fetchResults(formUrl(), { pushHistory: true });
            }, 450);
        });

        root.addEventListener('click', (event) => {
            const shortcut = event.target.closest('[data-post-filter-link]');
            const pagination = event.target.closest('[data-admin-pagination] a[href]');
            const clear = event.target.closest('[data-post-clear-filters]');

            if (shortcut) {
                event.preventDefault();
                syncFormFromUrl(shortcut.href);
                fetchResults(shortcut.href, { pushHistory: true });
                return;
            }

            if (clear && clear.getAttribute('aria-disabled') !== 'true') {
                event.preventDefault();
                const url = new URL(clear.href, window.location.origin);
                syncFormFromUrl(url);
                fetchResults(url, { pushHistory: true });
                return;
            }

            if (pagination) {
                const href = pagination.getAttribute('href');

                if (!href || href === '#') {
                    return;
                }

                event.preventDefault();
                fetchResults(href, { pushHistory: true });
            }
        });

        window.addEventListener('popstate', () => {
            syncFormFromUrl(window.location.href);
            fetchResults(window.location.href, {
                pushHistory: false,
                syncForm: true,
            });
        });

        if (typeof window.initializeJalaliDatepickers === 'function') {
            window.initializeJalaliDatepickers(form);
        }

        enhanceAjaxDeleteForms(results);
        updateShortcutState(window.location.href);
        updateClearState(window.location.href);
    });
})();
