<?php

namespace App\Support;

use App\Models\MenuItem;
use Illuminate\Support\Collection;

class Features
{
    public static function complaintsEnabled(): bool
    {
        return (bool) config('features.complaints_enabled', false);
    }

    /**
     * Whether a menu item points to a public complaint page.
     */
    public static function isComplaintMenuItem(MenuItem $item): bool
    {
        if (in_array((string) $item->type, ['complaints', 'complaints_track'], true)) {
            return true;
        }

        if (str_starts_with((string) $item->route_name, 'complaints.')) {
            return true;
        }

        $path = (string) parse_url((string) $item->url, PHP_URL_PATH);

        return $path !== '' && preg_match('#(^|/)complaints(/|$)#i', $path) === 1;
    }

    /**
     * Remove complaint links (recursively) from a menu tree when complaints are disabled.
     *
     * @param  Collection<int, MenuItem>  $items
     * @return Collection<int, MenuItem>
     */
    public static function filterMenuItems(Collection $items): Collection
    {
        if (self::complaintsEnabled()) {
            return $items;
        }

        return $items
            ->reject(fn (MenuItem $item) => self::isComplaintMenuItem($item))
            ->filter(function (MenuItem $item) {
                $original = $item->children;
                $children = self::filterMenuItems($original);
                $item->setRelation('children', $children);

                // Drop a dropdown parent whose only children were complaint links.
                return ! ($original->isNotEmpty() && $children->isEmpty());
            })
            ->values();
    }
}
