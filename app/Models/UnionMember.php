<?php

namespace App\Models;

use App\Support\PublicFileUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnionMember extends Model
{
    use HasFactory;

    public const STATUSES = ['active', 'inactive', 'suspended', 'expired'];

    protected $fillable = [
        'union_id',
        'full_name',
        'position',
        'image',
        'national_code',
        'mobile',
        'phone',
        'membership_code',
        'business_name',
        'business_license_number',
        'address',
        'status',
        'description',
        'attachments',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function union(): BelongsTo
    {
        return $this->belongsTo(GuildUnion::class, 'union_id');
    }

    public function getImageUrlAttribute(): string
    {
        return PublicFileUrl::make($this->image);
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasRole('super-admin')) {
            return $query;
        }

        return $query->where('union_id', $user->union_id ?: 0);
    }
}
