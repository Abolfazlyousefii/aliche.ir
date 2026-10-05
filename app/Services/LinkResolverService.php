<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Throwable;

class LinkResolverService
{
    /**
     * Resolve a frontend link by menu/content type.
     */
    public function resolve(?string $type, ?Model $model = null, ?string $url = null): string
    {
        $type = (string) $type;

        if ($type === 'custom') {
            return $this->normalizeCustomUrl($url);
        }

        $archiveRoutes = [
            'home' => 'home',
            'posts_index' => 'posts.index',
            'latest_posts' => 'posts.index',
            'daily_news' => 'posts.index',
            'important_news' => 'posts.index',
            'featured_news' => 'posts.index',
            'announcements_index' => 'announcements.index',
            'guilds_index' => 'guilds.index',
            'tourism_index' => 'tourism.index',
            'galleries_index' => 'galleries.index',
            'videos_index' => 'videos.index',
            'systems_index' => 'systems.index',
            'services_index' => 'electronic-services.index',
            'electronic_services_index' => 'electronic-services.index',
            'commissions_index' => 'commissions.index',
            'contact' => 'contact.create',
            'complaints' => 'complaints.create',
            'complaints_track' => 'complaints.track',
            'search' => 'search',
        ];

        if (isset($archiveRoutes[$type])) {
            return $this->safeRoute($archiveRoutes[$type]);
        }

        $detailRoutes = [
            'page' => 'pages.show',
            'post' => 'posts.show',
            'post_category' => 'posts.index',
            'union' => 'guilds.show',
            'tourism' => 'tourism.show',
            'gallery' => 'galleries.show',
            'video' => 'videos.show',
            'system' => 'systems.show',
            'service' => 'electronic-services.show',
            'electronic_service' => 'electronic-services.show',
            'commission' => 'commissions.show',
            'announcement' => 'announcements.show',
        ];

        if (isset($detailRoutes[$type]) && $model) {
            return $this->safeRoute($detailRoutes[$type], $model->getRouteKey());
        }

        return $this->normalizeCustomUrl($url);
    }

    private function normalizeCustomUrl(?string $url): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return '#';
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return '#';
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (in_array($scheme, ['http', 'https'], true) && in_array($host, [
            'aliche.ir',
            'www.aliche.ir',
            'localhost',
            '127.0.0.1',
        ], true)) {
            $path = '/'.ltrim((string) ($parts['path'] ?? '/'), '/');
            $resolved = url($path);

            if (filled($parts['query'] ?? null)) {
                $resolved .= '?'.$parts['query'];
            }

            if (filled($parts['fragment'] ?? null)) {
                $resolved .= '#'.$parts['fragment'];
            }

            return $resolved;
        }

        return $url;
    }

    public function safeRoute(string $name, mixed $parameters = []): string
    {
        try {
            if (! Route::has($name)) {
                return '#';
            }

            return route($name, $parameters);
        } catch (Throwable) {
            return '#';
        }
    }
}
