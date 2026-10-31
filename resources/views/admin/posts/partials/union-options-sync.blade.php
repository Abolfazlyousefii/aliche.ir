<script>
(() => {
    const select = document.querySelector('#union_id[data-union-options-url]');
    if (!select) return;
    const status = document.querySelector('[data-union-options-status]');
    let busy = false;
    let lastAttempt = 0;
    const refreshNames = async () => {
        if (busy || document.hidden || Date.now() - lastAttempt < 1000) return;
        busy = true;
        lastAttempt = Date.now();
        const abort = new AbortController();
        const timeout = setTimeout(() => abort.abort(), 8000);
        try {
            const response = await fetch(select.dataset.unionOptionsUrl, {
                credentials: 'same-origin', cache: 'no-store',
                headers: {'Accept': 'application/json'}, signal: abort.signal
            });
            if (!response.ok || response.redirected) throw new Error('Options unavailable');
            const payload = await response.json();
            if (!Array.isArray(payload.data)) throw new Error('Invalid options');
            const control = select.tomselect;
            const options = new Map(Array.from(select.options, option => [option.value, option]));
            payload.data.forEach(row => {
                const id = String(row.id);
                if (!/^\d+$/.test(id) || typeof row.text !== 'string') return;
                const text = row.text + (row.is_active ? '' : ' — غیرفعال');
                const existing = options.get(id);
                if (existing) existing.textContent = text;
                else select.add(new Option(text, id, false, false));
                if (control) {
                    if (control.options[id]) control.updateOption(id, {...control.options[id], text});
                    else control.addOption({value: id, text});
                }
            });
            // Only labels change: never clear/reselect the union or reload the news form.
            if (status) status.textContent = '';
        } catch (error) {
            if (status) status.textContent = 'دریافت نام‌های جدید انجام نشد؛ برای تلاش دوباره فهرست اتحادیه را باز کنید.';
        } finally {
            clearTimeout(timeout);
            busy = false;
        }
    };
    select.addEventListener('focus', refreshNames);
    select.tomselect?.on('focus', refreshNames);
    select.tomselect?.on('dropdown_open', refreshNames);
    window.addEventListener('focus', refreshNames);
    window.addEventListener('pageshow', refreshNames);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshNames(); });
})();
</script>
