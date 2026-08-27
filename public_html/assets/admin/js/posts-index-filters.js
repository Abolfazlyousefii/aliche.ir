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
        const filterToggle = root.querySelector('[data-post-filter-toggle]');
        const filterToggleText = root.querySelector('[data-post-filter-toggle-text]');
        const advancedFilters = root.querySelector('[data-post-advanced-filters]');
        const activeFilterCount = root.querySelector('[data-post-filter-count]');

        if (!form || !results) {
            return;
        }

        let searchTimer = null;
        let activeFilterRequest = null;
        let activeLoadMoreRequest = null;
        let infiniteObserver = null;
        let pendingDeleteForm = null;
        let nextPageUrl = null;
        let loadingMore = false;

        const clearLinks = () => [...root.querySelectorAll('[data-post-clear-filters]')];

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

        const setAdvancedExpanded = (expanded) => {
            if (!advancedFilters || !filterToggle) {
                return;
            }

            advancedFilters.hidden = !expanded;
            filterToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');

            if (filterToggleText) {
                filterToggleText.textContent = expanded ? 'بستن فیلترها' : 'فیلترهای بیشتر';
            }
        };

        filterToggle?.addEventListener('click', () => {
            const expanded = filterToggle.getAttribute('aria-expanded') === 'true';
            setAdvancedExpanded(!expanded);

            if (!expanded && typeof window.initializeJalaliDatepickers === 'function') {
                window.initializeJalaliDatepickers(advancedFilters);
            }
        });

        const setTodayState = (enabled) => {
            if (!todayInput) {
                return;
            }

            todayInput.disabled = !enabled;
            todayInput.value = enabled ? '1' : '';
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

            url.searchParams.delete('page');

            return url;
        };

        const advancedFilterNames = new Set([
            'type',
            'category_id',
            'union_id',
            'homepage_position',
            'from',
            'to',
        ]);

        const syncAdvancedStateFromUrl = (urlLike) => {
            const url = new URL(urlLike, window.location.origin);
            const hasAdvanced = [...advancedFilterNames].some((name) => {
                const value = url.searchParams.get(name);
                return value !== null && value !== '';
            });

            setAdvancedExpanded(hasAdvanced);
        };

        const updateShortcutState = (urlLike) => {
            const current = new URL(urlLike, window.location.origin);
            current.searchParams.delete('page');
            const currentQuery = current.searchParams.toString();

            root.querySelectorAll('[data-post-filter-link]').forEach((link) => {
                const target = new URL(link.href, window.location.origin);
                target.searchParams.delete('page');
                link.classList.toggle('is-active', target.searchParams.toString() === currentQuery);
            });
        };

        const updateClearState = (urlLike) => {
            const url = new URL(urlLike, window.location.origin);
            const activeEntries = [...url.searchParams.entries()]
                .filter(([key, value]) => key !== 'page' && value !== '');
            const active = activeEntries.length > 0;

            clearLinks().forEach((clearLink) => {
                clearLink.classList.toggle('disabled', !active);
                clearLink.classList.toggle('is-disabled', !active);

                if (active) {
                    clearLink.removeAttribute('aria-disabled');
                    clearLink.removeAttribute('tabindex');
                } else {
                    clearLink.setAttribute('aria-disabled', 'true');
                    clearLink.setAttribute('tabindex', '-1');
                }
            });

            if (activeFilterCount) {
                activeFilterCount.hidden = !active;
                activeFilterCount.textContent = `${activeEntries.length.toLocaleString('fa-IR')} فیلتر فعال`;
            }
        };

        const syncFormFromUrl = (urlLike, options = {}) => {
            const { syncAdvanced = true } = options;
            const url = new URL(urlLike, window.location.origin);

            form.querySelectorAll('input[name], select[name]').forEach((field) => {
                if (field === todayInput) {
                    return;
                }

                field.value = url.searchParams.get(field.name) ?? '';
            });

            setTodayState(url.searchParams.get('today') === '1');
            updateShortcutState(url);
            updateClearState(url);

            if (syncAdvanced) {
                syncAdvancedStateFromUrl(url);
            }
        };

        const updateResultsMeta = () => {
            const card = results.querySelector('[data-post-results-card]');

            if (!card) {
                nextPageUrl = null;
                return;
            }

            nextPageUrl = card.dataset.nextPageUrl || null;
        };

        const updateLoadedCounter = () => {
            const card = results.querySelector('[data-post-results-card]');
            const body = results.querySelector('[data-post-results-body]');
            const countNode = results.querySelector('[data-post-results-count]');

            if (!card || !body || !countNode) {
                return;
            }

            const total = Number(card.dataset.total || 0);
            const loaded = body.querySelectorAll('tr:not([data-post-empty-row])').length;
            card.dataset.loaded = String(loaded);

            if (total > 0) {
                countNode.innerHTML = `نمایش <strong>${loaded.toLocaleString('fa-IR')}</strong> از <strong>${total.toLocaleString('fa-IR')}</strong> خبر`;
            } else {
                countNode.textContent = 'نتیجه‌ای مطابق فیلترهای فعلی پیدا نشد.';
            }
        };

        const setInfiniteStatus = (state) => {
            const node = results.querySelector('[data-post-infinite-status]');
            const text = results.querySelector('[data-post-infinite-text]');

            if (!node) {
                return;
            }

            if (state === 'done') {
                node.hidden = true;
                return;
            }

            node.hidden = false;

            if (text) {
                text.textContent = state === 'loading'
                    ? 'در حال بارگذاری خبرهای بیشتر...'
                    : 'برای نمایش خبرهای بیشتر اسکرول کنید';
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

        const observeInfiniteScroll = () => {
            infiniteObserver?.disconnect();
            infiniteObserver = null;

            const sentinel = results.querySelector('[data-post-infinite-sentinel]');

            if (!sentinel || !nextPageUrl || !('IntersectionObserver' in window)) {
                setInfiniteStatus(nextPageUrl ? 'idle' : 'done');
                return;
            }

            setInfiniteStatus('idle');

            infiniteObserver = new IntersectionObserver((entries) => {
                const entry = entries[0];

                if (entry?.isIntersecting) {
                    loadMore();
                }
            }, {
                root: null,
                rootMargin: '600px 0px',
                threshold: 0,
            });

            infiniteObserver.observe(sentinel);
        };

        const applyResponse = (payload, requestedUrl, options = {}) => {
            const {
                historyMode = 'push',
                syncForm = false,
            } = options;

            results.innerHTML = payload.html;
            updateResultsMeta();
            updateLoadedCounter();
            enhanceAjaxDeleteForms(results);
            observeInfiniteScroll();

            const resolvedUrl = payload.url || requestedUrl.toString();
            const cleanResolvedUrl = new URL(resolvedUrl, window.location.origin);
            cleanResolvedUrl.searchParams.delete('page');

            if (syncForm) {
                syncFormFromUrl(cleanResolvedUrl);
            } else {
                updateShortcutState(cleanResolvedUrl);
                updateClearState(cleanResolvedUrl);
            }

            if (historyMode === 'push') {
                window.history.pushState({ postsFilters: true }, '', cleanResolvedUrl);
            } else if (historyMode === 'replace') {
                window.history.replaceState({ postsFilters: true }, '', cleanResolvedUrl);
            }

            const countText = Number(payload.total || 0).toLocaleString('fa-IR');
            setBusy(false, payload.filter_error || `${countText} خبر پیدا شد.`);
        };

        const fetchResults = async (urlLike, options = {}) => {
            const url = new URL(urlLike, window.location.origin);
            url.searchParams.delete('page');

            activeFilterRequest?.abort();
            activeLoadMoreRequest?.abort();
            loadingMore = false;

            activeFilterRequest = new AbortController();
            infiniteObserver?.disconnect();

            setBusy(true, 'در حال بروزرسانی نتایج...');

            try {
                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    signal: activeFilterRequest.signal,
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const payload = await response.json();
                applyResponse(payload, url, options);
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                setBusy(false, 'بروزرسانی نتایج با خطا مواجه شد. دوباره تلاش کنید.');
                console.error('Posts AJAX filters failed:', error);
            }
        };

        async function loadMore() {
            if (!nextPageUrl || loadingMore) {
                return;
            }

            loadingMore = true;
            setInfiniteStatus('loading');

            const url = new URL(nextPageUrl, window.location.origin);
            activeLoadMoreRequest?.abort();
            activeLoadMoreRequest = new AbortController();

            try {
                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    signal: activeLoadMoreRequest.signal,
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const payload = await response.json();
                const body = results.querySelector('[data-post-results-body]');

                if (!body) {
                    return;
                }

                body.insertAdjacentHTML('beforeend', payload.rows_html || '');

                nextPageUrl = payload.next_page_url || null;

                const card = results.querySelector('[data-post-results-card]');
                if (card) {
                    card.dataset.nextPageUrl = nextPageUrl || '';
                }

                enhanceAjaxDeleteForms(body);
                updateLoadedCounter();

                loadingMore = false;

                if (nextPageUrl) {
                    setInfiniteStatus('idle');
                    observeInfiniteScroll();
                } else {
                    setInfiniteStatus('done');
                    infiniteObserver?.disconnect();
                }
            } catch (error) {
                loadingMore = false;

                if (error.name === 'AbortError') {
                    return;
                }

                setInfiniteStatus('idle');

                if (statusNode) {
                    statusNode.textContent = 'بارگذاری خبرهای بیشتر با خطا مواجه شد. با کمی اسکرول دوباره تلاش می‌شود.';
                }

                console.error('Posts infinite scroll failed:', error);
            }
        }

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const hasExplicitDate = [...form.querySelectorAll('[data-post-date-filter]')]
                .some((field) => String(field.value || '').trim() !== '');

            if (hasExplicitDate) {
                setTodayState(false);
            }

            fetchResults(formUrl(), { historyMode: 'push' });
        });

        root.querySelectorAll('[data-post-auto-filter]').forEach((field) => {
            field.addEventListener('change', () => {
                fetchResults(formUrl(), { historyMode: 'push' });
            });
        });

        root.querySelectorAll('[data-post-date-filter]').forEach((field) => {
            field.addEventListener('change', () => {
                setTodayState(false);
                fetchResults(formUrl(), { historyMode: 'push' });
            });
        });

        searchInput?.addEventListener('input', () => {
            window.clearTimeout(searchTimer);

            searchTimer = window.setTimeout(() => {
                fetchResults(formUrl(), { historyMode: 'replace' });
            }, 250);
        });

        searchInput?.addEventListener('search', () => {
            window.clearTimeout(searchTimer);
            fetchResults(formUrl(), { historyMode: 'replace' });
        });

        root.addEventListener('click', (event) => {
            const shortcut = event.target.closest('[data-post-filter-link]');
            const clear = event.target.closest('[data-post-clear-filters]');

            if (shortcut) {
                event.preventDefault();
                const url = new URL(shortcut.href, window.location.origin);
                syncFormFromUrl(url);
                fetchResults(url, { historyMode: 'push' });
                return;
            }

            if (clear && clear.getAttribute('aria-disabled') !== 'true') {
                event.preventDefault();
                const url = new URL(clear.href, window.location.origin);
                syncFormFromUrl(url);
                setAdvancedExpanded(false);
                fetchResults(url, { historyMode: 'push' });
            }
        });

        window.addEventListener('popstate', () => {
            syncFormFromUrl(window.location.href);
            fetchResults(window.location.href, {
                historyMode: 'none',
                syncForm: true,
            });
        });

        if (typeof window.initializeJalaliDatepickers === 'function') {
            window.initializeJalaliDatepickers(form);
        }

        enhanceAjaxDeleteForms(results);
        updateResultsMeta();
        updateLoadedCounter();
        observeInfiniteScroll();
        updateShortcutState(window.location.href);
        updateClearState(window.location.href);
    });
})();
