<?php

namespace App\Support;

final class MediaKeywords
{
    /**
     * Parse the same comma/Persian comma/newline format used by news keywords.
     *
     * @return array<int, string>
     */
    public static function tags(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return collect(preg_split('/[,،\r\n]+/u', $value) ?: [])
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function normalize(?string $value): ?string
    {
        $tags = self::tags($value);

        return $tags === [] ? null : implode('، ', $tags);
    }
}
