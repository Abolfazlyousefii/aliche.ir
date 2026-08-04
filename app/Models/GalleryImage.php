<?php

namespace App\Models;

use App\Support\PublicFileUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'gallery_id',
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


    public function getPathAttribute(): ?string
    {
        return $this->image;
    }

    public function getImageUrlAttribute(): string
    {
        $image = $this->image;

        if (! $image) {
            return asset('assets/img/asnaf-gorgan-default.jpg');
        }

        return PublicFileUrl::make($image);
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }
}
