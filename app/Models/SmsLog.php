<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    use HasFactory;

    public const SEND_TYPES = ['single', 'selected', 'union_all', 'all'];
    public const STATUSES = ['pending', 'sent', 'failed', 'partial'];

    protected $fillable = [
        'sender_id',
        'union_id',
        'message',
        'recipients',
        'recipient_count',
        'send_type',
        'status',
        'provider_response',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'recipients' => 'array',
            'provider_response' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function union(): BelongsTo
    {
        return $this->belongsTo(GuildUnion::class, 'union_id');
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasRole('super-admin')) {
            return $query;
        }

        return $query->where(fn ($query) => $query
            ->where('sender_id', $user->id)
            ->orWhere('union_id', $user->union_id ?: 0));
    }

    public static function sendTypeLabels(): array
    {
        return [
            'single' => 'ارسال تکی',
            'selected' => 'اعضای انتخاب‌شده',
            'union_all' => 'همه اعضای یک اتحادیه',
            'all' => 'همه اعضای همه اتحادیه‌ها',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'pending' => 'در انتظار',
            'sent' => 'ارسال‌شده',
            'failed' => 'ناموفق',
            'partial' => 'ارسال ناقص',
        ];
    }

    public function getSendTypeLabelAttribute(): string
    {
        return self::sendTypeLabels()[$this->send_type] ?? $this->send_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
