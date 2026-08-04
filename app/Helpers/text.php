<?php

use Illuminate\Support\Str;

if (! function_exists('plain_text')) {
    function plain_text(mixed $value, ?int $limit = null): string
    {
        $text = trim(strip_tags(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        return $limit ? Str::limit($text, $limit) : $text;
    }
}


if (! function_exists('rich_text')) {
    function rich_text(mixed $value, ?string $fallback = null): string
    {
        $html = trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($html === '') {
            return $fallback ?? '';
        }

        return $html;
    }
}
