<?php

namespace App\Support;

use App\Models\User;

final class AdminCategoryAccess
{
    /**
     * Categories belong to their owning module. Reuse already-deployed
     * permissions rather than introducing a new production seed requirement.
     *
     * @var array<string, string>
     */
    private const MODULES = [
        'news' => 'posts',
        'tourism' => 'tourism',
        'gallery' => 'galleries',
        'video' => 'videos',
        'service' => 'electronic_services',
        'system' => 'systems',
        'union' => 'unions',
    ];

    public static function can(?User $user, string $type, string $action = 'view'): bool
    {
        $module = self::MODULES[$type] ?? null;

        return $module !== null && $user?->hasPermission($module.'.'.$action) === true;
    }

    /** @return list<string> */
    public static function allowedTypes(?User $user, string $action = 'view'): array
    {
        return array_values(array_filter(
            array_keys(self::MODULES),
            fn (string $type): bool => self::can($user, $type, $action)
        ));
    }
}
