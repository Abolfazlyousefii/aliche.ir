<?php

namespace App\Support;

use App\Services\SettingService;

class SiteSocialLinks
{
    /** Preserve both settings sources, including legacy keyed URLs. */
    public static function items(SettingService $settings): array
    {
        $items = [];
        $seen = [];
        foreach (['site.social_links', 'footer.footer_social_links'] as $setting) {
            foreach ((array) $settings->get($setting, []) as $key => $value) {
                $item = is_array($value) ? $value : ['url' => $value, 'title' => (string) $key];
                $url = trim((string) ($item['url'] ?? ''));
                $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
                if (! in_array($scheme, ['http', 'https'], true) || ! filter_var($url, FILTER_VALIDATE_URL) || isset($seen[$url])) {
                    continue;
                }
                $platform = (string) ($item['platform'] ?? 'custom');
                if ($platform === 'custom' || ! config('social_platforms.'.$platform)) {
                    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
                    $host = preg_replace('/^www\./', '', $host);
                    $platform = match ($host) {
                        'ble.ir', 'bale.ai', 'web.bale.ai' => 'bale',
                        't.me', 'telegram.me', 'telegram.org' => 'telegram',
                        'instagram.com' => 'instagram',
                        'wa.me', 'whatsapp.com', 'api.whatsapp.com' => 'whatsapp',
                        'eitaa.com' => 'eitaa',
                        'rubika.ir' => 'rubika',
                        default => 'custom',
                    };
                }
                $label = config('social_platforms.'.$platform.'.title', 'وب‌سایت');
                $title = trim((string) ($item['title'] ?? '')) ?: $label;
                $items[] = compact('url', 'platform', 'label', 'title');
                $seen[$url] = true;
            }
        }

        return $items;
    }
}
