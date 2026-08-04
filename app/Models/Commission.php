<?php

namespace App\Models;

use App\Support\PublicFileUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commission extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'pending', 'approved', 'rejected', 'published', 'archived'];

    protected $fillable = ['title', 'slug', 'description', 'image', 'members', 'attachments', 'status', 'published_at', 'created_by', 'approved_by', 'rejected_reason', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['members' => 'array', 'attachments' => 'array', 'published_at' => 'datetime', 'sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CommissionSession::class)->orderByDesc('session_date')->orderBy('sort_order');
    }

    public function publishedSessions(): HasMany
    {
        return $this->sessions()->published();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(CommissionTask::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeTasks(): HasMany
    {
        return $this->tasks()->active();
    }

    public function scopePublished($query)
    {
        return $query
            ->where('status', 'published')
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
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

    public function getImageUrlAttribute(): string
    {
        return PublicFileUrl::make($this->image);
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
