(() => {
    const root = document.querySelector('.chamber-board-page');
    const dialog = root?.querySelector('#chamber-member-dialog');
    const content = dialog?.querySelector('[data-chamber-dialog-content]');
    if (!root || !dialog || !content) return;
    let trigger;
    root.querySelectorAll('[data-chamber-member]').forEach(button => {
        button.addEventListener('click', () => {
            const profile = [...root.querySelectorAll('template[data-chamber-profile]')].find(template => template.dataset.chamberProfile === button.dataset.chamberMember);
            if (!profile) return;
            trigger = button;
            content.replaceChildren(profile.content.cloneNode(true));
            dialog.showModal();
        });
    });
    dialog.querySelector('[data-chamber-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => {
        const rect = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close();
    });
    dialog.addEventListener('close', () => trigger?.focus());
})();
