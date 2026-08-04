<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'pending', 'approved', 'rejected', 'published', 'archived'];
    public const LIMITED_STATUSES = ['draft', 'pending'];

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image',
        'attachment',
        'category_id',
        'union_id',
        'starts_at',
        'expires_at',
        'is_important',
        'show_on_home',
        'status',
        'visibility',
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
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_important' => 'boolean',
            'show_on_home' => 'boolean',
            'published_at' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AnnouncementCategory::class, 'category_id');
    }

    public function union(): BelongsTo
    {
        return $this->belongsTo(GuildUnion::class, 'union_id');
    }

    public function author(): BelongsTo
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
            ->where('visibility', 'public')
            ->where('starts_at', '<=', now())
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }


    public function scopePrivateVisibleTo($query, User $user)
    {
        return $query
            ->where('visibility', 'private')
            ->where('status', 'published')
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->where(fn ($query) => $query->whereNull('union_id')->orWhere('union_id', $user->union_id ?: 0));
    }

    public static function visibilityLabels(): array
    {
        return [
            'public' => 'عمومی',
            'private' => 'خصوصی',
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

    public function getVisibilityLabelAttribute(): string
    {
        return self::visibilityLabels()[$this->visibility] ?? $this->visibility;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    public function scopeShownOnHome($query)
    {
        return $query->where('show_on_home', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
