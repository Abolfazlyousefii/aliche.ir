(function () {
  "use strict";

  const qs = (selector, scope = document) => scope.querySelector(selector);
  const qsa = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

  const fallbackImageUrl = `${window.location.origin}/assets/img/asnaf-gorgan-default.jpg`;

  const setFallbackImage = (image) => {
    if (!image || image.dataset.fallbackApplied === 'true') return;

    image.dataset.fallbackApplied = 'true';

    if (image.matches('[data-guild-profile-optional-image]')) {
      image.hidden = true;
      const wrap = image.closest('[data-guild-profile-image-wrap]');
      const fallback = wrap ? qs('[data-guild-profile-image-fallback]', wrap) : null;
      if (fallback) fallback.hidden = false;
      wrap?.classList.add('is-image-failed');
      return;
    }

    image.src = image.closest('[data-tourism-directory]')
      ? `${window.location.origin}/assets/img/tourism-placeholder.svg`
      : fallbackImageUrl;
  };

  document.addEventListener('error', (event) => {
    const target = event.target;

    if (target instanceof HTMLImageElement) {
      setFallbackImage(target);
    }
  }, true);

  // Hero Swiper slider
  const heroSlider = qs('.hero-slider');
  if (window.Swiper && heroSlider) {
    const heroSlideCount = qsa('.swiper-slide', heroSlider).length;
    new Swiper(heroSlider, {
      loop: heroSlideCount > 1,
      speed: 650,
      effect: 'slide',
      autoplay: heroSlideCount > 1 ? {
        delay: 4200,
        disableOnInteraction: false
      } : false,
      pagination: {
        el: '.hero-slider-pagination',
        clickable: true
      },
      navigation: {
        nextEl: '.hero-slider-next',
        prevEl: '.hero-slider-prev'
      }
    });
  }


  // Progressive enhancement: turn the server-rendered prices into a measured, seamless track.
  qsa('[data-market-ticker]').forEach((ticker) => {
    if (ticker.dataset.marketTickerEnhanced === 'true') return;

    const viewport = qs('.market-ticker-items', ticker);
    const sourceItems = viewport ? qsa(':scope > [data-market-item]', viewport) : [];
    if (!viewport || !sourceItems.length) return;

    ticker.dataset.marketTickerEnhanced = 'true';
    const label = qs('.market-ticker-title', ticker);
    if (label) {
      label.classList.add('market-ticker-label');
      const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      const iconPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      icon.classList.add('market-ticker-label-icon');
      icon.setAttribute('viewBox', '0 0 24 24');
      icon.setAttribute('aria-hidden', 'true');
      icon.setAttribute('focusable', 'false');
      iconPath.setAttribute('d', 'M4 19V9m5 10V5m6 14v-7m5 7V3M3 19h18');
      icon.appendChild(iconPath);
      label.prepend(icon);
    }
    viewport.classList.add('market-ticker-viewport');

    const track = document.createElement('div');
    const sourceGroup = document.createElement('div');
    track.className = 'market-ticker-track';
    sourceGroup.className = 'market-ticker-group';
    sourceItems.forEach((item) => {
      item.classList.remove('is-active');
      sourceGroup.appendChild(item);
    });
    track.appendChild(sourceGroup);
    viewport.appendChild(track);

    const mobileSpeed = window.matchMedia('(max-width: 767.98px)');
    let duplicateGroup = null;
    let resizeObserver = null;
    let mutationObserver = null;
    let resizeFrame = 0;

    const getSourceItems = () => qsa(':scope > [data-market-item]', sourceGroup);

    const updateMetrics = () => {
      resizeFrame = 0;
      const itemCount = getSourceItems().length;
      const minimumGroupWidth = Math.ceil(viewport.getBoundingClientRect().width);
      sourceGroup.style.minWidth = `${minimumGroupWidth}px`;
      if (duplicateGroup) duplicateGroup.style.minWidth = `${minimumGroupWidth}px`;
      const groupWidth = Math.ceil(sourceGroup.getBoundingClientRect().width);
      const pixelsPerSecond = mobileSpeed.matches ? 36 : 42;

      ticker.dataset.marketCount = String(itemCount);
      ticker.classList.toggle('is-market-static', itemCount <= 1);
      ticker.classList.toggle('is-market-running', itemCount > 1 && groupWidth > 0);

      if (itemCount > 1 && groupWidth > 0) {
        ticker.style.setProperty('--market-ticker-distance', `${groupWidth}px`);
        ticker.style.setProperty('--market-ticker-duration', `${(groupWidth / pixelsPerSecond).toFixed(2)}s`);
      } else {
        ticker.style.removeProperty('--market-ticker-distance');
        ticker.style.removeProperty('--market-ticker-duration');
      }
    };

    const scheduleMetrics = () => {
      if (resizeFrame) window.cancelAnimationFrame(resizeFrame);
      resizeFrame = window.requestAnimationFrame(updateMetrics);
    };

    const syncDuplicateGroup = () => {
      duplicateGroup?.remove();
      duplicateGroup = null;

      if (getSourceItems().length > 1) {
        duplicateGroup = sourceGroup.cloneNode(true);
        duplicateGroup.setAttribute('aria-hidden', 'true');
        duplicateGroup.querySelectorAll('[id]').forEach((element) => element.removeAttribute('id'));
        duplicateGroup.querySelectorAll('[data-market-item]').forEach((item) => item.removeAttribute('data-market-item'));
        track.appendChild(duplicateGroup);
      }

      scheduleMetrics();
    };

    syncDuplicateGroup();

    if ('ResizeObserver' in window) {
      resizeObserver = new ResizeObserver(scheduleMetrics);
      resizeObserver.observe(viewport);
      resizeObserver.observe(sourceGroup);
    } else {
      window.addEventListener('resize', scheduleMetrics, { passive: true });
    }

    mutationObserver = new MutationObserver(syncDuplicateGroup);
    mutationObserver.observe(sourceGroup, { childList: true, subtree: true, characterData: true });
    mobileSpeed.addEventListener('change', scheduleMetrics);

    if (document.fonts) {
      document.fonts.ready.then(scheduleMetrics);
      document.fonts.addEventListener('loadingdone', scheduleMetrics);
    }

    window.addEventListener('pagehide', () => {
      resizeObserver?.disconnect();
      mutationObserver?.disconnect();
      if (!resizeObserver) window.removeEventListener('resize', scheduleMetrics);
      mobileSpeed.removeEventListener('change', scheduleMetrics);
      document.fonts?.removeEventListener('loadingdone', scheduleMetrics);
      if (resizeFrame) window.cancelAnimationFrame(resizeFrame);
    }, { once: true });
  });

  // The archive remains fully usable without JavaScript; this only collapses its mobile union filter.
  qsa('[data-news-filter-toggle]').forEach((toggle) => {
    if (toggle.dataset.newsFilterBound === 'true') return;

    const panelId = toggle.getAttribute('aria-controls');
    const panel = panelId ? document.getElementById(panelId) : null;
    const archive = toggle.closest('.news-archive-page');
    if (!panel || !archive) return;

    toggle.dataset.newsFilterBound = 'true';
    archive.classList.add('is-filter-enhanced');
    panel.hidden = true;

    const setExpanded = (expanded) => {
      toggle.setAttribute('aria-expanded', String(expanded));
      panel.hidden = !expanded;
    };

    toggle.addEventListener('click', () => {
      setExpanded(toggle.getAttribute('aria-expanded') !== 'true');
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        setExpanded(false);
        toggle.focus();
      }
    });
  });

  // News pagination is progressively enhanced; every page link remains a normal server URL.
  qsa('[data-news-archive]').forEach((archive) => {
    if (archive.dataset.newsArchiveBound === 'true') return;

    archive.dataset.newsArchiveBound = 'true';
    let activeController = null;
    let latestRequestId = 0;

    const resultsElement = () => qs('[data-news-archive-results]', archive);
    const pageNumber = (url) => Number(new URL(url, window.location.href).searchParams.get('page') || 1);
    const localizedNumber = (number) => new Intl.NumberFormat('fa-IR', { useGrouping: false }).format(number);

    const setLoading = (results, loading, message = '') => {
      if (!results) return;

      results.classList.toggle('is-loading', loading);
      results.setAttribute('aria-busy', String(loading));
      const feedback = qs('[data-news-archive-feedback]', results);
      if (feedback) feedback.textContent = message;

      if (loading) {
        results.classList.remove('has-error');
        results.style.minHeight = `${Math.ceil(results.getBoundingClientRect().height)}px`;
      } else {
        results.style.removeProperty('min-height');
      }
    };

    const showError = (results) => {
      if (!results) return;

      results.classList.add('has-error');
      results.setAttribute('aria-busy', 'false');
      results.style.removeProperty('min-height');
      const feedback = qs('[data-news-archive-feedback]', results);
      if (feedback) feedback.textContent = 'دریافت اخبار با خطا مواجه شد. دوباره تلاش کنید.';
    };

    const scrollToArchive = (results) => {
      const heading = qs('#news-archive-heading', archive);
      if (!heading) return;

      results.focus({ preventScroll: true });
      const headingRect = heading.getBoundingClientRect();
      const desktop = window.matchMedia('(min-width: 992px)').matches;
      const stickyHeight = desktop
        ? Number.parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--desktop-sticky-stack-height')) || 89
        : 0;
      const offset = stickyHeight + (desktop ? 18 : 14);
      const alreadyVisible = headingRect.top >= offset && headingRect.bottom <= window.innerHeight;
      if (alreadyVisible) return;

      const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      window.scrollTo({
        top: Math.max(0, window.scrollY + headingRect.top - offset),
        behavior: reducedMotion ? 'auto' : 'smooth'
      });
    };

    const validPayload = (payload) => payload
      && typeof payload.html === 'string'
      && payload.html.includes('data-news-archive-results')
      && Number.isInteger(payload.current_page)
      && Number.isInteger(payload.last_page)
      && Number.isInteger(payload.total)
      && typeof payload.url === 'string';

    const loadPage = async (destination, { pushHistory = false, historyNavigation = false } = {}) => {
      const requestedUrl = new URL(destination, window.location.href);
      const currentResults = resultsElement();
      if (!currentResults || requestedUrl.origin !== window.location.origin) return;

      activeController?.abort();
      const controller = new AbortController();
      const requestId = ++latestRequestId;
      activeController = controller;
      const requestedPage = pageNumber(requestedUrl);
      setLoading(currentResults, true, `در حال دریافت صفحه ${localizedNumber(requestedPage)} اخبار`);

      try {
        const response = await fetch(requestedUrl.href, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          signal: controller.signal,
          credentials: 'same-origin'
        });

        if (!response.ok) throw new Error(`News archive request failed with status ${response.status}`);

        let payload;
        try {
          payload = await response.json();
        } catch (error) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }

        if (!validPayload(payload)) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }

        const template = document.createElement('template');
        template.innerHTML = payload.html.trim();
        const nextResults = qs('[data-news-archive-results]', template.content);
        if (!nextResults) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }

        if (requestId !== latestRequestId || controller.signal.aborted) return;

        currentResults.replaceWith(nextResults);
        if (pushHistory) history.pushState({ newsArchive: true }, '', payload.url);

        const feedback = qs('[data-news-archive-feedback]', nextResults);
        if (feedback) feedback.textContent = `صفحه ${localizedNumber(payload.current_page)} اخبار بارگذاری شد`;
        scrollToArchive(nextResults);
      } catch (error) {
        if (error.name === 'AbortError' || requestId !== latestRequestId) return;

        if (historyNavigation) {
          window.location.reload();
          return;
        }

        setLoading(currentResults, false);
        showError(currentResults);
      } finally {
        if (requestId === latestRequestId) {
          activeController = null;
          const latestResults = resultsElement();
          if (latestResults?.classList.contains('is-loading')) setLoading(latestResults, false);
        }
      }
    };

    archive.addEventListener('click', (event) => {
      const link = event.target.closest('.news-archive-pagination a');
      if (!link || event.defaultPrevented || event.button !== 0) return;
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      if (link.target && link.target.toLowerCase() === '_blank') return;

      event.preventDefault();
      loadPage(link.href, { pushHistory: true });
    });

    window.addEventListener('popstate', () => {
      loadPage(window.location.href, { historyNavigation: true });
    });
  });

  // Announcements archive: mobile filters and server-rendered AJAX pagination.
  qsa('[data-announcements-archive]').forEach((archive) => {
    if (archive.dataset.announcementsArchiveBound === 'true') return;

    archive.dataset.announcementsArchiveBound = 'true';
    const filterToggle = qs('[data-announcements-filter-toggle]', archive);
    const filterPanel = qs('[data-announcements-filter-panel]', archive);

    if (filterToggle && filterPanel) {
      const mobileFilters = window.matchMedia('(max-width: 767.98px)');
      archive.classList.add('is-filter-enhanced');

      const setFilterExpanded = (expanded) => {
        filterToggle.setAttribute('aria-expanded', String(expanded));
        filterPanel.hidden = mobileFilters.matches ? !expanded : false;
      };

      const syncFilterViewport = () => {
        filterPanel.hidden = mobileFilters.matches
          ? filterToggle.getAttribute('aria-expanded') !== 'true'
          : false;
      };

      syncFilterViewport();
      mobileFilters.addEventListener('change', syncFilterViewport);

      filterToggle.addEventListener('click', () => {
        setFilterExpanded(filterToggle.getAttribute('aria-expanded') !== 'true');
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && filterToggle.getAttribute('aria-expanded') === 'true') {
          setFilterExpanded(false);
          filterToggle.focus();
        }
      });
    }

    let activeController = null;
    let latestRequestId = 0;
    const resultsElement = () => qs('[data-announcements-results]', archive);
    const localizedNumber = (number) => new Intl.NumberFormat('fa-IR', { useGrouping: false }).format(number);

    const setLoading = (results, loading, message = '') => {
      if (!results) return;

      results.classList.toggle('is-loading', loading);
      results.setAttribute('aria-busy', String(loading));
      const feedback = qs('[data-announcements-feedback]', results);
      if (feedback) feedback.textContent = message;

      if (loading) {
        results.classList.remove('has-error');
        results.style.minHeight = `${Math.ceil(results.getBoundingClientRect().height)}px`;
      } else {
        results.style.removeProperty('min-height');
      }
    };

    const showError = (results) => {
      if (!results) return;

      results.classList.add('has-error');
      results.setAttribute('aria-busy', 'false');
      results.style.removeProperty('min-height');
      const feedback = qs('[data-announcements-feedback]', results);
      if (feedback) feedback.textContent = 'دریافت اطلاعیه‌ها با خطا مواجه شد. دوباره تلاش کنید.';
    };

    const focusAndScroll = (results) => {
      const heading = qs('#announcements-heading', archive);
      if (!heading) return;

      results.focus({ preventScroll: true });
      const headingRect = heading.getBoundingClientRect();
      const desktop = window.matchMedia('(min-width: 992px)').matches;
      const stickyHeight = desktop
        ? Number.parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--desktop-sticky-stack-height')) || 89
        : 0;
      const offset = stickyHeight + (desktop ? 18 : 14);
      if (headingRect.top >= offset && headingRect.bottom <= window.innerHeight) return;

      window.scrollTo({
        top: Math.max(0, window.scrollY + headingRect.top - offset),
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
      });
    };

    const validPayload = (payload) => payload
      && typeof payload.html === 'string'
      && payload.html.includes('data-announcements-results')
      && Number.isInteger(payload.current_page)
      && Number.isInteger(payload.last_page)
      && Number.isInteger(payload.total)
      && typeof payload.url === 'string';

    const loadPage = async (destination, { pushHistory = false, historyNavigation = false } = {}) => {
      const requestedUrl = new URL(destination, window.location.href);
      const currentResults = resultsElement();
      if (!currentResults || requestedUrl.origin !== window.location.origin) return;

      activeController?.abort();
      const controller = new AbortController();
      const requestId = ++latestRequestId;
      activeController = controller;
      const requestedPage = Number(requestedUrl.searchParams.get('page') || 1);
      setLoading(currentResults, true, `در حال دریافت صفحه ${localizedNumber(requestedPage)} اطلاعیه‌ها`);

      try {
        const response = await fetch(requestedUrl.href, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          signal: controller.signal,
          credentials: 'same-origin'
        });

        if (!response.ok) throw new Error(`Announcements request failed with status ${response.status}`);

        let payload;
        try {
          payload = await response.json();
        } catch (error) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }

        if (!validPayload(payload)) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }

        const template = document.createElement('template');
        template.innerHTML = payload.html.trim();
        const nextResults = qs('[data-announcements-results]', template.content);
        if (!nextResults) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }

        if (requestId !== latestRequestId || controller.signal.aborted) return;

        currentResults.replaceWith(nextResults);
        if (pushHistory) history.pushState({ announcementsArchive: true }, '', payload.url);
        const feedback = qs('[data-announcements-feedback]', nextResults);
        if (feedback) feedback.textContent = `صفحه ${localizedNumber(payload.current_page)} اطلاعیه‌ها بارگذاری شد`;
        focusAndScroll(nextResults);
      } catch (error) {
        if (error.name === 'AbortError' || requestId !== latestRequestId) return;
        if (historyNavigation) {
          window.location.reload();
          return;
        }
        setLoading(currentResults, false);
        showError(currentResults);
      } finally {
        if (requestId === latestRequestId) {
          activeController = null;
          const latestResults = resultsElement();
          if (latestResults?.classList.contains('is-loading')) setLoading(latestResults, false);
        }
      }
    };

    archive.addEventListener('click', (event) => {
      const link = event.target.closest('.announcements-pagination a');
      if (!link || event.defaultPrevented || event.button !== 0) return;
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      if (link.target && link.target.toLowerCase() === '_blank') return;

      event.preventDefault();
      loadPage(link.href, { pushHistory: true });
    });

    window.addEventListener('popstate', () => {
      loadPage(window.location.href, { historyNavigation: true });
    });
  });

  // Guild directory: type links, debounced search and pagination share one server-rendered result partial.
  qsa('[data-guilds-directory]').forEach((directory) => {
    if (directory.dataset.guildsDirectoryBound === 'true') return;

    directory.dataset.guildsDirectoryBound = 'true';
    const searchForm = qs('[data-guilds-search-form]', directory);
    const searchInput = qs('[data-guild-search]', directory);
    const typeTabs = qs('[data-guild-type-tabs]', directory);
    let activeController = null;
    let latestRequestId = 0;
    let searchTimer = 0;

    const resultsElement = () => qs('[data-guilds-results]', directory);
    const fa = (number) => new Intl.NumberFormat('fa-IR').format(number);

    directory.addEventListener('error', (event) => {
      const image = event.target;
      if (!(image instanceof HTMLImageElement) || !image.matches('[data-guild-logo]')) return;
      image.closest('[data-guild-logo-wrap]')?.classList.add('is-image-failed');
    }, true);

    const setLoading = (results, loading) => {
      if (!results) return;
      results.classList.toggle('is-loading', loading);
      results.setAttribute('aria-busy', String(loading));
      typeTabs?.classList.toggle('is-loading', loading);
      const feedback = qs('[data-guilds-feedback]', results);
      if (feedback) feedback.textContent = loading ? 'در حال دریافت اتحادیه‌ها...' : '';
      if (loading) {
        results.classList.remove('has-error');
        results.style.minHeight = `${Math.ceil(results.getBoundingClientRect().height)}px`;
      } else {
        results.style.removeProperty('min-height');
      }
    };

    const showError = (results) => {
      if (!results) return;
      results.classList.add('has-error');
      results.setAttribute('aria-busy', 'false');
      results.style.removeProperty('min-height');
      const feedback = qs('[data-guilds-feedback]', results);
      if (feedback) feedback.textContent = 'دریافت اطلاعات اتحادیه‌ها با خطا مواجه شد. دوباره تلاش کنید.';
    };

    const syncControls = (url, payload) => {
      const params = url.searchParams;
      const activeType = params.get('type') || '';
      qsa('[data-guild-type-link]', directory).forEach((link) => {
        const active = (link.dataset.type || '') === activeType;
        link.classList.toggle('is-active', active);
        if (active) link.setAttribute('aria-current', 'page');
        else link.removeAttribute('aria-current');
      });

      if (payload?.type_counts) {
        Object.entries(payload.type_counts).forEach(([key, count]) => {
          const badge = qs(`[data-type-count="${CSS.escape(key)}"]`, directory);
          if (badge) badge.textContent = fa(count);
        });
      }

      if (searchInput && searchInput.value !== (params.get('search') || '')) {
        searchInput.value = params.get('search') || '';
      }

      if (searchForm) {
        let typeField = qs('input[name="type"]', searchForm);
        if (activeType && !typeField) {
          typeField = document.createElement('input');
          typeField.type = 'hidden';
          typeField.name = 'type';
          searchForm.appendChild(typeField);
        }
        if (typeField) {
          typeField.value = activeType;
          if (!activeType) typeField.remove();
        }
      }

      const filtered = Boolean(params.get('search') || activeType || params.get('category_id'));
      const clear = qs('[data-guilds-clear]', directory);
      if (clear) clear.hidden = !filtered;
      const count = qs('[data-guilds-result-count]', directory);
      if (count && payload) count.textContent = `${fa(payload.total)} ${filtered ? 'اتحادیه یافت شد' : 'اتحادیه فعال'}`;
    };

    const focusAndScroll = (results) => {
      const heading = qs('#guilds-directory-heading', directory);
      if (!heading) return;
      heading.focus({ preventScroll: true });
      const rect = heading.getBoundingClientRect();
      const desktop = window.matchMedia('(min-width: 992px)').matches;
      const sticky = desktop ? Number.parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--desktop-sticky-stack-height')) || 89 : 0;
      const offset = sticky + (desktop ? 18 : 14);
      if (rect.top >= offset && rect.bottom <= window.innerHeight) return;
      window.scrollTo({
        top: Math.max(0, window.scrollY + rect.top - offset),
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
      });
    };

    const validPayload = (payload) => payload
      && typeof payload.html === 'string'
      && payload.html.includes('data-guilds-results')
      && Number.isInteger(payload.current_page)
      && Number.isInteger(payload.last_page)
      && Number.isInteger(payload.total)
      && payload.type_counts && typeof payload.type_counts === 'object'
      && typeof payload.url === 'string';

    const loadDirectory = async (destination, { historyMode = 'push', historyNavigation = false, focus = true } = {}) => {
      const requestedUrl = new URL(destination, window.location.href);
      const currentResults = resultsElement();
      if (!currentResults || requestedUrl.origin !== window.location.origin) return;

      activeController?.abort();
      const controller = new AbortController();
      const requestId = ++latestRequestId;
      activeController = controller;
      setLoading(currentResults, true);

      try {
        const response = await fetch(requestedUrl.href, {
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          credentials: 'same-origin',
          signal: controller.signal
        });
        if (!response.ok) throw new Error(`Guild directory request failed with status ${response.status}`);

        let payload;
        try {
          payload = await response.json();
        } catch (error) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }
        if (!validPayload(payload)) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }

        const template = document.createElement('template');
        template.innerHTML = payload.html.trim();
        const nextResults = qs('[data-guilds-results]', template.content);
        if (!nextResults) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }
        if (requestId !== latestRequestId || controller.signal.aborted) return;

        currentResults.replaceWith(nextResults);
        const responseUrl = new URL(payload.url, window.location.href);
        if (historyMode === 'push') history.pushState({ guildsDirectory: true }, '', responseUrl);
        if (historyMode === 'replace') history.replaceState({ guildsDirectory: true }, '', responseUrl);
        syncControls(responseUrl, payload);
        const feedback = qs('[data-guilds-feedback]', nextResults);
        if (feedback) feedback.textContent = `${fa(payload.total)} اتحادیه بارگذاری شد`;
        if (focus) focusAndScroll(nextResults);
      } catch (error) {
        if (error.name === 'AbortError' || requestId !== latestRequestId) return;
        if (historyNavigation) {
          window.location.reload();
          return;
        }
        setLoading(currentResults, false);
        showError(currentResults);
      } finally {
        if (requestId === latestRequestId) {
          activeController = null;
          const latestResults = resultsElement();
          if (latestResults?.classList.contains('is-loading')) setLoading(latestResults, false);
          typeTabs?.classList.remove('is-loading');
        }
      }
    };

    directory.addEventListener('click', (event) => {
      const link = event.target.closest('[data-guild-type-link], .guilds-directory-pagination a');
      if (!link || event.defaultPrevented || event.button !== 0) return;
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      if (link.target && link.target.toLowerCase() === '_blank') return;
      event.preventDefault();
      loadDirectory(link.href);
    });

    searchInput?.addEventListener('input', () => {
      window.clearTimeout(searchTimer);
      const value = searchInput.value.trim();
      if (value.length === 1) return;
      searchTimer = window.setTimeout(() => {
        const url = new URL(searchForm.action, window.location.href);
        const formData = new FormData(searchForm);
        formData.forEach((fieldValue, key) => {
          const normalized = String(fieldValue).trim();
          if (normalized) url.searchParams.set(key, normalized);
        });
        url.searchParams.delete('page');
        loadDirectory(url, { historyMode: 'replace', focus: false });
      }, 300);
    });

    window.addEventListener('popstate', () => {
      window.clearTimeout(searchTimer);
      loadDirectory(window.location.href, { historyMode: 'none', historyNavigation: true });
    });

    syncControls(new URL(window.location.href));
  });

  // Systems directory: categories, debounced search and pagination share one server-rendered result partial.
  qsa('[data-systems-directory]').forEach((directory) => {
    if (directory.dataset.systemsDirectoryBound === 'true') return;

    directory.dataset.systemsDirectoryBound = 'true';
    const searchForm = qs('[data-systems-search-form]', directory);
    const searchInput = qs('[data-system-search]', directory);
    const categoryTabs = qs('[data-systems-category-tabs]', directory);
    let activeController = null;
    let latestRequestId = 0;
    let searchTimer = 0;
    let searchHistoryActive = new URL(window.location.href).searchParams.has('search');

    const resultsElement = () => qs('[data-systems-results]', directory);
    const fa = (number) => new Intl.NumberFormat('fa-IR').format(number);

    const setLoading = (results, loading) => {
      if (!results) return;
      results.classList.toggle('is-loading', loading);
      results.setAttribute('aria-busy', String(loading));
      categoryTabs?.classList.toggle('is-loading', loading);
      const feedback = qs('[data-systems-feedback]', results);
      if (feedback) feedback.textContent = loading ? 'در حال دریافت سامانه‌ها...' : '';
      if (loading) {
        results.classList.remove('has-error');
        results.style.minHeight = `${Math.ceil(results.getBoundingClientRect().height)}px`;
      } else {
        results.style.removeProperty('min-height');
      }
    };

    const showError = (results) => {
      if (!results) return;
      results.classList.add('has-error');
      results.setAttribute('aria-busy', 'false');
      results.style.removeProperty('min-height');
      const feedback = qs('[data-systems-feedback]', results);
      if (feedback) feedback.textContent = 'دریافت اطلاعات سامانه‌ها با خطا مواجه شد. دوباره تلاش کنید.';
    };

    const syncControls = (url, payload) => {
      const params = url.searchParams;
      const activeCategory = params.get('category') || '';
      qsa('[data-system-category-link]', directory).forEach((link) => {
        const active = (link.dataset.category || '') === activeCategory;
        link.classList.toggle('is-active', active);
        if (active) link.setAttribute('aria-current', 'page');
        else link.removeAttribute('aria-current');
      });

      if (payload?.category_counts) {
        Object.entries(payload.category_counts).forEach(([key, count]) => {
          const badge = qs(`[data-category-count="${CSS.escape(key)}"]`, directory);
          if (badge) badge.textContent = fa(count);
        });
        const activeCount = qs('[data-systems-active-count]', directory);
        if (activeCount && Number.isInteger(payload.category_counts.all)) {
          activeCount.textContent = fa(payload.category_counts.all);
        }
      }

      if (searchInput && searchInput.value !== (params.get('search') || '')) {
        searchInput.value = params.get('search') || '';
      }

      if (searchForm) {
        let categoryField = qs('input[name="category"]', searchForm);
        if (activeCategory && !categoryField) {
          categoryField = document.createElement('input');
          categoryField.type = 'hidden';
          categoryField.name = 'category';
          searchForm.appendChild(categoryField);
        }
        if (categoryField) {
          categoryField.value = activeCategory;
          if (!activeCategory) categoryField.remove();
        }
      }

      const filtered = Boolean(params.get('search') || activeCategory);
      const clear = qs('[data-systems-clear]', directory);
      if (clear) clear.hidden = !filtered;
      const count = qs('[data-systems-result-count]', directory);
      if (count && payload) count.textContent = `${fa(payload.total)} ${filtered ? 'سامانه یافت شد' : 'سامانه در فهرست'}`;
    };

    const focusAndScroll = () => {
      const heading = qs('#systems-directory-heading', directory);
      if (!heading) return;
      heading.focus({ preventScroll: true });
      const rect = heading.getBoundingClientRect();
      const desktop = window.matchMedia('(min-width: 992px)').matches;
      const sticky = desktop ? Number.parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--desktop-sticky-stack-height')) || 89 : 0;
      const offset = sticky + (desktop ? 18 : 14);
      if (rect.top >= offset && rect.bottom <= window.innerHeight) return;
      window.scrollTo({
        top: Math.max(0, window.scrollY + rect.top - offset),
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
      });
    };

    const validPayload = (payload) => payload
      && typeof payload.html === 'string'
      && payload.html.includes('data-systems-results')
      && Number.isInteger(payload.current_page)
      && Number.isInteger(payload.last_page)
      && Number.isInteger(payload.total)
      && payload.category_counts && typeof payload.category_counts === 'object'
      && typeof payload.url === 'string';

    const loadDirectory = async (destination, { historyMode = 'push', historyNavigation = false, focus = true } = {}) => {
      const requestedUrl = new URL(destination, window.location.href);
      const currentResults = resultsElement();
      if (!currentResults || requestedUrl.origin !== window.location.origin) return;

      activeController?.abort();
      const controller = new AbortController();
      const requestId = ++latestRequestId;
      activeController = controller;
      setLoading(currentResults, true);

      try {
        const response = await fetch(requestedUrl.href, {
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          credentials: 'same-origin',
          signal: controller.signal
        });
        if (!response.ok) throw new Error(`Systems directory request failed with status ${response.status}`);

        let payload;
        try {
          payload = await response.json();
        } catch (error) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }
        if (!validPayload(payload)) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }

        const template = document.createElement('template');
        template.innerHTML = payload.html.trim();
        const nextResults = qs('[data-systems-results]', template.content);
        if (!nextResults) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }
        if (requestId !== latestRequestId || controller.signal.aborted) return;

        currentResults.replaceWith(nextResults);
        const responseUrl = new URL(payload.url, window.location.href);
        if (historyMode === 'push') history.pushState({ systemsDirectory: true }, '', responseUrl);
        if (historyMode === 'replace') history.replaceState({ systemsDirectory: true }, '', responseUrl);
        syncControls(responseUrl, payload);
        const feedback = qs('[data-systems-feedback]', nextResults);
        if (feedback) feedback.textContent = `${fa(payload.total)} سامانه بارگذاری شد`;
        if (focus) focusAndScroll();
      } catch (error) {
        if (error.name === 'AbortError' || requestId !== latestRequestId) return;
        if (historyNavigation) {
          window.location.reload();
          return;
        }
        setLoading(currentResults, false);
        showError(currentResults);
      } finally {
        if (requestId === latestRequestId) {
          activeController = null;
          const latestResults = resultsElement();
          if (latestResults?.classList.contains('is-loading')) setLoading(latestResults, false);
          categoryTabs?.classList.remove('is-loading');
        }
      }
    };

    directory.addEventListener('click', (event) => {
      const link = event.target.closest('[data-system-category-link], .systems-directory-pagination a');
      if (!link || event.defaultPrevented || event.button !== 0) return;
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      if (link.target && link.target.toLowerCase() === '_blank') return;
      event.preventDefault();
      searchHistoryActive = false;
      loadDirectory(link.href);
    });

    searchInput?.addEventListener('input', () => {
      window.clearTimeout(searchTimer);
      const value = searchInput.value.trim();
      if (value.length === 1) return;
      searchTimer = window.setTimeout(() => {
        const url = new URL(searchForm.action, window.location.href);
        const formData = new FormData(searchForm);
        formData.forEach((fieldValue, key) => {
          const normalized = String(fieldValue).trim();
          if (normalized) url.searchParams.set(key, normalized);
        });
        url.searchParams.delete('page');
        const historyMode = searchHistoryActive ? 'replace' : 'push';
        searchHistoryActive = true;
        loadDirectory(url, { historyMode, focus: false });
      }, 300);
    });

    window.addEventListener('popstate', () => {
      window.clearTimeout(searchTimer);
      searchHistoryActive = new URL(window.location.href).searchParams.has('search');
      loadDirectory(window.location.href, { historyMode: 'none', historyNavigation: true });
    });

    syncControls(new URL(window.location.href));
  });

  // Galleries directory: debounced search and pagination share one server-rendered result partial.
  qsa('[data-galleries-directory]').forEach((directory) => {
    if (directory.dataset.galleriesDirectoryBound === 'true') return;

    directory.dataset.galleriesDirectoryBound = 'true';
    const searchForm = qs('[data-galleries-search-form]', directory);
    const searchInput = qs('[data-gallery-search]', directory);
    const typeTabs = qs('[data-galleries-type-tabs]', directory);
    let activeController = null;
    let latestRequestId = 0;
    let searchTimer = 0;
    let searchHistoryActive = new URL(window.location.href).searchParams.has('search');

    const resultsElement = () => qs('[data-galleries-results]', directory);
    const fa = (number) => new Intl.NumberFormat('fa-IR').format(number);

    directory.addEventListener('error', (event) => {
      const image = event.target;
      if (!(image instanceof HTMLImageElement) || !image.matches('[data-gallery-cover]')) return;
      image.closest('[data-gallery-cover-wrap]')?.classList.add('is-cover-failed');
    }, true);

    const setLoading = (results, loading) => {
      if (!results) return;
      results.classList.toggle('is-loading', loading);
      results.setAttribute('aria-busy', String(loading));
      typeTabs?.classList.toggle('is-loading', loading);
      const feedback = qs('[data-galleries-feedback]', results);
      if (feedback) feedback.textContent = loading ? 'در حال دریافت گالری‌ها...' : '';
      if (loading) {
        results.classList.remove('has-error');
        results.style.minHeight = `${Math.ceil(results.getBoundingClientRect().height)}px`;
      } else {
        results.style.removeProperty('min-height');
      }
    };

    const showError = (results) => {
      if (!results) return;
      results.classList.add('has-error');
      results.setAttribute('aria-busy', 'false');
      results.style.removeProperty('min-height');
      const feedback = qs('[data-galleries-feedback]', results);
      if (feedback) feedback.textContent = 'دریافت اطلاعات گالری‌ها با خطا مواجه شد. دوباره تلاش کنید.';
    };

    const syncControls = (url, payload) => {
      const params = url.searchParams;
      const activeType = params.get('type') || '';
      qsa('[data-gallery-type-link]', directory).forEach((link) => {
        const active = (link.dataset.type || '') === activeType;
        link.classList.toggle('is-active', active);
        if (active) link.setAttribute('aria-current', 'page');
        else link.removeAttribute('aria-current');
      });

      if (payload?.type_counts) {
        Object.entries(payload.type_counts).forEach(([key, count]) => {
          const badge = qs(`[data-gallery-type-count="${CSS.escape(key)}"]`, directory);
          if (badge) badge.textContent = fa(count);
        });
      }

      if (searchInput && searchInput.value !== (params.get('search') || '')) {
        searchInput.value = params.get('search') || '';
      }

      if (searchForm) {
        let typeField = qs('input[name="type"]', searchForm);
        if (activeType && !typeField) {
          typeField = document.createElement('input');
          typeField.type = 'hidden';
          typeField.name = 'type';
          searchForm.appendChild(typeField);
        }
        if (typeField) {
          typeField.value = activeType;
          if (!activeType) typeField.remove();
        }
      }

      const filtered = Boolean(params.get('search') || activeType);
      const clear = qs('[data-galleries-clear]', directory);
      if (clear) clear.hidden = !filtered;
      const count = qs('[data-galleries-count]', directory);
      if (count && payload) count.textContent = fa(payload.total);
    };

    const focusAndScroll = () => {
      const heading = qs('#galleries-directory-heading', directory);
      if (!heading) return;
      heading.focus({ preventScroll: true });
      const rect = heading.getBoundingClientRect();
      const desktop = window.matchMedia('(min-width: 992px)').matches;
      const sticky = desktop ? Number.parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--desktop-sticky-stack-height')) || 89 : 0;
      const offset = sticky + (desktop ? 18 : 14);
      if (rect.top >= offset && rect.bottom <= window.innerHeight) return;
      window.scrollTo({
        top: Math.max(0, window.scrollY + rect.top - offset),
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
      });
    };

    const validPayload = (payload) => payload
      && typeof payload.html === 'string'
      && payload.html.includes('data-galleries-results')
      && Number.isInteger(payload.current_page)
      && Number.isInteger(payload.last_page)
      && Number.isInteger(payload.total)
      && payload.type_counts && typeof payload.type_counts === 'object'
      && typeof payload.url === 'string';

    const loadDirectory = async (destination, { historyMode = 'push', historyNavigation = false, focus = true } = {}) => {
      const requestedUrl = new URL(destination, window.location.href);
      const currentResults = resultsElement();
      if (!currentResults || requestedUrl.origin !== window.location.origin) return;

      activeController?.abort();
      const controller = new AbortController();
      const requestId = ++latestRequestId;
      activeController = controller;
      setLoading(currentResults, true);

      try {
        const response = await fetch(requestedUrl.href, {
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          credentials: 'same-origin',
          signal: controller.signal
        });
        if (!response.ok) throw new Error(`Galleries directory request failed with status ${response.status}`);

        let payload;
        try {
          payload = await response.json();
        } catch (error) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }
        if (!validPayload(payload)) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }

        const template = document.createElement('template');
        template.innerHTML = payload.html.trim();
        const nextResults = qs('[data-galleries-results]', template.content);
        if (!nextResults) {
          if (requestId === latestRequestId) window.location.assign(requestedUrl.href);
          return;
        }
        if (requestId !== latestRequestId || controller.signal.aborted) return;

        currentResults.replaceWith(nextResults);
        const responseUrl = new URL(payload.url, window.location.href);
        if (historyMode === 'push') history.pushState({ galleriesDirectory: true }, '', responseUrl);
        if (historyMode === 'replace') history.replaceState({ galleriesDirectory: true }, '', responseUrl);
        syncControls(responseUrl, payload);
        const feedback = qs('[data-galleries-feedback]', nextResults);
        if (feedback) feedback.textContent = `${fa(payload.total)} گالری بارگذاری شد`;
        if (focus) focusAndScroll();
      } catch (error) {
        if (error.name === 'AbortError' || requestId !== latestRequestId) return;
        if (historyNavigation) {
          window.location.reload();
          return;
        }
        setLoading(currentResults, false);
        showError(currentResults);
      } finally {
        if (requestId === latestRequestId) {
          activeController = null;
          const latestResults = resultsElement();
          if (latestResults?.classList.contains('is-loading')) setLoading(latestResults, false);
          typeTabs?.classList.remove('is-loading');
        }
      }
    };

    directory.addEventListener('click', (event) => {
      const link = event.target.closest('[data-gallery-type-link], .galleries-directory-pagination a');
      if (!link || event.defaultPrevented || event.button !== 0) return;
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      if (link.target && link.target.toLowerCase() === '_blank') return;
      event.preventDefault();
      searchHistoryActive = false;
      loadDirectory(link.href);
    });

    searchInput?.addEventListener('input', () => {
      window.clearTimeout(searchTimer);
      const value = searchInput.value.trim();
      if (value.length === 1) return;
      searchTimer = window.setTimeout(() => {
        const url = new URL(searchForm.action, window.location.href);
        const formData = new FormData(searchForm);
        formData.forEach((fieldValue, key) => {
          const normalized = String(fieldValue).trim();
          if (normalized) url.searchParams.set(key, normalized);
        });
        url.searchParams.delete('page');
        const historyMode = searchHistoryActive ? 'replace' : 'push';
        searchHistoryActive = true;
        loadDirectory(url, { historyMode, focus: false });
      }, 300);
    });

    window.addEventListener('popstate', () => {
      window.clearTimeout(searchTimer);
      searchHistoryActive = new URL(window.location.href).searchParams.has('search');
      loadDirectory(window.location.href, { historyMode: 'none', historyNavigation: true });
    });

    syncControls(new URL(window.location.href));
  });

  // Header search
  const searchTrigger = qs('.search-trigger');
  const searchPanel = qs('#headerSearchPanel');
  const searchInput = qs('#siteSearchInput');
  const searchResults = qs('.header-search-results');
  const searchForm = qs('.header-search-form');

  const searchIndex = [
    { title: 'اتاق اصناف مرکز استان گلستان', type: 'معرفی', url: '#' },
    { title: 'اتحادیه‌های صنفی گرگان', type: 'اتحادیه‌ها', url: '#representatives' },
    { title: 'صدور و تمدید پروانه کسب', type: 'خدمات صنفی', url: '#commissions' },
    { title: 'ثبت شکایت و گزارش تخلف صنفی', type: 'بازرسی و نظارت', url: '#friendship' },
    { title: 'دوره آموزش احکام تجارت و کسب‌وکار', type: 'آموزش', url: '#fractions' },
    { title: 'بخشنامه‌ها و اطلاعیه‌های اصناف', type: 'اطلاعیه', url: '#multimedia' },
    { title: 'آدرس و تلفن اتاق اصناف مرکز استان گلستان', type: 'تماس', url: '#friendship' },
    { title: 'سامانه‌های الکترونیکی اصناف', type: 'سامانه‌ها', url: '#commissions' }
  ];

  function normalize(text) {
    return (text || '')
      .toString()
      .trim()
      .toLowerCase()
      .replace(/[ي]/g, 'ی')
      .replace(/[ك]/g, 'ک')
      .replace(/\s+/g, ' ');
  }

  function renderSearchResults(query) {
    if (!searchResults) return;
    const normalizedQuery = normalize(query);
    searchResults.innerHTML = '';

    if (!normalizedQuery) {
      searchResults.classList.remove('is-visible');
      return;
    }

    const results = searchIndex.filter((item) => normalize(`${item.title} ${item.type}`).includes(normalizedQuery)).slice(0, 6);
    searchResults.classList.add('is-visible');

    if (!results.length) {
      searchResults.innerHTML = '<div class="header-search-empty">موردی مطابق جستجوی شما پیدا نشد.</div>';
      return;
    }

    const fragment = document.createDocumentFragment();
    results.forEach((item) => {
      const link = document.createElement('a');
      link.className = 'header-search-result';
      link.href = item.url;
      link.innerHTML = `<strong>${item.title}</strong><span>${item.type}</span>`;
      link.addEventListener('click', closeHeaderSearch);
      fragment.appendChild(link);
    });
    searchResults.appendChild(fragment);
  }

  function openHeaderSearch() {
    if (!searchPanel || !searchTrigger) return;
    searchPanel.hidden = false;
    searchTrigger.classList.add('is-active');
    searchTrigger.setAttribute('aria-expanded', 'true');
    window.setTimeout(() => searchInput && searchInput.focus(), 40);
  }

  function closeHeaderSearch() {
    if (!searchPanel || !searchTrigger) return;
    searchPanel.hidden = true;
    searchTrigger.classList.remove('is-active');
    searchTrigger.setAttribute('aria-expanded', 'false');
  }

  if (searchTrigger && searchPanel) {
    searchTrigger.addEventListener('click', () => {
      if (searchPanel.hidden) openHeaderSearch();
      else closeHeaderSearch();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeHeaderSearch();
    });

    document.addEventListener('click', (event) => {
      if (searchPanel.hidden) return;
      const target = event.target;
      if (!searchPanel.contains(target) && !searchTrigger.contains(target)) closeHeaderSearch();
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', () => renderSearchResults(searchInput.value));
  }

  if (searchForm) {
    searchForm.addEventListener('submit', (event) => {
      event.preventDefault();
      renderSearchResults(searchInput ? searchInput.value : '');
    });
  }

  // Bootstrap Collapse remains the state owner; these hooks present it as an accessible mobile drawer.
  const mobileNav = qs('#mainNav');
  const mobileNavToggle = qs('.navbar-toggler[data-bs-target="#mainNav"]');
  const mobileNavClose = qs('.mobile-drawer-close');
  const mobileNavOverlay = qs('.mobile-nav-overlay');
  const mobileViewport = window.matchMedia('(max-width: 991.98px)');

  const setMobileDrawerState = (isOpen, returnFocus = false) => {
    if (!mobileNav) return;
    const drawerIsOpen = mobileViewport.matches && isOpen;
    mobileNav.setAttribute('aria-hidden', String(mobileViewport.matches && !isOpen));
    mobileNavToggle?.setAttribute('aria-expanded', String(drawerIsOpen));
    document.body.classList.toggle('mobile-nav-open', drawerIsOpen);

    if (mobileNavOverlay) {
      mobileNavOverlay.setAttribute('aria-hidden', String(!drawerIsOpen));
      if (drawerIsOpen) {
        mobileNavOverlay.hidden = false;
        // Commit the hidden state first so opacity has a real transition origin.
        void mobileNavOverlay.offsetWidth;
        mobileNavOverlay.classList.add('is-visible');
      } else {
        mobileNavOverlay.classList.remove('is-visible');
        mobileNavOverlay.hidden = true;
      }
    }

    if (returnFocus && mobileViewport.matches) mobileNavToggle?.focus();
  };

  const closeMobileDrawer = (returnFocus = true) => {
    if (!mobileNav || !mobileViewport.matches) return;
    const collapse = window.bootstrap?.Collapse.getOrCreateInstance(mobileNav, { toggle: false });
    if (collapse && (mobileNav.classList.contains('show') || mobileNav.classList.contains('collapsing'))) {
      mobileNav.dataset.returnFocus = String(returnFocus);
      collapse.hide();
    } else {
      setMobileDrawerState(false, returnFocus);
    }
  };

  if (mobileNav) {
    mobileNav.addEventListener('show.bs.collapse', () => {
      mobileNav.classList.remove('is-closing');
      setMobileDrawerState(true);
    });
    mobileNav.addEventListener('shown.bs.collapse', () => mobileNavClose?.focus());
    mobileNav.addEventListener('hide.bs.collapse', () => {
      mobileNav.classList.add('is-closing');
      mobileNavOverlay?.classList.remove('is-visible');
    });
    mobileNav.addEventListener('hidden.bs.collapse', () => {
      const returnFocus = mobileNav.dataset.returnFocus !== 'false';
      delete mobileNav.dataset.returnFocus;
      mobileNav.classList.remove('is-closing');
      setMobileDrawerState(false, returnFocus);
    });

    mobileNav.addEventListener('click', (event) => {
      if (event.target.closest('a[href]')) closeMobileDrawer(false);
    });
  }

  mobileNavClose?.addEventListener('click', () => closeMobileDrawer());
  mobileNavOverlay?.addEventListener('click', () => closeMobileDrawer());

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && mobileNav?.classList.contains('show')) closeMobileDrawer();
  });

  const syncMobileDrawerMode = () => {
    if (!mobileNav) return;
    if (!mobileViewport.matches) {
      window.bootstrap?.Collapse.getInstance(mobileNav)?.dispose();
      mobileNav.classList.remove('show', 'collapsing', 'is-closing');
      mobileNav.style.removeProperty('height');
      document.body.classList.remove('mobile-nav-open');
      if (mobileNavOverlay) {
        mobileNavOverlay.classList.remove('is-visible');
        mobileNavOverlay.hidden = true;
        mobileNavOverlay.setAttribute('aria-hidden', 'true');
      }
      mobileNavToggle?.setAttribute('aria-expanded', 'false');
      mobileNav.setAttribute('aria-hidden', 'false');
    } else {
      setMobileDrawerState(mobileNav.classList.contains('show'));
    }
  };

  mobileViewport.addEventListener('change', syncMobileDrawerMode);
  syncMobileDrawerMode();

  // Keep the desktop ticker and anchor offset aligned with the navbar's real height.
  const desktopStickyViewport = window.matchMedia('(min-width: 992px)');
  const stickyNavbar = qs('.main-navbar');
  const stickyTicker = qs('.market-ticker-wrap');

  const syncDesktopStickyHeights = () => {
    if (!desktopStickyViewport.matches || !stickyNavbar) return;

    const navHeight = Math.ceil(stickyNavbar.getBoundingClientRect().height);
    const tickerHeight = stickyTicker
      ? Math.ceil(stickyTicker.getBoundingClientRect().height)
      : 0;

    document.documentElement.style.setProperty('--desktop-sticky-nav-height', `${navHeight}px`);
    document.documentElement.style.setProperty('--desktop-sticky-stack-height', `${navHeight + tickerHeight}px`);
  };

  if ('ResizeObserver' in window && stickyNavbar) {
    const stickyRowsObserver = new ResizeObserver(syncDesktopStickyHeights);
    stickyRowsObserver.observe(stickyNavbar);
    if (stickyTicker) stickyRowsObserver.observe(stickyTicker);
  } else {
    window.addEventListener('resize', syncDesktopStickyHeights, { passive: true });
  }

  desktopStickyViewport.addEventListener('change', syncDesktopStickyHeights);
  window.addEventListener('load', syncDesktopStickyHeights, { once: true });
  syncDesktopStickyHeights();

  // Top navigation dropdowns
  const topNav = qs('#mainNav');
  qsa('.top-nav-item.has-top-submenu > .top-nav-link').forEach((button) => {
    button.addEventListener('click', () => {
      const item = button.closest('.top-nav-item');
      if (!item) return;
      const parent = item.parentElement;
      qsa('.top-nav-item.is-open', parent).forEach((openedItem) => {
        if (openedItem !== item) {
          openedItem.classList.remove('is-open');
          const openedButton = qs('.top-nav-link', openedItem);
          if (openedButton) openedButton.setAttribute('aria-expanded', 'false');
        }
      });
      const isOpen = item.classList.toggle('is-open');
      button.setAttribute('aria-expanded', String(isOpen));
    });
  });

  qsa('.top-submenu a').forEach((link) => {
    link.addEventListener('click', () => {
      const item = link.closest('.top-nav-item');
      if (!item) return;
      item.classList.remove('is-open');
      const button = qs('.top-nav-link', item);
      if (button) button.setAttribute('aria-expanded', 'false');
    });
  });

  document.addEventListener('click', (event) => {
    if (!topNav || topNav.contains(event.target)) return;
    qsa('.top-nav-item.is-open', topNav).forEach((item) => {
      item.classList.remove('is-open');
      const button = qs('.top-nav-link', item);
      if (button) button.setAttribute('aria-expanded', 'false');
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape' || !topNav) return;
    qsa('.top-nav-item.is-open', topNav).forEach((item) => {
      item.classList.remove('is-open');
      const button = qs('.top-nav-link', item);
      if (button) button.setAttribute('aria-expanded', 'false');
    });
  });

  // Vertical quick menu submenus
  // Desktop opens on hover via CSS; click remains available for keyboard/touch users.
  qsa('.quick-menu-item.has-submenu .quick-menu-toggle').forEach((button) => {
    button.addEventListener('click', () => {
      const item = button.closest('.quick-menu-item');
      if (!item) return;
      const parent = item.parentElement;
      qsa('.quick-menu-item.is-open', parent).forEach((openedItem) => {
        if (openedItem !== item) {
          openedItem.classList.remove('is-open');
          const openedButton = qs('.quick-menu-toggle', openedItem);
          if (openedButton) openedButton.setAttribute('aria-expanded', 'false');
        }
      });
      const isOpen = item.classList.toggle('is-open');
      button.setAttribute('aria-expanded', String(isOpen));
    });
  });

  // Guild news slider
  if (window.Swiper && qs('.guild-news-slider')) {
    new Swiper('.guild-news-slider', {
      loop: true,
      speed: 600,
      autoplay: {
        delay: 4000,
        disableOnInteraction: false
      },
      navigation: {
        nextEl: '.guild-slider-next',
        prevEl: '.guild-slider-prev'
      },
      pagination: {
        el: '.guild-news-slider .swiper-pagination',
        clickable: true
      }
    });
  }

  // Generic content sliders (used by inspection/unit visit galleries when rendered by CMS templates)
  qsa('.inspection-slider, .unit-visit-slider, .gallery-slider, .post-gallery-slider').forEach((slider) => {
    if (!window.Swiper || slider.swiper) return;

    new Swiper(slider, {
      loop: qsa('.swiper-slide', slider).length > 1,
      speed: 600,
      slidesPerView: 1,
      spaceBetween: 16,
      autoplay: {
        delay: 3500,
        disableOnInteraction: false
      },
      navigation: {
        nextEl: qs('.swiper-button-next, .slider-next', slider),
        prevEl: qs('.swiper-button-prev, .slider-prev', slider)
      },
      pagination: {
        el: qs('.swiper-pagination', slider),
        clickable: true
      },
      breakpoints: {
        768: { slidesPerView: Math.min(2, qsa('.swiper-slide', slider).length || 1) },
        1024: { slidesPerView: Math.min(3, qsa('.swiper-slide', slider).length || 1) }
      }
    });
  });

  function updateUnionPreviewFromItem(item) {
    if (!item) return;

    const panel = item.closest('[data-tab-panel]');
    const preview = panel ? qs('[data-union-preview]', panel) : null;
    if (!preview) return;

    const link = qs('[data-union-preview-link]', preview);
    const image = qs('[data-union-preview-image]', preview);
    const title = qs('[data-union-preview-title]', preview);
    const excerpt = qs('[data-union-preview-excerpt]', preview);
    const label = qs('[data-union-preview-label]', preview);

    const previewUrl = item.dataset.previewUrl || qs('a[href]', item)?.href || '#';
    const previewImage = item.dataset.previewImage || '';
    const previewTitle = item.dataset.previewTitle || qs('strong', item)?.textContent || '';
    const previewExcerpt = item.dataset.previewExcerpt || qs('small', item)?.textContent || '';
    const previewLabel = item.dataset.previewLabel || 'معرفی اتحادیه';

    if (link) link.href = previewUrl;
    if (image && previewImage) {
      image.src = previewImage;
      image.alt = previewTitle;
    }
    if (title) title.textContent = previewTitle;
    if (excerpt) {
      excerpt.textContent = previewExcerpt;
      excerpt.hidden = !previewExcerpt;
    }
    if (label) label.textContent = previewLabel;

    qsa('[data-union-preview-item]', panel).forEach((row) => {
      row.classList.toggle('is-preview-active', row === item);
    });
  }

  function initializeUnionPreview(panel) {
    if (!panel) return;
    const preferred = qs('[data-union-preview-item].is-preview-active', panel)
      || qs('[data-union-preview-item]', panel);
    updateUnionPreviewFromItem(preferred);
  }

  const representativesSection = qs('#representatives');
  if (representativesSection) {
    const selectPreviewItem = (event) => {
      const item = event.target.closest('[data-union-preview-item]');
      if (!item || !representativesSection.contains(item)) return;
      updateUnionPreviewFromItem(item);
    };

    representativesSection.addEventListener('mouseover', selectPreviewItem);
    representativesSection.addEventListener('focusin', selectPreviewItem);
    representativesSection.addEventListener('click', selectPreviewItem);

    qsa('[data-tab-panel]', representativesSection).forEach(initializeUnionPreview);
  }

  // Generic tab components
  qsa('[data-tab-group]').forEach((group) => {
    group.addEventListener('click', (event) => {
      const button = event.target.closest('[data-tab-target]');
      if (!button) return;

      const groupName = group.getAttribute('data-tab-group');
      const targetName = button.getAttribute('data-tab-target');
      const panelWrap = qs(`[data-tab-panels="${groupName}"]`);

      qsa('[data-tab-target]', group).forEach((item) => item.classList.remove('active'));
      button.classList.add('active');

      if (panelWrap) {
        let activePanel = null;
        qsa('[data-tab-panel]', panelWrap).forEach((panel) => {
          const active = panel.getAttribute('data-tab-panel') === targetName;
          panel.classList.toggle('active', active);
          if (active) activePanel = panel;
        });

        if (groupName === 'representatives') {
          initializeUnionPreview(activePanel);
        }

        qsa('input[type="search"]', panelWrap).forEach((input) => {
          input.value = '';
          filterList(input, '');
          if (input.matches('[data-union-ajax-input]')) {
            input.dispatchEvent(new Event('input', { bubbles: true }));
          }
        });
      }
    });
  });

  // Local list filters
  function filterList(input, query) {
    const normalizedQuery = normalize(query);
    const area = input.closest('[data-search-area]') || input.closest('section') || document;
    qsa('li', area).forEach((item) => {
      const isVisible = normalize(item.textContent).includes(normalizedQuery);
      item.toggleAttribute('data-filter-hidden', normalizedQuery && !isVisible);
    });
  }

  const unionSearchTimers = new WeakMap();
  const unionSearchControllers = new WeakMap();

  function renderUnionResults(input, items) {
    const area = input.closest('[data-search-area]') || input.closest('section') || document;
    const list = qs('[data-union-results]', area);
    if (!list) return;

    list.innerHTML = '';

    if (!items.length) {
      const item = document.createElement('li');
      const avatar = document.createElement('span');
      const content = document.createElement('div');
      const title = document.createElement('strong');
      const description = document.createElement('small');

      avatar.className = 'person-avatar avatar-1';
      title.textContent = 'اتحادیه‌ای یافت نشد';
      description.textContent = 'عبارت دیگری را برای جستجو وارد کنید.';
      content.append(title, description);
      item.append(avatar, content);
      list.appendChild(item);
      return;
    }

    const fragment = document.createDocumentFragment();
    items.forEach((union) => {
      const item = document.createElement('li');
      const link = document.createElement('a');
      const avatar = document.createElement('span');
      const content = document.createElement('div');
      const title = document.createElement('strong');
      const description = document.createElement('small');

      item.className = 'union-home-item';
      item.dataset.unionPreviewItem = '';
      item.dataset.previewUrl = union.preview_url || union.url || '#';
      item.dataset.previewImage = union.preview_image || union.image || '';
      item.dataset.previewTitle = union.preview_title || union.title || '';
      item.dataset.previewExcerpt = union.preview_excerpt || union.description || '';
      item.dataset.previewLabel = union.preview_label || 'معرفی اتحادیه';

      link.href = union.url;
      link.className = 'union-home-link';
      avatar.className = `person-avatar ${union.avatar_class || 'avatar-1'}`;
      if (union.image) {
        const image = document.createElement('img');
        image.src = safeUrl(union.image, '');
        image.alt = union.title || '';
        image.loading = 'lazy';
        avatar.appendChild(image);
      }
      title.textContent = union.title || '';
      description.textContent = union.description || '';
      content.append(title, description);
      link.append(avatar, content);
      item.appendChild(link);
      fragment.appendChild(item);
    });

    list.appendChild(fragment);
    initializeUnionPreview(list.closest('[data-tab-panel]'));
  }

  function fetchUnionResults(input, query) {
    const endpoint = input.getAttribute('data-union-search-url');
    if (!endpoint || !window.fetch) {
      filterList(input, query);
      return;
    }

    const previousController = unionSearchControllers.get(input);
    if (previousController) previousController.abort();

    const controller = new AbortController();
    unionSearchControllers.set(input, controller);

    const url = new URL(endpoint, window.location.origin);
    url.searchParams.set('q', query || '');
    const type = input.getAttribute('data-union-type');
    if (type) url.searchParams.set('type', type);

    input.setAttribute('aria-busy', 'true');

    fetch(url.toString(), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      signal: controller.signal
    })
      .then((response) => {
        if (!response.ok) throw new Error('Union search request failed.');
        return response.json();
      })
      .then((data) => renderUnionResults(input, Array.isArray(data.items) ? data.items : []))
      .catch((error) => {
        if (error.name !== 'AbortError') filterList(input, query);
      })
      .finally(() => {
        if (unionSearchControllers.get(input) === controller) {
          unionSearchControllers.delete(input);
          input.removeAttribute('aria-busy');
        }
      });
  }

  function debounceUnionSearch(input) {
    const previousTimer = unionSearchTimers.get(input);
    if (previousTimer) window.clearTimeout(previousTimer);

    const timer = window.setTimeout(() => fetchUnionResults(input, input.value), 250);
    unionSearchTimers.set(input, timer);
  }

  qsa('[data-filter-input]').forEach((input) => {
    input.addEventListener('input', () => {
      if (input.hasAttribute('data-union-search-url')) {
        debounceUnionSearch(input);
      } else {
        filterList(input, input.value);
      }
    });
  });

  qsa('[data-global-filter]').forEach((input) => {
    input.addEventListener('input', () => {
      const areaName = input.getAttribute('data-global-filter');
      const area = qs(`[data-search-area="${areaName}"]`);
      const normalizedQuery = normalize(input.value);
      if (!area) return;
      qsa('li', area).forEach((item) => {
        const isVisible = normalize(item.textContent).includes(normalizedQuery);
        item.toggleAttribute('data-filter-hidden', normalizedQuery && !isVisible);
      });
    });
  });



  // Homepage unions AJAX search
  function escapeHtml(value) {
    return (value || '').toString()
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function safeUrl(value, fallback = '#') {
    const raw = (value || '').toString().trim();
    if (!raw) return fallback;
    if (raw.startsWith('/') || raw.startsWith('#')) return raw;

    try {
      const parsed = new URL(raw, window.location.origin);
      if (['http:', 'https:', 'mailto:', 'tel:'].includes(parsed.protocol)) {
        return raw;
      }
    } catch (error) {
      return fallback;
    }

    return fallback;
  }

  function safeClassToken(value, fallback = 'avatar-1') {
    const token = (value || '').toString().trim();
    return /^[a-zA-Z0-9_-]+$/.test(token) ? token : fallback;
  }

  function renderUnionAjaxItems(list, items) {
    if (!list) return;

    if (!items.length) {
      list.innerHTML = '<li class="union-home-item union-home-empty"><span class="person-avatar avatar-1"></span><div><strong>نتیجه‌ای پیدا نشد</strong><small>عبارت دیگری را برای جستجوی اتحادیه امتحان کنید.</small></div></li>';
      return;
    }

    list.innerHTML = items.map((item) => {
      const previewUrl = safeUrl(item.preview_url || item.url);
      const previewImage = safeUrl(item.preview_image || item.image, '');
      const previewTitle = item.preview_title || item.title || '';
      const previewExcerpt = item.preview_excerpt || item.description || '';
      const previewLabel = item.preview_label || 'معرفی اتحادیه';

      return `<li class="union-home-item"
        data-union-preview-item
        data-preview-url="${escapeHtml(previewUrl)}"
        data-preview-image="${escapeHtml(previewImage)}"
        data-preview-title="${escapeHtml(previewTitle)}"
        data-preview-excerpt="${escapeHtml(previewExcerpt)}"
        data-preview-label="${escapeHtml(previewLabel)}">
        <a href="${escapeHtml(safeUrl(item.url))}" class="union-home-link">
          <span class="person-avatar ${escapeHtml(safeClassToken(item.avatar_class))}">${item.image ? `<img src="${escapeHtml(safeUrl(item.image, ''))}" alt="${escapeHtml(item.title)}" loading="lazy">` : ''}</span>
          <div><strong>${escapeHtml(item.title)}</strong><small>${escapeHtml(item.description || '')}</small></div>
        </a>
      </li>`;
    }).join('');

    initializeUnionPreview(list.closest('[data-tab-panel]'));
  }

  qsa('[data-union-ajax-input]').forEach((input) => {
    const section = input.closest('[data-union-ajax-url]');
    const panel = input.closest('[data-tab-panel]');
    const list = panel ? qs('[data-union-results]', panel) : null;
    const status = panel ? qs('[data-union-status]', panel) : null;
    const endpoint = section ? section.getAttribute('data-union-ajax-url') : '';
    let timer = null;
    let controller = null;

    const setStatus = (message) => {
      if (!status) return;
      status.hidden = !message;
      status.textContent = message || '';
    };

    const runSearch = () => {
      if (!endpoint || !list) return;
      const params = new URLSearchParams({
        q: input.value || '',
        type: input.getAttribute('data-union-type') || ''
      });

      if (controller) controller.abort();
      controller = new AbortController();
      setStatus('در حال جستجوی اتحادیه‌ها...');

      fetch(`${endpoint}?${params.toString()}`, {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        signal: controller.signal
      })
        .then((response) => {
          if (!response.ok) throw new Error('Union search failed');
          return response.json();
        })
        .then((payload) => {
          renderUnionAjaxItems(list, Array.isArray(payload.items) ? payload.items : []);
          setStatus('');
        })
        .catch((error) => {
          if (error.name === 'AbortError') return;
          setStatus('خطا در جستجو؛ لطفاً دوباره تلاش کنید.');
        });
    };

    input.addEventListener('input', () => {
      window.clearTimeout(timer);
      timer = window.setTimeout(runSearch, 250);
    });
  });

  // Tourism directory progressive enhancement.
  const tourismDirectory = qs('[data-tourism-directory]');
  if (tourismDirectory && tourismDirectory.dataset.ajaxBound !== 'true') {
    tourismDirectory.dataset.ajaxBound = 'true';
    let tourismRequest = null;
    let tourismSequence = 0;

    const setTourismLoading = (loading, message = '') => {
      const results = qs('[data-tourism-results]', tourismDirectory);
      const status = qs('[data-tourism-status]', tourismDirectory);
      results?.setAttribute('aria-busy', String(loading));
      qsa('[data-tourism-type-link]', tourismDirectory).forEach((link) => link.setAttribute('aria-disabled', String(loading)));
      if (status) status.textContent = message;
    };

    const updateTourismTabs = (activeType) => {
      qsa('[data-tourism-type-link]', tourismDirectory).forEach((link) => {
        const active = (link.dataset.tourismType || '') === (activeType || '');
        link.classList.toggle('is-active', active);
        link.toggleAttribute('aria-current', active);
        if (active) link.setAttribute('aria-current', 'page');
      });
    };

    const loadTourismResults = async (browserUrl, pushHistory = true) => {
      tourismRequest?.abort();
      const controller = new AbortController();
      const sequence = ++tourismSequence;
      tourismRequest = controller;
      const targetUrl = new URL(browserUrl, window.location.href);
      targetUrl.hash = '';
      setTourismLoading(true, 'در حال دریافت جاذبه‌ها...');

      try {
        const response = await fetch(targetUrl, {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          signal: controller.signal
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const payload = await response.json().catch(() => { throw new Error('Invalid tourism response'); });
        if (sequence !== tourismSequence || typeof payload.html !== 'string') return;
        const currentResults = qs('[data-tourism-results]', tourismDirectory);
        const template = document.createElement('template');
        template.innerHTML = payload.html.trim();
        const nextResults = template.content.firstElementChild;
        if (!currentResults || !nextResults?.matches('[data-tourism-results]')) throw new Error('Invalid tourism response');
        currentResults.replaceWith(nextResults);
        updateTourismTabs(payload.active_type);
        if (pushHistory) window.history.pushState({ tourismDirectory: true }, '', payload.url || targetUrl);
        setTourismLoading(false, `${Number(payload.total) || 0} جاذبه نمایش داده شد.`);
        qs('#tourism-results-title', tourismDirectory)?.focus({ preventScroll: true });
      } catch (error) {
        if (error.name === 'AbortError') return;
        if (error.message === 'Invalid tourism response') {
          window.location.assign(targetUrl);
          return;
        }
        setTourismLoading(false, 'دریافت اطلاعات گردشگری با خطا مواجه شد. دوباره تلاش کنید.');
      } finally {
        if (tourismRequest === controller) tourismRequest = null;
      }
    };

    tourismDirectory.addEventListener('click', (event) => {
      const link = event.target.closest('[data-tourism-type-link]');
      if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      event.preventDefault();
      loadTourismResults(link.href, true);
    });

    window.addEventListener('popstate', () => loadTourismResults(window.location.href, false));
  }

  // Lightbox
  const lightboxEl = qs('.lightbox');
  if (lightboxEl) {
    const lightboxImg = qs('.lightbox-img', lightboxEl);
    const lightboxClose = qs('.lightbox-close', lightboxEl);
    const lightboxPrev = qs('.lightbox-prev', lightboxEl);
    const lightboxNext = qs('.lightbox-next', lightboxEl);
    const lightboxCounter = qs('.lightbox-counter', lightboxEl);
    let currentIndex = 0;
    let imageSources = [];

    function openLightbox(index) {
      currentIndex = index;
      if (!imageSources.length) return;
      lightboxImg.src = imageSources[currentIndex];
      lightboxEl.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      updateCounter();
    }

    function closeLightbox() {
      lightboxEl.classList.remove('is-open');
      document.body.style.overflow = '';
    }

    function updateCounter() {
      if (!lightboxCounter) return;
      lightboxCounter.textContent = `${currentIndex + 1} از ${imageSources.length}`;
    }

    lightboxClose.addEventListener('click', closeLightbox);

    lightboxPrev.addEventListener('click', () => {
      if (imageSources.length === 0) return;
      currentIndex = (currentIndex - 1 + imageSources.length) % imageSources.length;
      lightboxImg.src = imageSources[currentIndex];
      updateCounter();
    });

    lightboxNext.addEventListener('click', () => {
      if (imageSources.length === 0) return;
      currentIndex = (currentIndex + 1) % imageSources.length;
      lightboxImg.src = imageSources[currentIndex];
      updateCounter();
    });

    document.addEventListener('keydown', (e) => {
      if (!lightboxEl.classList.contains('is-open')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') lightboxNext.click();
      if (e.key === 'ArrowRight') lightboxPrev.click();
    });

    lightboxEl.addEventListener('click', (e) => {
      if (e.target === lightboxEl) closeLightbox();
    });

    // Init from gallery-thumbs
    qsa('[data-gallery-group]').forEach((group) => {
      const thumbs = qsa('[data-gallery-item]', group);
      const sources = thumbs.map((t) => t.getAttribute('data-gallery-item'));

      thumbs.forEach((thumb, idx) => {
        thumb.addEventListener('click', () => {
          imageSources = sources;
          openLightbox(idx);
        });
      });
    });
  }
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


  // Progressive enhancement for homepage latest-news pagination.
  const latestNews = qs('[data-latest-news]');
  if (latestNews && latestNews.dataset.ajaxBound !== 'true') {
    latestNews.dataset.ajaxBound = 'true';
    const status = qs('[data-latest-news-status]');
    const endpoint = latestNews.dataset.endpoint;
    let newsRequest = null;

    const setNewsLoading = (loading) => {
      latestNews.classList.toggle('is-loading', loading);
      latestNews.setAttribute('aria-busy', String(loading));
      qsa('.latest-news-page-button', latestNews).forEach((control) => {
        control.classList.toggle('is-loading-disabled', loading);
        control.setAttribute('aria-disabled', String(loading || control.classList.contains('is-disabled')));
      });
      if (status) status.textContent = loading ? 'در حال دریافت اخبار...' : '';
    };

    const loadNewsPage = async (pageUrl, pushHistory = true) => {
      if (!endpoint) return;
      newsRequest?.abort();
      const controller = new AbortController();
      newsRequest = controller;
      const browserUrl = new URL(pageUrl, window.location.href);
      const requestUrl = new URL(endpoint, window.location.origin);
      requestUrl.search = browserUrl.search;
      setNewsLoading(true);
      status?.classList.add('visually-hidden');

      try {
        const response = await fetch(requestUrl, {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          signal: controller.signal
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        if (typeof data.html !== 'string') throw new Error('Invalid response');
        latestNews.innerHTML = data.html;
        if (pushHistory) window.history.pushState({ latestNews: true }, '', browserUrl);
        if (status) status.textContent = `صفحه ${data.current_page} اخبار بارگذاری شد.`;
        qs('#latest-news-title')?.focus({ preventScroll: true });
        qs('#latest-news')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      } catch (error) {
        if (error.name !== 'AbortError' && status) {
          status.classList.remove('visually-hidden');
          status.textContent = 'دریافت اخبار با خطا مواجه شد. دوباره تلاش کنید.';
        }
      } finally {
        if (newsRequest === controller) setNewsLoading(false);
      }
    };

    latestNews.addEventListener('click', (event) => {
      const link = event.target.closest('a.latest-news-page-button');
      if (!link || link.getAttribute('aria-disabled') === 'true') return;
      event.preventDefault();
      loadNewsPage(link.href);
    });

    window.addEventListener('popstate', () => loadNewsPage(window.location.href, false));
  }

  // Homepage board members: three cards per mobile/tablet slide and all six on desktop.
  const membersSwiperElement = qs('[data-members-swiper]');
  if (membersSwiperElement && membersSwiperElement.dataset.membersSwiperBound !== 'true' && window.Swiper) {
    membersSwiperElement.dataset.membersSwiperBound = 'true';
    const slideCount = qsa('.swiper-slide', membersSwiperElement).length;

    if (slideCount) {
      new Swiper(membersSwiperElement, {
        slidesPerView: Math.min(3, slideCount),
        slidesPerGroup: 3,
        spaceBetween: 10,
        loop: false,
        rewind: false,
        watchOverflow: true,
        grabCursor: slideCount > 3,
        allowTouchMove: slideCount > 3,
        pagination: {
          el: qs('.members-swiper-pagination', membersSwiperElement),
          clickable: true
        },
        breakpoints: {
          768: {
            slidesPerView: Math.min(3, slideCount),
            slidesPerGroup: 3,
            spaceBetween: 14
          },
          992: {
            slidesPerView: Math.min(6, slideCount),
            slidesPerGroup: 6,
            spaceBetween: 14,
            allowTouchMove: false,
            grabCursor: false
          }
        }
      });
    }
  }

  // Accessible mobile footer accordions; panels remain permanently visible on desktop.
  const footerAccordions = qsa('[data-footer-accordion="quick-links"]');
  if (footerAccordions.length) {
    const footerMobile = window.matchMedia('(max-width: 767.98px)');

    const syncFooterAccordions = () => {
      footerAccordions.forEach((accordion) => {
        const toggle = qs('.footer-accordion-toggle', accordion);
        const panel = toggle ? document.getElementById(toggle.getAttribute('aria-controls')) : null;
        if (!toggle || !panel) return;
        const expanded = !footerMobile.matches;
        toggle.setAttribute('aria-expanded', String(expanded));
        panel.hidden = !expanded;
      });
    };

    footerAccordions.forEach((accordion) => {
      const toggle = qs('.footer-accordion-toggle', accordion);
      if (!toggle || toggle.dataset.footerAccordionBound === 'true') return;
      toggle.dataset.footerAccordionBound = 'true';
      toggle.addEventListener('click', () => {
        if (!footerMobile.matches) return;
        const panel = document.getElementById(toggle.getAttribute('aria-controls'));
        if (!panel) return;
        const expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!expanded));
        panel.hidden = expanded;
      });
    });

    footerMobile.addEventListener('change', syncFooterAccordions);
    syncFooterAccordions();
  }


  // Progressive enhancement for the public contact form.
  function setupContactForm() {
    const form = qs('[data-contact-form]');
    if (!form || form.dataset.contactFormBound === 'true') return;

    form.dataset.contactFormBound = 'true';
    const page = form.closest('[data-contact-page]');
    const status = page ? qs('[data-contact-form-status]', page) : null;
    const submitButton = qs('[data-contact-submit]', form);
    const submitLabel = qs('[data-contact-submit-label]', form);
    const messageField = qs('#message', form);
    const counter = qs('[data-message-counter]', form);
    const defaultSubmitLabel = submitLabel?.textContent || 'ارسال پیام';
    let requestController = null;

    const toPersianDigits = (value) => String(value).replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);

    const updateCounter = () => {
      if (!messageField || !counter) return;
      counter.textContent = `${toPersianDigits(messageField.value.length)} از ۵۰۰۰`;
    };

    const setSubmitting = (submitting) => {
      form.classList.toggle('is-submitting', submitting);
      form.setAttribute('aria-busy', String(submitting));
      if (submitButton) {
        submitButton.disabled = submitting;
        submitButton.classList.toggle('is-loading', submitting);
      }
      if (submitLabel) submitLabel.textContent = submitting ? 'در حال ارسال...' : defaultSubmitLabel;
    };

    const renderStatus = (type, message, title = '') => {
      if (!status) return;
      const iconPath = type === 'success'
        ? 'm5 12 4 4L19 6'
        : 'M12 9v4m0 4h.01M12 3 2.5 20h19L12 3Z';
      status.innerHTML = `
        <div class="contact-alert contact-alert-${type}" role="${type === 'success' ? 'status' : 'alert'}">
          <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="${iconPath}"></path></svg></span>
          <div>${title ? `<strong>${title}</strong>` : ''}<p></p></div>
        </div>`;
      const paragraph = qs('p', status);
      if (paragraph) paragraph.textContent = message;
      status.focus({ preventScroll: true });
      status.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    const clearServerErrors = () => {
      qsa('[data-contact-server-error]', form).forEach((error) => error.remove());
      qsa('.contact-control.is-invalid', form).forEach((field) => {
        field.classList.remove('is-invalid');
        field.removeAttribute('aria-invalid');
      });
    };

    const renderValidationErrors = (errors) => {
      clearServerErrors();
      const fields = Object.entries(errors || {});
      fields.forEach(([name, messages]) => {
        const field = form.elements.namedItem(name);
        if (!(field instanceof HTMLElement)) return;
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
        const error = document.createElement('span');
        error.className = 'contact-field-error';
        error.dataset.contactServerError = 'true';
        error.textContent = Array.isArray(messages) ? messages[0] : String(messages);
        field.insertAdjacentElement('afterend', error);
      });
      const firstInvalid = qs('.contact-control.is-invalid', form);
      firstInvalid?.focus({ preventScroll: true });
      firstInvalid?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    messageField?.addEventListener('input', updateCounter);
    updateCounter();

    form.addEventListener('input', (event) => {
      const field = event.target;
      if (!(field instanceof HTMLElement)) return;
      field.classList.remove('is-invalid');
      field.removeAttribute('aria-invalid');
      const next = field.nextElementSibling;
      if (next?.matches('[data-contact-server-error]')) next.remove();
    });

    form.addEventListener('submit', async (event) => {
      if (event.defaultPrevented || !window.fetch || !window.FormData) return;
      event.preventDefault();

      requestController?.abort();
      requestController = new AbortController();
      clearServerErrors();
      setSubmitting(true);

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin',
          signal: requestController.signal
        });

        const payload = await response.json().catch(() => ({}));

        if (response.status === 422 && payload.errors) {
          renderValidationErrors(payload.errors);
          renderStatus('danger', 'لطفاً فیلدهای مشخص‌شده را بررسی و دوباره ارسال کنید.', 'اطلاعات فرم کامل نیست');
          return;
        }

        if (response.status === 429) {
          renderStatus('danger', 'تعداد درخواست‌ها زیاد است. لطفاً یک دقیقه دیگر دوباره تلاش کنید.', 'ارسال موقتاً محدود شده است');
          return;
        }

        if (!response.ok) throw new Error(payload.message || `HTTP ${response.status}`);

        form.reset();
        updateCounter();
        renderStatus('success', payload.message || 'پیام شما با موفقیت ثبت شد.');
      } catch (error) {
        if (error.name !== 'AbortError') {
          renderStatus('danger', 'ارسال پیام با خطا مواجه شد. اتصال اینترنت را بررسی کرده و دوباره تلاش کنید.', 'پیام ارسال نشد');
        }
      } finally {
        setSubmitting(false);
      }
    });
  }

  function setupGuildProfile() {
    const page = qs('[data-guild-profile]');
    if (!page || page.dataset.guildProfileBound === 'true') return;

    page.dataset.guildProfileBound = 'true';
    const navLinks = qsa('[data-guild-profile-nav]', page);
    const sectionMap = new Map();

    navLinks.forEach((link) => {
      const href = link.getAttribute('href') || '';
      if (!href.startsWith('#')) return;
      const section = qs(href, page);
      if (section) sectionMap.set(section, link);
    });

    page.addEventListener('click', (event) => {
      const link = event.target.closest('a[href^="#"]');
      if (!link || !page.contains(link)) return;
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;

      const target = qs(link.getAttribute('href'), page);
      if (!target) return;

      event.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      if (history.replaceState) history.replaceState(null, '', link.getAttribute('href'));
    });

    if ('IntersectionObserver' in window && sectionMap.size) {
      const observer = new IntersectionObserver((entries) => {
        const visible = entries
          .filter((entry) => entry.isIntersecting)
          .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

        if (!visible) return;
        navLinks.forEach((link) => link.classList.remove('is-active'));
        sectionMap.get(visible.target)?.classList.add('is-active');
      }, {
        rootMargin: '-22% 0px -62% 0px',
        threshold: [0.01, 0.2, 0.5]
      });

      sectionMap.forEach((link, section) => observer.observe(section));
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    setupAccessibleFormValidation();
    setupContactForm();
    setupGuildProfile();
  });
})();
