<?php

namespace App\Models;

use App\Support\PublicFileUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Media extends Model
{
    protected $fillable = ['file_name','original_name','path','disk','mime_type','extension','size','width','height','alt_text','title','caption','description','uploaded_by','hash'];

    protected function casts(): array { return ['size'=>'integer','width'=>'integer','height'=>'integer']; }

    public function uploader(){ return $this->belongsTo(User::class, 'uploaded_by'); }

    public function scopeImages(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where('mime_type', 'like', 'image/%')
                ->orWhereIn('extension', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
        });
    }
    public function postsAsFeatured(){ return $this->hasMany(Post::class, 'featured_media_id'); }
    public function postsInGallery(){ return $this->belongsToMany(Post::class, 'news_media')->withPivot('sort_order')->withTimestamps(); }
    public function getUrlAttribute(): string
    {
        if (Str::startsWith($this->path, ['http://','https://','/','assets/'])) return Str::startsWith($this->path, 'assets/') ? asset($this->path) : $this->path;
        return PublicFileUrl::make($this->path);
    }
    public function inUse(): bool { return $this->postsAsFeatured()->exists() || $this->postsInGallery()->exists(); }
}
