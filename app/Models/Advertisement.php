<?php

namespace App\Models;

use App\Support\PublicFileUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Advertisement extends Model
{
    use HasFactory;

    public const TARGETS = ['_self', '_blank'];

    protected $fillable = [
        'position_id',
        'title',
        'image',
        'link',
        'target',
        'starts_at',
        'expires_at',
        'clicks_count',
        'views_count',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'clicks_count' => 'integer',
            'views_count' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(AdvertisementPosition::class, 'position_id');
    }

    public function scopeDisplayable($query)
    {
        return $query
            ->where('advertisements.is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->whereHas('position', fn ($query) => $query->where('is_active', true));
    }

    public static function targetLabels(): array
    {
        return [
            '_self' => 'همان پنجره',
            '_blank' => 'پنجره جدید',
        ];
    }

    public function getImageUrlAttribute(): string
    {
        return PublicFileUrl::make($this->image);
    }

    public function getTargetLabelAttribute(): string
    {
        return self::targetLabels()[$this->target] ?? $this->target;
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) {
            return 'غیرفعال';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'زمان‌بندی شده';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'منقضی شده';
        }

        return 'در حال نمایش';
    }
}
