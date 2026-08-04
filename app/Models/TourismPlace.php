<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\PublicFileUrl;
use Illuminate\Support\Str;

class TourismPlace extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'pending', 'approved', 'rejected', 'published', 'archived'];
    public const TYPES = ['nature', 'historic', 'shopping', 'accommodation', 'restaurant', 'other'];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'featured_image',
        'gallery',
        'category_id',
        'tourism_type',
        'badge',
        'image',
        'location',
        'type',
        'address',
        'map_url',
        'latitude',
        'longitude',
        'phone',
        'working_hours',
        'visit_price',
        'status',
        'published_at',
        'created_by',
        'approved_by',
        'rejected_reason',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'published_at' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }


    public function getHomeImageUrlAttribute(): string
    {
        $image = $this->image ?: $this->featured_image;

        return $this->resolveImageUrl($image);
    }

    public function getHomeDescriptionAttribute(): string
    {
        return plain_text($this->short_description ?: $this->description);
    }

    public function getHomeBadgeAttribute(): string
    {
        return $this->badge ?: ($this->category?->title ?: 'گردشگری');
    }

    public function getHomeLocationAttribute(): string
    {
        return $this->location ?: (string) $this->address;
    }


    public function getGalleryItemsAttribute(): array
    {
        return collect($this->gallery ?? [])->map(function ($image) {
            $path = is_array($image) ? ($image['path'] ?? $image['image'] ?? null) : $image;
            $caption = is_array($image) ? ($image['caption'] ?? $this->title) : $this->title;

            return [
                'url' => $this->resolveImageUrl($path),
                'caption' => $caption ?: $this->title,
            ];
        })->values()->all();
    }

    private function resolveImageUrl(?string $image): string
    {
        if (! $image) {
            return asset('assets/img/asnaf-gorgan-default.jpg');
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return PublicFileUrl::make($image);
        }

        if (Str::startsWith($image, ['/storage/', '/media-files/', '/uploaded-media/'])) {
            return PublicFileUrl::make($image);
        }

        if (Str::startsWith($image, ['/'])) {
            return $image;
        }

        if (Str::startsWith($image, ['assets/'])) {
            return asset($image);
        }

        return PublicFileUrl::make($image);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePublished($query)
    {
        return $query
            ->where('status', 'published')
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }


    public static function typeLabels(): array
    {
        return [
            'nature' => 'طبیعی',
            'historic' => 'تاریخی',
            'shopping' => 'مراکز خرید',
            'accommodation' => 'اقامت',
            'restaurant' => 'رستوران',
            'other' => 'سایر',
        ];
    }

    public function getTourismTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->tourism_type] ?? $this->tourism_type;
    }

    public static function statusLabels(): array
    {
        return [
            'draft' => 'پیش‌نویس',
            'pending' => 'در انتظار بررسی',
            'approved' => 'تایید شده',
            'rejected' => 'رد شده',
            'published' => 'منتشر شده',
            'archived' => 'آرشیو شده',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
