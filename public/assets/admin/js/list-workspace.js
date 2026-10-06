/* Phase 4: progressive, mobile-friendly list presentation.
   GET filters and paginator intentionally work with JavaScript disabled. */
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    document.querySelectorAll('table[data-admin-list-table]').forEach(table => {
        const columns = Array.from(table.querySelectorAll('thead th'))
            .map(th => th.textContent.replace(/\s+/g, ' ').trim());

        if (!columns.length) return;

        table.querySelectorAll('tbody tr').forEach(row => {
            const cells = Array.from(row.children).filter(el => el.tagName === 'TD');

            if (cells.length === 1 && cells[0].hasAttribute('colspan')) {
                row.classList.add('admin-list-empty-row');
                return;
            }

            if (cells.length !== columns.length) return;

            cells.forEach((cell, index) => {
                cell.dataset.adminListLabel = columns[index];
                if (index === 0) cell.classList.add('admin-list-first-cell');
                if (index === cells.length - 1) cell.classList.add('admin-list-actions-cell');
            });
            row.classList.add('admin-list-item-row');
        });

        // Enable mobile card layout only after every visible cell has a label.
        // Drag-sorted galleries require a real table to preserve row reorder.
        if (!table.hasAttribute('data-admin-list-sortable')) {
            table.classList.add('admin-list-cards-ready');
        }
    });

    // Media library view controls were previously decorative. Reuse this
    // script only on its index route so the grid/list toggle is functional.
    const mediaGrid = document.querySelector('[data-admin-media-grid]');
    const mediaButtons = Array.from(document.querySelectorAll('[data-admin-media-view]'));

    if (mediaGrid && mediaButtons.length) {
        const setMediaView = mode => {
            const listMode = mode === 'list';
            mediaGrid.classList.toggle('admin-media-list-mode', listMode);
            mediaButtons.forEach(button => {
                const active = button.dataset.adminMediaView === mode;
                button.classList.toggle('is-active', active);
                button.setAttribute('aria-pressed', String(active));
            });
        };
        mediaButtons.forEach(button => {
            button.addEventListener('click', () => setMediaView(button.dataset.adminMediaView));
        });
        setMediaView('grid');
    }
});
