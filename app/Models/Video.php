<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    use HasFactory;

    public const VIDEO_TYPES = ['upload', 'aparat'];
    public const STATUSES = ['draft', 'pending', 'approved', 'rejected', 'published', 'archived'];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'video_type',
        'video_file',
        'aparat_url',
        'category_id',
        'union_id',
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
            'published_at' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function union(): BelongsTo
    {
        return $this->belongsTo(GuildUnion::class, 'union_id');
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
            'upload' => 'آپلود مستقیم',
            'aparat' => 'لینک آپارات',
        ];
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

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->video_type] ?? $this->video_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function getAparatEmbedUrlAttribute(): ?string
    {
        if (! $this->aparat_url) {
            return null;
        }

        if (preg_match('~/v/([A-Za-z0-9]+)~', $this->aparat_url, $matches)) {
            return 'https://www.aparat.com/video/video/embed/videohash/'.$matches[1].'/vt/frame';
        }

        if (preg_match('~/videohash/([A-Za-z0-9]+)~', $this->aparat_url, $matches)) {
            return 'https://www.aparat.com/video/video/embed/videohash/'.$matches[1].'/vt/frame';
        }

        return $this->aparat_url;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
