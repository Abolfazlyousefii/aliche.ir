<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\LinkResolverService;

class MenuItem extends Model
{
    use HasFactory;

    public const TYPES = ['custom', 'home', 'posts_index', 'latest_posts', 'daily_news', 'important_news', 'featured_news', 'announcements_index', 'guilds_index', 'tourism_index', 'galleries_index', 'videos_index', 'systems_index', 'services_index', 'commissions_index', 'contact', 'complaints', 'complaints_track', 'page', 'post', 'announcement', 'union', 'tourism', 'gallery', 'video', 'system', 'service', 'commission'];
    public const TARGETS = ['_self', '_blank'];

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'type',
        'url',
        'route_name',
        'reference_type',
        'reference_id',
        'target',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'reference_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with('children');
    }

    public function adminChildren(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with('adminChildren');
    }

    public function getResolvedUrlAttribute(): string
    {
        $model = null;

        if ($this->reference_id) {
            $modelMap = [
                'page' => Page::class,
                'post' => Post::class,
                'post_category' => Category::class,
                'announcement' => Announcement::class,
                'union' => GuildUnion::class,
                'tourism' => TourismPlace::class,
                'gallery' => Gallery::class,
                'video' => Video::class,
                'system' => System::class,
                'service' => ElectronicService::class,
                'commission' => Commission::class,
            ];

            if (isset($modelMap[$this->type])) {
                $model = $modelMap[$this->type]::query()->find($this->reference_id);
            }
        }

        $resolver = app(LinkResolverService::class);

        if ($this->route_name) {
            $url = $resolver->safeRoute($this->route_name);

            return $url !== '#' ? $url : $resolver->resolve($this->route_name, $model, $this->url);
        }

        return $resolver->resolve($this->type, $model, $this->url);
    }
}
