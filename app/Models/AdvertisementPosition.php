<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdvertisementPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'key',
        'description',
        'width',
        'height',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class, 'position_id')->orderBy('sort_order')->orderByDesc('starts_at');
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }
}
