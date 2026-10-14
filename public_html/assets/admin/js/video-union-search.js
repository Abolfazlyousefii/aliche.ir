/* Searchable union selector for admin video create/edit. No external dependency. */
(function (root) {
    'use strict';

    const normalize = (value) => String(value ?? '')
        .normalize('NFKC')
        .toLocaleLowerCase('fa-IR')
        .replace(/[يى]/g, 'ی')
        .replace(/ك/g, 'ک')
        .replace(/[ۀة]/g, 'ه')
        .replace(/[\u064B-\u065F\u0670\u0640]/g, '')
        .replace(/[\u200c\u200d_-]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    const matchOptions = (entries, term, limit = 70) => {
        const query = normalize(term);
        const results = entries
            .filter((entry) => !query || normalize(entry.label).includes(query))
            .sort((a, b) => {
                if (!query) return a.index - b.index;
                const aPrefix = normalize(a.label).startsWith(query) ? 0 : 1;
                const bPrefix = normalize(b.label).startsWith(query) ? 0 : 1;
                return aPrefix - bPrefix || a.index - b.index;
            });
        return { matches: results.slice(0, limit), total: results.length };
    };

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = { normalize, matchOptions };
    }
    if (typeof document === 'undefined') return;

    const init = (wrapper) => {
        const native = wrapper.querySelector('[data-video-union-native]');
        if (!native || wrapper.dataset.videoUnionReady === '1') return;
        const entries = Array.from(native.options).map((option, index) => ({
            value: option.value,
            label: option.textContent.trim(),
            index,
        }));
        const selectedText = () => native.selectedOptions[0]?.textContent?.trim() || entries[0]?.label || '';
        const inputId = native.id + '_search';
        const listId = native.id + '_search_options';

        const inputWrap = document.createElement('div');
        inputWrap.className = 'vus-control';
        const searchIcon = document.createElement('span');
        searchIcon.className = 'vus-search-icon';
        searchIcon.setAttribute('aria-hidden', 'true');
        searchIcon.textContent = '⌕';
        const input = document.createElement('input');
        input.type = 'search';
        input.id = inputId;
        input.className = 'vus-input';
        input.autocomplete = 'off';
        input.spellcheck = false;
        input.placeholder = 'جستجو یا انتخاب اتحادیه…';
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-haspopup', 'listbox');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-controls', listId);
        input.setAttribute('aria-label', 'جستجو و انتخاب اتحادیه');

        const expandButton = document.createElement('button');
        expandButton.type = 'button';
        expandButton.className = 'vus-expand';
        expandButton.setAttribute('aria-label', 'نمایش فهرست اتحادیه‌ها');
        expandButton.textContent = '⌄';

        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'vus-clear';
        clearButton.textContent = '×';
        clearButton.setAttribute('aria-label', 'انتخاب ویدیوی عمومی (پاک کردن اتحادیه)');
        clearButton.title = 'حذف اتحادیه و انتخاب ویدیوی عمومی';

        inputWrap.append(searchIcon, input, clearButton, expandButton);

        const popup = document.createElement('div');
        popup.className = 'vus-popup';
        popup.hidden = true;
        const list = document.createElement('div');
        list.className = 'vus-list';
        list.id = listId;
        list.setAttribute('role', 'listbox');
        list.setAttribute('aria-label', 'نتایج جستجوی اتحادیه');
        const status = document.createElement('p');
        status.className = 'vus-status';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        popup.append(list, status);
        wrapper.append(inputWrap, popup);

        const label = wrapper.parentElement?.querySelector('label[for="' + native.id + '"]');
        if (label) label.htmlFor = inputId;

        let opened = false;
        let current = [];
        let active = -1;
        let querying = false;

        const restoreLabel = () => {
            input.value = selectedText();
            querying = false;
        };
        const focusActive = () => {
            const options = Array.from(list.querySelectorAll('[role="option"]'));
            options.forEach((option, i) => option.classList.toggle('is-active', i === active));
            if (active >= 0 && options[active]) {
                input.setAttribute('aria-activedescendant', options[active].id);
                options[active].scrollIntoView({ block: 'nearest' });
            } else {
                input.removeAttribute('aria-activedescendant');
            }
        };
        const close = () => {
            opened = false;
            popup.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            restoreLabel();
        };
        const choose = (entry) => {
            native.value = entry.value;
            // The original select remains the only submitted form control.
            native.dispatchEvent(new Event('change', { bubbles: true }));
            close();
            input.focus({ preventScroll: true });
        };
        const draw = (query = '') => {
            const result = matchOptions(entries, query);
            current = result.matches;
            list.replaceChildren();
            current.forEach((entry, index) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'vus-option';
                button.id = listId + '_' + index;
                button.setAttribute('role', 'option');
                button.setAttribute('aria-selected', String(entry.value === native.value));
                button.dataset.value = entry.value;
                button.textContent = entry.label;
                if (entry.value === native.value) button.classList.add('is-selected');
                button.addEventListener('pointerdown', (event) => event.preventDefault());
                button.addEventListener('click', () => choose(entry));
                list.appendChild(button);
            });
            active = current.findIndex((entry) => entry.value === native.value);
            if (query) active = current.length ? 0 : -1;
            focusActive();
            status.textContent = result.total === 0
                ? 'اتحادیه‌ای با این نام یافت نشد.'
                : result.total > current.length
                    ? result.total.toLocaleString('fa-IR') + ' نتیجه؛ برای مشاهده بقیه، عبارت دقیق‌تری بنویسید.'
                    : result.total.toLocaleString('fa-IR') + ' گزینه';
        };
        const open = () => {
            if (opened) return;
            opened = true;
            popup.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            draw('');
        };
        input.addEventListener('focus', () => {
            open();
            // Selecting the current label makes replacing it with a new search easy.
            if (!querying) input.select();
        });
        input.addEventListener('click', () => open());
        input.addEventListener('input', () => {
            querying = true;
            open();
            draw(input.value);
        });
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                if (opened) { event.preventDefault(); close(); }
                return;
            }
            if (event.key === 'Tab') { close(); return; }
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                if (!opened) open();
                if (current.length) {
                    active = event.key === 'ArrowDown'
                        ? (active + 1) % current.length
                        : (active + current.length - 1) % current.length;
                    focusActive();
                }
                return;
            }
            if (event.key === 'Home' && opened) { event.preventDefault(); active = 0; focusActive(); return; }
            if (event.key === 'End' && opened) { event.preventDefault(); active = current.length - 1; focusActive(); return; }
            if (event.key === 'Enter' && opened) {
                event.preventDefault(); // Never accidentally submit the video form.
                if (current[active]) choose(current[active]);
            }
        });
        expandButton.addEventListener('click', () => {
            if (opened) close();
            else { open(); input.focus({ preventScroll: true }); }
        });
        clearButton.addEventListener('click', () => {
            choose(entries[0]); // First native option is the general/unassigned video.
        });
        document.addEventListener('pointerdown', (event) => {
            if (opened && !wrapper.contains(event.target)) close();
        });
        native.addEventListener('change', () => {
            if (opened) draw(querying ? input.value : '');
            else restoreLabel();
            clearButton.hidden = native.value === '';
        });

        wrapper.dataset.videoUnionReady = '1';
        native.classList.add('vus-native-hidden');
        restoreLabel();
        clearButton.hidden = native.value === '';
    };

    const mount = () => document.querySelectorAll('[data-video-union-picker]').forEach(init);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mount);
    else mount();
})(typeof window !== 'undefined' ? window : globalThis);
