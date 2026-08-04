<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CongratulationMessage extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'pending', 'approved', 'rejected', 'published', 'archived'];

    protected $fillable = [
        'title',
        'slug',
        'body',
        'manager_name',
        'manager_position',
        'manager_image',
        'union_id',
        'message_type',
        'recipient_type',
        'recipient_id',
        'recipient_name',
        'recipient_mobile',
        'sms_log_id',
        'show_on_home',
        'show_on_union_page',
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
            'show_on_home' => 'boolean',
            'show_on_union_page' => 'boolean',
            'published_at' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
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

    public function scopeForHome($query)
    {
        return $query->published()->where('show_on_home', true);
    }

    public function scopeForUnionPage($query)
    {
        return $query->published()->where('show_on_union_page', true);
    }


    public static function messageTypeLabels(): array
    {
        return [
            'congratulation' => 'تبریک',
            'condolence' => 'تسلیت',
        ];
    }

    public function getMessageTypeLabelAttribute(): string
    {
        return self::messageTypeLabels()[$this->message_type] ?? $this->message_type;
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
