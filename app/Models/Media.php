<?php

namespace App\Models;

use App\Support\PublicFileUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Media extends Model
{
    protected $fillable = ['file_name', 'original_name', 'path', 'disk', 'mime_type', 'extension', 'size', 'width', 'height', 'alt_text', 'title', 'caption', 'description', 'uploaded_by', 'hash'];

    protected function casts(): array
    {
        return ['size' => 'integer', 'width' => 'integer', 'height' => 'integer'];
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeImages(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where('mime_type', 'like', 'image/%')
                ->orWhereIn('extension', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'svg']);
        });
    }

    public function postsAsFeatured()
    {
        return $this->hasMany(Post::class, 'featured_media_id');
    }

    public function postsInGallery()
    {
        return $this->belongsToMany(Post::class, 'news_media', 'media_id', 'news_id')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function getUrlAttribute(): string
    {
        if (Str::startsWith($this->path, ['http://', 'https://', '/', 'assets/'])) {
            return Str::startsWith($this->path, 'assets/') ? asset($this->path) : $this->path;
        }

        return PublicFileUrl::make($this->path);
    }

    public function getSrcsetAttribute(): ?string
    {
        return PublicFileUrl::srcset($this->path);
    }

    public function isExternalOrAsset(): bool
    {
        return Str::startsWith((string) $this->path, ['assets/', 'http://', 'https://', '/']);
    }

    public function inUse(): bool
    {
        if ($this->postsAsFeatured()->exists() || $this->postsInGallery()->exists()) {
            return true;
        }

        $path = PublicFileUrl::normalizeStoragePath((string) $this->path);
        if ($path === '') {
            return false;
        }

        $references = [
            [Post::class, 'featured_image'], [PostGallery::class, 'image'],
            [Gallery::class, 'cover_image'], [GalleryImage::class, 'image'],
            [GuildUnion::class, 'logo'], [GuildUnion::class, 'cover_image'],
            [GuildUnion::class, 'manager_image'], [GuildUnion::class, 'price_list_image'],
            [UnionMember::class, 'image'],
            [Page::class, 'featured_image'], [Announcement::class, 'featured_image'],
            [Video::class, 'cover_image'], [TourismPlace::class, 'featured_image'],
            [TourismPlace::class, 'image'], [System::class, 'image'],
            [Advertisement::class, 'image'], [ChamberMember::class, 'photo'],
            [CongratulationMessage::class, 'manager_image'],
            [ElectronicService::class, 'image'], [Commission::class, 'image'],
        ];

        // A conservative substring check intentionally accepts false positives:
        // preventing an unsafe deletion is preferable to deleting a shared
        // legacy URL whose stored representation differs by prefix/host.
        foreach ($references as [$model, $column]) {
            if ($model::query()->where($column, 'like', '%'.$path.'%')->exists()) {
                return true;
            }
        }

        $embeddedReferences = [
            [Post::class, ['body']],
            [Page::class, ['body']],
            [Announcement::class, ['body']],
            [ElectronicService::class, ['body']],
            [Commission::class, ['description', 'attachments']],
            [System::class, ['description']],
            [TourismPlace::class, ['description', 'gallery']],
            [GuildUnion::class, ['description', 'settings', 'president_buttons']],
            [HomeSection::class, ['content', 'settings']],
        ];

        foreach ($embeddedReferences as [$model, $columns]) {
            $used = $model::query()->where(function (Builder $query) use ($columns, $path) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', '%'.$path.'%');
                }
            })->exists();

            if ($used) {
                return true;
            }
        }

        return SiteSetting::query()->where('value', 'like', '%'.$path.'%')->exists();
    }
}
