<?php

namespace App\Models;

use App\Support\PublicFileUrl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectronicService extends Model
{
    use HasFactory;

    public const LINK_TYPES = ['internal', 'external', 'none'];
    public const TARGETS = ['_self', '_blank'];
    public const STATUSES = ['draft', 'pending', 'approved', 'rejected', 'published', 'archived'];

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'body',
        'icon',
        'image',
        'link',
        'link_type',
        'target',
        'category_id',
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
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->tap(fn ($query) => \App\Support\Features::excludeComplaintRecords($query));
    }

    public static function linkTypeLabels(): array
    {
        return [
            'internal' => 'داخلی',
            'external' => 'خارجی',
            'none' => 'بدون لینک',
        ];
    }

    public function getLinkTypeLabelAttribute(): string
    {
        return self::linkTypeLabels()[$this->link_type] ?? $this->link_type;
    }

    public static function targetLabels(): array
    {
        return [
            '_self' => 'همان پنجره',
            '_blank' => 'پنجره جدید',
        ];
    }

    public function getTargetLabelAttribute(): string
    {
        return self::targetLabels()[$this->target] ?? $this->target;
    }

    public function getImageUrlAttribute(): string
    {
        return PublicFileUrl::make($this->image);
    }

    public function getPublicLinkAttribute(): ?string
    {
        $link = trim((string) $this->link);

        if ($this->link_type === 'none' || $link === '' || $link === '#') {
            return null;
        }

        if (str_starts_with($link, '/')) {
            return url($link);
        }

        if (! filter_var($link, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower((string) parse_url($link, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($link, PHP_URL_HOST));

        if (! in_array($scheme, ['http', 'https'], true)
            || $host === ''
            || in_array($host, ['example.com', 'www.example.com'], true)) {
            return null;
        }

        return $link;
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

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
