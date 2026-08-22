<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\PublicFileUrl;

class UnionType extends Model
{
    use HasFactory;

    public const ICON_FACTORY = 'factory';
    public const ICON_CART = 'cart';
    public const ICON_BRIEFCASE = 'briefcase';
    public const ICON_TARGET = 'target';
    public const ICON_STOREFRONT = 'storefront';
    public const ICON_TOOLS = 'tools';
    public const ICON_FOOD = 'food';
    public const ICON_TRANSPORT = 'transport';

    protected $fillable = ['title', 'slug', 'icon', 'image', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public static function iconOptions(): array
    {
        return [
            self::ICON_FACTORY => 'تولید و صنعت',
            self::ICON_CART => 'توزیع و فروش',
            self::ICON_BRIEFCASE => 'خدمات',
            self::ICON_TARGET => 'تخصصی',
            self::ICON_STOREFRONT => 'فروشگاه و بازار',
            self::ICON_TOOLS => 'فنی و تعمیرات',
            self::ICON_FOOD => 'مواد غذایی و پذیرایی',
            self::ICON_TRANSPORT => 'حمل‌ونقل',
        ];
    }

    public static function defaultIconForSlug(?string $slug): string
    {
        return match (trim((string) $slug)) {
            'production' => self::ICON_FACTORY,
            'distribution' => self::ICON_CART,
            'service' => self::ICON_BRIEFCASE,
            'specialized' => self::ICON_TARGET,
            default => self::ICON_STOREFRONT,
        };
    }

    public function getResolvedIconAttribute(): string
    {
        $icon = trim((string) $this->icon);

        return array_key_exists($icon, self::iconOptions())
            ? $icon
            : self::defaultIconForSlug($this->slug);
    }

    public function getIconLabelAttribute(): string
    {
        return self::iconOptions()[$this->resolved_icon] ?? 'آیکون اتحادیه';
    }

    public function unions(): HasMany
    {
        return $this->hasMany(GuildUnion::class, 'union_type_id');
    }

    public function getImageUrlAttribute(): string
    {
        return PublicFileUrl::make($this->image);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
