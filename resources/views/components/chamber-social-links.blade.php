@props(['links'])
@if(count($links))
@once
<style>
.chamber-social-links{display:flex;flex-wrap:wrap;gap:10px;margin-block:14px;max-width:100%}.chamber-social-links a{display:inline-flex;align-items:center;gap:8px;min-height:44px;max-width:100%;padding:8px 12px;border:1px solid #d8e3ea;border-radius:10px;background:#fff;color:#173e52;text-decoration:none;font-size:14px;line-height:1.6}.chamber-social-links a:hover{background:#eff7fa;border-color:#4d8ca5}.chamber-social-links a:focus-visible{outline:3px solid #167493;outline-offset:3px}.chamber-social-links svg{display:block;width:24px;height:24px;flex-shrink:0}.chamber-social-links span{overflow-wrap:anywhere}.chamber-social-heading{font-size:17px;margin:18px 0 8px}
</style>
@endonce
<nav class="chamber-social-links" aria-label="شبکه‌های اجتماعی اتاق">
@foreach($links as $social)
    <a data-social-platform="{{ $social['platform'] }}" href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $social['title'] }}" aria-label="{{ $social['label'] }} — {{ $social['title'] }}">
        <x-social-icon :name="$social['platform']" /><span>{{ $social['label'] === 'سفارشی' ? $social['title'] : $social['label'] }}</span>
    </a>
@endforeach
</nav>
@endif
