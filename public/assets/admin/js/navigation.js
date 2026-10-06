/* Lightweight, same-page search of the permission-filtered admin menu. */
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const root = document.querySelector('[data-admin-quick-search]');
    const input = root?.querySelector('[data-admin-quick-input]');
    const results = root?.querySelector('[data-admin-quick-results]');
    const empty = root?.querySelector('[data-admin-quick-empty]');
    const toggle = document.querySelector('[data-admin-search-toggle]');
    if (!root || !input || !results || !empty) return;

    const links = Array.from(root.querySelectorAll('[data-admin-quick-link]'));
    const normalize = value => String(value ?? '')
        .toLocaleLowerCase('fa')
        .replace(/[يى]/g, 'ی')
        .replace(/ك/g, 'ک')
        .replace(/[\u064B-\u065F]/g, '')
        .replace(/\s+/g, ' ')
        .trim();

    const visible = () => links.filter(link => !link.hidden);
    const closeResults = () => {
        results.hidden = true;
        input.setAttribute('aria-expanded', 'false');
    };
    const closeMobile = () => {
        root.classList.remove('is-mobile-open');
        toggle?.setAttribute('aria-expanded', 'false');
        closeResults();
    };

    const showResults = () => {
        const query = normalize(input.value);
        let shown = 0;
        links.forEach(link => {
            const matches = query === '' || normalize(link.dataset.searchText).includes(query);
            link.hidden = !matches || shown >= 8;
            if (matches && shown < 8) shown++;
        });
        empty.hidden = shown !== 0;
        results.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };

    input.addEventListener('focus', showResults);
    input.addEventListener('input', showResults);
    input.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            event.preventDefault();
            closeMobile();
            input.blur();
        } else if (event.key === 'ArrowDown') {
            const first = visible()[0];
            if (first && !results.hidden) {
                event.preventDefault();
                first.focus();
            }
        } else if (event.key === 'Enter' && !results.hidden) {
            const first = visible()[0];
            if (first) {
                event.preventDefault();
                window.location.assign(first.href);
            }
        }
    });

    results.addEventListener('keydown', event => {
        const options = visible();
        const index = options.indexOf(document.activeElement);
        if (event.key === 'Escape') {
            event.preventDefault();
            input.focus();
            closeMobile();
        } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            if (index < 0) return;
            event.preventDefault();
            const next = index + (event.key === 'ArrowDown' ? 1 : -1);
            if (next < 0) input.focus();
            else (options[next] ?? options[0])?.focus();
        }
    });

    toggle?.addEventListener('click', () => {
        const open = !root.classList.contains('is-mobile-open');
        root.classList.toggle('is-mobile-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        if (open) {
            input.focus();
            showResults();
        } else {
            closeResults();
        }
    });

    document.addEventListener('pointerdown', event => {
        if (!root.contains(event.target) && !toggle?.contains(event.target)) {
            closeMobile();
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !results.hidden) closeMobile();
    });
});
