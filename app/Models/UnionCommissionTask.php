<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnionCommissionTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'union_commission_id',
        'title',
        'description',
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

    public function commission(): BelongsTo
    {
        return $this->belongsTo(UnionCommission::class, 'union_commission_id');
    }
}
