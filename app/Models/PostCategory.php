<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
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

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'category_id');
    }

    public function tourismPlaces(): HasMany
    {
        return $this->hasMany(TourismPlace::class, 'category_id');
    }

    public function systems(): HasMany
    {
        return $this->hasMany(System::class, 'category_id');
    }

    public function electronicServices(): HasMany
    {
        return $this->hasMany(ElectronicService::class, 'category_id');
    }
}
