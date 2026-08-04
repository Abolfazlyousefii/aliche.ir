<?php

namespace App\Services;

use App\Models\SlugHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

class SlugRedirectService
{
    /** @param class-string<Model> $modelClass */
    public function redirectIfLegacy(string $modelClass, string $slug, string $routeName): ?RedirectResponse
    {
        if ($modelClass::query()->where('slug', $slug)->exists()) {
            return null;
        }

        $history = SlugHistory::query()
            ->where('sluggable_type', $modelClass)
            ->where('old_slug', $slug)
            ->latest()
            ->first();

        if (! $history) {
            return null;
        }

        return redirect()->route($routeName, $this->latestSlug($modelClass, $history->new_slug), 301);
    }

    /** @param class-string<Model> $modelClass */
    private function latestSlug(string $modelClass, string $slug): string
    {
        $seen = [];

        while (! isset($seen[$slug])) {
            $seen[$slug] = true;

            $history = SlugHistory::query()
                ->where('sluggable_type', $modelClass)
                ->where('old_slug', $slug)
                ->latest()
                ->first();

            if (! $history) {
                break;
            }

            $slug = $history->new_slug;
        }

        return $slug;
    }
}
