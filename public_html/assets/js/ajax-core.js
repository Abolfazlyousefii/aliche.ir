(function () {
  "use strict";

  const qs = (selector, scope = document) => scope.querySelector(selector);
  const qsa = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

  const isPlainPrimaryClick = (event) => (
    event.button === 0
    && !event.defaultPrevented
    && !event.metaKey
    && !event.ctrlKey
    && !event.shiftKey
    && !event.altKey
  );

  const prefersReducedMotion = () => (
    window.matchMedia
    && window.matchMedia('(prefers-reduced-motion: reduce)').matches
  );

  async function fetchJson(url, signal) {
    const response = await fetch(url, {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      signal
    });

    if (!response.ok) {
      throw new Error(`Request failed with status ${response.status}`);
    }

    const contentType = response.headers.get('content-type') || '';
    if (!contentType.includes('application/json')) {
      throw new Error('Expected a JSON response.');
    }

    return response.json();
  }

  function parseHtmlElement(html, selector) {
    const template = document.createElement('template');
    template.innerHTML = (html || '').trim();
    return template.content.querySelector(selector);
  }

  function formatFaNumber(value) {
    const number = Number(value || 0);

    try {
      return new Intl.NumberFormat('fa-IR').format(number);
    } catch (error) {
      return String(number);
    }
  }

  // ---------------------------------------------------------------------------
  // Generic delegated tabs
  // Keeps tabs working even when their markup is rendered/replaced dynamically.
  // The legacy handler in main.js can coexist with this delegated fallback.
  // ---------------------------------------------------------------------------
  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-tab-target]');
    if (!button) return;

    const group = button.closest('[data-tab-group]');
    if (!group) return;

    if (button.tagName === 'A') {
      const href = button.getAttribute('href') || '';
      if (href === '' || href === '#' || href.startsWith('#')) {
        event.preventDefault();
      }
    }

    const groupName = group.getAttribute('data-tab-group');
    const targetName = button.getAttribute('data-tab-target');
    if (!groupName || !targetName) return;

    const panelWrap = qs(`[data-tab-panels="${groupName}"]`);
    if (!panelWrap) return;

    const targetPanel = qsa('[data-tab-panel]', panelWrap)
      .find((panel) => panel.getAttribute('data-tab-panel') === targetName);

    const alreadyActive = (
      button.classList.contains('active')
      && targetPanel
      && targetPanel.classList.contains('active')
    );

    qsa('[data-tab-target]', group).forEach((item) => {
      const active = item === button;
      item.classList.toggle('active', active);
      item.setAttribute('aria-selected', active ? 'true' : 'false');

      if (item.matches('button, [role="tab"]')) {
        item.setAttribute('tabindex', active ? '0' : '-1');
      }
    });

    qsa('[data-tab-panel]', panelWrap).forEach((panel) => {
      const active = panel.getAttribute('data-tab-panel') === targetName;
      panel.classList.toggle('active', active);
      panel.setAttribute('aria-hidden', active ? 'false' : 'true');
    });

    // main.js already performs this reset when its original handler is active.
    // Only do it here when this delegated fallback was the handler that changed
    // the tab, preventing duplicate AJAX searches.
    if (!alreadyActive) {
      qsa('input[type="search"]', panelWrap).forEach((input) => {
        if (!input.closest(`[data-tab-panel="${targetName}"]`)) return;

        input.value = '';
        if (input.matches('[data-union-ajax-input]')) {
          input.dispatchEvent(new Event('input', { bubbles: true }));
        }
      });
    }
  });

  // Keyboard support for tab groups.
  document.addEventListener('keydown', (event) => {
    if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;

    const current = event.target.closest('[data-tab-target]');
    const group = current ? current.closest('[data-tab-group]') : null;
    if (!current || !group) return;

    const tabs = qsa('[data-tab-target]', group).filter((item) => !item.disabled);
    if (tabs.length < 2) return;

    const currentIndex = Math.max(0, tabs.indexOf(current));
    let nextIndex = currentIndex;

    if (event.key === 'Home') nextIndex = 0;
    if (event.key === 'End') nextIndex = tabs.length - 1;
    if (event.key === 'ArrowLeft') nextIndex = (currentIndex + 1) % tabs.length;
    if (event.key === 'ArrowRight') nextIndex = (currentIndex - 1 + tabs.length) % tabs.length;

    if (nextIndex === currentIndex) return;

    event.preventDefault();
    tabs[nextIndex].focus();
    tabs[nextIndex].click();
  });

  // ---------------------------------------------------------------------------
  // Homepage latest-news AJAX pagination
  // Backend: /home/latest-news
  // Markup: [data-latest-news][data-endpoint]
  // ---------------------------------------------------------------------------
  qsa('[data-latest-news][data-endpoint]').forEach((root) => {
    let controller = null;
    const status = qs('[data-latest-news-status]');

    const setStatus = (message) => {
      if (!status) return;
      status.textContent = message || '';
    };

    const loadPage = async (navigationUrl, fallbackUrl) => {
      if (!window.fetch) {
        window.location.assign(fallbackUrl || navigationUrl);
        return;
      }

      const endpoint = root.getAttribute('data-endpoint');
      if (!endpoint) {
        window.location.assign(fallbackUrl || navigationUrl);
        return;
      }

      const target = new URL(navigationUrl, window.location.href);
      const requestUrl = new URL(endpoint, window.location.origin);

      target.searchParams.forEach((value, key) => {
        requestUrl.searchParams.set(key, value);
      });

      if (controller) controller.abort();
      const requestController = new AbortController();
      controller = requestController;

      root.setAttribute('aria-busy', 'true');
      setStatus('در حال دریافت اخبار...');

      try {
        const payload = await fetchJson(requestUrl.toString(), requestController.signal);

        if (typeof payload.html !== 'string') {
          throw new Error('Latest-news response does not contain HTML.');
        }

        root.innerHTML = payload.html;
        setStatus('اخبار بروزرسانی شد.');

        const heading = qs('#latest-news-title');
        if (heading) {
          heading.focus({ preventScroll: true });
        }
      } catch (error) {
        if (error.name === 'AbortError') return;

        // Progressive enhancement: if AJAX fails, the original Laravel link
        // remains a fully functional fallback.
        window.location.assign(fallbackUrl || navigationUrl);
      } finally {
        if (controller === requestController) {
          root.setAttribute('aria-busy', 'false');
          controller = null;
        }
      }
    };

    root.addEventListener('click', (event) => {
      if (!isPlainPrimaryClick(event)) return;

      const link = event.target.closest('[data-pagination] a[href]');
      if (!link || !root.contains(link)) return;
      if (link.target && link.target !== '_self') return;
      if (link.hasAttribute('download')) return;

      const targetUrl = new URL(link.href, window.location.href);
      if (targetUrl.origin !== window.location.origin) return;

      event.preventDefault();
      loadPage(targetUrl.toString(), link.href);
    });
  });

  // ---------------------------------------------------------------------------
  // Guild directory AJAX controller
  // Handles type tabs, search, clear-filter links, pagination and browser history.
  // Backend already returns the partial HTML + pagination metadata as JSON.
  // ---------------------------------------------------------------------------
  qsa('[data-guilds-directory]').forEach((root) => {
    let controller = null;
    const directoryPath = window.location.pathname;

    const getResults = () => qs('[data-guilds-results]', root);
    const searchForm = qs('[data-guilds-search-form]', root);
    const resultCount = qs('[data-guilds-result-count]', root);
    const clearLink = qs('[data-guilds-clear]', root);

    const setFeedback = (message) => {
      const feedback = qs('[data-guilds-feedback]', root);
      if (feedback) feedback.textContent = message || '';
    };

    const setBusy = (busy) => {
      const results = getResults();
      if (results) results.setAttribute('aria-busy', busy ? 'true' : 'false');
      root.toggleAttribute('data-ajax-loading', busy);
    };

    const syncHiddenField = (form, name, value) => {
      if (!form) return;

      let field = qs(`input[type="hidden"][name="${name}"]`, form);

      if (!value) {
        if (field) field.remove();
        return;
      }

      if (!field) {
        field = document.createElement('input');
        field.type = 'hidden';
        field.name = name;
        form.appendChild(field);
      }

      field.value = value;
    };

    const syncDirectoryState = (url, payload) => {
      const stateUrl = new URL(url, window.location.href);
      const currentType = stateUrl.searchParams.get('type') || '';
      const currentSearch = stateUrl.searchParams.get('search') || '';
      const currentCategory = stateUrl.searchParams.get('category_id') || '';
      const hasFilters = Boolean(currentType || currentSearch || currentCategory);

      qsa('[data-guild-type-link]', root).forEach((link) => {
        const linkType = link.getAttribute('data-type') || '';
        const active = linkType === currentType;

        link.classList.toggle('is-active', active);

        if (active) {
          link.setAttribute('aria-current', 'page');
        } else {
          link.removeAttribute('aria-current');
        }

        // Keep every tab usable without JS and preserve the current search/category.
        const tabUrl = new URL(stateUrl.pathname, window.location.origin);

        if (currentSearch) tabUrl.searchParams.set('search', currentSearch);
        if (currentCategory) tabUrl.searchParams.set('category_id', currentCategory);
        if (linkType) tabUrl.searchParams.set('type', linkType);

        link.href = `${tabUrl.pathname}${tabUrl.search}`;
      });

      if (payload && payload.type_counts) {
        Object.entries(payload.type_counts).forEach(([type, count]) => {
          const target = qs(`[data-type-count="${type}"]`, root);
          if (target) target.textContent = formatFaNumber(count);
        });
      }

      if (searchForm) {
        const searchInput = qs('[name="search"]', searchForm);
        if (searchInput && searchInput.value !== currentSearch) {
          searchInput.value = currentSearch;
        }

        syncHiddenField(searchForm, 'type', currentType);
        syncHiddenField(searchForm, 'category_id', currentCategory);
      }

      if (resultCount && payload && Number.isFinite(Number(payload.total))) {
        resultCount.textContent = `${formatFaNumber(payload.total)} ${hasFilters ? 'اتحادیه یافت شد' : 'اتحادیه فعال'}`;
      }

      if (clearLink) {
        clearLink.hidden = !hasFilters;
      }
    };

    const replaceResults = (html) => {
      const current = getResults();
      const replacement = parseHtmlElement(html, '[data-guilds-results]');

      if (!current || !replacement) {
        throw new Error('Guild directory response does not contain a results element.');
      }

      current.replaceWith(replacement);
      return replacement;
    };

    const loadDirectory = async (url, options = {}) => {
      const {
        pushState = true,
        fallback = true,
        focusResults = true
      } = options;

      const targetUrl = new URL(url, window.location.href);

      if (!window.fetch) {
        if (fallback) window.location.assign(targetUrl.toString());
        return;
      }

      if (controller) controller.abort();
      const requestController = new AbortController();
      controller = requestController;

      setBusy(true);
      setFeedback('در حال دریافت نتایج...');

      try {
        const payload = await fetchJson(targetUrl.toString(), requestController.signal);

        if (typeof payload.html !== 'string') {
          throw new Error('Guild directory response does not contain HTML.');
        }

        const replacement = replaceResults(payload.html);
        const canonicalUrl = payload.url
          ? new URL(payload.url, window.location.href)
          : targetUrl;

        syncDirectoryState(canonicalUrl, payload);
        setFeedback('فهرست اتحادیه‌ها بروزرسانی شد.');

        if (pushState) {
          window.history.pushState(
            { guildsDirectory: true },
            '',
            `${canonicalUrl.pathname}${canonicalUrl.search}${canonicalUrl.hash}`
          );
        }

        if (focusResults) {
          replacement.focus({ preventScroll: true });

          // On smaller screens keep the refreshed result area visible, while
          // respecting reduced-motion preferences.
          if (window.innerWidth < 768) {
            replacement.scrollIntoView({
              behavior: prefersReducedMotion() ? 'auto' : 'smooth',
              block: 'start'
            });
          }
        }
      } catch (error) {
        if (error.name === 'AbortError') return;

        setFeedback('بروزرسانی خودکار انجام نشد؛ صفحه به روش عادی باز می‌شود.');

        if (fallback) {
          window.location.assign(targetUrl.toString());
        }
      } finally {
        if (controller === requestController) {
          setBusy(false);
          controller = null;
        }
      }
    };

    root.addEventListener('click', (event) => {
      if (!isPlainPrimaryClick(event)) return;

      const typeLink = event.target.closest('[data-guild-type-link][href]');
      const paginationLink = event.target.closest('[data-guilds-results] [data-pagination] a[href]');
      const clear = event.target.closest('[data-guilds-clear][href]');
      const link = typeLink || paginationLink || clear;

      if (!link || !root.contains(link)) return;
      if (link.target && link.target !== '_self') return;
      if (link.hasAttribute('download')) return;

      const targetUrl = new URL(link.href, window.location.href);
      if (targetUrl.origin !== window.location.origin) return;

      event.preventDefault();

      // Changing filters must always start from page one.
      if (typeLink || clear) {
        targetUrl.searchParams.delete('page');
      }

      loadDirectory(targetUrl.toString(), {
        pushState: true,
        fallback: true,
        focusResults: true
      });
    });

    if (searchForm) {
      searchForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const action = searchForm.getAttribute('action') || window.location.pathname;
        const targetUrl = new URL(action, window.location.origin);
        const formData = new FormData(searchForm);

        formData.forEach((value, key) => {
          const normalized = String(value).trim();
          if (normalized) {
            targetUrl.searchParams.set(key, normalized);
          }
        });

        targetUrl.searchParams.delete('page');

        loadDirectory(targetUrl.toString(), {
          pushState: true,
          fallback: true,
          focusResults: true
        });
      });
    }

    // Back / Forward must restore the matching server-rendered result state.
    window.addEventListener('popstate', () => {
      if (window.location.pathname !== directoryPath) return;

      loadDirectory(window.location.href, {
        pushState: false,
        fallback: true,
        focusResults: false
      });
    });

    syncDirectoryState(window.location.href, null);
  });

  // ---------------------------------------------------------------------------
  // News archive AJAX pagination
  // Backend: GET /posts (JSON when requested through AJAX)
  // Markup: [data-news-archive] + [data-news-archive-results]
  // ---------------------------------------------------------------------------
  qsa('[data-news-archive]').forEach((root) => {
    let controller = null;
    const archivePath = window.location.pathname;

    const getResults = () => qs('[data-news-archive-results]', root);

    const normalizeArchiveUrl = (rawUrl) => {
      const target = new URL(rawUrl, window.location.href);
      const current = new URL(window.location.href);

      // Some production/proxy configurations can generate http:// links while
      // the public page itself is https://. Keep same-host pagination on the
      // current public protocol instead of treating it as cross-origin.
      if (target.hostname === current.hostname && target.port === current.port) {
        target.protocol = current.protocol;
      }

      return target;
    };

    const setFeedback = (message) => {
      const feedback = qs('[data-news-archive-feedback]', root);
      if (feedback) feedback.textContent = message || '';
    };

    const setBusy = (busy) => {
      const results = getResults();
      if (!results) return;

      results.setAttribute('aria-busy', busy ? 'true' : 'false');
      results.classList.toggle('is-loading', busy);
    };

    const replaceResults = (html) => {
      const current = getResults();
      const replacement = parseHtmlElement(html, '[data-news-archive-results]');

      if (!current || !replacement) {
        throw new Error('News archive response does not contain a results element.');
      }

      current.replaceWith(replacement);
      return replacement;
    };

    const loadArchivePage = async (rawUrl, options = {}) => {
      const {
        pushState = true,
        fallback = true,
        scrollResults = true
      } = options;

      const targetUrl = normalizeArchiveUrl(rawUrl);

      if (targetUrl.origin !== window.location.origin) {
        if (fallback) window.location.assign(targetUrl.toString());
        return;
      }

      if (!window.fetch) {
        if (fallback) window.location.assign(targetUrl.toString());
        return;
      }

      if (controller) controller.abort();

      const requestController = new AbortController();
      controller = requestController;

      setBusy(true);
      setFeedback('در حال دریافت اخبار...');

      try {
        const payload = await fetchJson(targetUrl.toString(), requestController.signal);

        if (typeof payload.html !== 'string') {
          throw new Error('News archive response does not contain HTML.');
        }

        const replacement = replaceResults(payload.html);

        if (pushState) {
          const historyUrl = `${targetUrl.pathname}${targetUrl.search}${targetUrl.hash}`;
          const currentHistoryUrl = `${window.location.pathname}${window.location.search}${window.location.hash}`;

          if (historyUrl !== currentHistoryUrl) {
            window.history.pushState(
              { newsArchive: true },
              '',
              historyUrl
            );
          }
        }

        const pageNumber = Number(payload.current_page || 0);
        setFeedback(
          pageNumber > 0
            ? `صفحه ${formatFaNumber(pageNumber)} اخبار بارگذاری شد.`
            : 'اخبار بروزرسانی شد.'
        );

        replacement.focus({ preventScroll: true });

        if (scrollResults) {
          replacement.scrollIntoView({
            behavior: prefersReducedMotion() ? 'auto' : 'smooth',
            block: 'start'
          });
        }
      } catch (error) {
        if (error.name === 'AbortError') return;

        setFeedback('بروزرسانی خودکار انجام نشد؛ صفحه به روش عادی باز می‌شود.');

        // Progressive enhancement: the original Laravel pagination URL is
        // always kept as a working fallback if AJAX cannot complete.
        if (fallback) {
          window.location.assign(targetUrl.toString());
        }
      } finally {
        if (controller === requestController) {
          setBusy(false);
          controller = null;
        }
      }
    };

    root.addEventListener('click', (event) => {
      if (!isPlainPrimaryClick(event)) return;

      const link = event.target.closest(
        '[data-news-archive-results] [data-pagination] a[href]'
      );

      if (!link || !root.contains(link)) return;
      if (link.target && link.target !== '_self') return;
      if (link.hasAttribute('download')) return;

      const targetUrl = normalizeArchiveUrl(link.href);
      if (targetUrl.origin !== window.location.origin) return;

      event.preventDefault();

      loadArchivePage(targetUrl.toString(), {
        pushState: true,
        fallback: true,
        scrollResults: true
      });
    });

    // Restore the correct result page when the user uses browser Back/Forward.
    window.addEventListener('popstate', () => {
      if (window.location.pathname !== archivePath) return;

      loadArchivePage(window.location.href, {
        pushState: false,
        fallback: true,
        scrollResults: false
      });
    });
  });
})();