<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    use HasFactory;

    public const DEFAULT_KEYS = [
        'hero_slider',
        'quick_menu',
        'electronic_services',
        'important_news',
        'daily_news',
        'announcements',
        'unions',
        'advertisements',
        'tourism',
        'commissions',
        'systems',
        'congratulation_messages',
        'videos',
        'galleries',
        'contact',
        'chamber_members',
    ];

    protected $fillable = [
        'key',
        'title',
        'subtitle',
        'content',
        'settings',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function keyLabels(): array
    {
        return [
            'hero_slider' => 'اسلایدر اصلی',
            'quick_menu' => 'دسترسی سریع',
            'electronic_services' => 'خدمات الکترونیک',
            'important_news' => 'خبرهای مهم',
            'daily_news' => 'اخبار روزانه',
            'announcements' => 'اطلاعیه‌ها',
            'unions' => 'اتحادیه‌ها',
            'advertisements' => 'تبلیغات',
            'tourism' => 'گردشگری',
            'commissions' => 'کمیسیون‌ها',
            'systems' => 'سامانه‌ها',
            'congratulation_messages' => 'پیام‌های تبریک',
            'videos' => 'ویدیوها',
            'galleries' => 'گالری تصاویر',
            'contact' => 'ارتباط با ما',
            'chamber_members' => 'اعضای اتاق اصناف',
        ];
    }

    public function getKeyLabelAttribute(): string
    {
        return self::keyLabels()[$this->key] ?? $this->key;
    }
}
