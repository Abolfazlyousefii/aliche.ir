<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnionPrice extends Model
{
    use HasFactory;

    protected $fillable = ['union_id', 'title', 'price', 'currency', 'type', 'updated_on', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'updated_on' => 'date', 'sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function union(): BelongsTo
    {
        return $this->belongsTo(GuildUnion::class, 'union_id');
    }
}
