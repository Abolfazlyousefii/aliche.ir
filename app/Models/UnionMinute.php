<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnionMinute extends Model
{
    use HasFactory;

    protected $fillable = ['union_id', 'title', 'meeting_date', 'file', 'description', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['meeting_date' => 'date', 'sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function union(): BelongsTo
    {
        return $this->belongsTo(GuildUnion::class, 'union_id');
    }
}
