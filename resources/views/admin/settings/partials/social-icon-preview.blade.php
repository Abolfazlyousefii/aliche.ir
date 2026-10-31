@once
@php
    $socialPreviews = [];
    foreach (array_keys(config('social_platforms', [])) as $platform) {
        $socialPreviews[$platform] = view('components.social-icon', ['name' => $platform, 'attributes' => new \Illuminate\View\ComponentAttributeBag])->render();
    }
@endphp
<style>.setting-social-preview{display:inline-flex;align-items:center;gap:6px;margin-top:6px}.setting-social-preview svg{width:24px;height:24px}.setting-social-preview .social-brand-icon{display:inline-flex}</style>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const icons = @json($socialPreviews);
    function preview(row) {
        const select = row.querySelector('select[name$="[platform]"]');
        if (!select) return;
        let target = row.querySelector('[data-social-preview]');
        if (!target) {
            target = document.createElement('span');
            target.dataset.socialPreview = '';
            target.className = 'setting-social-preview';
            select.insertAdjacentElement('afterend', target);
        }
        target.innerHTML = icons[select.value] || icons.custom || '';
    }
    document.querySelectorAll('[data-social-list]').forEach(function (list) {
        list.querySelectorAll('[data-row]').forEach(preview);
        list.addEventListener('change', function (event) {
            if (event.target.matches('select[name$="[platform]"]')) preview(event.target.closest('[data-row]'));
        });
        new MutationObserver(function (changes) {
            changes.forEach(function (change) {
                change.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1 && node.matches('[data-row]')) preview(node);
                });
            });
        }).observe(list.querySelector('[data-rows]'), {childList:true});
    });
});
</script>
@endpush
@endonce
