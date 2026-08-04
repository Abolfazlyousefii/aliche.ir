<?php

namespace App\Models;

use App\Support\PublicFileUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    public const TYPES = ['news', 'article', 'announcement', 'video'];
    public const STATUSES = ['draft', 'pending', 'approved', 'rejected', 'published', 'archived'];
    public const HOMEPAGE_POSITIONS = ['normal', 'top', 'featured'];
    public const LIMITED_STATUSES = ['draft', 'pending'];

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image',
        'featured_media_id',
        'category_id',
        'union_id',
        'type',
        'homepage_position',
        'is_important',
        'is_featured',
        'featured_order',
        'is_top',
        'views_count',
        'status',
        'published_at',
        'created_by',
        'approved_by',
        'rejected_reason',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'homepage_position' => 'string',
            'is_important' => 'boolean',
            'is_featured' => 'boolean',
            'featured_order' => 'integer',
            'is_top' => 'boolean',
            'views_count' => 'integer',
            'published_at' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }


    public function getShortDescriptionAttribute(): ?string
    {
        return $this->excerpt;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->body;
    }

    public function getSummaryAttribute(): string
    {
        return plain_text($this->excerpt ?: $this->body, 120);
    }

    public function getCategoryTitleAttribute(): string
    {
        return $this->category?->title ?: 'عمومی';
    }

    public function getFeaturedImageUrlAttribute(): string
    {
        $image = $this->featuredMedia?->url ?: $this->featured_image;

        return PublicFileUrl::make($image);
    }

    public function getHasGalleryBadgeAttribute(): bool
    {
        return $this->relationLoaded('galleries') ? $this->galleries->isNotEmpty() : $this->galleries()->exists();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public static function typeLabels(): array
    {
        return [
            'news' => 'خبر',
            'article' => 'مقاله',
            'announcement' => 'اطلاعیه',
            'video' => 'ویدیو',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'draft' => 'پیش‌نویس',
            'pending' => 'در انتظار تایید',
            'approved' => 'تایید شده',
            'rejected' => 'رد شده',
            'published' => 'منتشر شده',
            'archived' => 'آرشیو شده',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function union(): BelongsTo
    {
        return $this->belongsTo(GuildUnion::class, 'union_id');
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(PostGallery::class)->orderBy('sort_order')->orderBy('id');
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function mediaGallery()
    {
        return $this->belongsToMany(Media::class, 'news_media', 'news_id', 'media_id')->withPivot('sort_order')->withTimestamps()->orderBy('news_media.sort_order');
    }

    public function slugHistories()
    {
        return $this->morphMany(SlugHistory::class, 'sluggable');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }


    public function canBeApproved(): bool
    {
        return in_array($this->status, ['draft', 'pending', 'rejected'], true) && $this->is_active;
    }

    public function canBePublished(): bool
    {
        return in_array($this->status, ['pending', 'approved', 'draft'], true) && $this->is_active;
    }

    public function canBeRejected(): bool
    {
        return in_array($this->status, ['draft', 'pending', 'approved'], true);
    }

    public function canBeUnpublished(): bool
    {
        return $this->status === 'published';
    }

    public function scopePublished($query)
    {
        return $query
            ->where('status', 'published')
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopePublishedOn($query, \Carbon\Carbon $date)
    {
        return $query->whereBetween('published_at', [
            $date->copy()->startOfDay(),
            $date->copy()->endOfDay(),
        ]);
    }

    public static function homepagePositionLabels(): array
    {
        return [
            'normal' => 'خبر عادی',
            'top' => 'خبر تاپ؛ نمایش در اسلایدر',
            'featured' => 'خبر ویژه؛ نمایش کنار اسلایدر',
        ];
    }

    public function getHomepagePositionLabelAttribute(): string
    {
        return self::homepagePositionLabels()[$this->homepage_position ?: 'normal'] ?? 'خبر عادی';
    }

    public function scopeImportant($query)
    {
        return $query->where('homepage_position', 'featured');
    }

    public function scopeTop($query)
    {
        return $query->where('homepage_position', 'top');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
