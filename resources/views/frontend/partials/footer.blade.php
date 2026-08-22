@php
    $settings = app(\App\Services\SettingService::class);
    $footerItems = app(\App\Services\MenuService::class)->items('footer');
    $siteTitle = $settings->get('site.site_title', 'اتاق اصناف مرکز استان گلستان');
    $logo = image_url(
        $settings->get('site.site_logo')
            ?: $settings->get('header.desktop_logo')
            ?: $settings->get('header.header_logo'),
        'assets/img/asnaf-seal.svg'
    );
    $description = $settings->get('footer.footer_description', $settings->get('footer.description', 'اتاق اصناف مرکز استان گلستان به عنوان نماینده جامعه صنفی استان، پشتیبان کسب‌وکارهای صنفی، ناظر بر فعالیت اتحادیه‌های صنفی و تسهیل‌گر تعامل با دستگاه‌های اجرایی و نظارتی است.'));
    $rawPhone = $settings->get('site.phone', '۰۱۷-۳۲۱۵۲۹۱۲');
    $phone = fa_number($rawPhone);
    $phoneAscii = strtr((string) $rawPhone, [
        '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
        '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
    ]);
    $phoneHref = preg_replace('/[^0-9+]/', '', $phoneAscii) ?? '';
    $phoneHref = (str_starts_with($phoneHref, '+') ? '+' : '').str_replace('+', '', $phoneHref);
    $address = $settings->get('site.address', 'گرگان، خیابان مطهری جنوبی، روبروی پمپ بنزین، ساختمان اتاق اصناف');
    $email = $settings->get('site.email', 'info@asnaf-gorgan.ir');
    $copyright = $settings->get('footer.copyright_text', $settings->get('footer.copyright', 'تمام حقوق مادی و معنوی این وبسایت متعلق به اتاق اصناف مرکز استان گلستان می‌باشد'));
    $socials = collect($settings->get('footer.footer_social_links', $settings->get('site.social_links', [])));
    $columns = collect($settings->get('footer.footer_columns', []));
    $contactInfo = collect($settings->get('footer.footer_contact_info', []));
    $quickFallbacks = collect([
        ['title' => 'صفحه اصلی', 'url' => route('home')],
        ['title' => 'آرشیو اخبار', 'url' => route('posts.index')],
        ['title' => 'اتحادیه‌های صنفی', 'url' => route('guilds.index')],
        ['title' => 'سامانه خدمات صنفی', 'url' => route('systems.index')],
        ['title' => 'گالری تصاویر', 'url' => route('galleries.index')],
        ['title' => 'گردشگری', 'url' => route('tourism.index')],
    ]);
    if ($columns->isEmpty()) {
        $fallbackLinks = $footerItems->take(8)->map(fn ($item) => ['title' => $item->title, 'url' => $item->resolved_url, 'target' => $item->target])->values();
        $columns = collect([['title' => 'دسترسی سریع', 'links' => ($fallbackLinks->isNotEmpty() ? $fallbackLinks : $quickFallbacks)->all()]]);
    }

    $seenFooterLinks = [];
    $footerGroups = collect();
    foreach ($columns as $column) {
        $uniqueLinks = collect($column['links'] ?? [])->filter(function ($item) use (&$seenFooterLinks) {
            $title = trim((string) ($item['title'] ?? ''));
            $url = trim((string) ($item['url'] ?? ''));
            $key = mb_strtolower($title).'|'.$url;
            if ($title === '' || $url === '' || isset($seenFooterLinks[$key])) return false;
            $seenFooterLinks[$key] = true;
            return true;
        })->values();
        if ($uniqueLinks->isNotEmpty()) $footerGroups->push(['title' => $column['title'] ?? 'لینک‌های مفید', 'links' => $uniqueLinks]);
    }
    $remainingFooterItems = $footerItems->filter(function ($item) use (&$seenFooterLinks) {
        $title = trim((string) $item->title);
        $url = trim((string) $item->resolved_url);
        $key = mb_strtolower($title).'|'.$url;
        if ($title === '' || $url === '' || isset($seenFooterLinks[$key])) return false;
        $seenFooterLinks[$key] = true;
        return true;
    })->map(fn ($item) => ['title' => $item->title, 'url' => $item->resolved_url, 'target' => $item->target])->values();
    if ($remainingFooterItems->isNotEmpty()) $footerGroups->push(['title' => 'سایر پیوندها', 'links' => $remainingFooterItems]);
    $allFooterLinks = $footerGroups->flatMap(fn ($group) => $group['links'])->values();
    $footerGroups = $allFooterLinks->isNotEmpty()
        ? collect([['title' => $footerGroups->first()['title'] ?? 'دسترسی سریع', 'links' => $allFooterLinks]])
        : collect();

    $validSocials = $socials->map(fn ($social, $key) => is_array($social) ? array_merge(['title' => $key], $social) : ['title' => $key, 'url' => $social])
        ->filter(fn ($social) => filled($social['url'] ?? null));
@endphp
<footer class="site-footer">
<div class="site-container">
<div class="footer-main">
<div class="footer-col footer-brand-col">
<img alt="{{ $siteTitle }}" src="{{ $logo }}" onerror="this.onerror=null;this.src='{{ asset('assets/img/asnaf-seal.svg') }}';"/>
<h3>{{ $siteTitle }}</h3>
<div>{!! $description !!}</div>
</div>
@foreach($footerGroups as $column)
@php
    $panelId = 'footer-links-'.$loop->iteration;
@endphp
<div class="footer-col footer-accordion" data-footer-accordion="quick-links">
<button class="footer-accordion-toggle" type="button" aria-expanded="false" aria-controls="{{ $panelId }}"><span>{{ $column['title'] }}</span><span class="footer-accordion-symbol" aria-hidden="true"></span></button>
<div class="footer-accordion-panel" id="{{ $panelId }}">
<ul>
@foreach($column['links'] as $item)
<li><a href="{{ $item['url'] }}" target="{{ $item['target'] ?? '_self' }}" @if(($item['target'] ?? '_self') === '_blank') rel="noopener" @endif><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg><span>{{ $item['title'] }}</span></a></li>
@endforeach
</ul>
</div>
</div>
@endforeach
<div class="footer-col footer-contact-col">
<h3 class="footer-contact-heading">اطلاعات تماس</h3>
<div class="footer-contact-panel">
@if($contactInfo->isNotEmpty())
@foreach($contactInfo as $contact)
<div class="footer-contact-item"><span class="fc-icon"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Zm0 8v6m0-10v1"/></svg></span><span>{!! fa_number($contact['value'] ?? '') !!}</span></div>
@endforeach
@else
<div class="footer-contact-item"><span class="fc-icon"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-12A7 7 0 1 0 5 9c0 5.8 7 12 7 12Zm0-9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"/></svg></span><span>{{ $address }}</span></div>
@if(filled($phoneHref))<a class="footer-contact-item" href="tel:{{ $phoneHref }}"><span class="fc-icon"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M7.1 3.5 4.8 5.8c-.8.8.2 3.8 3.1 6.7s5.9 3.9 6.7 3.1l2.3-2.3 3.3 3.3-1.5 1.5c-2.3 2.3-7.5.7-12-3.8S.6 4.6 2.9 2.3L4.4.8l2.7 2.7Z"/></svg></span><span>{!! $phone !!}</span></a>@endif
@if(filled($email))<a class="footer-contact-item" href="mailto:{{ $email }}"><span class="fc-icon"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 5h18v14H3V5Zm1.5 1.5L12 13l7.5-6.5M4.5 17.5l5.3-5m9.7 5-5.3-5"/></svg></span><span>{{ $email }}</span></a>@endif
@endif
</div>
</div>
</div>
<div class="footer-divider"></div>
<div class="footer-bottom">
@if($validSocials->isNotEmpty())
<div class="footer-social">
@foreach($validSocials as $social)
<a href="{{ $social['url'] }}" aria-label="{{ $social['title'] }}" target="{{ $social['target'] ?? '_blank' }}" rel="noopener"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M8 12a4 4 0 0 0 4 4h4a4 4 0 0 0 0-8h-2M16 12a4 4 0 0 0-4-4H8a4 4 0 0 0 0 8h2"/></svg></a>
@endforeach
</div>
@endif
<div class="footer-copy">{{ fa_number($copyright) }}</div>
</div>
</div>
</footer>
