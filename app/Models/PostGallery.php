<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'image',
        'caption',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }


    public function getImageUrlAttribute(): string
    {
        $image = $this->image;

        if (! $image) {
            return asset('assets/img/asnaf-gorgan-default.jpg');
        }

        if (Str::startsWith($image, ['http://', 'https://', '/'])) {
            return $image;
        }

        if (Str::startsWith($image, ['assets/'])) {
            return asset($image);
        }

        return Storage::url($image);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
