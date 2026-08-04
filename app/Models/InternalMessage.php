<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalMessage extends Model
{
    use HasFactory;

    public const TYPE_DIRECT = 'direct';
    public const TYPE_ROLE = 'role';
    public const TYPE_UNION = 'union';
    public const TYPE_BROADCAST = 'broadcast';
    public const TYPE_REPLY = 'reply';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_IMPORTANT = 'important';
    public const PRIORITY_URGENT = 'urgent';

    /** @var array<int, string> */
    public const TYPES = [
        self::TYPE_DIRECT,
        self::TYPE_ROLE,
        self::TYPE_UNION,
        self::TYPE_BROADCAST,
        self::TYPE_REPLY,
    ];

    /** @var array<int, string> */
    public const PRIORITIES = [
        self::PRIORITY_LOW,
        self::PRIORITY_NORMAL,
        self::PRIORITY_IMPORTANT,
        self::PRIORITY_URGENT,
    ];

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'subject',
        'body',
        'type',
        'priority',
        'allow_reply',
        'read_at',
        'sent_at',
        'parent_id',
        'meta',
    ];

    protected $casts = [
        'allow_reply' => 'boolean',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
        'meta' => 'array',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->latest();
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function priorityLabel(): string
    {
        return [
            self::PRIORITY_LOW => 'کم',
            self::PRIORITY_NORMAL => 'عادی',
            self::PRIORITY_IMPORTANT => 'مهم',
            self::PRIORITY_URGENT => 'فوری',
        ][$this->priority] ?? $this->priority;
    }

    public function typeLabel(): string
    {
        return [
            self::TYPE_DIRECT => 'مستقیم',
            self::TYPE_ROLE => 'نقش',
            self::TYPE_UNION => 'اتحادیه',
            self::TYPE_BROADCAST => 'عمومی',
            self::TYPE_REPLY => 'پاسخ',
        ][$this->type] ?? $this->type;
    }
}
