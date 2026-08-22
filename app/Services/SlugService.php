<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class SlugService
{
    public function make(?string $value, string $fallback = 'content'): string
    {
        $slug = trim((string) $value);
        $slug = preg_replace('/[\p{Pd}_\s\x{200C}]+/u', '-', $slug) ?: '';
        $slug = preg_replace('/[^\p{Arabic}\p{L}\p{N}\-]+/u', '', $slug) ?: '';
        $slug = preg_replace('/-+/u', '-', trim($slug, '-')) ?: '';

        return mb_strtolower($slug ?: $fallback, 'UTF-8');
    }

    public function unique(string $modelClass, ?string $value, ?int $ignoreId = null, string $column = 'slug', string $fallback = 'content'): string
    {
        /** @var class-string<Model> $modelClass */
        $base = $this->make($value, $fallback);
        $slug = $base;
        $i = 2;
        while ($modelClass::query()->where($column, $slug)->when($ignoreId, fn($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }
}
