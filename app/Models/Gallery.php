<?php

namespace App\Models;

use App\Support\PublicFileUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gallery extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'pending', 'approved', 'rejected', 'published', 'archived'];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'category_id',
        'union_id',
        'display_location',
        'status',
        'published_at',
        'created_by',
        'approved_by',
        'rejected_reason',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }


    public function getDisplayCoverPathAttribute(): ?string
    {
        $candidates = collect([$this->cover_image]);

        if ($this->relationLoaded('images')) {
            $candidates = $candidates->concat($this->images->pluck('image'));
        }

        return $candidates
            ->filter(fn ($path) => filled($path))
            ->first(fn ($path) => $this->isUsablePublicImagePath((string) $path));
    }

    private function isUsablePublicImagePath(string $path): bool
    {
        $path = trim($path);

        if ($path === '') {
            return false;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $localPath = PublicFileUrl::sameApplicationStoragePath($path);

            return $localPath === null || PublicFileUrl::exists($localPath);
        }

        if (str_starts_with($path, '/')
            && ! str_starts_with($path, '/storage/')
            && ! str_starts_with($path, '/media/')
            && ! str_starts_with($path, '/media-files/')
            && ! str_starts_with($path, '/uploaded-media/')) {
            return true;
        }

        $normalized = PublicFileUrl::normalizeStoragePath($path);
        $publicFile = public_path(str_replace('/', DIRECTORY_SEPARATOR, $normalized));

        return is_file($publicFile) || PublicFileUrl::exists($normalized);
    }

    public function getFeaturedImageAttribute(): ?string
    {
        return $this->display_cover_path;
    }

    public function getCoverImageUrlAttribute(): string
    {
        return PublicFileUrl::make($this->display_cover_path);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function union(): BelongsTo
    {
        return $this->belongsTo(GuildUnion::class, 'union_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class)->orderBy('sort_order')->orderBy('id');
    }


    public static function displayLocationLabels(): array
    {
        return [
            'home' => 'فقط صفحه اصلی',
            'union' => 'فقط صفحه اتحادیه',
            'both' => 'صفحه اصلی و اتحادیه',
        ];
    }

    public function scopeForHome($query)
    {
        return $query->whereIn('display_location', ['home', 'both']);
    }

    public function scopeForUnion($query)
    {
        return $query->whereIn('display_location', ['union', 'both']);
    }

    public function getDisplayLocationLabelAttribute(): string
    {
        return self::displayLocationLabels()[$this->display_location] ?? $this->display_location;
    }

    public function scopePublished($query)
    {
        return $query
            ->where('status', 'published')
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public static function statusLabels(): array
    {
        return [
            'draft' => 'پیش‌نویس',
            'pending' => 'در انتظار بررسی',
            'approved' => 'تایید شده',
            'rejected' => 'رد شده',
            'published' => 'منتشر شده',
            'archived' => 'آرشیو شده',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
