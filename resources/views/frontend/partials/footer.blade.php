@php
    $settings = app(\App\Services\SettingService::class);
    $footerItems = app(\App\Services\MenuService::class)->items('footer');
    $logo = image_url($settings->get('footer.footer_logo'), 'assets/img/asnaf-footer-mark.svg');
    $description = $settings->get('footer.footer_description', $settings->get('footer.description', 'اتاق اصناف مرکز استان گلستان به عنوان نماینده جامعه صنفی استان، پشتیبان کسب‌وکارهای صنفی، ناظر بر فعالیت اتحادیه‌های صنفی و تسهیل‌گر تعامل با دستگاه‌های اجرایی و نظارتی است.'));
    $phone = fa_number($settings->get('site.phone', '۰۱۷-۳۲۱۵۲۹۱۲'));
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
        $columns = collect([
            ['title' => 'دسترسی سریع', 'links' => $footerItems->take(8)->map(fn($item) => ['title' => $item->title, 'url' => $item->resolved_url, 'target' => $item->target])->values()->all() ?: $quickFallbacks->all()],
        ]);
    }
@endphp
<footer class="site-footer">
<div class="site-container">
<div class="footer-main">
<div class="footer-col footer-brand-col">
<img alt="اتاق اصناف مرکز استان گلستان" src="{{ $logo }}"/>
<div>{!! $description !!}</div>
</div>
@foreach($columns as $column)
<div class="footer-col">
<h4>{{ $column['title'] ?? 'لینک‌های مفید' }}</h4>
<ul>
@foreach(($column['links'] ?? []) as $item)
<li><a href="{{ $item['url'] ?? '#' }}" target="{{ $item['target'] ?? '_self' }}">{{ $item['icon'] ?? '' }} {{ $item['title'] ?? '' }}</a></li>
@endforeach
</ul>
</div>
@endforeach
<div class="footer-col">
<h4>اطلاعات تماس</h4>
@if($contactInfo->isNotEmpty())
@foreach($contactInfo as $contact)
<div class="footer-contact-item"><span class="fc-icon"><svg aria-hidden="true" class="ui-icon" viewBox="0 0 24 24"><path d="M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Zm0 8v6m0-10v1"/></svg></span><span>{!! fa_number($contact['value'] ?? '') !!}</span></div>
@endforeach
@else
<div class="footer-contact-item"><span class="fc-icon"><svg aria-hidden="true" class="ui-icon" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-12A7 7 0 1 0 5 9c0 5.8 7 12 7 12Zm0-9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"/></svg></span><span>{{ $address }}</span></div>
<div class="footer-contact-item"><span class="fc-icon"><svg aria-hidden="true" class="ui-icon" viewBox="0 0 24 24"><path d="M7.1 3.5 4.8 5.8c-.8.8.2 3.8 3.1 6.7s5.9 3.9 6.7 3.1l2.3-2.3 3.3 3.3-1.5 1.5c-2.3 2.3-7.5.7-12-3.8S.6 4.6 2.9 2.3L4.4.8l2.7 2.7Z"/></svg></span><span>{!! $phone !!}</span></div>
<div class="footer-contact-item"><span class="fc-icon"><svg aria-hidden="true" class="ui-icon" viewBox="0 0 24 24"><path d="M3 5h18v14H3V5Zm1.5 1.5L12 13l7.5-6.5M4.5 17.5l5.3-5m9.7 5-5.3-5"/></svg></span><span>{{ $email }}</span></div>
@endif
</div>
</div>
<div class="footer-divider"></div>
<div class="footer-orgs">
@forelse($footerItems->take(10) as $item)
<a href="{{ $item->resolved_url }}" target="{{ $item->target }}">{{ $item->icon }} {{ $item->title }}</a>
@empty
@foreach($quickFallbacks as $item)
<a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
@endforeach
@endforelse
</div>
<div class="footer-divider"></div>
<div class="footer-bottom">
<div class="footer-social">
@foreach($socials as $key => $social)
@php
    $social = is_array($social) ? $social : ['title' => $key, 'url' => $social, 'icon' => mb_substr((string) $key, 0, 1)];
@endphp
@if(filled($social['url'] ?? null))
<a href="{{ $social['url'] }}" aria-label="{{ $social['title'] ?? $key }}" target="{{ $social['target'] ?? '_blank' }}" rel="noopener">{{ $social['icon'] ?? mb_substr((string) ($social['title'] ?? $key), 0, 1) }}</a>
@endif
@endforeach
</div>
<div class="footer-copy">{{ fa_number($copyright) }}</div>
</div>
</div>
</footer>
