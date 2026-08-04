<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnionCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'union_id',
        'title',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function union(): BelongsTo
    {
        return $this->belongsTo(GuildUnion::class, 'union_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(UnionCommissionTask::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeTasks(): HasMany
    {
        return $this->tasks()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
