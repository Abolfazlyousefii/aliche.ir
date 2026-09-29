<?php

namespace App\Support;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Features
{
    /** Titles like «ثبت شکایت»، «پیگیری شکایت»، «نحوه ثبت شکایت صنفی». News headlines that merely contain the word are not matched. */
    private const COMPLAINT_TITLE_PATTERN = '/(ثبت|پیگیری|رسیدگی[\s\x{200C}]*به)[\s\x{200C}]*(ی[\s\x{200C}]*)?(شکایت|شکایات)/u';

    /** @var array<int, string> SQL LIKE patterns matching the title pattern above. */
    private const COMPLAINT_TITLE_LIKES = ['%ثبت شکایت%', '%ثبت‌شکایت%', '%پیگیری شکایت%', '%پیگیری شکایات%', '%رسیدگی به شکایت%'];

    public static function complaintsEnabled(): bool
    {
        return (bool) config('features.complaints_enabled', false);
    }

    public static function isComplaintTitle(?string $title): bool
    {
        return preg_match(self::COMPLAINT_TITLE_PATTERN, (string) $title) === 1;
    }

    public static function isComplaintUrl(?string $url): bool
    {
        $path = (string) parse_url((string) $url, PHP_URL_PATH);

        return $path !== '' && preg_match('#(^|/)complaints?(/|$)#i', $path) === 1;
    }

    /**
     * Whether a menu item points to (or is titled as) a public complaint page.
     */
    public static function isComplaintMenuItem(MenuItem $item): bool
    {
        return in_array((string) $item->type, ['complaints', 'complaints_track'], true)
            || str_starts_with((string) $item->route_name, 'complaints.')
            || self::isComplaintUrl($item->url)
            || self::isComplaintTitle($item->title);
    }

    /**
     * Hide complaint-related records (electronic services, systems) from public queries.
     */
    public static function excludeComplaintRecords(Builder $query, string $linkColumn = 'link'): Builder
    {
        if (self::complaintsEnabled()) {
            return $query;
        }

        $table = $query->getModel()->getTable();

        return $query->where(function (Builder $query) use ($table, $linkColumn) {
            foreach (self::COMPLAINT_TITLE_LIKES as $like) {
                $query->where("{$table}.title", 'not like', $like);
            }

            $query->where("{$table}.slug", 'not like', '%complaint%')
                ->where(fn (Builder $q) => $q->whereNull("{$table}.{$linkColumn}")
                    ->orWhere("{$table}.{$linkColumn}", 'not like', '%/complaint%'));
        });
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
