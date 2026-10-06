/* Shared editor workspace: accessible tabs, validation focus and unsaved-change protection. */
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    document.querySelectorAll('[data-admin-form-workspace]').forEach(workspace => {
        const form = workspace.closest('form');
        if (!form) return;

        const tabs = Array.from(workspace.querySelectorAll('[data-admin-workspace-tab]'));
        const panes = Array.from(workspace.querySelectorAll('[data-admin-workspace-pane]'));
        const status = workspace.querySelector('[data-admin-workspace-status]');
        const save = workspace.querySelector('[data-admin-workspace-submit]');
        const errors = Array.from(workspace.querySelectorAll('[data-admin-workspace-error]'));
        if (!tabs.length || tabs.length !== panes.length) return;

        let dirty = false;
        let submitting = false;

        const updateStatus = (heading, message) => {
            const label = status?.querySelector('strong');
            const hint = status?.querySelector('span');
            if (label) label.textContent = heading;
            if (hint) hint.textContent = message;
        };
        const setDirty = () => {
            if (submitting || dirty) return;
            dirty = true;
            workspace.classList.add('has-unsaved-changes');
            updateStatus('تغییرات ذخیره نشده', 'برای ثبت تمام تغییرات، دکمه ذخیره را بزنید.');
        };
        const activate = (id, focusTab = false) => {
            const target = tabs.find(tab => tab.dataset.adminWorkspaceTab === id);
            if (!target) return;

            tabs.forEach(tab => {
                const active = tab === target;
                tab.classList.toggle('is-active', active);
                tab.setAttribute('aria-selected', String(active));
                tab.tabIndex = active ? 0 : -1;
            });
            panes.forEach(pane => {
                pane.classList.toggle('is-active', pane.dataset.adminWorkspacePane === id);
            });
            if (focusTab) target.focus();
        };
        const findFieldForError = field => {
            const parts = field.split('.');
            const bracket = parts.map((part, index) => index ? '[' + part + ']' : part).join('');
            const candidates = [field, bracket, parts[0] + '[]'];
            const controls = Array.from(form.querySelectorAll('[name]'));
            return controls.find(control => candidates.includes(control.name))
                || controls.find(control => control.name.startsWith(bracket + '['));
        };
        const showField = (field, shouldFocus = true) => {
            const control = findFieldForError(field);
            const pane = control?.closest('[data-admin-workspace-pane]');
            if (!pane) return false;
            activate(pane.dataset.adminWorkspacePane);
            if (shouldFocus && control) {
                if (control.getClientRects().length) {
                    control.focus({ preventScroll: true });
                    control.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    const tab = tabs.find(item => item.dataset.adminWorkspaceTab === pane.dataset.adminWorkspacePane);
                    tab?.focus();
                }
            }
            return true;
        };

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => activate(tab.dataset.adminWorkspaceTab));
            tab.addEventListener('keydown', event => {
                let next = index;
                if (event.key === 'ArrowLeft' || event.key === 'ArrowDown') next = (index + 1) % tabs.length;
                else if (event.key === 'ArrowRight' || event.key === 'ArrowUp') next = (index + tabs.length - 1) % tabs.length;
                else if (event.key === 'Home') next = 0;
                else if (event.key === 'End') next = tabs.length - 1;
                else return;
                event.preventDefault();
                activate(tabs[next].dataset.adminWorkspaceTab, true);
            });
        });

        errors.forEach(button => button.addEventListener('click', () => {
            showField(button.dataset.adminWorkspaceError);
        }));

        // Activate JavaScript-dependent tab hiding only after handlers are ready.
        workspace.dataset.workspaceEnhanced = 'true';
        if (errors.length) {
            // Server-side Laravel validation knows the exact field name.
            const firstMatching = errors.find(button => showField(button.dataset.adminWorkspaceError, false));
            if (!firstMatching) activate(tabs[0].dataset.adminWorkspaceTab);
        } else {
            activate(tabs[0].dataset.adminWorkspaceTab);
        }

        form.addEventListener('invalid', event => {
            const pane = event.target.closest('[data-admin-workspace-pane]');
            if (pane) activate(pane.dataset.adminWorkspacePane);
        }, true);

        form.addEventListener('input', setDirty, true);
        form.addEventListener('change', setDirty, true);
        // Dynamic row editors use event delegation; additions/removals need
        // tracking even when no input has been typed.
        form.addEventListener('click', event => {
            if (event.target.closest('[data-add-row], [data-remove-unsaved-row], [data-remove-president-row]')) {
                setDirty();
            }
        }, true);

        // TinyMCE maintains editor content outside the original textarea.
        if (window.tinymce?.on) {
            window.tinymce.on('AddEditor', event => {
                const editor = event.editor;
                if (editor?.targetElm && form.contains(editor.targetElm)) {
                    editor.on('input change keyup', setDirty);
                }
            });
            // Handle editors initialized before the listener was attached.
            window.tinymce.editors?.forEach(editor => {
                if (editor.targetElm && form.contains(editor.targetElm)) {
                    editor.on('input change keyup', setDirty);
                }
            });
        }

        form.addEventListener('submit', () => {
            // A native submit event only fires after HTML5 validation succeeds.
            submitting = true;
            if (save) save.disabled = true;
            updateStatus('در حال ذخیره‌سازی', 'لطفاً تا پایان ثبت اطلاعات صبر کنید.');
        });

        window.addEventListener('beforeunload', event => {
            if (dirty && !submitting) {
                event.preventDefault();
                event.returnValue = '';
            }
        });
    });
});
