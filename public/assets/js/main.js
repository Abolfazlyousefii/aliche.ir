(function () {
  "use strict";

  const qs = (selector, scope = document) => scope.querySelector(selector);
  const qsa = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

  const fallbackImageUrl = `${window.location.origin}/assets/img/asnaf-gorgan-default.jpg`;

  const setFallbackImage = (image) => {
    if (!image || image.dataset.fallbackApplied === 'true') return;

    image.dataset.fallbackApplied = 'true';
    image.src = fallbackImageUrl;
  };

  document.addEventListener('error', (event) => {
    const target = event.target;

    if (target instanceof HTMLImageElement) {
      setFallbackImage(target);
    }
  }, true);

  // Hero Swiper slider
  if (window.Swiper && qs('.hero-slider')) {
    new Swiper('.hero-slider', {
      loop: true,
      speed: 650,
      effect: 'slide',
      autoplay: {
        delay: 4200,
        disableOnInteraction: false
      },
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


  // Market ticker slider
  qsa('[data-market-ticker]').forEach((ticker) => {
    const items = qsa('[data-market-item]', ticker);
    if (items.length <= 1) return;

    let activeIndex = Math.max(0, items.findIndex((item) => item.classList.contains('is-active')));
    items.forEach((item, index) => item.classList.toggle('is-active', index === activeIndex));

    const showItem = (nextIndex) => {
      items[activeIndex].classList.remove('is-active');
      activeIndex = (nextIndex + items.length) % items.length;
      items[activeIndex].classList.add('is-active');
    };

    let timer = window.setInterval(() => showItem(activeIndex + 1), 3500);

    ticker.addEventListener('mouseenter', () => window.clearInterval(timer));
    ticker.addEventListener('mouseleave', () => {
      timer = window.setInterval(() => showItem(activeIndex + 1), 3500);
    });
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
        qsa('[data-tab-panel]', panelWrap).forEach((panel) => {
          const active = panel.getAttribute('data-tab-panel') === targetName;
          panel.classList.toggle('active', active);
        });

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

      link.href = union.url;
      link.className = 'd-flex align-items-center gap-2 text-decoration-none';
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
      const socials = (item.social_links || [])
        .filter((link) => link && link.url)
        .map((link) => `<a href="${escapeHtml(safeUrl(link.url))}" target="_blank" rel="noopener noreferrer">${escapeHtml(link.label)}</a>`)
        .join('');

      return `<li class="union-home-item">
        <a href="${escapeHtml(safeUrl(item.url))}" class="d-flex align-items-center gap-2 text-decoration-none">
          <span class="person-avatar ${escapeHtml(safeClassToken(item.avatar_class))}">${item.image ? `<img src="${escapeHtml(safeUrl(item.image, ''))}" alt="${escapeHtml(item.title)}" loading="lazy">` : ''}</span>
          <div><strong>${escapeHtml(item.title)}</strong><small>${escapeHtml(item.description || '')}</small></div>
        </a>
        <div class="union-home-actions"><a href="${escapeHtml(safeUrl(item.complaint_url))}">ثبت شکایت</a>${socials}</div>
      </li>`;
    }).join('');
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
  

  document.addEventListener('DOMContentLoaded', () => setupAccessibleFormValidation());
})();
