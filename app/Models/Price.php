<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;

    public const TYPES = ['gold', 'coin', 'silver', 'currency', 'service', 'other'];

    protected $fillable = [
        'title',
        'type',
        'amount',
        'unit',
        'source',
        'published_at',
        'fetched_at',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:0',
            'published_at' => 'datetime',
            'fetched_at' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
