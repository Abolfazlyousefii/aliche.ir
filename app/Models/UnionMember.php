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

    public function isSeedPlaceholderProfile(): bool
    {
        $name = trim((string) $this->full_name);
        $membershipCode = trim((string) $this->membership_code);
        $businessName = trim((string) $this->business_name);
        $description = trim((string) $this->description);

        return blank($this->position)
            && blank($this->image)
            && preg_match('/^عضو صنفی\s+\d+\s+/u', $name) === 1
            && $membershipCode !== ''
            && str_starts_with($membershipCode, 'G'.(int) $this->union_id.'-')
            && str_starts_with($businessName, 'واحد صنفی ')
            && $description === 'عضو فعال برای نمایش در صفحه اتحادیه';
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasRole('super-admin')) {
            return $query;
        }

        return $query->where('union_id', $user->union_id ?: 0);
    }
}
