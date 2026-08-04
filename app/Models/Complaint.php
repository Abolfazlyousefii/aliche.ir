<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    use HasFactory;

    public const STATUSES = [
        'registered',
        'reviewing',
        'need_more_info',
        'answered',
        'closed',
        'rejected',
    ];

    protected $fillable = [
        'tracking_code',
        'union_id',
        'full_name',
        'national_code',
        'mobile',
        'subject',
        'body',
        'attachment',
        'status',
        'admin_response',
        'internal_note',
        'answered_by',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
        ];
    }

    public function union(): BelongsTo
    {
        return $this->belongsTo(GuildUnion::class, 'union_id');
    }

    public function answerer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasRole('super-admin')) {
            return $query;
        }

        return $query->where('union_id', $user->union_id ?: 0);
    }

    public static function statusLabels(): array
    {
        return [
            'registered' => 'ثبت‌شده',
            'reviewing' => 'در حال بررسی',
            'need_more_info' => 'نیازمند اطلاعات بیشتر',
            'answered' => 'پاسخ داده‌شده',
            'closed' => 'بسته‌شده',
            'rejected' => 'ردشده',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
