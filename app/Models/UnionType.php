<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\PublicFileUrl;

class UnionType extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'icon', 'image', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function unions(): HasMany
    {
        return $this->hasMany(GuildUnion::class, 'union_type_id');
    }

    public function getImageUrlAttribute(): string
    {
        return PublicFileUrl::make($this->image);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
