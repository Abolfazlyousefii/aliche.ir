<?php

namespace App\Observers;

use App\Models\SlugHistory;
use Illuminate\Database\Eloquent\Model;

class SlugHistoryObserver
{
    public function updating(Model $model): void
    {
        if (! $model->isDirty('slug')) {
            return;
        }

        $oldSlug = (string) $model->getOriginal('slug');
        $newSlug = (string) $model->getAttribute('slug');

        if ($oldSlug === '' || $newSlug === '' || $oldSlug === $newSlug) {
            return;
        }

        SlugHistory::query()->updateOrCreate(
            [
                'sluggable_type' => $model::class,
                'old_slug' => $oldSlug,
            ],
            [
                'sluggable_id' => $model->getKey(),
                'new_slug' => $newSlug,
            ]
        );
    }
}
