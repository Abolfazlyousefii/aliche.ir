<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'pending', 'approved', 'rejected', 'published', 'archived'];
    public const LIMITED_STATUSES = ['draft', 'pending'];
    public const TEMPLATES = ['default', 'blank', 'landing'];


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

    public static function templateLabels(): array
    {
        return [
            'default' => 'قالب پیش‌فرض',
            'blank' => 'صفحه ساده',
            'landing' => 'صفحه فرود',
        ];
    }

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image',
        'template',
        'meta_title',
        'meta_description',
        'meta_keywords',
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
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
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
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
