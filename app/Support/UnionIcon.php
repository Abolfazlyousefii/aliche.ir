<?php

namespace App\Support;

class UnionIcon
{
    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            'link' => 'لینک',
            'phone' => 'تلفن',
            'mobile' => 'موبایل',
            'email' => 'ایمیل',
            'website' => 'وب‌سایت',
            'external' => 'لینک خارجی',
            'document' => 'سند',
            'rules' => 'قوانین',
            'message' => 'پیام',
            'education' => 'آموزش',
            'commission' => 'کمیسیون',
            'news' => 'خبر',
            'announcement' => 'اطلاعیه',
            'minutes' => 'صورت‌جلسه',
            'prices' => 'نرخ‌نامه',
            'gallery' => 'گالری',
            'shield' => 'ایمنی / نظارت',
        ];
    }

    public static function resolve(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $normalized = strtolower($value);
        $aliases = [
            'briefcase' => 'commission',
            'globe' => 'website',
            '📋' => 'document',
            '⚖️' => 'rules',
            '⚖' => 'rules',
            '💰' => 'prices',
            '📚' => 'education',
            '🛡️' => 'shield',
            '🛡' => 'shield',
            '📰' => 'news',
            '📢' => 'announcement',
            '📅' => 'minutes',
            '🖼️' => 'gallery',
            '🖼' => 'gallery',
        ];

        $normalized = $aliases[$normalized] ?? $normalized;

        return array_key_exists($normalized, self::options()) ? $normalized : null;
    }

    public static function normalize(?string $value, string $fallback = 'link'): string
    {
        return self::resolve($value) ?? (self::resolve($fallback) ?: 'link');
    }
}
